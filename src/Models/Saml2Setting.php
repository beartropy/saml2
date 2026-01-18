<?php

namespace Beartropy\Saml2\Models;

use Illuminate\Database\Eloquent\Model;

class Saml2Setting extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'beartropy_saml2_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Check if the setup has been completed (not first deploy).
     * Returns true if the table doesn't exist (for env-only setups without migrations).
     */
    public static function isSetupComplete(): bool
    {
        try {
            return static::get('setup_complete') === 'true';
        } catch (\Illuminate\Database\QueryException $e) {
            // Table doesn't exist - treat as setup complete (env-only mode)
            return true;
        }
    }

    /**
     * Check if this is the first deploy (setup not complete).
     */
    public static function isFirstDeploy(): bool
    {
        return !static::isSetupComplete();
    }

    /**
     * Mark setup as complete.
     */
    public static function markSetupComplete(): void
    {
        static::set('setup_complete', 'true');
    }

    /**
     * Reset to first deploy state.
     */
    public static function resetToFirstDeploy(): void
    {
        static::where('key', 'setup_complete')->delete();
    }
}
