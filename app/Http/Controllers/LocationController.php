<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Neighborhood;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function getDistricts(Request $request)
    {
        $request->validate([
            'province_id' => 'required|integer|exists:provinces,id',
        ]);

        $districts = District::where('province_id', $request->province_id)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return response()->json($districts);
    }

    public function getNeighborhoods(Request $request)
    {
        $request->validate([
            'district_id' => 'required|integer|exists:districts,id',
        ]);

        $neighborhoods = Neighborhood::where('district_id', $request->district_id)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return response()->json($neighborhoods);
    }
}
