<?php

namespace App\Services;

use Vinkla\Hashids\Facades\Hashids;

class HashIdService
{
    /**
     * Encode an ID to a hash
     */
    public static function encode(int $id): string
    {
        return Hashids::encode($id);
    }

    /**
     * Decode a hash to an ID
     */
    public static function decode(string $hash): ?int
    {
        $decoded = Hashids::decode($hash);
        return empty($decoded) ? null : $decoded[0];
    }

    /**
     * Encode multiple IDs to a hash
     */
    public static function encodeMultiple(array $ids): string
    {
        return Hashids::encode($ids);
    }

    /**
     * Decode a hash to multiple IDs
     */
    public static function decodeMultiple(string $hash): array
    {
        return Hashids::decode($hash);
    }

    /**
     * Generate an obfuscated URL with encoded ID
     */
    public static function encodeUrl(string $routeName, int $id, array $parameters = []): string
    {
        $encodedId = self::encode($id);
        $parameters = array_merge(['id' => $encodedId], $parameters);
        return route($routeName, $parameters);
    }

    /**
     * Get the real ID from a request parameter
     */
    public static function decodeFromRequest($request, string $parameter = 'id'): ?int
    {
        $hash = $request->route($parameter) ?? $request->input($parameter);
        
        if (!$hash) {
            return null;
        }

        // If it's already a numeric ID (for backward compatibility)
        if (is_numeric($hash)) {
            return (int) $hash;
        }

        return self::decode($hash);
    }

    /**
     * Validate that a hash is valid
     */
    public static function isValidHash(string $hash): bool
    {
        return !empty(Hashids::decode($hash));
    }
}