<?php

namespace TwoThirds\EloquentTraits;

use Exception;
use Illuminate\Database\Eloquent\Builder;

trait DefaultSort
{
    /**
     * Register the global sort scope
     *
     * @return void
     */
    public static function bootDefaultSort()
    {
        static::addGlobalScope('sort', function (Builder $builder) {
            if (! property_exists(static::class, 'defaultSort')) {
                throw new Exception('DefaultSort trait requires a static property called defaultSort');
            }

            foreach ((array) static::$defaultSort as $column => $direction) {
                if (is_numeric($column)) {
                    $builder->orderBy($direction, 'asc');

                    continue;
                }

                $builder->orderBy($column, $direction);
            }
        });

        Builder::macro(
            'noSort',
            function () {
                return static::withoutGlobalScope('sort');
            }
        );
    }
}
