<?php

namespace OWC\PrefillGravityForms\Services;

use Exception;

class CacheService
{
    private static int $defaultExpiration = HOUR_IN_SECONDS;

    /**
     * @throws Exception
     */
    public static function getArrayFromTransient(string $transientKey): array
    {
        if ('' === trim($transientKey)) {
            throw new Exception('Transient key is empty.', 500);
        }

        $cachedResponse = get_transient($transientKey);

        if (! is_string($cachedResponse) || '' === $cachedResponse) {
            return [];
        }

        $decrypted = EncryptionService::decrypt($cachedResponse);

        if (! is_array($decrypted)) {
            throw new Exception('Decrypted data is not an array.', 500);
        }

        return $decrypted;
    }

    /**
     * @throws Exception
     */
    public static function setTransient(string $transientKey, $data, int $expiration = 0): void
    {
        if ('' === trim($transientKey)) {
            throw new Exception('Transient key is empty.', 500);
        }

        $encrypted = EncryptionService::encrypt($data);

        set_transient($transientKey, $encrypted, $expiration ?: static::$defaultExpiration);
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
