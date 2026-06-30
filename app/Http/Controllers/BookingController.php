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
    // HELPER: Base URL Paymenku
    // ──────────────────────────────────────────────────────────────────────────
    private function paymentkuBaseUrl(): string
    {
        return config('services.paymentku.base_url', 'https://paymenku.com/api/v1');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPER: Header Paymenku
    // ✅ Nama header HARUS persis: X-Paymenku-Signature & X-Paymenku-Timestamp
    // ✅ Signature = hmac_sha256(timestamp + "." + body, secret)
    // ──────────────────────────────────────────────────────────────────────────
    private function paymentkuHeaders(string $bodyJson = ''): array
    {
        $timestamp = (string) time();
        $secret    = config('services.paymentku.secret_key');
        $signature = hash_hmac('sha256', $timestamp . '.' . $bodyJson, $secret);

        return [
            'Authorization'         => 'Bearer ' . $secret,
            'Content-Type'          => 'application/json',
            'Accept'                => 'application/json',
            'X-Paymenku-Timestamp'  => $timestamp,  // ✅ huruf kecil 'enku'
            'X-Paymenku-Signature'  => $signature,  // ✅ huruf kecil 'enku'
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPER: Map status Paymenku → status lokal DB
    // ✅ Sesuai dokumentasi: pending, paid, expired, cancelled, failed, refunded
    // ──────────────────────────────────────────────────────────────────────────
    private function mapPaymentkuStatus(string $rawStatus): ?string
    {
        $map = [
            'paid'      => 'Lunas',
            'pending'   => 'Menunggu Pembayaran',
            'expired'   => 'Kadaluarsa',
            'cancelled' => 'Dibatalkan',
            'cancel'    => 'Dibatalkan',
            'failed'    => 'Gagal Bayar',
            'failure'   => 'Gagal Bayar',
            'refunded'  => 'Refund',
            'refund'    => 'Refund',
        ];

        return $map[strtolower(trim($rawStatus))] ?? null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /booking/slots?station_id=X
    // ──────────────────────────────────────────────────────────────────────────
    public function slots(Request $request)
    {
        $request->validate(['station_id' => 'required']);

        $today = Carbon::today('Asia/Jakarta')->toDateString();

        $cancelledStatuses = ['Batal', 'Dibatalkan', 'Dibatalkan Host', 'Kadaluarsa', 'Gagal Bayar'];

        $bookedSlots = Booking::where('station_id', $request->station_id)
            ->whereNotIn('status', $cancelledStatuses)
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
                ->get($this->paymentkuBaseUrl() . '/payment-channels');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            Log::warning('Paymenku: Gagal fetch payment channels', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return response()->json(['status' => 'error', 'data' => []], 500);

        } catch (\Exception $e) {
            Log::error('Paymenku: Exception fetch payment channels', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'data' => []], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /booking/check-status/{bookingCode}
    // Polling status dari Vue setiap ~5 detik
    // ──────────────────────────────────────────────────────────────────────────
    public function checkStatus(string $bookingCode)
    {
        $booking = Booking::where('booking_code', $bookingCode)
            ->where('user_id', Auth::id())
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking tidak ditemukan.'], 404);
        }

        // Status sudah final — tidak perlu hit API lagi
        $finalStatuses = ['Lunas', 'Kadaluarsa', 'Dibatalkan', 'Gagal Bayar', 'Refund', 'Dibatalkan Host'];
        if (in_array($booking->status, $finalStatuses)) {
            return response()->json([
                'booking_code' => $bookingCode,
                'status'       => $booking->status,
                'is_final'     => true,
            ]);
        }

        try {
            $headers  = $this->paymentkuHeaders();
            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->get($this->paymentkuBaseUrl() . '/check-status/' . $bookingCode);

            if ($response->successful()) {
                $data      = $response->json();
                $rawStatus = $data['data']['status']
                    ?? $data['status']
                    ?? $data['data']['transaction_status']
                    ?? '';

                $newStatus = $this->mapPaymentkuStatus($rawStatus);

                if ($newStatus && $newStatus !== $booking->status) {
                    $booking->update(['status' => $newStatus]);
                    Log::info("checkStatus: Booking {$bookingCode} diupdate → {$newStatus}");
                }

                $fresh = $booking->fresh();
                return response()->json([
                    'booking_code'   => $bookingCode,
                    'status'         => $fresh->status,
                    'is_final'       => in_array($fresh->status, $finalStatuses),
                    'paymenku_data'  => $data,
                ]);
            }

            Log::warning('Paymenku: checkStatus gagal', [
                'booking_code' => $bookingCode,
                'http_status'  => $response->status(),
                'body'         => $response->body(),
            ]);

            return response()->json([
                'booking_code' => $bookingCode,
                'status'       => $booking->status,
                'is_final'     => false,
            ]);

        } catch (\Exception $e) {
            Log::error('Paymenku: Exception checkStatus', [
                'booking_code' => $bookingCode,
                'error'        => $e->getMessage(),
            ]);

            return response()->json([
                'booking_code' => $bookingCode,
                'status'       => $booking->status,
                'is_final'     => false,
            ]);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /booking
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

            $cancelledStatuses = ['Batal', 'Dibatalkan', 'Dibatalkan Host', 'Kadaluarsa', 'Gagal Bayar'];
            $conflictQuery     = Booking::whereNotIn('status', $cancelledStatuses)
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

        // ── Hit Paymenku API ───────────────────────────────────────────────────
        try {
            $amount      = max((int) $request->total_price, 1000);
            $channelCode = $request->payment_channel ?? 'qris';

            $payload = [
                'channel_code'   => $channelCode,
                'amount'         => $amount,
                'reference_id'   => $bookingCode,
                'customer_name'  => Auth::user()->name ?? Auth::user()->username ?? 'Customer',
                'customer_email' => Auth::user()->email ?? 'customer@example.com',
                'return_url'     => url('/profile?tab=orders'),
                // ✅ WAJIB: Paymenku kirim webhook ke sini setiap status berubah
                'callback_url'   => url('/paymenku/webhook'),
                'order_items'    => [
                    [
                        'name'     => 'EV Charging - ' . ($stationName ?? $request->station_name),
                        'quantity' => 1,
                        'price'    => $amount,
                    ],
                ],
            ];

            $phone = Auth::user()->nomor_telepon
                ?? Auth::user()->phone
                ?? Auth::user()->no_hp
                ?? null;

            if ($phone) {
                $payload['customer_phone'] = $phone;
            }

            $bodyJson = json_encode($payload);
            $headers  = $this->paymentkuHeaders($bodyJson);

            $response = Http::timeout(15)
                ->withHeaders($headers)
                ->post($this->paymentkuBaseUrl() . '/transaction/create', $payload);

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

                Log::info("Paymenku: Booking {$bookingCode} berhasil dibuat", [
                    'payment_url' => $paymentUrl,
                    'channel'     => $channelCode,
                ]);

                return response()->json([
                    'message'      => 'Booking berhasil dibuat!',
                    'booking_code' => $bookingCode,
                    'payment_url'  => $paymentUrl,
                    'qris_url'     => $qrisUrl ?? $paymentUrl,
                    'channel_code' => $channelCode,
                    'amount'       => $amount,
                ], 201);
            }

            Log::error('Paymenku: Gagal buat transaksi', [
                'booking_code' => $bookingCode,
                'http_status'  => $response->status(),
                'body'         => $response->body(),
            ]);

            Booking::where('booking_code', $bookingCode)->update(['status' => 'Gagal Bayar']);

            return response()->json([
                'message'      => 'Gagal membuat transaksi: ' . $response->body(),
                'booking_code' => $bookingCode,
            ], 500);

        } catch (\Exception $e) {
            Log::error('Paymenku: Exception store', [
                'booking_code' => $bookingCode,
                'error'        => $e->getMessage(),
            ]);

            Booking::where('booking_code', $bookingCode)->update(['status' => 'Gagal Bayar']);

            return response()->json([
                'message'      => 'Error sistem: ' . $e->getMessage(),
                'booking_code' => $bookingCode,
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /paymenku/webhook  (tanpa CSRF)
    // ✅ Header dari dokumentasi: X-Paymenku-Signature, X-Paymenku-Timestamp
    // ✅ Payload dari dokumentasi: reference_id, status, event, trx_id, dll
    // ──────────────────────────────────────────────────────────────────────────
    public function handleWebhook(Request $request)
    {
        $jsonContent     = $request->getContent();
        $timestamp       = $request->header('X-Paymenku-Timestamp') ?? '';
        $signatureHeader = $request->header('X-Paymenku-Signature') ?? '';

        $secret      = config('services.paymentku.webhook_secret')
            ?? config('services.paymentku.secret_key');
        $mySignature = hash_hmac('sha256', $timestamp . '.' . $jsonContent, $secret);

        if (!hash_equals($mySignature, $signatureHeader)) {
            Log::warning('Webhook Paymenku: Signature tidak valid!', [
                'received'     => $signatureHeader,
                'expected'     => $mySignature,
                'timestamp'    => $timestamp,
                'body_preview' => substr($jsonContent, 0, 300),
            ]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $data    = json_decode($jsonContent, true);

        // ✅ Sesuai dokumentasi Paymenku: field "reference_id"
        $orderId = $data['reference_id'] ?? null;
        $status  = $data['status']       ?? '';
        $event   = $data['event']        ?? '';

        Log::info('Webhook Paymenku diterima', [
            'event'        => $event,
            'reference_id' => $orderId,
            'status'       => $status,
            'trx_id'       => $data['trx_id'] ?? null,
        ]);

        if (!$orderId) {
            Log::warning('Webhook Paymenku: reference_id kosong', ['payload' => $data]);
            return response()->json(['message' => 'reference_id tidak ditemukan'], 400);
        }

        $newStatus = $this->mapPaymentkuStatus($status);

        if ($newStatus) {
            $updated = Booking::where('booking_code', $orderId)->update(['status' => $newStatus]);

            Log::info("Webhook Paymenku: {$orderId} → {$newStatus}", [
                'rows_updated'    => $updated,
                'trx_id'          => $data['trx_id']          ?? null,
                'amount'          => $data['amount']          ?? null,
                'amount_received' => $data['amount_received'] ?? null,
                'payment_channel' => $data['payment_channel'] ?? null,
                'paid_at'         => $data['paid_at']         ?? null,
            ]);

            if ($updated === 0) {
                Log::warning("Webhook Paymenku: Booking '{$orderId}' tidak ditemukan di DB!");
            }
        } else {
            Log::warning("Webhook Paymenku: Status tidak dikenal '{$status}' untuk {$orderId}");
        }

        // Selalu return 200 agar Paymenku tidak retry
        return response()->json(['message' => 'OK']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DELETE /host/booking/{id}
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