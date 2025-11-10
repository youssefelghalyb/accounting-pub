<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Modules\Settings\Models\OrganizationSetting;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $orgSettings = OrganizationSetting::first();
        
        // Get locale from session, or use organization default, or fallback to 'en'
        $locale = Session::get('locale', $orgSettings->default_language ?? 'en');
        
        // Validate locale is in available languages
        if ($orgSettings && !$orgSettings->isLanguageAvailable($locale)) {
            $locale = $orgSettings->default_language ?? 'en';
            Session::put('locale', $locale);
        }
        
        App::setLocale($locale);
        
        return $next($request);
    }
}