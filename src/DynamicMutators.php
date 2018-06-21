<?php

namespace TwoThirds\EloquentTraits;

trait DynamicMutators
{
    /**
     * Dynamic setters stack
     *
     * @var array
     */
    protected static $dynamicSetters = [];

    /**
     * Dynamic getters stack
     *
     * @var array
     */
    protected static $dynamicGetters = [];

    /**
     * Get all the registered dynamic setters
     *
     * @return array
     */
    public static function getDynamicSetters()
    {
        return static::$dynamicSetters;
    }

    /**
     * Get all the registered dynamic getters
     *
     * @return array
     */
    public static function getDynamicGetters()
    {
        return static::$dynamicGetters;
    }

    /**
     * Overloads the Laravel getAttribute method to run dynamic getters
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        foreach (static::$dynamicGetters as $getter) {
            if (! is_null($result = $this->$getter($key))) {
                return $result;
            }
        }

        return parent::getAttribute($key);
    }

    /**
     * Overloads the Laravel setAttribute method to run dynamic setters
     *
     * @param string $field
     * @param mixed $value
     *
     * @return mixed
     */
    public function setAttribute($field, $value)
    {
        foreach (static::$dynamicSetters as $setter) {
            $value = $this->$setter($field, $value);
        }

        return parent::setAttribute($field, $value);
    }

    /**
     * Registers a dynamic getter on the stack
     *
     * @param string $getter
     *
     * @return void
     */
    protected static function registerGetter(string $getter)
    {
        if (! in_array($getter, static::$dynamicGetters)) {
            array_push(static::$dynamicGetters, $getter);
        }
    }

    /**
     * Registers a dynamic setter on the stack
     *
     * @param string $setter
     *
     * @return void
     */
    protected static function registerSetter(string $setter)
    {
        if (! in_array($setter, static::$dynamicSetters)) {
            array_push(static::$dynamicSetters, $setter);
        }
    }
}
