<?php

namespace TwoThirds\EloquentTraits;

trait DeferredRelationships
{
    /**
     * Deferred relationship data to be executed after model create
     *
     * @var array
     */
    protected $deferredRelationships = [
        'sync'                 => [],
        'syncWithoutDetaching' => [],
        'attach'               => [],
    ];

    /**
     * Boot the DeferredRelationships trait
     *
     * @return void
     */
    public static function bootDeferredRelationships()
    {
        static::saved(function ($model) {
            $model->handleDeferredRelationships();
        });
    }

    /**
     * Sets the provided data to be attached on save
     *
     * @param string $relationship
     * @param mixed[] ...$args
     *
     * @return void
     */
    public function attach(string $relationship, ...$args)
    {
        $this->deferRelationship('attach', $relationship, $args);
    }

    /**
     * Detach the relationships from the model
     *
     * @param string $relationship
     * @param mixed[] ...$args
     *
     * @return void
     */
    public function detach(string $relationship, ...$args)
    {
        $this->$relationship()->detach(...$args);
    }

    /**
     * Sets the provided data to be synced on save
     *
     * @param string $relationship
     * @param mixed[] ...$args
     *
     * @return void
     */
    public function sync(string $relationship, ...$args)
    {
        $this->deferRelationship('sync', $relationship, $args);
    }

    /**
     * SyncWithoutDetaching the relationships from the model
     *
     * @param string $relationship
     * @param mixed[] ...$args
     *
     * @return void
     */
    public function syncWithoutDetaching(string $relationship, ...$args)
    {
        $this->deferRelationship('syncWithoutDetaching', $relationship, $args);
    }

    /**
     * Toggle the relationships on the model
     *
     * @param string $relationship
     * @param mixed[] ...$args
     *
     * @return void
     */
    public function toggle(string $relationship, ...$args)
    {
        $this->$relationship()->toggle(...$args);
    }

    /**
     * If the model exists, call the relationship method immediately,
     *     otherwise defer to on save
     *
     * @param string $relationship
     * @param array $args
     * @param string $method
     *
     * @return void
     */
    protected function deferRelationship(string $method, string $relationship, $args)
    {
        if ($this->exists) {
            $this->callRelationship($relationship, $method, $args);

            return;
        }

        $this->deferredRelationships[$method][$relationship] = $args;
    }

    /**
     * Handles deferred relationships on save
     *
     * @return void
     */
    protected function handleDeferredRelationships()
    {
        foreach ($this->deferredRelationships as $method => $relationships) {
            foreach ($relationships as $relationship => $args) {
                $this->callRelationship($relationship, $method, $args);
            }
        }
    }

    /**
     * Call the correct method on the relationship
     *
     * @param string $relationship
     * @param string $method
     * @param array $args
     */
    protected function callRelationship($relationship, $method, $args)
    {
        $this->$relationship()->$method(...$args);
    }
}
