<?php

namespace Laraditz\Shopee\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class EncryptedToken implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException | \RuntimeException $e) {
            // Plaintext row, or no app key configured: hand back the raw value.
            return $value;
        }
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        if (config('shopee.encrypt_tokens', false)) {
            return Crypt::encryptString($value);
        }

        return $value;
    }
}
