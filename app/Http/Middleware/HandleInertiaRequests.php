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
                'name' => $request->user()->name, // Nama bawaan
                'email' => $request->user()->email,
                
                // --- TAMBAHKAN DATA CUSTOM INI ---
                'username' => $request->user()->username,
                'nomor_plat' => $request->user()->nomor_plat,
                'nomor_telepon' => $request->user()->nomor_telepon,
                'gender' => $request->user()->gender,
                'birthDate' => $request->user()->birthDate,
                'city' => $request->user()->city,
                'idType' => $request->user()->idType,
                'idNumber' => $request->user()->idNumber,
                'nomor_plat' => $request->user()->nomor_plat,
                'car_brand' => $request->user()->car_brand,
                'car_type' => $request->user()->car_type,
                'car_series' => $request->user()->car_series,
                // ---------------------------------
            ] : null,
        ],
        'flash' => [
            'message' => fn () => $request->session()->get('message'),
        ],
    ]);

    }
}
