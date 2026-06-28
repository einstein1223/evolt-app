<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Station;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Inertia\Inertia;

class BookingController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // HELPER: Buat header Paymentku dengan HMAC-SHA256 signature
    // Signature = hmac_sha256(timestamp + "." + body_json, secret_key)
    // ──────────────────────────────────────────────────────────────────────────
    private function paymentkuHeaders(string $bodyJson = ''): array
    {
        $timestamp = (string) time(); // Unix timestamp
        $secret    = config('services.paymentku.secret_key');
        $signature = hash_hmac('sha256', $timestamp . '.' . $bodyJson, $secret);

        return [
            'Authorization'        => 'Bearer ' . $secret,
            'Content-Type'         => 'application/json',
            'Accept'               => 'application/json',
            'X-PaymenKu-Timestamp' => $timestamp,
            'X-PaymenKu-Signature' => $signature,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /booking/slots?station_id=X
    // ──────────────────────────────────────────────────────────────────────────
    public function slots(Request $request)
    {
        $request->validate(['station_id' => 'required']);

        $today = Carbon::today('Asia/Jakarta')->toDateString();

        $bookedSlots = Booking::where('station_id', $request->station_id)
            ->whereNotIn('status', ['Batal', 'Dibatalkan', 'Dibatalkan Host', 'Kadaluarsa', 'Gagal Bayar'])
            ->whereDate('booking_date', $today)
            ->pluck('booking_slot')
            ->filter()
            ->values()
            ->toArray();

        return response()->json(['booked_slots' => $bookedSlots]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /booking/payment-channels
    // ──────────────────────────────────────────────────────────────────────────
    public function paymentChannels()
    {
        try {
            $headers  = $this->paymentkuHeaders();
            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->get('https://paymenku.com/api/v1/payment-channels');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            Log::warning('Paymentku: Gagal fetch payment channels', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return response()->json(['status' => 'error', 'data' => []], 500);

        } catch (\Exception $e) {
            Log::error('Paymentku: Exception fetch payment channels', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'data' => []], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /booking/check-status/{bookingCode}
    // Cek status pembayaran dari Paymentku
    // ──────────────────────────────────────────────────────────────────────────
    public function checkStatus(string $bookingCode)
    {
        // Pastikan booking milik user yang login
        $booking = Booking::where('booking_code', $bookingCode)
            ->where('user_id', Auth::id())
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking tidak ditemukan.'], 404);
        }

        try {
            $headers  = $this->paymentkuHeaders();
            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->get("https://paymenku.com/api/v1/check-status/{$bookingCode}");

            if ($response->successful()) {
                $data   = $response->json();
                $status = strtolower($data['data']['status'] ?? $data['status'] ?? '');

                $statusMap = [
                    'paid'      => 'Lunas',
                    'pending'   => 'Menunggu Pembayaran',
                    'expired'   => 'Kadaluarsa',
                    'cancelled' => 'Dibatalkan',
                    'failed'    => 'Gagal Bayar',
                    'refunded'  => 'Refund',
                ];

                if (isset($statusMap[$status])) {
                    $booking->update(['status' => $statusMap[$status]]);
                }

                return response()->json([
                    'booking_code'   => $bookingCode,
                    'status'         => $booking->fresh()->status,
                    'paymentku_data' => $data,
                ]);
            }

            return response()->json(['message' => 'Gagal cek status dari Paymentku.'], 500);

        } catch (\Exception $e) {
            Log::error('Paymentku: Exception check status', [
                'booking_code' => $bookingCode,
                'error'        => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Gagal terhubung ke Paymentku.'], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /booking
    // Simpan booking baru + buat transaksi ke Paymentku (QRIS default)
    // ──────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $duration = (int) filter_var($request->duration, FILTER_SANITIZE_NUMBER_INT);
        $request->merge(['duration' => max($duration, 1)]);

        $request->validate([
            'station_name'    => 'required|string',
            'booking_time'    => 'required|date',
            'booking_slot'    => 'required|string|regex:/^\d{2}:\d{2}$/',
            'duration'        => 'required|integer|min:1',
            'total_price'     => 'required|numeric|min:0',
            'port_type'       => 'nullable|string|max:50',
            'payment_channel' => 'nullable|string|max:50',
        ]);

        $bookingCode = null;
        $stationName = null;

        DB::transaction(function () use ($request, $duration, &$bookingCode, &$stationName) {
            $today       = Carbon::today('Asia/Jakarta')->toDateString();
            $bookingSlot = $request->booking_slot;

            $station = Station::where('name', $request->station_name)->first()
                ?? Station::where('name', 'LIKE', '%' . trim($request->station_name) . '%')->first();

            $stationId   = $station?->id ?? null;
            $stationName = $station?->name ?? $request->station_name;
            $location    = $station?->location ?? $station?->address ?? '-';

            // Cek konflik slot — gunakan lock agar race condition tidak terjadi
            $conflictQuery = Booking::whereNotIn('status', ['Batal', 'Dibatalkan', 'Dibatalkan Host', 'Kadaluarsa', 'Gagal Bayar'])
                ->where('booking_slot', $bookingSlot)
                ->whereDate('booking_date', $today)
                ->lockForUpdate();

            if ($stationId) {
                $conflictQuery->where('station_id', $stationId);
            } else {
                $conflictQuery->where('station_name', $stationName);
            }

            if ($conflictQuery->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'booking_slot' => 'Maaf, slot jam ' . $bookingSlot . ' sudah dipesan. Silakan pilih jam lain.',
                ]);
            }

            $bookingDateTime = Carbon::parse($today . ' ' . $bookingSlot, 'Asia/Jakarta');
            $endDateTime     = $bookingDateTime->copy()->addMinutes($duration);
            $bookingCode     = 'EV-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Ymd');

            Booking::create([
                'user_id'      => Auth::id(),
                'plate_number' => Auth::user()->nomor_plat ?? '-',
                'station_id'   => $stationId,
                'station_name' => $stationName,
                'booking_code' => $bookingCode,
                'location'     => $location,
                'booking_date' => $bookingDateTime,
                'end_time'     => $endDateTime,
                'booking_slot' => $bookingSlot,
                'duration'     => $duration,
                'total_price'  => $request->total_price,
                'port_type'    => $request->port_type ?? 'Regular',
                'status'       => 'Menunggu Pembayaran',
            ]);
        });

      // ── Hit Paymentku ──────────────────────────────────────────────────────
        try {
            // Minimum amount Paymentku = 1000
            $amount      = max((int) $request->total_price, 1000);
            $channelCode = $request->payment_channel ?? 'qris';

            $payload = [
                'channel_code'   => $channelCode,
                'amount'         => $amount,
                'reference_id'   => $bookingCode,
                'customer_name'  => Auth::user()->name  ?? 'Customer',
                'customer_email' => Auth::user()->email ?? 'customer@example.com',
                'return_url'     => url('/profile'),
                'order_items'    => [
                    [
                        'name'     => 'EV Charging - ' . ($stationName ?? $request->station_name),
                        'quantity' => 1,
                        'price'    => $amount,
                    ],
                ],
            ];

            $phone = Auth::user()->nomor_telepon ?? Auth::user()->phone ?? Auth::user()->no_hp ?? null;
            if ($phone) $payload['customer_phone'] = $phone;

            // PERBAIKAN 1: Ambil langsung dari env() agar tidak null
            $secretKey = env('PAYMENKU_SECRET_KEY'); 

            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $secretKey, // <-- Gunakan variabel $secretKey
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])
                ->post('https://paymenku.com/api/v1/transaction/create', $payload);

            if ($response->successful()) {
                $paymentData = $response->json();

                $paymentUrl = $paymentData['data']['checkout_url']
                    ?? $paymentData['data']['payment_url']
                    ?? $paymentData['data']['pay_url'] 
                    ?? $paymentData['checkout_url']
                    ?? null;

                $qrisUrl = $paymentData['data']['qris_url']
                    ?? $paymentData['data']['qr_url']
                    ?? $paymentData['data']['qr_image']
                    ?? null;

                return response()->json([
                    'message'      => 'Booking berhasil dibuat!',
                    'booking_code' => $bookingCode,
                    'payment_url'  => $paymentUrl,
                    'qris_url'     => $qrisUrl ?? $paymentUrl,
                    'channel_code' => $channelCode,
                    'amount'       => $amount,
                ], 201);
            }

            Log::error('Paymentku Error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            // PERBAIKAN 2: Tampilkan pesan error asli dari API Paymenku ke layar Vue!
            Booking::where('booking_code', $bookingCode)->update(['status' => 'Gagal Bayar']);
            return response()->json([
                'message' => 'Error API Paymenku: ' . $response->body(), // <-- Ini akan memunculkan alasan aslinya
                'booking_code' => $bookingCode,
            ], 500);

        } catch (\Exception $e) {
            Log::error('Paymentku Exception', ['error' => $e->getMessage()]);
            Booking::where('booking_code', $bookingCode)->update(['status' => 'Gagal Bayar']);
            
            return response()->json([
                'message' => 'Error Sistem: ' . $e->getMessage(),
                'booking_code' => $bookingCode,
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /paymentku/webhook
    // Verifikasi: X-PaymenKu-Signature = hmac_sha256(timestamp + "." + body, secret)
    // ──────────────────────────────────────────────────────────────────────────
    public function handleWebhook(Request $request)
    {
        $jsonContent     = $request->getContent();
        $timestamp       = $request->header('X-PaymenKu-Timestamp') ?? '';
        $signatureHeader = $request->header('X-PaymenKu-Signature') ?? '';
        $mySignature     = hash_hmac('sha256', $timestamp . '.' . $jsonContent, config('services.paymentku.webhook_secret'));

        if (!hash_equals($mySignature, $signatureHeader)) {
            Log::warning('Webhook Paymentku: Signature tidak valid!', [
                'received'  => $signatureHeader,
                'expected'  => $mySignature,
                'timestamp' => $timestamp,
            ]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $data    = json_decode($jsonContent, true);
        $orderId = $data['reference_id'] ?? null;
        $status  = strtolower($data['status'] ?? '');

        if (!$orderId) {
            Log::warning('Webhook Paymentku: reference_id tidak ditemukan', ['payload' => $data]);
            return response()->json(['message' => 'reference_id tidak ditemukan'], 400);
        }

        $statusMap = [
            'paid'      => 'Lunas',
            'pending'   => 'Menunggu Pembayaran',
            'expired'   => 'Kadaluarsa',
            'cancelled' => 'Dibatalkan',
            'failed'    => 'Gagal Bayar',
            'refunded'  => 'Refund',
        ];

        $newStatus = $statusMap[$status] ?? null;

        if ($newStatus) {
            Booking::where('booking_code', $orderId)->update(['status' => $newStatus]);
            Log::info("Webhook Paymentku: Booking {$orderId} → {$newStatus}", [
                'trx_id'          => $data['trx_id']          ?? null,
                'amount'          => $data['amount']          ?? null,
                'amount_received' => $data['amount_received'] ?? null,
                'payment_channel' => $data['payment_channel'] ?? null,
                'paid_at'         => $data['paid_at']         ?? null,
            ]);
        } else {
            Log::warning("Webhook Paymentku: Status tidak dikenal '{$status}' untuk order {$orderId}");
        }

        return response()->json(['message' => 'Webhook berhasil diproses']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DELETE /host/booking/{id}
    // Host membatalkan booking dari stasiunnya sendiri
    // ──────────────────────────────────────────────────────────────────────────
    public function destroyByHost(Request $request, $id)
    {
        $station = Station::where('user_id', Auth::id())->first();

        if (!$station) {
            return response()->json(['message' => 'Station tidak ditemukan.'], 403);
        }

        $booking = Booking::where('id', $id)
            ->where('station_id', $station->id)
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking tidak ditemukan atau bukan milik station Anda.'], 404);
        }

        $booking->update(['status' => 'Dibatalkan Host']);

        return response()->json(['message' => 'Booking berhasil dibatalkan.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /history
    // ──────────────────────────────────────────────────────────────────────────
    public function history()
    {
        $bookings = Booking::where('user_id', Auth::id())
            ->orderBy('booking_date', 'desc')
            ->get();

        return Inertia::render('user/History', ['bookings' => $bookings]);
    }
}