# Settings Module Overview

## Introduction

The Settings Module provides organization-wide configuration and customization capabilities. It handles system preferences, localization, branding, and regional settings that affect the entire application.

**Location:** `Modules/Settings/`

**Version:** 1.0

**Laravel Version:** 12.x

---

## Module Structure

```
Modules/Settings/
├── app/
│   ├── Http/
│   │   └── Controllers/          # HTTP controllers
│   ├── Models/                   # Eloquent models
│   └── Providers/                # Service providers
├── config/
│   └── form-builder.php          # Form builder configuration
├── database/
│   ├── migrations/               # Database migrations
│   └── seeders/                  # Database seeders
├── resources/
│   ├── views/                    # Blade templates
│   └── lang/                     # Language files (en, ar, fr)
├── routes/
│   └── web.php                   # Web routes
├── tests/
│   ├── Feature/                  # Feature tests
│   └── Unit/                     # Unit tests
└── module.json                   # Module metadata
```

---

## Core Features

### 1. Organization Settings

**Purpose:** Manage organization-wide settings and configurations.

**Key Features:**
- Organization profile (name, address, contact info)
- Logo upload and management
- Regional settings (timezone, date/time formats)
- Localization (language preferences)
- Currency settings
- Branding (primary and secondary colors)
- System preferences (notifications, audit logs)
- CEO information

**Controllers:** `OrganizationSettingsController`

**Models:** `OrganizationSetting`

**Routes:**
- `settings.organization` - View/edit organization settings
- `settings.organization.update` - Update settings

---

### 2. Language Switching

**Purpose:** Enable multi-language support throughout the application.

**Key Features:**
- Dynamic language switching
- Session-based locale management
- Available languages configuration
- RTL support for Arabic
- Language file management

**Controllers:** `LanguageController`

**Routes:**
- `language.switch` - Switch application language

**Supported Languages:**
- English (en)
- Arabic (ar)
- French (fr)

---

## Organization Settings Configuration

### Basic Information

| Setting | Type | Description | Example |
|---------|------|-------------|---------|
| organization_name | string | Organization name | "Acme Corporation" |
| address | text | Physical address | "123 Business St, City" |
| phone | string | Contact phone | "+1234567890" |
| email | string | Contact email | "info@acme.com" |
| website | string | Organization website | "https://acme.com" |
| logo_path | string | Path to logo file | "logos/logo.png" |

### Regional Settings

| Setting | Type | Description | Example |
|---------|------|-------------|---------|
| timezone | string | Default timezone | "UTC", "America/New_York" |
| date_format | string | Date display format | "Y-m-d", "m/d/Y", "d/m/Y" |
| time_format | string | Time display format | "H:i:s", "h:i A" |

### Localization

| Setting | Type | Description | Example |
|---------|------|-------------|---------|
| default_language | string | Default system language | "en" |
| available_languages | array | Languages enabled | ["en", "ar", "fr"] |

### Currency

| Setting | Type | Description | Example |
|---------|------|-------------|---------|
| currency | string | Currency code (ISO 4217) | "USD", "EUR", "EGP" |
| currency_symbol | string | Currency symbol | "$", "€", "E£" |

### System Preferences

| Setting | Type | Description | Default |
|---------|------|-------------|---------|
| enable_notifications | boolean | Enable system notifications | true |
| enable_audit_logs | boolean | Enable audit logging | true |

### Branding

| Setting | Type | Description | Example |
|---------|------|-------------|---------|
| primary_color | string | Primary brand color (hex) | "#1E40AF" |
| secondary_color | string | Secondary brand color (hex) | "#7C3AED" |

### Management

| Setting | Type | Description | Example |
|---------|------|-------------|---------|
| ceo_name | string | CEO full name | "John Doe" |
| ceo_email | string | CEO email | "ceo@acme.com" |
| ceo_phone | string | CEO phone | "+1234567890" |

---

## Form Builder Configuration

The Settings module uses a dynamic form builder configuration for rendering settings forms.

**Location:** `Modules/Settings/config/form-builder.php`

### Form Structure

```php
return [
    'sections' => [
        [
            'title' => 'Organization Information',
            'fields' => [
                [
                    'name' => 'organization_name',
                    'label' => 'Organization Name',
                    'type' => 'text',
                    'required' => false,
                ],
                // ... more fields
            ],
        ],
        // ... more sections
    ],
];
```

### Field Types

