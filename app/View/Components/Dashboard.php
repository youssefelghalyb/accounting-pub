<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\App;
use Modules\Settings\Models\OrganizationSetting;

class Dashboard extends Component
{
    public $orgSettings;
    public $pageTitle;

    /**
     * Create a new component instance.
     */
    public function __construct($pageTitle = 'Dashboard')
    {
        $this->orgSettings = OrganizationSetting::first() ?? $this->getDefaultSettings();
        $this->pageTitle = $pageTitle;
        
        // Set locale based on organization settings if not already set
        if (!session()->has('locale') && $this->orgSettings->default_language) {
            App::setLocale($this->orgSettings->default_language);
            session()->put('locale', $this->orgSettings->default_language);
        }
    }

    /**
     * Get default organization settings
     */
    private function getDefaultSettings()
    {
        return (object) [
            'organization_name' => config('app.name', 'Dashboard'),
            'logo_path' => null,
            'primary_color' => '#3490dc',
            'secondary_color' => '#ffed4a',
            'currency_symbol' => '$',
            'default_language' => 'en',
            'available_languages' => ['en', 'ar'],
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('layouts.dashboard');
    }
}