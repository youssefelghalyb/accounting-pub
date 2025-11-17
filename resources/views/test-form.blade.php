<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Form Builder</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .animate-slide-in {
            animation: slideIn 0.4s ease-out;
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        .form-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Custom Checkbox Styles */
        .custom-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 5px;
            background-color: white;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .custom-checkbox:hover {
            border-color: #9ca3af;
            transform: scale(1.05);
        }

        .custom-checkbox:checked {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-color: #6366f1;
        }

        .custom-checkbox:checked::after {
            content: '';
            position: absolute;
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .custom-checkbox:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        /* Custom Radio Styles */
        .custom-radio {
            appearance: none;
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            background-color: white;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .custom-radio:hover {
            border-color: #9ca3af;
            transform: scale(1.05);
        }

        .custom-radio:checked {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-color: #6366f1;
        }

        .custom-radio:checked::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: white;
        }

        .custom-radio:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        /* Glass morphism effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 via-gray-50 to-slate-100">
    <!-- Compact Header -->
    <div class="form-gradient py-6 shadow-lg">
        <div class="max-w-5xl mx-auto px-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">Dynamic Form Builder</h1>
                <p class="text-indigo-100 text-sm mt-1">Professional form generation made simple</p>
            </div>
            <div class="hidden sm:flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-white text-sm font-medium">Auto-save enabled</span>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="max-w-5xl mx-auto px-6 py-8">
        <div class="glass-effect rounded-2xl shadow-xl p-6 lg:p-8">
            <form id="dynamicForm" class="space-y-6"></form>
        </div>

        <!-- Output Section -->
        <div id="output" class="mt-6 hidden animate-fade-in">
            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl shadow-lg p-6 border border-emerald-200">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-emerald-900">Form Submitted Successfully</h2>
                </div>
                <div class="bg-slate-900 rounded-xl p-5 overflow-x-auto shadow-inner">
                    <pre id="formData" class="text-emerald-400 text-xs font-mono leading-relaxed"></pre>
                </div>
            </div>
        </div>
    </div>



    <script>
        // Form Builder Class
        class FormBuilder {
            constructor(formElement) {
                this.form = formElement;
                this.fields = [];
                this.groups = [];
            }

            // Define form fields (can be flat array or grouped)
            defineFields(fieldsArray) {
                this.fields = fieldsArray;
                return this;
            }

            // Define form groups
            defineGroups(groupsArray) {
                this.groups = groupsArray;
                return this;
            }

            // Build the form
            build() {
                this.form.innerHTML = '';
                this.form.className = 'space-y-6';
                
                // If groups are defined, use grouped layout
                if (this.groups.length > 0) {
                    this.buildGroupedForm();
                } else {
                    // Use flat layout
                    this.buildFlatForm(this.fields);
                }

                // Add submit button
                const submitBtn = document.createElement('button');
                submitBtn.type = 'submit';
                submitBtn.className = 'w-full sm:w-auto px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2';
                submitBtn.innerHTML = `
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Submit Form
                    </span>
                `;
                
                const buttonWrapper = document.createElement('div');
                buttonWrapper.className = 'pt-6 mt-6 border-t border-gray-200 flex justify-between items-center';
                
                const buttonContainer = document.createElement('div');
                buttonContainer.className = 'flex gap-3';
                buttonContainer.appendChild(submitBtn);
                
                // Add reset button
                const resetBtn = document.createElement('button');
                resetBtn.type = 'button';
                resetBtn.className = 'px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2';
                resetBtn.textContent = 'Reset';
                resetBtn.addEventListener('click', () => this.form.reset());
                buttonContainer.appendChild(resetBtn);
                
                buttonWrapper.appendChild(buttonContainer);
                
                // Add field count
                const fieldCount = document.createElement('div');
                fieldCount.className = 'hidden sm:block text-sm text-gray-500';
                const totalFields = this.groups.length > 0 
                    ? this.groups.reduce((acc, group) => acc + group.fields.length, 0)
                    : this.fields.length;
                fieldCount.innerHTML = `<span class="font-medium">${totalFields}</span> fields`;
                buttonWrapper.appendChild(fieldCount);
                
                this.form.appendChild(buttonWrapper);

                // Add form submit handler
                this.form.addEventListener('submit', this.handleSubmit.bind(this));
            }

            // Build grouped form
            buildGroupedForm() {
                this.groups.forEach((group, index) => {
                    const section = document.createElement('div');
                    section.className = 'space-y-4 animate-slide-in';
                    section.style.animationDelay = `${index * 0.05}s`;
                    
                    // Add group title if provided
                    if (group.title) {
                        const titleWrapper = document.createElement('div');
                        titleWrapper.className = 'border-l-4 border-indigo-500 pl-4 py-2 bg-gradient-to-r from-indigo-50/50 to-transparent';
                        
                        const title = document.createElement('h3');
                        title.className = 'text-lg font-bold text-gray-800 flex items-center gap-2';
                        title.innerHTML = `
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            ${group.title}
                        `;
                        titleWrapper.appendChild(title);
                        section.appendChild(titleWrapper);
                    }

                    // Build fields for this group
                    const fieldsContainer = document.createElement('div');
                    fieldsContainer.className = 'space-y-4';
                    this.buildFlatForm(group.fields, fieldsContainer);
                    section.appendChild(fieldsContainer);
                    
                    this.form.appendChild(section);
                });
            }

            // Build flat form layout
            buildFlatForm(fields, container = this.form) {
                // Group fields by rows based on grid spans
                let currentRow = [];
                let currentSpan = 0;

                fields.forEach((field, index) => {
                    const gridSpan = field.grid || 12;
                    
                    // If adding this field exceeds 12 columns, start a new row
                    if (currentSpan + gridSpan > 12) {
                        this.createRow(currentRow, container);
                        currentRow = [];
                        currentSpan = 0;
                    }
                    
                    currentRow.push(field);
                    currentSpan += gridSpan;
                    
                    // If we've reached exactly 12 or it's the last field, create the row
                    if (currentSpan === 12 || index === fields.length - 1) {
                        this.createRow(currentRow, container);
                        currentRow = [];
                        currentSpan = 0;
                    }
                });
            }

            // Create a row with fields
            createRow(fields, container) {
                const row = document.createElement('div');
                row.className = 'grid grid-cols-12 gap-4';

                fields.forEach(field => {
                    const formGroup = this.createFormGroup(field);
                    row.appendChild(formGroup);
                });

                container.appendChild(row);
            }

            // Create individual form group
            createFormGroup(field) {
                const formGroup = document.createElement('div');
                const gridSpan = field.grid || 12;
                formGroup.className = `col-span-12 md:col-span-${gridSpan}`;

                // Create label (for non-checkbox/radio fields)
                if (field.label && field.type !== 'checkbox' && field.type !== 'radio') {
                    const label = document.createElement('label');
                    label.htmlFor = field.name;
                    label.className = 'block text-sm font-semibold text-gray-700 mb-2';
                    label.textContent = field.label;
                    if (field.required) {
                        const required = document.createElement('span');
                        required.className = 'text-red-500 ml-1';
                        required.textContent = '*';
                        label.appendChild(required);
                    }
                    formGroup.appendChild(label);
                }

                // Create input element
                let input;
                const baseClasses = 'w-full px-4 py-2.5 rounded-lg border border-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-opacity-50 text-sm';
                
                if (field.type === 'textarea') {
                    input = document.createElement('textarea');
                    input.className = `${baseClasses} resize-none min-h-[100px]`;
                } else if (field.type === 'select') {
                    input = document.createElement('select');
                    input.className = `${baseClasses} cursor-pointer bg-white`;
                    if (field.options) {
                        field.options.forEach(option => {
                            const opt = document.createElement('option');
                            opt.value = option.value || option;
                            opt.textContent = option.label || option;
                            input.appendChild(opt);
                        });
                    }
                } else if (field.type === 'checkbox') {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-150';
                    
                    input = document.createElement('input');
                    input.type = 'checkbox';
                    input.className = 'custom-checkbox mt-0.5';
                    
                    if (field.borderColor) {
                        input.style.setProperty('--custom-color', field.borderColor);
                        input.addEventListener('change', (e) => {
                            if (e.target.checked) {
                                e.target.style.background = field.borderColor;
                                e.target.style.borderColor = field.borderColor;
                            } else {
                                e.target.style.background = 'white';
                                e.target.style.borderColor = '#d1d5db';
                            }
                        });
                    }
                    
                    const checkLabel = document.createElement('label');
                    checkLabel.htmlFor = field.name;
                    checkLabel.className = 'text-sm text-gray-700 cursor-pointer select-none flex-1 leading-snug';
                    checkLabel.textContent = field.label || '';
                    
                    if (field.required) {
                        const required = document.createElement('span');
                        required.className = 'text-red-500 ml-1 font-semibold';
                        required.textContent = '*';
                        checkLabel.appendChild(required);
                    }
                    
                    wrapper.appendChild(input);
                    wrapper.appendChild(checkLabel);
                    formGroup.innerHTML = '';
                    formGroup.appendChild(wrapper);
                } else if (field.type === 'radio') {
                    // Radio buttons need special handling for groups
                    const wrapper = document.createElement('div');
                    wrapper.className = 'space-y-2';
                    
                    if (field.label) {
                        const label = document.createElement('div');
                        label.className = 'text-sm font-semibold text-gray-700 mb-3';
                        label.textContent = field.label;
                        if (field.required) {
                            const required = document.createElement('span');
                            required.className = 'text-red-500 ml-1';
                            required.textContent = '*';
                            label.appendChild(required);
                        }
                        wrapper.appendChild(label);
                    }
                    
                    if (field.options) {
                        const radioInputs = [];
                        
                        field.options.forEach((option, idx) => {
                            const radioWrapper = document.createElement('div');
                            radioWrapper.className = 'flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-150';
                            
                            const radio = document.createElement('input');
                            radio.type = 'radio';
                            radio.name = field.name;
                            radio.id = `${field.name}_${idx}`;
                            radio.value = option.value || option;
                            radio.className = 'custom-radio';
                            radio.required = field.required;
                            
                            radioInputs.push(radio);
                            
                            if (field.borderColor) {
                                radio.addEventListener('change', (e) => {
                                    // Reset all radio buttons in this group to default style
                                    radioInputs.forEach(r => {
                                        if (r !== e.target) {
                                            r.style.background = 'white';
                                            r.style.borderColor = '#d1d5db';
                                        }
                                    });
                                    
                                    // Apply custom color to the checked radio
                                    if (e.target.checked) {
                                        e.target.style.background = field.borderColor;
                                        e.target.style.borderColor = field.borderColor;
                                    }
                                });
                            }
                            
                            const radioLabel = document.createElement('label');
                            radioLabel.htmlFor = `${field.name}_${idx}`;
                            radioLabel.className = 'text-sm text-gray-700 cursor-pointer select-none flex-1 leading-snug';
                            radioLabel.textContent = option.label || option;
                            
                            radioWrapper.appendChild(radio);
                            radioWrapper.appendChild(radioLabel);
                            wrapper.appendChild(radioWrapper);
                        });
                    }
                    
                    formGroup.innerHTML = '';
                    formGroup.appendChild(wrapper);
                    return formGroup;
                } else {
                    input = document.createElement('input');
                    input.type = field.type || 'text';
                    input.className = baseClasses;
                }

                // Set common attributes (for non-checkbox/radio fields)
                if (field.type !== 'checkbox' && field.type !== 'radio') {
                    input.name = field.name;
                    input.id = field.name;
                    
                    if (field.placeholder) {
                        input.placeholder = field.placeholder;
                    }
                    
                    // Apply border color and focus ring color
                    if (field.borderColor) {
                        input.style.borderColor = field.borderColor;
                        input.addEventListener('focus', () => {
                            input.style.borderColor = field.borderColor;
                            input.style.boxShadow = `0 0 0 3px ${field.borderColor}15`;
                        });
                        input.addEventListener('blur', () => {
                            input.style.boxShadow = '';
                            input.style.borderColor = field.borderColor;
                        });
                    } else {
                        input.classList.add('focus:border-indigo-500', 'focus:ring-indigo-500');
                    }
                    
                    if (field.required) {
                        input.required = true;
                    }

                    if (field.value) {
                        input.value = field.value;
                    }

                    formGroup.appendChild(input);
                } else if (field.type === 'checkbox') {
                    // For checkbox, set ID for the input inside wrapper
                    input.name = field.name;
                    input.id = field.name;
                }

                return formGroup;
            }

            // Handle form submission
            handleSubmit(e) {
                e.preventDefault();
                const formData = new FormData(this.form);
                const data = {};
                
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }

                // Display the form data
                const output = document.getElementById('output');
                const formDataPre = document.getElementById('formData');
                formDataPre.textContent = JSON.stringify(data, null, 2);
                output.classList.remove('hidden');

                // Scroll to output
                setTimeout(() => {
                    output.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 100);

                console.log('Form Data:', data);
                return data;
            }
        }

        // ============================================
        // EXAMPLE 1: FLAT FORM (No Groups)
        // ============================================
        const flatFormFields = [
            {
                type: 'text',
                name: 'firstName',
                label: 'First Name',
                placeholder: 'Enter your first name',
                borderColor: '#6366f1',
                grid: 6,
                required: true
            },
            {
                type: 'text',
                name: 'lastName',
                label: 'Last Name',
                placeholder: 'Enter your last name',
                borderColor: '#6366f1',
                grid: 6,
                required: true
            },
            {
                type: 'email',
                name: 'email',
                label: 'Email Address',
                placeholder: 'your.email@example.com',
                borderColor: '#10b981',
                grid: 12,
                required: true
            }
        ];

        // ============================================
        // EXAMPLE 2: GROUPED FORM WITH ALL FIELD TYPES
        // ============================================
        const groupedFormFields = [
            {
                title: 'Personal Information',
                fields: [
                    {
                        type: 'text',
                        name: 'firstName',
                        label: 'First Name',
                        placeholder: 'Enter your first name',
                        borderColor: '#6366f1',
                        grid: 6,
                        required: true
                    },
                    {
                        type: 'text',
                        name: 'lastName',
                        label: 'Last Name',
                        placeholder: 'Enter your last name',
                        borderColor: '#6366f1',
                        grid: 6,
                        required: true
                    },
                    {
                        type: 'email',
                        name: 'email',
                        label: 'Email Address',
                        placeholder: 'your.email@example.com',
                        borderColor: '#10b981',
                        grid: 8,
                        required: true
                    },
                    {
                        type: 'tel',
                        name: 'phone',
                        label: 'Phone Number',
                        placeholder: '+1 (555) 123-4567',
                        borderColor: '#f59e0b',
                        grid: 4
                    },
                    {
                        type: 'date',
                        name: 'birthdate',
                        label: 'Birth Date',
                        borderColor: '#8b5cf6',
                        grid: 6
                    },
                    {
                        type: 'select',
                        name: 'gender',
                        label: 'Gender',
                        borderColor: '#ec4899',
                        grid: 6,
                        options: [
                            { value: '', label: 'Select gender' },
                            { value: 'male', label: 'Male' },
                            { value: 'female', label: 'Female' },
                            { value: 'other', label: 'Other' },
                            { value: 'prefer-not', label: 'Prefer not to say' }
                        ]
                    }
                ]
            },
            {
                title: 'Preferences',
                fields: [
                    {
                        type: 'radio',
                        name: 'subscription',
                        label: 'Subscription Plan',
                        borderColor: '#06b6d4',
                        grid: 12,
                        required: true,
                        options: [
                            { value: 'free', label: 'Free - Basic features with limited access' },
                            { value: 'pro', label: 'Pro - Full access with priority support ($9.99/month)' },
                            { value: 'enterprise', label: 'Enterprise - Custom solutions for teams ($49.99/month)' }
                        ]
                    },
                    {
                        type: 'radio',
                        name: 'contactMethod',
                        label: 'Preferred Contact Method',
                        borderColor: '#f59e0b',
                        grid: 6,
                        options: [
                            { value: 'email', label: 'Email' },
                            { value: 'phone', label: 'Phone' },
                            { value: 'sms', label: 'SMS' }
                        ]
                    },
                    {
                        type: 'select',
                        name: 'timezone',
                        label: 'Timezone',
                        borderColor: '#8b5cf6',
                        grid: 6,
                        options: [
                            { value: '', label: 'Select timezone' },
                            { value: 'est', label: 'Eastern Time (ET)' },
                            { value: 'cst', label: 'Central Time (CT)' },
                            { value: 'mst', label: 'Mountain Time (MT)' },
                            { value: 'pst', label: 'Pacific Time (PT)' }
                        ]
                    }
                ]
            },
            {
                title: 'Address Details',
                fields: [
                    {
                        type: 'text',
                        name: 'street',
                        label: 'Street Address',
                        placeholder: '123 Main Street, Apt 4B',
                        borderColor: '#6366f1',
                        grid: 12,
                        required: true
                    },
                    {
                        type: 'text',
                        name: 'city',
                        label: 'City',
                        placeholder: 'Enter your city',
                        borderColor: '#ef4444',
                        grid: 5,
                        required: true
                    },
                    {
                        type: 'select',
                        name: 'state',
                        label: 'State',
                        borderColor: '#f59e0b',
                        grid: 4,
                        options: [
                            { value: '', label: 'Select state' },
                            { value: 'ca', label: 'California' },
                            { value: 'ny', label: 'New York' },
                            { value: 'tx', label: 'Texas' },
                            { value: 'fl', label: 'Florida' }
                        ]
                    },
                    {
                        type: 'text',
                        name: 'zipCode',
                        label: 'Zip Code',
                        placeholder: '12345',
                        borderColor: '#ec4899',
                        grid: 3,
                        required: true
                    }
                ]
            },
            {
                title: 'Additional Information',
                fields: [
                    {
                        type: 'textarea',
                        name: 'message',
                        label: 'Message',
                        placeholder: 'Tell us more about yourself...',
                        borderColor: '#8b5cf6',
                        grid: 12
                    }
                ]
            },
            {
                fields: [
                    {
                        type: 'checkbox',
                        name: 'newsletter',
                        label: 'Subscribe to newsletter for updates and exclusive offers',
                        borderColor: '#10b981',
                        grid: 6
                    },
                    {
                        type: 'checkbox',
                        name: 'marketing',
                        label: 'Receive marketing communications',
                        borderColor: '#06b6d4',
                        grid: 6
                    },
                    {
                        type: 'checkbox',
                        name: 'terms',
                        label: 'I agree to the Terms of Service and Privacy Policy',
                        borderColor: '#6366f1',
                        grid: 12,
                        required: true
                    }
                ]
            }
        ];

        // ============================================
        // Initialize the form builder
        // ============================================
        const formElement = document.getElementById('dynamicForm');
        const formBuilder = new FormBuilder(formElement);
        
        // OPTION 1: Build flat form (uncomment to use)
        // formBuilder.defineFields(flatFormFields).build();
        
        // OPTION 2: Build grouped form (currently active)
        formBuilder.defineGroups(groupedFormFields).build();
    </script>
</body>
</html>