- `text` - Single-line text input
- `email` - Email input with validation
- `url` - URL input with validation
- `tel` - Telephone input
- `textarea` - Multi-line text area
- `file` - File upload (logo)
- `select` - Dropdown selection
- `checkbox` - Boolean checkbox
- `color` - Color picker

### Validation

Each field can specify:
- `required` - Whether field is required
- `validation` - Additional validation rules
- Custom validation messages

---

## Data Flow

### Settings Update Flow

```
User Accesses Settings Page
    ↓
Load Current Settings (singleton)
    ↓
Render Form Using Form Builder Config
    ↓
User Modifies Settings
    ↓
Submit Form
    ↓
Validate Input
    ↓
    ├── Handle File Upload (logo)
    │   ├── Validate file (type, size)
    │   ├── Store in storage/app/public/logos/
    │   └── Save file path
    │
    └── Update Settings Record
    ↓
Redirect with Success Message
```

### Language Switch Flow

```
User Selects Language
    ↓
Validate Language Code
    ↓
Check Available Languages
    ↓
Set Session Locale
    ↓
Update User Preference (if authenticated)
    ↓
Redirect Back
    ↓
Application Reloads with New Language
```

---

## Business Rules

### Organization Settings Rules
1. Only one settings record exists (singleton pattern)
2. Settings are created on first access if not exist
3. Logo must be image file (jpg, png, gif)
4. Logo max size: 2MB
5. Color codes must be valid hex format (#RRGGBB)
6. Timezone must be valid PHP timezone
7. Default language must be in available_languages array

### Language Rules
1. Language code must be 2-letter ISO 639-1 code
2. Language must be in available_languages array
3. Language files must exist in resources/lang/
4. Session locale persists until changed
5. RTL layout auto-applied for Arabic

---

## Database Schema

### organization_settings Table

```sql
CREATE TABLE organization_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_name VARCHAR(255),
    address TEXT,
    phone VARCHAR(255),
    email VARCHAR(255),
    website VARCHAR(255),
    logo_path VARCHAR(255),
    timezone VARCHAR(255),
    date_format VARCHAR(255),
    time_format VARCHAR(255),
    default_language VARCHAR(255),
    available_languages TEXT,  -- JSON array
    currency VARCHAR(255),
    currency_symbol VARCHAR(255),
    enable_notifications BOOLEAN DEFAULT TRUE,
    enable_audit_logs BOOLEAN DEFAULT TRUE,
    primary_color VARCHAR(255),
    secondary_color VARCHAR(255),
    ceo_name VARCHAR(255),
    ceo_email VARCHAR(255),
    ceo_phone VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Casts:**
```php
[
    'enable_notifications' => 'boolean',
    'enable_audit_logs' => 'boolean',
    'available_languages' => 'array',
]
```

---

## Usage Examples

### 1. Get Organization Settings

```php
use Modules\Settings\app\Models\OrganizationSetting;

$settings = OrganizationSetting::first();
// or
$settings = OrganizationSetting::firstOrCreate([]);

echo $settings->organization_name;
echo $settings->currency_symbol;
```

### 2. Update Settings

```php
$settings = OrganizationSetting::first();
$settings->update([
    'organization_name' => 'New Name',
    'primary_color' => '#FF5733',
]);
```

### 3. Check Available Language

```php
$settings = OrganizationSetting::first();
if ($settings->isLanguageAvailable('ar')) {
    // Arabic is available
}
```

### 4. Upload Logo

```php
if ($request->hasFile('logo')) {
    $path = $request->file('logo')->store('logos', 'public');
    $settings->update(['logo_path' => $path]);
}
```

### 5. Switch Language

```php
app()->setLocale('ar');
session(['locale' => 'ar']);
```

---

## Localization

### Language Files Structure

```
resources/lang/
├── en/
│   ├── messages.php
│   ├── validation.php
│   └── ...
├── ar/
│   ├── messages.php
│   ├── validation.php
│   └── ...
└── fr/
    ├── messages.php
    ├── validation.php
    └── ...
```

### Translation Usage

In Blade templates:
```blade
{{ __('messages.welcome') }}
{{ __('messages.hello', ['name' => $user->name]) }}
```

In PHP:
```php
$message = __('messages.welcome');
$translated = trans('messages.hello', ['name' => 'John']);
```

### RTL Support

For Arabic language:
```blade
@if(app()->getLocale() == 'ar')
    <html dir="rtl">
@else
    <html dir="ltr">
@endif
```

---

## Integration with Other Modules

### HR Module Integration

The Settings module provides:
- Currency symbol for salary display
- Date/time formats for employee hire dates
- Timezone for timestamp display
- Language for UI localization
- Organization name for reports

**Usage in HR:**
```php
$settings = OrganizationSetting::first();

// Display salary with currency
echo $settings->currency_symbol . number_format($employee->salary, 2);

// Format dates
$date = $employee->hire_date->format($settings->date_format);

// Organization name in reports
$report->setOrganization($settings->organization_name);
```

---

## Common Use Cases

### 1. Display Organization Logo

```blade
@php
    $settings = \Modules\Settings\app\Models\OrganizationSetting::first();
@endphp

@if($settings && $settings->logo_path)
    <img src="{{ asset('storage/' . $settings->logo_path) }}"
         alt="{{ $settings->organization_name }}">
@endif
```

### 2. Format Currency

```php
$settings = OrganizationSetting::first();

function formatCurrency($amount) use ($settings) {
    return $settings->currency_symbol . number_format($amount, 2);
}

echo formatCurrency(1234.56); // $1,234.56
```

### 3. Apply Branding Colors

```blade
<style>
    :root {
        --primary-color: {{ $settings->primary_color ?? '#1E40AF' }};
        --secondary-color: {{ $settings->secondary_color ?? '#7C3AED' }};
    }
</style>
```

### 4. Language Switcher

```blade
<select onchange="switchLanguage(this.value)">
    @foreach($settings->available_languages as $lang)
        <option value="{{ $lang }}"
                {{ app()->getLocale() == $lang ? 'selected' : '' }}>
            {{ strtoupper($lang) }}
        </option>
    @endforeach
</select>

<script>
function switchLanguage(lang) {
    fetch('{{ route("language.switch") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ language: lang })
    }).then(() => location.reload());
}
</script>
```

---

## Security Considerations

### Access Control
- Settings access should be restricted to administrators
- Implement role-based access control
- Audit log changes to settings
- Validate file uploads strictly

### File Upload Security
- Validate file type (images only)
- Validate file size (max 2MB)
- Store files outside web root when possible
- Generate unique filenames
- Scan uploaded files for malware

### Validation
- Sanitize all user input
- Validate color codes against hex pattern
- Validate URLs and emails
- Prevent XSS in text fields
- Validate timezone against PHP timezone list

---

## Testing

### Unit Tests
- Model tests (OrganizationSetting)
- Singleton pattern tests
- Language availability tests
- Cast and accessor tests

### Feature Tests
- Settings CRUD operations
- File upload tests
- Language switch tests
- Form validation tests
- Integration tests with HR module

**Test Location:** `Modules/Settings/tests/`

---

## Configuration Files

### module.json

```json
{
    "name": "Settings",
    "alias": "settings",
    "description": "Application Settings Module",
    "keywords": ["settings", "configuration", "localization"],
    "priority": 0,
    "providers": [
        "Modules\\Settings\\Providers\\SettingsServiceProvider"
    ],
    "files": []
}
```

### form-builder.php

Dynamic form configuration for settings forms. See source file for complete configuration.

---

## Future Enhancements

### Planned Features
1. Email server configuration (SMTP settings)
2. Backup and restore settings
3. Import/export settings
4. Multiple organization support (multi-tenancy)
5. Advanced branding (custom CSS, fonts)
6. Social media links
7. Business hours configuration
8. Holiday calendar
9. Tax settings
10. Integration with external services

### Technical Improvements
1. Settings cache for performance
2. Settings API endpoints
3. Settings versioning
4. Settings migration tool
5. Advanced validation rules
6. Custom field types in form builder
7. Settings history/audit trail
8. Real-time settings updates

---

## Dependencies

### Laravel Packages
- `nwidart/laravel-modules` - Modular architecture
- `laravel/framework` - Core framework

### Frontend Dependencies
- Blade templating
- Tailwind CSS (for color picker)
- Alpine.js (for interactive components)

---

## Support and Documentation

For more information, see:
- [Database Schema](../schema/DATABASE_SCHEMA.md)
- [Model Relations](../schema/MODEL_RELATIONS.md)
- [Controllers and API](../CONTROLLERS_API.md)

---

## License

This module is part of the Accounting Application and follows the same license.
