<?php

namespace OWC\PrefillGravityForms\Services;

use Exception;

class CacheService
{
    private static int $defaultExpiration = HOUR_IN_SECONDS;

    public static function getArrayFromTransient(string $transientKey): array
    {
        if ('' === trim($transientKey)) {
            return [];
        }

        $cachedResponse = get_transient($transientKey);

        if (! is_array($cachedResponse) || [] === $cachedResponse) {
            return [];
        }

        return $cachedResponse;
    }

    /**
     * @throws Exception
     */
    public static function setTransient(string $transientKey, $data, int $expiration = 0): void
    {
        if ('' === trim($transientKey)) {
            throw new Exception('Transient key is empty.', 500);
        }

        set_transient($transientKey, $data, $expiration ?: static::$defaultExpiration);
    }

    /**
     * Formats a transient key by hashing the provided base key using SHA-256.
     *
     * @param mixed $baseKey
     */
    public static function formatTransientKey($baseKey): string
    {
        return is_string($baseKey) && '' !== $baseKey ? hash('sha256', $baseKey) : '';
    }
}
