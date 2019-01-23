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
        return static::$dynamicSetters[get_called_class()] ?? [];
    }

    /**
     * Get all the registered dynamic getters
     *
     * @return array
     */
    public static function getDynamicGetters()
    {
        return static::$dynamicGetters[get_called_class()] ?? [];
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
        foreach (static::getDynamicGetters() as $getter) {
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
        foreach (static::getDynamicSetters() as $setter) {
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
        $class = get_called_class();

        if (! isset(static::$dynamicGetters[$class])) {
            static::$dynamicGetters[$class] = [];
        }

        if (! in_array($getter, static::$dynamicGetters[$class])) {
            array_push(static::$dynamicGetters[$class], $getter);
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
        $class = get_called_class();

        if (! isset(static::$dynamicSetters[$class])) {
            static::$dynamicSetters[$class] = [];
        }

        if (! in_array($setter, static::$dynamicSetters[$class])) {
            array_push(static::$dynamicSetters[$class], $setter);
        }
    }
}
