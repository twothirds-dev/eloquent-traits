<?php

namespace TwoThirds\EloquentTraits\Validation;

use Illuminate\Support\Str;
use TwoThirds\EloquentTraits\Exceptions\EnumValidationException;

class Enum
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     *
     * @return bool
     */
    public function validate($attribute, $value, array $parameters)
    {
        @list($model, $field) = $parameters;

        if (! class_exists($model) && ! class_exists($model = $this->resolveClass($model))) {
            throw new EnumValidationException('Class not found: "' . $model . '"');
        }

        $attribute = $field ?? $attribute;

        if (! $enums = $model::getEnum($attribute)) {
            throw new EnumValidationException('No Enum defined for ' . $attribute . ' in ' . $model);
        }

        return in_array($value, $enums);
    }

    /**
     * Resolve the class name for the provided model string
     *
     * @param string $model
     *
     * @return string
     */
    protected function resolveClass(string $model)
    {
        return 'App\\' . Str::studly($model);
    }
}
