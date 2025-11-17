<x-dashboard page-title="Dashboard Overview">

    @php


$formConfig = [
    'groups' => [
        [
            'title' => 'Personal Information',
            'fields' => [
                [
                    'type' => 'text',
                    'name' => 'first_name',
                    'label' => 'First Name',
                    'placeholder' => 'Enter your first name',
                    'borderColor' => '#6366f1',
                    'grid' => 6,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'name' => 'last_name',
                    'label' => 'Last Name',
                    'placeholder' => 'Enter your last name',
                    'borderColor' => '#6366f1',
                    'grid' => 6,
                    'required' => true
                ],
                [
                    'type' => 'email',
                    'name' => 'email',
                    'label' => 'Email Address',
                    'placeholder' => 'your.email@example.com',
                    'borderColor' => '#10b981',
                    'grid' => 8,
                    'required' => true
                ],
                [
                    'type' => 'tel',
                    'name' => 'phone',
                    'label' => 'Phone Number',
                    'placeholder' => '+1 (555) 123-4567',
                    'borderColor' => '#f59e0b',
                    'grid' => 4
                ],
                [
                    'type' => 'date',
                    'name' => 'birth_date',
                    'label' => 'Birth Date',
                    'borderColor' => '#8b5cf6',
                    'grid' => 6
                ],
                [
                    'type' => 'select',
                    'name' => 'gender',
                    'label' => 'Gender',
                    'borderColor' => '#ec4899',
                    'grid' => 6,
                    'options' => [
                        ['value' => '', 'label' => 'Select gender'],
                        ['value' => 'male', 'label' => 'Male'],
                        ['value' => 'female', 'label' => 'Female'],
                        ['value' => 'other', 'label' => 'Other'],
                        ['value' => 'prefer-not', 'label' => 'Prefer not to say']
                    ]
                ]
            ]
        ],
        [
            'title' => 'Profile & Documents',
            'fields' => [
                [
                    'type' => 'image',
                    'name' => 'profile_picture',
                    'label' => 'Profile Picture',
                    'borderColor' => '#06b6d4',
                    'grid' => 6,
                    'helperText' => 'PNG, JPG, GIF up to 5MB',
                    'required' => false
                ],
                [
                    'type' => 'file',
                    'name' => 'resume',
                    'label' => 'Upload Resume/CV',
                    'borderColor' => '#8b5cf6',
                    'grid' => 6,
                    'accept' => '.pdf,.doc,.docx',
                    'helperText' => 'PDF or Word documents only',
                    'required' => false
                ],
                [
                    'type' => 'textarea',
                    'name' => 'bio',
                    'label' => 'Biography',
                    'placeholder' => 'Tell us about yourself...',
                    'borderColor' => '#8b5cf6',
                    'grid' => 12,
                    'rows' => 5,
                    'maxLength' => 500
                ]
            ]
        ],
        [
            'title' => 'Preferences',
            'fields' => [
                [
                    'type' => 'radio',
                    'name' => 'subscription_plan',
                    'label' => 'Subscription Plan',
                    'borderColor' => '#06b6d4',
                    'grid' => 12,
                    'layout' => 'column',
                    'required' => true,
                    'options' => [
                        ['value' => 'free', 'label' => 'Free - Basic features with limited access'],
                        ['value' => 'pro', 'label' => 'Pro - Full access with priority support ($9.99/month)'],
                        ['value' => 'enterprise', 'label' => 'Enterprise - Custom solutions for teams ($49.99/month)']
                    ]
                ],
                [
                    'type' => 'radio',
                    'name' => 'contact_method',
                    'label' => 'Preferred Contact Method',
                    'borderColor' => '#f59e0b',
                    'grid' => 12,
                    'layout' => 'row',
                    'options' => [
                        ['value' => 'email', 'label' => 'Email'],
                        ['value' => 'phone', 'label' => 'Phone'],
                        ['value' => 'sms', 'label' => 'SMS'],
                        ['value' => 'whatsapp', 'label' => 'WhatsApp']
                    ]
                ],
                [
                    'type' => 'select',
                    'name' => 'timezone',
                    'label' => 'Timezone',
                    'borderColor' => '#8b5cf6',
                    'grid' => 6,
                    'options' => [
                        ['value' => '', 'label' => 'Select timezone'],
                        ['value' => 'est', 'label' => 'Eastern Time (ET)'],
                        ['value' => 'cst', 'label' => 'Central Time (CT)'],
                        ['value' => 'mst', 'label' => 'Mountain Time (MT)'],
                        ['value' => 'pst', 'label' => 'Pacific Time (PT)']
                    ]
                ],
                [
                    'type' => 'select',
                    'name' => 'language',
                    'label' => 'Preferred Language',
                    'borderColor' => '#10b981',
                    'grid' => 6,
                    'options' => [
                        ['value' => '', 'label' => 'Select language'],
                        ['value' => 'en', 'label' => 'English'],
                        ['value' => 'es', 'label' => 'Spanish'],
                        ['value' => 'fr', 'label' => 'French'],
                        ['value' => 'de', 'label' => 'German'],
                        ['value' => 'ar', 'label' => 'Arabic']
                    ]
                ]
            ]
        ],
        [
            'title' => 'Address Details',
            'fields' => [
                [
                    'type' => 'text',
                    'name' => 'street_address',
                    'label' => 'Street Address',
                    'placeholder' => '123 Main Street, Apt 4B',
                    'borderColor' => '#6366f1',
                    'grid' => 12,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'name' => 'city',
                    'label' => 'City',
                    'placeholder' => 'Enter your city',
                    'borderColor' => '#ef4444',
                    'grid' => 5,
                    'required' => true
                ],
                [
                    'type' => 'select',
                    'name' => 'state',
                    'label' => 'State/Province',
                    'borderColor' => '#f59e0b',
                    'grid' => 4,
                    'required' => true,
                    'options' => [
                        ['value' => '', 'label' => 'Select state'],
                        ['value' => 'ca', 'label' => 'California'],
                        ['value' => 'ny', 'label' => 'New York'],
                        ['value' => 'tx', 'label' => 'Texas'],
                        ['value' => 'fl', 'label' => 'Florida'],
                        ['value' => 'il', 'label' => 'Illinois']
                    ]
                ],
                [
                    'type' => 'text',
                    'name' => 'zip_code',
                    'label' => 'Zip Code',
                    'placeholder' => '12345',
                    'borderColor' => '#ec4899',
                    'grid' => 3,
                    'required' => true
                ],
                [
                    'type' => 'select',
                    'name' => 'country',
                    'label' => 'Country',
                    'borderColor' => '#14b8a6',
                    'grid' => 12,
                    'required' => true,
                    'options' => [
                        ['value' => '', 'label' => 'Select a country'],
                        ['value' => 'us', 'label' => 'United States'],
                        ['value' => 'uk', 'label' => 'United Kingdom'],
                        ['value' => 'ca', 'label' => 'Canada'],
                        ['value' => 'au', 'label' => 'Australia'],
                        ['value' => 'de', 'label' => 'Germany'],
                        ['value' => 'fr', 'label' => 'France']
                    ]
                ]
            ]
        ],
        [
            'title' => 'Additional Information',
            'fields' => [
                [
                    'type' => 'textarea',
                    'name' => 'comments',
                    'label' => 'Additional Comments',
                    'placeholder' => 'Any additional information you\'d like to share...',
                    'borderColor' => '#8b5cf6',
                    'grid' => 12,
                    'rows' => 4,
                    'maxLength' => 1000
                ],
                [
                    'type' => 'select',
                    'name' => 'hear_about',
                    'label' => 'How did you hear about us?',
                    'borderColor' => '#06b6d4',
                    'grid' => 12,
                    'options' => [
                        ['value' => '', 'label' => 'Please select an option'],
                        ['value' => 'search', 'label' => 'Search Engine (Google, Bing, etc.)'],
                        ['value' => 'social', 'label' => 'Social Media'],
                        ['value' => 'friend', 'label' => 'Friend or Colleague'],
                        ['value' => 'advertisement', 'label' => 'Advertisement'],
                        ['value' => 'blog', 'label' => 'Blog or Article'],
                        ['value' => 'other', 'label' => 'Other']
                    ]
                ]
            ]
        ],
        [
            'title' => 'Customization & Settings',
            'fields' => [
                [
                    'type' => 'color',
                    'name' => 'theme_color',
                    'label' => 'Primary Theme Color',
                    'value' => '#6366f1',
                    'grid' => 6,
                    'required' => false
                ],
                [
                    'type' => 'color',
                    'name' => 'accent_color',
                    'label' => 'Accent Color',
                    'value' => '#ec4899',
                    'grid' => 6,
                    'required' => false
                ],
                [
                    'type' => 'radio',
                    'name' => 'email_notifications',
                    'label' => 'Email Notifications',
                    'layout' => 'row',
                    'grid' => 12,
                    'options' => [
                        ['value' => '1', 'label' => 'Enabled'],
                        ['value' => '0', 'label' => 'Disabled']
                    ],
                    'value' => '1'
                ]
            ]
        ],
        [
            'title' => 'Agreements & Consent',
            'fields' => [
                [
                    'type' => 'checkbox',
                    'name' => 'newsletter',
                    'label' => 'Subscribe to our newsletter for updates and exclusive offers',
                    'borderColor' => '#10b981',
                    'grid' => 6,
                    'value' => true
                ],
                [
                    'type' => 'checkbox',
                    'name' => 'marketing',
                    'label' => 'I agree to receive marketing communications',
                    'borderColor' => '#06b6d4',
                    'grid' => 6,
                    'value' => false
                ],
                [
                    'type' => 'checkbox',
                    'name' => 'terms',
                    'label' => 'I agree to the Terms of Service and Privacy Policy',
                    'borderColor' => '#6366f1',
                    'grid' => 12,
                    'required' => true
                ]
            ]
        ]
    ]
];
    @endphp
    <x-dashboard.packages.form-builder  :formConfig="$formConfig"/>
</x-dashboard>