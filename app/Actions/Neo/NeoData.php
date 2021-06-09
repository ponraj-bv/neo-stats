<?php

namespace App\Actions\Neo;


use Carbon\Carbon;
use Illuminate\Support\Collection;

class NeoData
{

    public static function getAsteroidCount(Collection $collection): Collection
    {
        return $collection->transform(function ($data) {
            return count($data);
        })->sortKeys();
    }

    public static function getAverageDiameter(Collection $collection)
    {
        $result =  $collection->flatMap(function ($values) {
            return $values;
        })->avg('estimated_diameter.kilometers.estimated_diameter_max');

        return number_format($result, 2);
    }

    public function transform($collection)
    {
        $data = $collection->flatMap(function ($values) {
            return $values;
        });

        return $data->transform(function ($value) {
            return [
                'id' => $value['id'],
                'speed' => number_format($value['close_approach_data'][0]['relative_velocity']['kilometers_per_hour'], 2),
                'date' => Carbon::parse($value['close_approach_data'][0]['close_approach_date_full'])->format('d M Y')
            ];
        });
    }

    public static function getFastestAsteroid(Collection $collection)
    {
       $data = (new self)->transform($collection);
       return $data->sortByDesc('speed')->first();
    }

    public static function getClosestAsteroid(Collection $collection)
    {
        $data = (new self)->transform($collection);
        return $data->sortBy('date')->first();
    }
}
