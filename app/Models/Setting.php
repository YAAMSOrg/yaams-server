<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    /**
     * Default values for every known instance setting. Used to render the
     * settings page and to backfill keys that predate a new setting, so
     * callers never have to hardcode defaults across the codebase.
     */
    public static function defaults(): array
    {
        return [
            'app_name'                    => config('app.name', 'YAAMS'),
            'allow_user_airline_creation' => '0',
            'allow_registration'          => '1',
            'support_email'               => null,
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::find($key);

        if ($row) {
            return $row->value;
        }

        // Fall back to the declared default before the caller-supplied one.
        return $default ?? (static::defaults()[$key] ?? null);
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
