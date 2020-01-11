<?php

namespace TwoThirds\EloquentTraits;

use ReflectionClass;
use Illuminate\Support\ServiceProvider;
use TwoThirds\EloquentTraits\Validation\Enum;

class EloquentTraitsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $extend = static::canUseDependentValidation() ? 'extendDependent' : 'extend';

        $this->app['validator']->{$extend}(
            'enum',
            Enum::class . '@validate',
            'The :attribute is invalid.'
        );
    }

    /**
     * Determine whether we can register a dependent validator.
     *
     * @return bool
     */
    public static function canUseDependentValidation()
    {
        $validator = new ReflectionClass('\Illuminate\Validation\Factory');

        return $validator->hasMethod('extendDependent');
    }
}
