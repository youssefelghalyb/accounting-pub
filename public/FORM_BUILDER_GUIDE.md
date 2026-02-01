# Form Builder Component Documentation

## Table of Contents
1. [Introduction](#introduction)
2. [Basic Usage](#basic-usage)
3. [Field Types](#field-types)
4. [Configuration Options](#configuration-options)
5. [Advanced Features](#advanced-features)
6. [Examples](#examples)
7. [Best Practices](#best-practices)

---

## Introduction

The Form Builder Component is a powerful, flexible Laravel Blade component that allows you to dynamically create forms with a clean, modern design. It supports validation, error handling, various field types, and provides an intuitive API for both simple and complex forms.

### Key Features
- **Multiple field types**: text, email, number, textarea, select, checkbox, radio, file, image, date, color, and more
- **Responsive grid layout**: 12-column grid system for flexible layouts
- **Grouped forms**: Organize fields into logical sections with titles
- **Client & server-side validation**: Built-in error handling and display
- **Custom styling**: Border colors, custom classes, and modern design elements
- **File uploads**: With preview support for images and files
- **Boolean handling**: Checkboxes and radio buttons properly submit as boolean values

---

## Basic Usage

### Simple Form Example

```blade
<x-dashboard.packages.form-builder
    :action="route('users.store')"
    method="POST"
    :formConfig="[
        'fields' => [
            [
                'name' => 'username',
                'type' => 'text',
                'label' => 'Username',
                'placeholder' => 'Enter your username',
                'required' => true,
                'grid' => 12
            ]
        ]
    ]"
/>
```

### Component Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `action` | String | `#` | Form submission URL |
| `method` | String | `POST` | HTTP method (POST, PUT, PATCH, etc.) |
| `formConfig` | Array | `null` | Form configuration array |

---

## Field Types

### 1. Text Input

Standard text input field.

```php
[
    'type' => 'text',
    'name' => 'first_name',
    'label' => 'First Name',
    'placeholder' => 'Enter your first name',
    'required' => true,
    'grid' => 6
]
```

**Options:**
- `borderColor`: Custom border color (e.g., '#3b82f6')
- `value`: Pre-fill value (useful for edit forms)

### 2. Email Input

Email input with built-in browser validation.

```php
[
    'type' => 'email',
    'name' => 'email',
    'label' => 'Email Address',
    'placeholder' => 'user@example.com',
    'required' => true,
    'grid' => 6
]
```

### 3. Number Input

Numeric input field.

```php
[
    'type' => 'number',
    'name' => 'salary',
    'label' => 'Salary',
    'placeholder' => 'Enter salary',
    'required' => true,
    'grid' => 6
]
```

### 4. Textarea

Multi-line text input with optional character counter.

```php
[
    'type' => 'textarea',
    'name' => 'bio',
    'label' => 'Biography',
    'placeholder' => 'Tell us about yourself...',
    'rows' => 5,
    'maxLength' => 500,  // Enables character counter
    'grid' => 12
]
```

**Options:**
- `rows`: Number of visible rows (default: 4)
- `maxLength`: Maximum character limit with visual counter

### 5. Select Dropdown

Dropdown selection field.

```php
[
    'type' => 'select',
    'name' => 'department_id',
    'label' => 'Department',
    'required' => true,
    'grid' => 6,
    'options' => [
        ['value' => '', 'label' => 'Select department'],
        ['value' => '1', 'label' => 'Sales'],
        ['value' => '2', 'label' => 'Marketing'],
        ['value' => '3', 'label' => 'Engineering']
    ],
    'value' => '2'  // Pre-selected value
]
```

**Options format:**
- Array of arrays: `['value' => 'x', 'label' => 'Y']`
- Simple array: `['option1', 'option2']` (uses same value for both value and label)

### 6. Checkbox

Single checkbox that submits as boolean (`true`/`false`).

```php
[
    'type' => 'checkbox',
    'name' => 'terms',
    'label' => 'I agree to the Terms of Service',
    'required' => true,
    'grid' => 12,
    'value' => true  // Pre-checked state (true/false)
]
```

**Important:**
- Checked: Submits as `true` (boolean)
- Unchecked: Submits as `false` (boolean)
- Supports `borderColor` for custom styling

### 7. Radio Buttons

Single selection from multiple options. Supports boolean conversion.

```php
[
    'type' => 'radio',
    'name' => 'subscription_plan',
    'label' => 'Subscription Plan',
    'required' => true,
    'layout' => 'column',  // or 'row'
    'grid' => 12,
    'options' => [
        ['value' => 'free', 'label' => 'Free Plan'],
        ['value' => 'pro', 'label' => 'Pro Plan - $9.99/month'],
        ['value' => 'enterprise', 'label' => 'Enterprise - $49.99/month']
    ],
    'value' => 'pro'  // Pre-selected value
]
```

**Boolean Radio Buttons:**
```php
[
    'type' => 'radio',
    'name' => 'is_active',
    'label' => 'Status',
    'layout' => 'row',
    'options' => [
        ['value' => '1', 'label' => 'Active'],    // Automatically converts to 'true'
        ['value' => '0', 'label' => 'Inactive']   // Automatically converts to 'false'
    ]
]
```

**Options:**
- `layout`: `'column'` (default, vertical) or `'row'` (horizontal)
- Values `'1'`, `1`, `'0'`, `0` are automatically converted to boolean `'true'`/`'false'`

### 8. File Upload

File upload with drag-and-drop interface and preview.

```php
[
    'type' => 'file',
    'name' => 'resume',
    'label' => 'Upload Resume',
    'accept' => '.pdf,.doc,.docx',
    'helperText' => 'PDF or Word documents only',
    'required' => false,
    'grid' => 6,
    'value' => '/storage/resumes/current.pdf'  // Edit mode: shows link to current file
]
```

**Options:**
- `accept`: Allowed file types (e.g., `.pdf,.doc,.docx`)
- `multiple`: Allow multiple files (default: false)
- `helperText`: Help text shown below upload area
- `value`: URL to existing file (shows preview in edit mode)

### 9. Image Upload

Image upload with visual preview.

```php
[
    'type' => 'image',
    'name' => 'profile_picture',
    'label' => 'Profile Picture',
    'helperText' => 'PNG, JPG, GIF up to 5MB',
    'required' => false,
    'grid' => 6,
    'value' => '/storage/images/profile.jpg'  // Edit mode: shows image preview
]
```

**Features:**
- Accepts image files only
- Shows preview after selection
- Displays current image in edit mode
- Remove button to clear selection

### 10. Date Input

Date picker input.

```php
[
    'type' => 'date',
    'name' => 'hire_date',
    'label' => 'Hire Date',
    'required' => true,
    'grid' => 6,
    'value' => '2024-01-15'  // Format: YYYY-MM-DD
]
```

### 11. Color Picker

Modern color picker with hex input and color swatches.

```php
[
    'type' => 'color',
    'name' => 'brand_color',
    'label' => 'Brand Color',
    'required' => false,
    'grid' => 6,
    'value' => '#6366f1'  // Default color (hex format)
]
```

**Features:**
- Visual color preview
- Hex code input field
- Quick color swatches
- Real-time sync between picker and text input

### 12. Other Input Types

The component supports all HTML5 input types:
- `tel` - Telephone number
- `url` - URL input
- `password` - Password input
- `time` - Time picker
- `datetime-local` - Date and time picker

---

## Configuration Options

### Field-Level Options

| Option | Type | Description | Applicable To |
|--------|------|-------------|---------------|
| `name` | String | **Required.** Field name attribute | All |
| `type` | String | **Required.** Field type | All |
| `label` | String | Field label text | All |
| `placeholder` | String | Placeholder text | text, email, number, textarea, etc. |
| `required` | Boolean | Mark field as required | All |
| `grid` | Integer | Grid columns (1-12) | All |
| `borderColor` | String | Custom border color (hex) | Most fields |
| `value` | Mixed | Pre-fill/edit value | All |
| `options` | Array | Options for select/radio | select, radio |
| `rows` | Integer | Visible rows | textarea |
| `maxLength` | Integer | Max character limit | textarea |
| `accept` | String | Allowed file types | file |
| `multiple` | Boolean | Allow multiple files | file |
| `helperText` | String | Helper text below field | file, image |
| `layout` | String | Layout: 'row' or 'column' | radio |

### Grid System

The form uses a 12-column grid system:
- `grid: 12` - Full width
- `grid: 6` - Half width
- `grid: 4` - One-third width
- `grid: 3` - One-quarter width

**Example:**
```php
[
    ['name' => 'first_name', 'grid' => 6],  // 50% width
    ['name' => 'last_name', 'grid' => 6],   // 50% width (same row)
    ['name' => 'bio', 'grid' => 12]         // 100% width (new row)
]
```

---

## Advanced Features

### 1. Grouped Forms

Organize fields into sections with titles:

```php
'groups' => [
    [
        'title' => 'Personal Information',
        'fields' => [
            ['name' => 'first_name', 'type' => 'text', 'grid' => 6],
            ['name' => 'last_name', 'type' => 'text', 'grid' => 6]
        ]
    ],
    [
        'title' => 'Contact Details',
        'fields' => [
            ['name' => 'email', 'type' => 'email', 'grid' => 6],
            ['name' => 'phone', 'type' => 'tel', 'grid' => 6]
        ]
    ]
]
```

### 2. Edit Mode (Pre-filling Values)

For edit forms, pass values in the field configuration:

```php
[
    'name' => 'first_name',
    'type' => 'text',
    'label' => 'First Name',
    'value' => $user->first_name,  // Pre-fill from model
    'grid' => 6
]
```

The component automatically handles Laravel's `old()` input for validation errors.

### 3. Custom Border Colors

Add visual distinction to field groups:

```php
[
    'name' => 'email',
    'type' => 'email',
    'label' => 'Email',
    'borderColor' => '#10b981',  // Green border
    'grid' => 6
]
```

### 4. Validation Error Display

The component automatically displays validation errors:
- Server-side errors are shown in a summary at the top
- Individual field errors are displayed below each field
- Fields with errors get red borders

**Controller example:**
```php
$request->validate([
    'email' => 'required|email|unique:users',
    'first_name' => 'required|string|max:255'
]);
```

### 5. Success Messages

Display success messages after form submission:

```php
// Controller
return redirect()->back()->with('success', 'User created successfully!');
```

The component will display a green success banner automatically.

---

## Examples

### Example 1: Simple Contact Form

```blade
<x-dashboard.packages.form-builder
    :action="route('contact.submit')"
    method="POST"
    :formConfig="[
        'fields' => [
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Full Name',
                'required' => true,
                'grid' => 12
            ],
            [
                'name' => 'email',
                'type' => 'email',
                'label' => 'Email Address',
                'required' => true,
                'grid' => 6
            ],
            [
                'name' => 'phone',
                'type' => 'tel',
                'label' => 'Phone Number',
                'grid' => 6
            ],
            [
                'name' => 'message',
                'type' => 'textarea',
                'label' => 'Message',
                'placeholder' => 'Your message here...',
                'required' => true,
                'rows' => 5,
                'maxLength' => 1000,
                'grid' => 12
            ]
        ]
    ]"
/>
```

### Example 2: User Registration Form

```php
$formConfig = [
    'groups' => [
        [
            'title' => 'Account Information',
            'fields' => [
                [
                    'type' => 'text',
                    'name' => 'username',
                    'label' => 'Username',
                    'required' => true,
                    'borderColor' => '#6366f1',
                    'grid' => 6
                ],
                [
                    'type' => 'email',
                    'name' => 'email',
                    'label' => 'Email',
                    'required' => true,
                    'borderColor' => '#6366f1',
                    'grid' => 6
                ],
                [
                    'type' => 'password',
                    'name' => 'password',
                    'label' => 'Password',
                    'required' => true,
                    'borderColor' => '#6366f1',
                    'grid' => 6
                ],
                [
                    'type' => 'password',
                    'name' => 'password_confirmation',
                    'label' => 'Confirm Password',
                    'required' => true,
                    'borderColor' => '#6366f1',
                    'grid' => 6
                ]
            ]
        ],
        [
            'title' => 'Profile Information',
            'fields' => [
                [
                    'type' => 'image',
                    'name' => 'avatar',
                    'label' => 'Profile Picture',
                    'helperText' => 'JPG or PNG, max 2MB',
                    'borderColor' => '#8b5cf6',
                    'grid' => 12
                ],
                [
                    'type' => 'date',
                    'name' => 'birth_date',
                    'label' => 'Date of Birth',
                    'borderColor' => '#8b5cf6',
                    'grid' => 6
                ],
                [
                    'type' => 'select',
                    'name' => 'country',
                    'label' => 'Country',
                    'borderColor' => '#8b5cf6',
                    'grid' => 6,
                    'options' => [
                        ['value' => '', 'label' => 'Select country'],
                        ['value' => 'us', 'label' => 'United States'],
                        ['value' => 'uk', 'label' => 'United Kingdom'],
                        ['value' => 'ca', 'label' => 'Canada']
                    ]
                ]
            ]
        ],
        [
            'title' => 'Preferences',
            'fields' => [
                [
                    'type' => 'checkbox',
                    'name' => 'newsletter',
                    'label' => 'Subscribe to newsletter',
                    'borderColor' => '#10b981',
                    'grid' => 6,
                    'value' => true
                ],
                [
                    'type' => 'checkbox',
                    'name' => 'terms',
                    'label' => 'I agree to the Terms of Service',
                    'borderColor' => '#ef4444',
                    'required' => true,
                    'grid' => 6
                ]
            ]
        ]
    ]
];
```

```blade
<x-dashboard.packages.form-builder
    :action="route('users.store')"
    method="POST"
    :formConfig="$formConfig"
/>
```

### Example 3: Employee Edit Form

```php
// Controller
public function edit(Employee $employee)
{
    $formConfig = [
        'groups' => [
            [
                'title' => 'Personal Information',
                'fields' => [
                    [
                        'name' => 'first_name',
                        'type' => 'text',
                        'label' => 'First Name',
                        'value' => $employee->first_name,
                        'required' => true,
                        'grid' => 6
                    ],
                    [
                        'name' => 'last_name',
                        'type' => 'text',
                        'label' => 'Last Name',
                        'value' => $employee->last_name,
                        'required' => true,
                        'grid' => 6
                    ],
                    [
                        'name' => 'profile_picture',
                        'type' => 'image',
                        'label' => 'Profile Picture',
                        'value' => $employee->profile_picture_url,  // Shows current image
                        'grid' => 12
                    ]
                ]
            ],
            [
                'title' => 'Employment Details',
                'fields' => [
                    [
                        'name' => 'is_active',
                        'type' => 'radio',
                        'label' => 'Employment Status',
                        'layout' => 'row',
                        'options' => [
                            ['value' => '1', 'label' => 'Active'],
                            ['value' => '0', 'label' => 'Inactive']
                        ],
                        'value' => $employee->is_active ? '1' : '0',
                        'grid' => 12
                    ],
                    [
                        'name' => 'department_id',
                        'type' => 'select',
                        'label' => 'Department',
                        'value' => $employee->department_id,
                        'options' => $departments->map(fn($d) => [
                            'value' => $d->id,
                            'label' => $d->name
                        ])->toArray(),
                        'grid' => 6
                    ]
                ]
            ]
        ]
    ];

    return view('employees.edit', compact('employee', 'formConfig'));
}
```

```blade
<x-dashboard.packages.form-builder
    :action="route('employees.update', $employee)"
    method="PUT"
    :formConfig="$formConfig"
/>
```

### Example 4: Advanced Form with Color Picker

```php
$formConfig = [
    'fields' => [
        [
            'type' => 'text',
            'name' => 'category_name',
            'label' => 'Category Name',
            'required' => true,
            'grid' => 6
        ],
        [
            'type' => 'color',
            'name' => 'category_color',
            'label' => 'Category Color',
            'value' => '#6366f1',
            'required' => true,
            'grid' => 6
        ],
        [
            'type' => 'textarea',
            'name' => 'description',
            'label' => 'Description',
            'maxLength' => 500,
            'rows' => 4,
            'grid' => 12
        ]
    ]
];
```

---

## Best Practices

### 1. Organization
- Use **groups** for complex forms with multiple sections
- Keep related fields together
- Use descriptive labels and helper text

### 2. Grid Layout
- Mobile-first: Fields stack on mobile (col-span-12 takes effect)
- Use `grid: 6` for side-by-side fields on desktop
- Full width (`grid: 12`) for textareas and long text fields

### 3. Validation
- Always set `required: true` for mandatory fields
- Use Laravel validation rules in your controller
- The component handles error display automatically

### 4. Boolean Fields
- For yes/no choices, use **radio buttons** with values `'1'` and `'0'` (auto-converts to boolean)
- For single opt-in choices (like "agree to terms"), use **checkboxes**

### 5. Edit Forms
- Always pass existing values in the `value` property
- For file/image fields, pass the URL to display current file
- The component automatically handles `old()` input after validation errors

### 6. Styling
- Use `borderColor` to visually group related fields
- Choose colors that match your design system
- Use consistent colors across similar field groups

### 7. Accessibility
- Always provide clear labels
- Use `helperText` for additional context
- Mark required fields with `required: true`

### 8. File Uploads
- Specify `accept` attribute to limit file types
- Provide clear `helperText` about file requirements
- For images, always use `type: 'image'` for better UX

---

## Controller Integration

### Create Action

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'is_active' => 'required|boolean',  // From radio with values 1/0
        'terms' => 'required|boolean',      // From checkbox
        'profile_picture' => 'nullable|image|max:2048'
    ]);

    // Boolean fields come as 'true'/'false' strings, Laravel casts them automatically
    $user = User::create($validated);

    return redirect()->route('users.index')
        ->with('success', 'User created successfully!');
}
```

### Update Action

```php
public function update(Request $request, User $user)
{
    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'profile_picture' => 'nullable|image|max:2048'
    ]);

    $user->update($validated);

    return redirect()->back()
        ->with('success', 'User updated successfully!');
}
```

---

## Troubleshooting

### Issue: Checkbox submits as "on" instead of boolean

**Solution:** This is fixed in the latest version. Checkboxes now submit as `"true"` (checked) or `"false"` (unchecked).

### Issue: Radio buttons with values 1/0 fail boolean validation

**Solution:** This is fixed in the latest version. Radio button values `'1'`, `1`, `'0'`, `0` are automatically converted to `'true'`/`'false'`.

### Issue: File preview not showing in edit mode

**Solution:** Make sure you're passing the full URL in the `value` property:
```php
'value' => asset('storage/' . $user->profile_picture)
```

### Issue: Color picker not working

**Solution:** Ensure you're using `type: 'color'` (not `type: 'text'`) and the value is in hex format (e.g., `'#6366f1'`).

### Issue: Grid layout not responsive

**Solution:** The grid is mobile-first. On small screens, all fields take full width. Use `md:col-span-{n}` classes are applied automatically.

---

## Additional Resources

- [Laravel Validation Documentation](https://laravel.com/docs/validation)
- [Tailwind CSS Grid Documentation](https://tailwindcss.com/docs/grid-template-columns)
- [HTML Input Types Reference](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input)

---

## Support

For issues or questions:
1. Check this documentation
2. Review the examples above
3. Examine the component source code at `resources/views/components/dashboard/packages/form-builder.blade.php`

---

**Version:** 2.0
**Last Updated:** 2025-11-17
