<?php

namespace TwoThirds\EloquentTraits;

use Exception;
use Illuminate\Support\Arr;
use TwoThirds\EloquentTraits\Exceptions\InvalidEnumException;

trait Enums
{
    /**
     * Enum property getter
     *
     * @param string $field
     *
     * @return mixed|false
     */
    public static function getEnum(string $field)
    {
        $instance = new static;

        if ($instance->hasEnumProperty($field)) {
            $property = $instance->getEnumProperty($field);

            return $instance->$property;
        }

        return false;
    }

    /**
     * Get a random enum
     *
     * @param string $field
     *
     * @return mixed|false
     */
    public static function randomEnum(string $field)
    {
        if ($array = static::getEnum($field)) {
            return array_random($array);
        }

        return false;
    }

    /**
     * Get an array that will work with a select field
     *
     * @param string $field
     *
     * @return mixed|false
     */
    public static function getSelectEnum(string $field)
    {
        $values = static::getEnum($field);

        if (Arr::isAssoc($values)) {
            return $values;
        }

        return array_combine($values, $values);
    }

    /**
     * Boots the enums trait and registers the custom mutator
     *
     * @return void
     */
    public static function bootEnums()
    {
        if (! in_array(DynamicMutators::class, array_flip(class_uses_recursive(static::class)))) {
            throw new Exception('The Enums trait requires the DynamicMutators trait as a dependency.');
        }

        static::registerSetter('enumSetter');
    }

    /**
     * Check for the presence of a property that starts
     *     with enum for the provided attribute
     *
     * @param string $field
     * @param mixed $value
     *
     * @throws InvalidEnumException
     *
     * @return $this
     */
    public function enumSetter($field, $value)
    {
        if ($this->hasEnumProperty($field)) {
            if (! $this->isValidEnum($field, $value)) {
                throw new InvalidEnumException('Invalid value for ' . static::class . "::$field ($value)");
            }

            if ($this->isKeyedEnum($field, $value)) {
                $value = $this->getKeyedEnum($field, $value);
            }
        }

        return $value;
    }

    /**
     * Gets the expected enum property
     *
     * @param string $field
     *
     * @return string
     */
    protected function getEnumProperty(string $field)
    {
        return 'enum' . str_plural(studly_case($field));
    }

    /**
     * Gets the enum value by key
     *
     * @param string $field
     * @param mixed $key
     *
     * @return mixed
     */
    protected function getKeyedEnum(string $field, $key)
    {
        return static::getEnum($field)[$key];
    }

    /**
     * Is an enum property defined for the provided field
     *
     * @param string $field
     *
     * @return bool
     */
    protected function hasEnumProperty(string $field)
    {
        $property = $this->getEnumProperty($field);

        return isset($this->$property) && is_array($this->$property);
    }

    /**
     * Is the provided value a key in the enum
     *
     * @param string $field
     * @param mixed $key
     *
     * @return bool
     */
    protected function isKeyedEnum(string $field, $key)
    {
        return in_array($key, array_keys(static::getEnum($field)), true);
    }

    /**
     * Is the value a valid enum in any way
     *
     * @param string $field
     * @param mixed $value
     *
     * @return bool
     */
    protected function isValidEnum(string $field, $value)
    {
        return $this->isValueEnum($field, $value) ||
            $this->isKeyedEnum($field, $value);
    }

    /**
     * Is the provided value in the enum
     *
     * @param string $field
     * @param mixed $value
     *
     * @return bool
     */
    protected function isValueEnum(string $field, $value)
    {
        return in_array($value, static::getEnum($field));
    }
}
