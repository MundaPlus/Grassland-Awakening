<?php

namespace App\Traits;

use App\Services\HashIdService;

trait HasObfuscatedId
{
    /**
     * Get the obfuscated/encoded ID for this model
     */
    public function getObfuscatedIdAttribute(): string
    {
        return HashIdService::encode($this->id);
    }

    /**
     * Get the obfuscated/encoded ID for this model (alias)
     */
    public function getHashedIdAttribute(): string
    {
        return $this->obfuscated_id;
    }

    /**
     * Find a model by its obfuscated ID
     */
    public static function findByObfuscatedId(string $obfuscatedId): ?static
    {
        $realId = HashIdService::decode($obfuscatedId);
        
        if ($realId === null) {
            return null;
        }

        return static::find($realId);
    }

    /**
     * Find a model by its obfuscated ID or fail
     */
    public static function findByObfuscatedIdOrFail(string $obfuscatedId): static
    {
        $model = static::findByObfuscatedId($obfuscatedId);
        
        if ($model === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        }

        return $model;
    }

    /**
     * Route model binding with obfuscated IDs
     */
    public function getRouteKeyName(): string
    {
        return 'obfuscated_id';
    }

    /**
     * Get the value for the route key
     */
    public function getRouteKey(): string
    {
        return $this->obfuscated_id;
    }

    /**
     * Resolve route model binding with obfuscated IDs
     */
    public function resolveRouteBinding($value, $field = null): ?static
    {
        // If it's the obfuscated_id field or no field specified
        if ($field === null || $field === 'obfuscated_id') {
            return static::findByObfuscatedId($value);
        }

        // For other fields, use default behavior
        return $this->where($field, $value)->first();
    }
}