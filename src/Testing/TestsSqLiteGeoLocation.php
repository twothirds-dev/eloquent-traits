<?php

namespace TwoThirds\EloquentTraits\Testing;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\SQLiteConnection;
use AnthonyMartin\GeoLocation\GeoLocation;

trait TestsSqLiteGeoLocation
{
    /**
     * Sets up all of the mysql geo location functions in sqlite
     *
     * @return $this
     */
    public function setupGeoLocationTesting()
    {
        if (DB::connection() instanceof SQLiteConnection) {
            DB::connection()
                ->getPdo()
                ->sqliteCreateFunction('POINT', function ($lat, $lng) {
                    return "POINT($lat $lng)";
                }, 2);

            DB::connection()
                ->getPdo()
                ->sqliteCreateFunction('astext', function ($param) {
                    return $param;
                }, 1);

            DB::connection()
                ->getPdo()
                ->sqliteCreateFunction('st_distance', function ($to, $from) {
                    list($latTo, $lonTo) = $this->splitGeoPoint($to);
                    list($latFrom, $lonFrom) = $this->splitGeoPoint($from);

                    return GeoLocation::fromDegrees($latTo, $lonTo)
                        ->distanceTo(GeoLocation::fromDegrees($latFrom, $lonFrom), 'miles');
                }, 2);
        }

        return $this;
    }

    /**
     * Gets the latitude and longitude from a point string
     *
     * @param string $point
     *
     * @return array
     */
    protected function splitGeoPoint($point)
    {
        preg_match("/POINT\((?'latitude'.+) (?'longitude'.+)\)/", $point, $matches);

        return [$matches['latitude'], $matches['longitude']];
    }
}
