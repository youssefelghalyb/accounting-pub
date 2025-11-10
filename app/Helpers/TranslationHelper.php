<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;

class TranslationHelper
{
    /**
     * Translate from module or fallback to app translations
     * 
     * @param string $module Module name (e.g., 'Settings', 'Users')
     * @param string $key Translation key (e.g., 'sidebar.dashboard')
     * @param array $replace Replacement values
     * @param string|null $locale Specific locale
     * @return string
     */
    public static function trans($module, $key, $replace = [], $locale = null)
    {
        $locale = $locale ?? App::getLocale();
        
        // Try module translation first
        $modulePath = module_path($module, "Resources/lang/{$locale}/{$key}.php");
        
        if (File::exists($modulePath)) {
            $translations = require $modulePath;
            $value = data_get($translations, str_replace("{$key}.", '', $key));
            
            if ($value) {
                return self::makeReplacements($value, $replace);
            }
        }
        
        // Fallback to app translations
        return __($key, $replace, $locale);
    }
    
    /**
     * Make replacements in translation string
     */
    private static function makeReplacements($line, array $replace)
    {
        if (empty($replace)) {
            return $line;
        }

        foreach ($replace as $key => $value) {
            $line = str_replace(
                [':'.$key, ':'.strtoupper($key), ':'.ucfirst($key)],
                [$value, strtoupper($value), ucfirst($value)],
                $line
            );
        }

        return $line;
    }
}