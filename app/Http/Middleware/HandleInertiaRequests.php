<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
 public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'username' => $request->user()->username, // <-- PENTING: Kirim username
                'email' => $request->user()->email,
                'nomor_plat' => $request->user()->nomor_plat, // <-- PENTING: Kirim plat
                'nomor_telepon' => $request->user()->nomor_telepon, // <-- PENTING: Kirim no hp
                'role' => $request->user()->role, // <-- Kirim role juga
                // Kirim data lain jika perlu:
                'gender' => $request->user()->gender,
                'city' => $request->user()->city,
                'birthDate' => $request->user()->birthDate,
                'idType' => $request->user()->idType,
                'idNumber' => $request->user()->idNumber,
            ] : null,
        ],
        'flash' => [
            'message' => fn () => $request->session()->get('message'),
        ],
    ]);
}
}
