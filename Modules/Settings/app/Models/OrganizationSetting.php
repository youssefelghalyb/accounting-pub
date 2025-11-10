<?php

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Settings\Database\Factories\OrganizationSettingFactory;

class OrganizationSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'organization_name',
        'address',
        'phone',
        'email',
        'website',
        'logo_path',
        'timezone',
        'date_format',
        'time_format',
        'default_language',
        'available_languages',
        'currency',
        'currency_symbol',
        'enable_notifications',
        'enable_audit_logs',
        'primary_color',
        'secondary_color',
        'CEO_name',
        'CEO_email',
        'CEO_phone',
    ];

    protected $casts = [
        'enable_notifications' => 'boolean',
        'enable_audit_logs' => 'boolean',
        'available_languages' => 'array',
    ];

    /**
     * Get available languages or return default
     */
    public function getAvailableLanguagesAttribute($value)
    {
        $languages = json_decode($value, true);
        return $languages ?: ['en', 'ar'];
    }

    /**
     * Check if a language is available
     */
    public function isLanguageAvailable($locale)
    {
        return in_array($locale, $this->available_languages);
    }
}
