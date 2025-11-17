<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;

class LocaleHelper
{
    /**
     * List of RTL language codes
     */
    const RTL_LANGUAGES = [
        'ar',    // Arabic
        'he',    // Hebrew
        'fa',    // Persian (Farsi)
        'ur',    // Urdu
        'yi',    // Yiddish
        'arc',   // Aramaic
        'azb',   // South Azerbaijani
        'ckb',   // Central Kurdish (Sorani)
        'dv',    // Dhivehi
        'ku',    // Kurdish
        'ps',    // Pashto
        'sd',    // Sindhi
        'ug',    // Uyghur
    ];

    /**
     * Check if the current locale is RTL
     *
     * @param string|null $locale
     * @return bool
     */
    public static function isRtl($locale = null)
    {
        $locale = $locale ?? App::getLocale();
        return in_array($locale, self::RTL_LANGUAGES);
    }

    /**
     * Get text direction for current locale
     *
     * @param string|null $locale
     * @return string
     */
    public static function getDirection($locale = null)
    {
        return self::isRtl($locale) ? 'rtl' : 'ltr';
    }

    /**
     * Get align class for current locale
     *
     * @param string|null $locale
     * @return string
     */
    public static function getAlignClass($locale = null)
    {
        return self::isRtl($locale) ? 'text-right' : 'text-left';
    }

    /**
     * Get float class for current locale
     *
     * @param string $direction 'left' or 'right'
     * @param string|null $locale
     * @return string
     */
    public static function getFloatClass($direction = 'left', $locale = null)
    {
        if (!self::isRtl($locale)) {
            return 'float-' . $direction;
        }
        
        // Flip direction for RTL
        return $direction === 'left' ? 'float-right' : 'float-left';
    }

    /**
     * Get margin/padding side for current locale
     *
     * @param string $side 'left' or 'right'
     * @param string|null $locale
     * @return string
     */
    public static function getSide($side, $locale = null)
    {
        if (!self::isRtl($locale)) {
            return $side;
        }
        
        // Flip side for RTL
        return $side === 'left' ? 'right' : 'left';
    }
}