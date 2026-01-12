<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OtpController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'nomor_telepon' => 'required|numeric',
        ]);

        $nomor = $request->nomor_telepon;
        
        // Ubah 08 ke 628
        if (substr($nomor, 0, 1) == '0') {
            $nomor = '62' . substr($nomor, 1);
        }

        $otp = rand(1000, 9999); 
        
        // Ganti TOKEN di .env nanti, untuk tes pake string dulu gpp
        $token = env('FONNTE_TOKEN', 'TOKEN_ASLI_KAMU_DISINI'); 

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $nomor,
                'message' => "Kode Verifikasi E-VOLT: *$otp*",
            ]);

            if ($response->successful()) {
                return response()->json([
                    'status' => 'success', 
                    'message' => 'OTP Terkirim',
                    'dev_otp' => $otp
                ]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Gagal Fonnte'], 500);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}