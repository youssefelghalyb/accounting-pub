<?php

return [
    // Page Titles
    'page_title' => 'Organization Settings',
    'settings' => 'Settings',
    
    // Messages
    'success' => 'Organization settings updated successfully!',
    'error' => 'Failed to update organization settings.',
    'no_settings' => 'No settings found.',
    'create_default' => 'Creating default settings...',
    
    // Current Information
    'current_logo' => 'Current Logo',
    'no_logo' => 'No logo uploaded',
    
    // Group Titles
    'basic_information' => 'Basic Information',
    'regional_settings' => 'Regional Settings',
    'appearance_branding' => 'Appearance & Branding',
    'ceo_information' => 'CEO Information',
    'system_settings' => 'System Settings',
    
    // Basic Information Fields
    'organization_name' => 'Organization Name',
    'organization_name_placeholder' => 'Enter organization name',
    'address' => 'Address',
    'address_placeholder' => 'Enter organization address',
    'phone' => 'Phone Number',
    'phone_placeholder' => '+1 (555) 123-4567',
    'email' => 'Email Address',
    'email_placeholder' => 'info@organization.com',
    'website' => 'Website',
    'website_placeholder' => 'https://www.organization.com',
    'logo' => 'Organization Logo',
    'logo_helper' => 'PNG, JPG, GIF up to 2MB',
    
    // Regional Settings Fields
    'timezone' => 'Timezone',
    'date_format' => 'Date Format',
    'time_format' => 'Time Format',
    'language' => 'Language',
    'default_language' => 'Default Language',
    'available_languages' => 'Available Languages',
    'currency' => 'Currency',
    'currency_symbol' => 'Currency Symbol',
    'currency_symbol_placeholder' => '$',
    
    // Timezone Options
    'timezone_utc' => 'UTC',
    'timezone_eastern' => 'Eastern Time (ET)',
    'timezone_central' => 'Central Time (CT)',
    'timezone_mountain' => 'Mountain Time (MT)',
    'timezone_pacific' => 'Pacific Time (PT)',
    'timezone_london' => 'London (GMT)',
    'timezone_paris' => 'Paris (CET)',
    'timezone_dubai' => 'Dubai (GST)',
    'timezone_tokyo' => 'Tokyo (JST)',
    'timezone_cairo' => 'Cairo (EET)',
    
    // Language Options
    'lang_english' => 'English',
    'lang_spanish' => 'Spanish',
    'lang_french' => 'French',
    'lang_german' => 'German',
    'lang_arabic' => 'Arabic',
    'lang_chinese' => 'Chinese',
    'lang_japanese' => 'Japanese',
    
    // Date Format Options
    'date_format_ymd' => 'YYYY-MM-DD (2025-01-15)',
    'date_format_mdy' => 'MM/DD/YYYY (01/15/2025)',
    'date_format_dmy' => 'DD/MM/YYYY (15/01/2025)',
    'date_format_dmy_dash' => 'DD-MM-YYYY (15-01-2025)',
    'date_format_long' => 'Month Day, Year (January 15, 2025)',
    
    // Time Format Options
    'time_format_24' => '24-hour (14:30:00)',
    'time_format_12' => '12-hour (02:30:00 PM)',
    'time_format_24_short' => '24-hour short (14:30)',
    'time_format_12_short' => '12-hour short (02:30 PM)',
    
    // Currency Options
    'currency_usd' => 'US Dollar (USD)',
    'currency_eur' => 'Euro (EUR)',
    'currency_gbp' => 'British Pound (GBP)',
    'currency_jpy' => 'Japanese Yen (JPY)',
    'currency_aed' => 'UAE Dirham (AED)',
    'currency_sar' => 'Saudi Riyal (SAR)',
    'currency_egp' => 'Egyptian Pound (EGP)',
    'currency_cad' => 'Canadian Dollar (CAD)',
    'currency_aud' => 'Australian Dollar (AUD)',
    'currency_inr' => 'Indian Rupee (INR)',
    
    // Appearance Fields
    'primary_color' => 'Primary Color',
    'primary_color_placeholder' => '#6366f1',
    'secondary_color' => 'Secondary Color',
    'secondary_color_placeholder' => '#8b5cf6',
    'theme' => 'Theme',
    'theme_light' => 'Light',
    'theme_dark' => 'Dark',
    'theme_auto' => 'Auto',
    
    // CEO Information Fields
    'ceo_name' => 'CEO Name',
    'ceo_name_placeholder' => 'Enter CEO full name',
    'ceo_email' => 'CEO Email',
    'ceo_email_placeholder' => 'ceo@organization.com',
    'ceo_phone' => 'CEO Phone',
    'ceo_phone_placeholder' => '+1 (555) 123-4567',
    
    // System Settings Fields
    'enable_notifications' => 'Enable system notifications',
    'enable_audit_logs' => 'Enable audit logs',
    'enable_maintenance_mode' => 'Enable maintenance mode',
    'enable_registration' => 'Enable user registration',
    'enable_2fa' => 'Enable two-factor authentication',
    
    // Buttons
    'save' => 'Save Settings',
    'save_changes' => 'Save Changes',
    'cancel' => 'Cancel',
    'reset' => 'Reset to Default',
    'upload' => 'Upload',
    'remove' => 'Remove',
    'browse' => 'Browse',
    
    // Validation Messages
    'validation' => [
        'organization_name_required' => 'Organization name is required',
        'organization_name_max' => 'Organization name must not exceed 255 characters',
        'address_max' => 'Address must not exceed 500 characters',
        'phone_max' => 'Phone number must not exceed 20 characters',
        'email_invalid' => 'Please enter a valid email address',
        'email_max' => 'Email must not exceed 255 characters',
        'website_invalid' => 'Please enter a valid URL',
        'website_max' => 'Website URL must not exceed 255 characters',
        'logo_image' => 'Logo must be an image file',
        'logo_mimes' => 'Logo must be PNG, JPG, JPEG, or GIF',
        'logo_max' => 'Logo size must not exceed 2MB',
        'timezone_required' => 'Timezone is required',
        'date_format_required' => 'Date format is required',
        'time_format_required' => 'Time format is required',
        'language_required' => 'Language is required',
        'currency_required' => 'Currency is required',
        'currency_max' => 'Currency code must not exceed 10 characters',
        'currency_symbol_required' => 'Currency symbol is required',
        'currency_symbol_max' => 'Currency symbol must not exceed 5 characters',
        'primary_color_required' => 'Primary color is required',
        'primary_color_max' => 'Primary color must be a valid hex color',
        'secondary_color_required' => 'Secondary color is required',
        'secondary_color_max' => 'Secondary color must be a valid hex color',
        'ceo_name_max' => 'CEO name must not exceed 255 characters',
        'ceo_email_invalid' => 'CEO email must be a valid email address',
        'ceo_email_max' => 'CEO email must not exceed 255 characters',
        'ceo_phone_max' => 'CEO phone must not exceed 20 characters',
    ],
    
    // Helper Texts
    'helpers' => [
        'organization_name' => 'The official name of your organization',
        'address' => 'Physical address of your organization',
        'phone' => 'Primary contact phone number',
        'email' => 'Primary contact email address',
        'website' => 'Your organization\'s website URL',
        'logo' => 'Upload your organization logo (recommended size: 200x200px)',
        'timezone' => 'Select your organization\'s timezone',
        'date_format' => 'Choose how dates will be displayed',
        'time_format' => 'Choose how times will be displayed',
        'language' => 'Default language for the system',
        'currency' => 'Default currency for financial operations',
        'currency_symbol' => 'Symbol to display with currency amounts',
        'primary_color' => 'Main color used throughout the application',
        'secondary_color' => 'Accent color for highlights and buttons',
        'ceo_name' => 'Full name of the Chief Executive Officer',
        'ceo_email' => 'CEO\'s email address',
        'ceo_phone' => 'CEO\'s contact phone number',
        'enable_notifications' => 'Allow system to send notifications',
        'enable_audit_logs' => 'Track all system activities for security',
    ],
    
    // Sections
    'section_titles' => [
        'general' => 'General Settings',
        'localization' => 'Localization',
        'branding' => 'Branding',
        'contact' => 'Contact Information',
        'features' => 'Features',
    ],
];