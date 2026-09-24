<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting:{$key}");
    }

    /**
     * Reads a value stored with setEncrypted(). There was no encrypted-
     * settings mechanism anywhere in the app before this - every other
     * integration's key lives in .env - but the OpenAI key needs to be
     * Super-Admin-configurable at runtime, which means it has to live in the
     * database. Encrypted with Crypt (keyed off APP_KEY) rather than a new
     * secrets table, so this stays inside the settings mechanism that
     * already exists instead of introducing a parallel one.
     *
     * Never throws: a rotated APP_KEY, a corrupted value, or a value that
     * was never actually encrypted all just fall back to $default, the same
     * as "not configured". Callers (e.g. AiVerifier) are expected to treat
     * that as a fail-safe "AI isn't available right now", not an error.
     */
    public static function getEncrypted(string $key, mixed $default = null): mixed
    {
        $value = static::get($key);

        if ($value === null || $value === '') {
            return $default;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            Log::warning("Setting::getEncrypted could not decrypt [{$key}]: {$e->getMessage()}");

            return $default;
        }
    }

    public static function setEncrypted(string $key, string $value): void
    {
        static::set($key, Crypt::encryptString($value));
    }
}
