<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User; // Pastikan model User di-import untuk pengecekan spesifik

class LoginRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk request.
     */
    public function rules(): array
    {
        return [
            // 'email' adalah nama input v-model dari form Vue
            'email' => ['required', 'string'], 
            'password' => ['required', 'string'],
            // [TAMBAHAN] Validasi input role dari frontend
            'role' => ['required', 'string', 'in:user,host'], 
        ];
    }

    /**
     * Coba autentikasi kredensial request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Auth::attempt sekarang akan mengecek email/username + password + role sekaligus
        if (! Auth::attempt($this->getCredentials(), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // [TAMBAHAN] Logika untuk memberikan pesan error yang spesifik jika salah role
            $loginInput = $this->input('email');
            $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            $requestedRole = $this->input('role');
            
            $userExists = User::where($fieldType, $loginInput)->first();
            
            $errorMessage = trans('auth.failed'); // Default error: "Kredensial tidak cocok."
            
            if ($userExists && $userExists->role !== $requestedRole) {
                $errorMessage = 'Akun ini tidak memiliki akses sebagai ' . ucfirst($requestedRole) . '.';
            }

            throw ValidationException::withMessages([
                'email' => $errorMessage,
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Menyiapkan kredensial untuk login (bisa email atau username, PLUS ROLE).
     */
    public function getCredentials(): array
    {
        // Ambil input dari form (v-model="form.email")
        $loginInput = $this->input('email');

        // Cek apakah inputan adalah email atau username
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Kembalikan array yang benar untuk dicoba oleh Auth::attempt()
        return [
            $fieldType => $loginInput,
            'password' => $this->input('password'),
            // [TAMBAHAN] Masukkan role ke dalam array kredensial yang akan dicek
            'role' => $this->input('role'), 
        ];
    }

    /**
     * Pastikan request login tidak di-rate limit.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Dapatkan throttle key untuk request.
     */
    public function throttleKey(): string
    {
        // Kunci throttle berdasarkan input 'email' (yang bisa jadi username) dan IP
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}