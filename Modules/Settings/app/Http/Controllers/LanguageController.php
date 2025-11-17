<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Modules\Settings\Models\OrganizationSetting;

class LanguageController extends Controller
{
    public function switch($locale)
    {
        $orgSettings = OrganizationSetting::first();
        
        // Check if locale is in available languages
        if ($orgSettings && $orgSettings->isLanguageAvailable($locale)) {
            Session::put('locale', $locale);
        }
        
        return redirect()->back();
    }
}

