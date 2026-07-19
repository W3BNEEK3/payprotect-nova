<?php

namespace App\Helpers;

/**
 * AES-256-GCM authenticated encryption. GCM mode is deliberate over CBC — it
 * detects tampering (a corrupted or truncated ciphertext fails to decrypt
 * loudly, rather than silently producing garbage plaintext), which matters
 * for card data specifically.
 */
class Crypto
{
    private const CIPHER = 'aes-256-gcm';

    private static function key(): string
    {
        $key = env('APP_ENCRYPTION_KEY');

        if (!$key) {
            throw new \RuntimeException('APP_ENCRYPTION_KEY is not set in .env');
        }

        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        if (strlen($key) !== 32) {
            throw new \RuntimeException('APP_ENCRYPTION_KEY must decode to exactly 32 bytes for AES-256');
        }

        return $key;
    }

    /**
     * Returns raw binary (iv + auth tag + ciphertext, concatenated) — safe to
     * store directly in a VARBINARY column via a prepared statement. Do not
     * treat the return value as a UTF-8 string.
     */
    public static function encrypt(string $plaintext): string
    {
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = random_bytes($ivLength);
        $tag = '';

        $ciphertext = openssl_encrypt($plaintext, self::CIPHER, self::key(), OPENSSL_RAW_DATA, $iv, $tag);

        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed');
        }

        return $iv . $tag . $ciphertext;
    }

    public static function decrypt(string $binary): string
    {
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = substr($binary, 0, $ivLength);
        $tag = substr($binary, $ivLength, 16);
        $ciphertext = substr($binary, $ivLength + 16);

        $plaintext = openssl_decrypt($ciphertext, self::CIPHER, self::key(), OPENSSL_RAW_DATA, $iv, $tag);

        if ($plaintext === false) {
            throw new \RuntimeException('Decryption failed — data may be corrupted, truncated, or the key is wrong');
        }

        return $plaintext;
    }
}
