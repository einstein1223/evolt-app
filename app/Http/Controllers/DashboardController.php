<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Brand;
use App\Models\CarType;
use App\Models\Location;
use App\Models\Station;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('user/UserDashboard', [
            // Mengirim data dari Database ke Vue
            'dbBrands'    => Brand::all(),
            'dbTypes'     => CarType::all(),
            'dbLocations' => Location::all(),
            'dbStations'  => Station::all(),
        ]);
    }
}