<?php

namespace TwoThirds\EloquentTraits;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

trait GeoLocation
{
    /**
     * Allows disabling the automatic location update
     *
     * @var bool
     */
    public static $autoLocationUpdate = true;

    /**
     * If the value of the location address changed, update the location field
     *
     * @return void
     */
    public static function bootGeoLocation()
    {
        static::saving(function ($model) {
            $original = new static($model->getOriginal());

            if ($original->locationAddress() !== $model->locationAddress()) {
                $model->updateLocation();
            }
        });
    }

    /**
     * Adds the location field to the query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithGeo(Builder $query)
    {
        return $query->selectRaw($this->getTable() . '.*')
            ->selectRaw('astext(location) as location');
    }

    /**
     * Gets the location attribute as an array
     *
     * @param string|null $location
     *
     * @return array|null
     */
    public function getLocationAttribute($location)
    {
        if (preg_match('/^POINT\((\S+)[ ,](\S+)\)$/', $location, $matches)) {
            return array_slice($matches, 1);
        }

        return null;
    }

    /**
     * Set the location field appropriately
     *
     * @param array|string $location
     */
    public function setLocationAttribute($location)
    {
        $this->attributes['location'] = DB::raw(
            is_array($location) ?
                'POINT(' . implode(',', $location) . ')' :
                $location
        );
    }

    /**
     * Provides the string that defines the full address for the model
     *
     * @return string
     */
    abstract public function locationAddress() : string;

    /**
     * Looks up the lat and long from the google map geocode api
     *
     * @return $this
     */
    public function updateLocation()
    {
        if (! static::$autoLocationUpdate) {
            return $this;
        }

        $url = 'https://maps.google.com/maps/api/geocode/json?address=' .
            urlencode($this->locationAddress());

        if ($key = config('services.google-maps.api-key')) {
            $url .= "&key=$key";
        }

        $response = json_decode(file_get_contents($url));

        if ($response->status === 'OK') {
            $this->location = [
                $response->results[0]->geometry->location->lat,
                $response->results[0]->geometry->location->lng,
            ];
        }

        return $this;
    }

    /**
     * Sort the results by distance from the provided location
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array|string $location
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeDistanceFrom(Builder $query, $location)
    {
        if (is_array($location)) {
            $location = implode(',', $location);
        }

        return $query
            ->withGeo()
            ->selectRaw(sprintf(
                'st_distance(location,POINT(%s)) as distance',
                $location
            ))
            ->orderByRaw(
                '(0 - distance) DESC'
            );
    }
}
