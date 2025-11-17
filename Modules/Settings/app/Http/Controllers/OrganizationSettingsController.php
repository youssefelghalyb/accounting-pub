<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Modules\Settings\Models\OrganizationSetting;

class OrganizationSettingsController extends Controller
{
    /**
     * Display the organization settings form.
     */
    public function index()
    {
        // Get the first (and only) organization settings record
        $settings = OrganizationSetting::first();
        
        // If no settings exist, create default ones
        if (!$settings) {
            $settings = OrganizationSetting::create([
                'organization_name' => __('settings::settings.organization_name_placeholder'),
                'timezone' => 'UTC',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i:s',
                'default_language' => 'en',
                'available_languages' => ['en', 'ar'],
                'currency' => 'USD',
                'currency_symbol' => '$',
                'enable_notifications' => true,
                'enable_audit_logs' => true,
                'primary_color' => '#6366f1',
                'secondary_color' => '#8b5cf6',
            ]);
        }

        $formConfig = $this->getFormConfig($settings);
        
        return view('settings::settings', compact('formConfig', 'settings'));
    }

    /**
     * Update the organization settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,gif|max:2048',
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'time_format' => 'required|string',
            'default_language' => 'required|string',
            'available_languages' => 'nullable|array',
            'currency' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:5',
            'enable_notifications' => 'nullable',
            'enable_audit_logs' => 'nullable',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'CEO_name' => 'nullable|string|max:255',
            'CEO_email' => 'nullable|email|max:255',
            'CEO_phone' => 'nullable|string|max:20',
        ], [
            'organization_name.required' => __('settings::settings.validation.organization_name_required'),
            'organization_name.max' => __('settings::settings.validation.organization_name_max'),
            'address.max' => __('settings::settings.validation.address_max'),
            'phone.max' => __('settings::settings.validation.phone_max'),
            'email.email' => __('settings::settings.validation.email_invalid'),
            'email.max' => __('settings::settings.validation.email_max'),
            'website.url' => __('settings::settings.validation.website_invalid'),
            'website.max' => __('settings::settings.validation.website_max'),
            'logo.image' => __('settings::settings.validation.logo_image'),
            'logo.mimes' => __('settings::settings.validation.logo_mimes'),
            'logo.max' => __('settings::settings.validation.logo_max'),
            'timezone.required' => __('settings::settings.validation.timezone_required'),
            'date_format.required' => __('settings::settings.validation.date_format_required'),
            'time_format.required' => __('settings::settings.validation.time_format_required'),
            'default_language.required' => __('settings::settings.validation.language_required'),
            'currency.required' => __('settings::settings.validation.currency_required'),
            'currency.max' => __('settings::settings.validation.currency_max'),
            'currency_symbol.required' => __('settings::settings.validation.currency_symbol_required'),
            'currency_symbol.max' => __('settings::settings.validation.currency_symbol_max'),
            'primary_color.required' => __('settings::settings.validation.primary_color_required'),
            'primary_color.max' => __('settings::settings.validation.primary_color_max'),
            'secondary_color.required' => __('settings::settings.validation.secondary_color_required'),
            'secondary_color.max' => __('settings::settings.validation.secondary_color_max'),
            'CEO_name.max' => __('settings::settings.validation.ceo_name_max'),
            'CEO_email.email' => __('settings::settings.validation.ceo_email_invalid'),
            'CEO_email.max' => __('settings::settings.validation.ceo_email_max'),
            'CEO_phone.max' => __('settings::settings.validation.ceo_phone_max'),
        ]);

        // Handle checkboxes (they won't be in the request if unchecked)
        $validated['enable_notifications'] = $request->has('enable_notifications') ? true : false;
        $validated['enable_audit_logs'] = $request->has('enable_audit_logs') ? true : false;

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            $settings = OrganizationSetting::first();
            if ($settings && $settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            // Store new logo
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo_path'] = $logoPath;
        }

        // Update or create settings
        $settings = OrganizationSetting::first();
        if ($settings) {
            $settings->update($validated);
        } else {
            OrganizationSetting::create($validated);
        }

        return redirect()->route('settings.organization')
            ->with('success', __('settings::settings.success'));
    }

    /**
     * Get the form configuration array.
     */
    private function getFormConfig($settings)
    {
        return [
            'groups' => [
                [
                    'title' =>__('settings::settings.page_title'),
                    'fields' => [
                        [
                            'type' => 'text',
                            'name' => 'organization_name',
                            'label' => __('settings::settings.organization_name'),
                            'placeholder' => __('settings::settings.organization_name_placeholder'),
                            'borderColor' => '#6366f1',
                            'grid' => 12,
                            'required' => true,
                            'value' => $settings->organization_name ?? ''
                        ],
                        [
                            'type' => 'text',
                            'name' => 'address',
                            'label' => __('settings::settings.address'),
                            'placeholder' => __('settings::settings.address_placeholder'),
                            'borderColor' => '#6366f1',
                            'grid' => 12,
                            'value' => $settings->address ?? ''
                        ],
                        [
                            'type' => 'tel',
                            'name' => 'phone',
                            'label' => __('settings::settings.phone'),
                            'placeholder' => __('settings::settings.phone_placeholder'),
                            'borderColor' => '#10b981',
                            'grid' => 6,
                            'value' => $settings->phone ?? ''
                        ],
                        [
                            'type' => 'email',
                            'name' => 'email',
                            'label' => __('settings::settings.email'),
                            'placeholder' => __('settings::settings.email_placeholder'),
                            'borderColor' => '#10b981',
                            'grid' => 6,
                            'value' => $settings->email ?? ''
                        ],
                        [
                            'type' => 'text',
                            'name' => 'website',
                            'label' => __('settings::settings.website'),
                            'placeholder' => __('settings::settings.website_placeholder'),
                            'borderColor' => '#8b5cf6',
                            'grid' => 12,
                            'value' => $settings->website ?? ''
                        ],
                        [
                            'type' => 'image',
                            'name' => 'logo',
                            'label' => __('settings::settings.logo'),
                            'borderColor' => '#06b6d4',
                            'grid' => 12,
                            'helperText' => __('settings::settings.logo_helper'),
                        ]
                    ]
                ],
                [
                    'title' => __('settings::settings.regional_settings'),
                    'fields' => [
                        [
                            'type' => 'select',
                            'name' => 'timezone',
                            'label' => __('settings::settings.timezone'),
                            'borderColor' => '#f59e0b',
                            'grid' => 6,
                            'required' => true,
                            'value' => $settings->timezone ?? 'UTC',
                            'options' => [
                                ['value' => 'UTC', 'label' => __('settings::settings.timezone_utc')],
                                ['value' => 'America/New_York', 'label' => __('settings::settings.timezone_eastern')],
                                ['value' => 'America/Chicago', 'label' => __('settings::settings.timezone_central')],
                                ['value' => 'America/Denver', 'label' => __('settings::settings.timezone_mountain')],
                                ['value' => 'America/Los_Angeles', 'label' => __('settings::settings.timezone_pacific')],
                                ['value' => 'Europe/London', 'label' => __('settings::settings.timezone_london')],
                                ['value' => 'Europe/Paris', 'label' => __('settings::settings.timezone_paris')],
                                ['value' => 'Asia/Dubai', 'label' => __('settings::settings.timezone_dubai')],
                                ['value' => 'Asia/Tokyo', 'label' => __('settings::settings.timezone_tokyo')],
                                ['value' => 'Africa/Cairo', 'label' => __('settings::settings.timezone_cairo')],
                            ]
                        ],
                        [
                            'type' => 'select',
                            'name' => 'default_language',
                            'label' => __('settings::settings.default_language'),
                            'borderColor' => '#10b981',
                            'grid' => 6,
                            'required' => true,
                            'value' => $settings->default_language ?? 'en',
                            'options' => [
                                ['value' => 'en', 'label' => __('settings::settings.lang_english')],
                                ['value' => 'ar', 'label' => __('settings::settings.lang_arabic')],
                                ['value' => 'es', 'label' => __('settings::settings.lang_spanish')],
                                ['value' => 'fr', 'label' => __('settings::settings.lang_french')],
                                ['value' => 'de', 'label' => __('settings::settings.lang_german')],
                                ['value' => 'zh', 'label' => __('settings::settings.lang_chinese')],
                                ['value' => 'ja', 'label' => __('settings::settings.lang_japanese')],
                            ]
                        ],
                        [
                            'type' => 'select',
                            'name' => 'date_format',
                            'label' => __('settings::settings.date_format'),
                            'borderColor' => '#8b5cf6',
                            'grid' => 3,
                            'required' => true,
                            'value' => $settings->date_format ?? 'Y-m-d',
                            'options' => [
                                ['value' => 'Y-m-d', 'label' => __('settings::settings.date_format_ymd')],
                                ['value' => 'm/d/Y', 'label' => __('settings::settings.date_format_mdy')],
                                ['value' => 'd/m/Y', 'label' => __('settings::settings.date_format_dmy')],
                                ['value' => 'd-m-Y', 'label' => __('settings::settings.date_format_dmy_dash')],
                                ['value' => 'F j, Y', 'label' => __('settings::settings.date_format_long')],
                            ]
                        ],
                        [
                            'type' => 'select',
                            'name' => 'time_format',
                            'label' => __('settings::settings.time_format'),
                            'borderColor' => '#8b5cf6',
                            'grid' => 3,
                            'required' => true,
                            'value' => $settings->time_format ?? 'H:i:s',
                            'options' => [
                                ['value' => 'H:i:s', 'label' => __('settings::settings.time_format_24')],
                                ['value' => 'h:i:s A', 'label' => __('settings::settings.time_format_12')],
                                ['value' => 'H:i', 'label' => __('settings::settings.time_format_24_short')],
                                ['value' => 'h:i A', 'label' => __('settings::settings.time_format_12_short')],
                            ]
                        ],
                        [
                            'type' => 'select',
                            'name' => 'currency',
                            'label' => __('settings::settings.currency'),
                            'borderColor' => '#ec4899',
                            'grid' => 3,
                            'required' => true,
                            'value' => $settings->currency ?? 'USD',
                            'options' => [
                                ['value' => 'USD', 'label' => __('settings::settings.currency_usd')],
                                ['value' => 'EUR', 'label' => __('settings::settings.currency_eur')],
                                ['value' => 'GBP', 'label' => __('settings::settings.currency_gbp')],
                                ['value' => 'JPY', 'label' => __('settings::settings.currency_jpy')],
                                ['value' => 'AED', 'label' => __('settings::settings.currency_aed')],
                                ['value' => 'SAR', 'label' => __('settings::settings.currency_sar')],
                                ['value' => 'EGP', 'label' => __('settings::settings.currency_egp')],
                            ]
                        ],
                        [
                            'type' => 'text',
                            'name' => 'currency_symbol',
                            'label' => __('settings::settings.currency_symbol'),
                            'placeholder' => __('settings::settings.currency_symbol_placeholder'),
                            'borderColor' => '#ec4899',
                            'grid' => 3,
                            'required' => true,
                            'value' => $settings->currency_symbol ?? '$'
                        ]
                    ]
                ],
                [
                    'title' => __('settings::settings.appearance_branding'),
                    'fields' => [
                        [
                            'type' => 'text',
                            'name' => 'primary_color',
                            'label' => __('settings::settings.primary_color'),
                            'placeholder' => __('settings::settings.primary_color_placeholder'),
                            'borderColor' => '#6366f1',
                            'grid' => 6,
                            'required' => true,
                            'value' => $settings->primary_color ?? '#6366f1'
                        ],
                        [
                            'type' => 'text',
                            'name' => 'secondary_color',
                            'label' => __('settings::settings.secondary_color'),
                            'placeholder' => __('settings::settings.secondary_color_placeholder'),
                            'borderColor' => '#8b5cf6',
                            'grid' => 6,
                            'required' => true,
                            'value' => $settings->secondary_color ?? '#8b5cf6'
                        ],
                    ]
                ],
                [
                    'title' => __('settings::settings.ceo_information'),
                    'fields' => [
                        [
                            'type' => 'text',
                            'name' => 'CEO_name',
                            'label' => __('settings::settings.ceo_name'),
                            'placeholder' => __('settings::settings.ceo_name_placeholder'),
                            'borderColor' => '#6366f1',
                            'grid' => 12,
                            'value' => $settings->CEO_name ?? ''
                        ],
                        [
                            'type' => 'email',
                            'name' => 'CEO_email',
                            'label' => __('settings::settings.ceo_email'),
                            'placeholder' => __('settings::settings.ceo_email_placeholder'),
                            'borderColor' => '#10b981',
                            'grid' => 6,
                            'value' => $settings->CEO_email ?? ''
                        ],
                        [
                            'type' => 'tel',
                            'name' => 'CEO_phone',
                            'label' => __('settings::settings.ceo_phone'),
                            'placeholder' => __('settings::settings.ceo_phone_placeholder'),
                            'borderColor' => '#10b981',
                            'grid' => 6,
                            'value' => $settings->CEO_phone ?? ''
                        ],
                    ]
                ],
                [
                    'title' => __('settings::settings.system_settings'),
                    'fields' => [
                        [
                            'type' => 'checkbox',
                            'name' => 'enable_notifications',
                            'label' => __('settings::settings.enable_notifications'),
                            'borderColor' => '#10b981',
                            'grid' => 6,
                            'value' => $settings->enable_notifications ?? true
                        ],
                        [
                            'type' => 'checkbox',
                            'name' => 'enable_audit_logs',
                            'label' => __('settings::settings.enable_audit_logs'),
                            'borderColor' => '#06b6d4',
                            'grid' => 6,
                            'value' => $settings->enable_audit_logs ?? true
                        ],
                    ]
                ]
            ]
        ];
    }
}