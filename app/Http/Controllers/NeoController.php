<?php

namespace App\Http\Controllers;

use App\Actions\Neo\NeoData;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NeoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
        $endDate = Carbon::parse($request->end_date)->format('Y-m-d');

        $response = Http::get("https://api.nasa.gov/neo/rest/v1/feed", [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'api_key' => 'j0rAoznvHTqpk1uH1QPgB7Rv6YkxjeUBcgOcugr5'
        ]);

        $asteroid_data = NeoData::getAsteroidCount(collect($response->json()['near_earth_objects']));
        $asteroid_count = $asteroid_data->values();
        $date_range = $asteroid_data->keys()->transform(function ($date) {
            return Carbon::parse($date)->format('d M Y');
        });
        $average_diameter = NeoData::getAverageDiameter(collect($response->json()['near_earth_objects']));
        $fastest_asteroid = NeoData::getFastestAsteroid(collect($response->json()['near_earth_objects']));
        $closest_asteroid = NeoData::getClosestAsteroid(collect($response->json()['near_earth_objects']));

        return response()->json(compact('average_diameter', 'date_range', 'asteroid_count', 'fastest_asteroid',
            'closest_asteroid'));
    }
}
