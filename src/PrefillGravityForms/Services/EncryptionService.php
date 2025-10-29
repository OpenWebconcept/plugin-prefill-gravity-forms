<?php

namespace OWC\PrefillGravityForms\Services;

use Exception;

class EncryptionService
{
    private static string $cipher = 'aes-256-gcm';
    private const TAG_LENGTH = 16;

    /**
     * Encrypts the given data array into a secure string.
     */
    public static function encrypt(array $data): string
    {
        $iv = random_bytes(openssl_cipher_iv_length(self::$cipher));
        $tag = '';
        $plaintext = serialize($data);

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::$cipher,
            static::getEncryptionKey(),
            0,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        return base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Decrypts the given string back into a data array.
     *
     * @throws Exception
     * @return mixed
     */
    public static function decrypt(string $encrypted)
    {
        $raw = base64_decode($encrypted);

        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $iv = substr($raw, 0, $ivLength);
        $tag = substr($raw, $ivLength, self::TAG_LENGTH);
        $ciphertext = substr($raw, $ivLength + self::TAG_LENGTH);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::$cipher,
            static::getEncryptionKey(),
            0,
            $iv,
            $tag
        );

        if (! is_string($plaintext)) {
            throw new Exception('Decryption failed. Invalid data or key.', 500);
        }

        return unserialize($plaintext);
    }

    /**
     * @throws Exception
     */
    private static function getEncryptionKey(): string
    {
        if (! defined('PG_CACHE_ENCRYPTION_KEY') || strlen(PG_CACHE_ENCRYPTION_KEY) < 32) {
            throw new Exception('Encryption key is not defined or too short. Please define a constant PG_CACHE_ENCRYPTION_KEY with at least 32 characters in wp-config.php', 500);
        }


        return PG_CACHE_ENCRYPTION_KEY;
    }
}
