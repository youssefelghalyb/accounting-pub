@props(['formConfig' => null, 'action' => '#', 'method' => 'POST'])

<!-- Main Container -->
<div class="max-w-7xl mx-auto px-6 py-8">
    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session()->has('errors') && session('errors')->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <p class="text-red-800 font-medium mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                        @foreach (session('errors')->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
    <div class="glass-effect rounded-2xl shadow-xl p-6 lg:p-8">
        <form id="dynamicForm" action="{{ $action }}" method="{{ $method }}" enctype="multipart/form-data"
            class="space-y-6">
            @csrf
        </form>
    </div>

    <!-- Output Section -->
    <div id="output" class="mt-6 hidden animate-fade-in">
        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl shadow-lg p-6 border border-emerald-200">
            <div class="flex items-center gap-3 mb-4">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7">
                        </path>
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



@push('styles')
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
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
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

        /* File Upload Styles */
        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-upload-input {
            position: absolute;
            left: -9999px;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 3rem 1.5rem;
            border: 2px dashed #d1d5db;
            border-radius: 0.75rem;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            border-color: #9ca3af;
            background: #f3f4f6;
        }

        .file-upload-label.has-file {
            border-color: #10b981;
            background: #d1fae5;
        }

        .file-upload-label.has-file:hover {
            background: #a7f3d0;
        }

        .file-preview {
            margin-top: 0.75rem;
            padding: 0.75rem;
            background: #f9fafb;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }

        .file-preview-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
        }

        /* Image Upload Specific */
        .image-upload-preview {
            position: relative;
            display: inline-block;
        }

        .image-remove-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            width: 2rem;
            height: 2rem;
            background: rgba(239, 68, 68, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .image-remove-btn:hover {
            background: rgba(220, 38, 38, 1);
            transform: scale(1.1);
        }

        /* Textarea Character Counter */
        .textarea-wrapper {
            position: relative;
        }

        .char-counter {
            position: absolute;
            bottom: 0.75rem;
            right: 0.75rem;
            font-size: 0.75rem;
            color: #9ca3af;
            background: rgba(255, 255, 255, 0.9);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            pointer-events: none;
        }

        .char-counter.limit-reached {
            color: #ef4444;
            font-weight: 600;
        }

        /* Glass morphism effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Form (Build)er Class
        class FormBuilder {
            constructor(formElement) {
                this.form = formElement;
                this.fields = [];
                this.groups = [];
                this.errors = @json(session()->has('errors') ? session('errors')->getBag('default')->toArray() : []);
                this.oldInput = @json(old());
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
            // Build the form
            build() {
                // Save the CSRF token before clearing
                const csrfToken = this.form.querySelector('input[name="_token"]');
                const methodField = this.form.querySelector('input[name="_method"]');

                this.form.innerHTML = '';
                this.form.className = 'space-y-6';

                // Re-add CSRF token at the beginning
                if (csrfToken) {
                    this.form.appendChild(csrfToken);
                }
                if (methodField) {
                    this.form.appendChild(methodField);
                }

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
                submitBtn.className =
                    'w-full sm:w-auto px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2';
                submitBtn.innerHTML = `
        <span class="flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ __('common.save') }}
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
                resetBtn.className =
                    'px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2';
                resetBtn.textContent = '{{ __('common.cancel') }}';
                resetBtn.addEventListener('click', () => this.form.reset());
                buttonContainer.appendChild(resetBtn);

                buttonWrapper.appendChild(buttonContainer);

                // Add field count
                const fieldCount = document.createElement('div');
                fieldCount.className = 'hidden sm:block text-sm text-gray-500';
                const totalFields = this.groups.length > 0 ?
                    this.groups.reduce((acc, group) => acc + group.fields.length, 0) :
                    this.fields.length;
                fieldCount.innerHTML = `<span class="font-medium">${totalFields}</span> fields`;
                buttonWrapper.appendChild(fieldCount);

                this.form.appendChild(buttonWrapper);
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
                        titleWrapper.className =
                            'border-l-4 border-indigo-500 pl-4 py-2 bg-gradient-to-r from-indigo-50/50 to-transparent';

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
            // Create individual form group
            createFormGroup(field) {
                const formGroup = document.createElement('div');
                const gridSpan = field.grid || 12;
                formGroup.className = `col-span-12 md:col-span-${gridSpan}`;

                // Create label (for non-checkbox/radio/file/image fields)
                if (field.label && field.type !== 'checkbox' && field.type !== 'radio' && field.type !== 'file' && field
                    .type !== 'image') {

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
                const baseClasses =
                    'w-full px-4 py-2.5 rounded-lg border border-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-opacity-50 text-sm';

                if (field.type === 'textarea') {
                    // Create wrapper for textarea with character counter
                    const textareaWrapper = document.createElement('div');
                    textareaWrapper.className = 'textarea-wrapper';

                    input = document.createElement('textarea');
                    input.className = `${baseClasses} resize-none min-h-[100px] pr-20`;
                    input.rows = field.rows || 4;

                    // Add character counter if maxLength is specified
                    if (field.maxLength) {
                        input.maxLength = field.maxLength;

                        const charCounter = document.createElement('div');
                        charCounter.className = 'char-counter';
                        charCounter.textContent = `0 / ${field.maxLength}`;

                        input.addEventListener('input', (e) => {
                            const length = e.target.value.length;
                            charCounter.textContent = `${length} / ${field.maxLength}`;

                            if (length >= field.maxLength) {
                                charCounter.classList.add('limit-reached');
                            } else {
                                charCounter.classList.remove('limit-reached');
                            }
                        });

                        textareaWrapper.appendChild(input);
                        textareaWrapper.appendChild(charCounter);

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

                        if (field.placeholder) {
                            input.placeholder = field.placeholder;
                        }

                        input.name = field.name;
                        input.id = field.name;

                        if (field.required) {
                            input.required = true;
                        }

                        // Use old input if available, otherwise use field value
                        const fieldValue = this.oldInput && this.oldInput[field.name] !== undefined
                            ? this.oldInput[field.name]
                            : field.value;

                        if (fieldValue) {
                            input.value = fieldValue;
                            // Update character counter if present
                            if (field.maxLength) {
                                const event = new Event('input');
                                input.dispatchEvent(event);
                            }
                        }

                        formGroup.appendChild(textareaWrapper);
                        return formGroup;
                    }
                } else if (field.type === 'file' || field.type === 'image') {
                    // Add label for file/image fields
                    if (field.label) {
                        const label = document.createElement('label');
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

                    // File/Image upload with modern design
                    const fileWrapper = document.createElement('div');
                    fileWrapper.className = 'file-upload-wrapper';

                    input = document.createElement('input');
                    input.type = 'file';
                    input.className = 'file-upload-input';
                    input.id = field.name;
                    input.name = field.name;

                    // Set accept attribute for images
                    if (field.type === 'image') {
                        input.accept = 'image/*';
                    } else if (field.accept) {
                        input.accept = field.accept;
                    }

                    if (field.multiple) {
                        input.multiple = true;
                    }

                    if (field.required) {
                        input.required = true;
                    }

                    const uploadLabel = document.createElement('label');
                    uploadLabel.htmlFor = field.name;
                    uploadLabel.className = 'file-upload-label';

                    // Apply custom border color if specified
                    if (field.borderColor) {
                        uploadLabel.style.borderColor = field.borderColor;
                    }

                    uploadLabel.innerHTML = `
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <div>
                <p class="text-sm font-semibold text-gray-700">${field.type === 'image' ? 'Click to upload image' : 'Click to upload file'}</p>
                <p class="text-xs text-gray-500 mt-1">${field.helperText || (field.type === 'image' ? 'PNG, JPG, GIF up to 10MB' : 'Any file type')}</p>
            </div>
        `;

                    const previewContainer = document.createElement('div');
                    previewContainer.className = 'file-preview hidden';

                    // Handle file selection
                    input.addEventListener('change', (e) => {
                        const files = e.target.files;
                        if (files && files.length > 0) {
                            uploadLabel.classList.add('has-file');

                            // Apply custom color when file is selected
                            if (field.borderColor) {
                                uploadLabel.style.borderColor = field.borderColor;
                                uploadLabel.style.backgroundColor = `${field.borderColor}10`;
                            }

                            previewContainer.classList.remove('hidden');
                            previewContainer.innerHTML = '';

                            Array.from(files).forEach((file, index) => {
                                if (field.type === 'image' && file.type.startsWith('image/')) {
                                    // Image preview
                                    const reader = new FileReader();
                                    reader.onload = (event) => {
                                        const imagePreviewWrapper = document.createElement('div');
                                        imagePreviewWrapper.className = 'image-upload-preview';

                                        const img = document.createElement('img');
                                        img.src = event.target.result;
                                        img.className = 'file-preview-image';

                                        const removeBtn = document.createElement('div');
                                        removeBtn.className = 'image-remove-btn';
                                        removeBtn.innerHTML = `
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            `;
                                        removeBtn.addEventListener('click', () => {
                                            input.value = '';
                                            uploadLabel.classList.remove('has-file');
                                            uploadLabel.style.borderColor = field
                                                .borderColor || '#d1d5db';
                                            uploadLabel.style.backgroundColor = '';
                                            previewContainer.classList.add('hidden');
                                            previewContainer.innerHTML = '';
                                        });

                                        imagePreviewWrapper.appendChild(img);
                                        imagePreviewWrapper.appendChild(removeBtn);
                                        previewContainer.appendChild(imagePreviewWrapper);
                                    };
                                    reader.readAsDataURL(file);
                                } else {
                                    // File preview (name and size)
                                    const fileInfo = document.createElement('div');
                                    fileInfo.className =
                                        'flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200 mb-2';
                                    fileInfo.innerHTML = `
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">${file.name}</p>
                                    <p class="text-xs text-gray-500">${(file.size / 1024).toFixed(2)} KB</p>
                                </div>
                            </div>
                            <button type="button" class="text-red-500 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        `;

                                    fileInfo.querySelector('button').addEventListener('click', () => {
                                        input.value = '';
                                        uploadLabel.classList.remove('has-file');
                                        uploadLabel.style.borderColor = field.borderColor ||
                                            '#d1d5db';
                                        uploadLabel.style.backgroundColor = '';
                                        previewContainer.classList.add('hidden');
                                        previewContainer.innerHTML = '';
                                    });

                                    previewContainer.appendChild(fileInfo);
                                }
                            });
                        }
                    });

                    fileWrapper.appendChild(input);
                    fileWrapper.appendChild(uploadLabel);
                    formGroup.appendChild(fileWrapper);

                    // Show existing file/image preview if value exists (edit mode)
                    if (field.value) {
                        const existingPreviewContainer = document.createElement('div');
                        existingPreviewContainer.className = 'mt-3';

                        const existingLabel = document.createElement('div');
                        existingLabel.className = 'text-xs font-semibold text-gray-600 mb-2';
                        existingLabel.textContent = 'Current File:';
                        existingPreviewContainer.appendChild(existingLabel);

                        if (field.type === 'image') {
                            // Display existing image preview
                            const existingImageWrapper = document.createElement('div');
                            existingImageWrapper.className = 'relative inline-block';

                            const existingImage = document.createElement('img');
                            existingImage.src = field.value;
                            existingImage.className = 'max-w-xs max-h-48 rounded-lg border-2 border-gray-200 shadow-sm';
                            existingImage.alt = 'Current image';

                            existingImageWrapper.appendChild(existingImage);
                            existingPreviewContainer.appendChild(existingImageWrapper);
                        } else {
                            // Display existing file link
                            const existingFileLink = document.createElement('a');
                            existingFileLink.href = field.value;
                            existingFileLink.target = '_blank';
                            existingFileLink.className =
                                'inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition-colors';
                            existingFileLink.innerHTML = `
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-indigo-700">View Current File</span>
                            `;
                            existingPreviewContainer.appendChild(existingFileLink);
                        }

                        formGroup.appendChild(existingPreviewContainer);
                    }

                    formGroup.appendChild(previewContainer);

                    return formGroup;
                } else if (field.type === 'select') {
                    input = document.createElement('select');
                    input.className = `${baseClasses} cursor-pointer bg-white`;

                    // Get the value to use (old input or field value)
                    const selectedValue = this.oldInput && this.oldInput[field.name] !== undefined
                        ? this.oldInput[field.name]
                        : field.value;

                    if (field.options) {
                        field.options.forEach(option => {
                            const opt = document.createElement('option');
                            opt.value = option.value || option;
                            opt.textContent = option.label || option;

                            // Set selected if this matches the current value
                            if (selectedValue && opt.value == selectedValue) {
                                opt.selected = true;
                            }

                            input.appendChild(opt);
                        });
                    }
                } else if (field.type === 'checkbox') {
                    const wrapper = document.createElement('div');
                    wrapper.className =
                        'flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-150';

                    // Add hidden input with value "false" to ensure unchecked checkboxes submit as false
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = field.name;
                    hiddenInput.value = 'false';
                    wrapper.appendChild(hiddenInput);

                    input = document.createElement('input');
                    input.type = 'checkbox';
                    input.className = 'custom-checkbox mt-0.5';
                    input.value = 'true'; // Set value to "true" for checked state

                    // Check if old input exists and set checked state
                    const oldValue = this.oldInput && this.oldInput[field.name] !== undefined
                        ? this.oldInput[field.name]
                        : field.value;

                    // Handle both boolean and string values
                    if (oldValue === true || oldValue === 'true' || oldValue === '1' || oldValue === 1) {
                        input.checked = true;
                    }

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
                    // Radio buttons with layout control (row or column)
                    const wrapper = document.createElement('div');
                    const layout = field.layout || 'column'; // Default to column layout

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

                        // Create container for radio options based on layout
                        const optionsContainer = document.createElement('div');
                        if (layout === 'row') {
                            // Horizontal layout with flex-wrap
                            optionsContainer.className = 'flex flex-wrap gap-3';
                        } else {
                            // Vertical layout (default)
                            optionsContainer.className = 'space-y-2';
                        }

                        field.options.forEach((option, idx) => {
                            const radioWrapper = document.createElement('div');
                            if (layout === 'row') {
                                radioWrapper.className =
                                    'flex items-center gap-2 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-150 border border-gray-200';
                            } else {
                                radioWrapper.className =
                                    'flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-150';
                            }

                            const radio = document.createElement('input');
                            radio.type = 'radio';
                            radio.name = field.name;
                            radio.id = `${field.name}_${idx}`;

                            // Handle boolean conversion: convert "1"/"0" to "true"/"false"
                            let radioValue = option.value || option;
                            if (radioValue === '1' || radioValue === 1) {
                                radioValue = 'true';
                            } else if (radioValue === '0' || radioValue === 0) {
                                radioValue = 'false';
                            }
                            radio.value = radioValue;

                            radio.className = 'custom-radio';
                            radio.required = field.required;

                            // Check if this radio should be selected based on old input or field value
                            let selectedValue = this.oldInput && this.oldInput[field.name] !== undefined
                                ? this.oldInput[field.name]
                                : field.value;

                            // Normalize selected value for comparison
                            if (selectedValue === '1' || selectedValue === 1 || selectedValue === true) {
                                selectedValue = 'true';
                            } else if (selectedValue === '0' || selectedValue === 0 || selectedValue === false) {
                                selectedValue = 'false';
                            }

                            if (selectedValue && radio.value == selectedValue) {
                                radio.checked = true;
                            }

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
                            radioLabel.className =
                                'text-sm text-gray-700 cursor-pointer select-none leading-snug';
                            if (layout === 'row') {
                                radioLabel.className += ' whitespace-nowrap';
                            } else {
                                radioLabel.className += ' flex-1';
                            }
                            radioLabel.textContent = option.label || option;

                            radioWrapper.appendChild(radio);
                            radioWrapper.appendChild(radioLabel);
                            optionsContainer.appendChild(radioWrapper);
                        });

                        wrapper.appendChild(optionsContainer);
                    }

                    formGroup.innerHTML = '';
                    formGroup.appendChild(wrapper);
                    return formGroup;
                } else if (field.type === 'color') {
                    // Modern color picker
                    const colorPickerWrapper = document.createElement('div');
                    colorPickerWrapper.className = 'flex items-center gap-3';

                    // Hidden color input (browser default)
                    const colorInput = document.createElement('input');
                    colorInput.type = 'color';
                    colorInput.id = `${field.name}_picker`;
                    colorInput.className = 'sr-only';

                    // Get the value to use (old input or field value)
                    const colorValue = this.oldInput && this.oldInput[field.name] !== undefined
                        ? this.oldInput[field.name]
                        : (field.value || '#6366f1');

                    colorInput.value = colorValue;

                    // Visual color display button
                    const colorDisplay = document.createElement('button');
                    colorDisplay.type = 'button';
                    colorDisplay.className =
                        'w-16 h-16 rounded-xl border-4 border-gray-200 shadow-md hover:shadow-lg transition-all duration-200 cursor-pointer relative overflow-hidden';
                    colorDisplay.style.backgroundColor = colorValue;

                    // Ripple effect on click
                    colorDisplay.addEventListener('click', () => {
                        colorInput.click();
                    });

                    // Text input to show/edit hex value
                    input = document.createElement('input');
                    input.type = 'text';
                    input.className =
                        'flex-1 px-4 py-2.5 rounded-lg border border-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm';
                    input.name = field.name;
                    input.id = field.name;
                    input.value = colorValue;
                    input.placeholder = '#000000';
                    input.pattern = '^#[0-9A-Fa-f]{6}$';

                    if (field.required) {
                        input.required = true;
                    }

                    // Sync color picker with text input
                    colorInput.addEventListener('input', (e) => {
                        const newColor = e.target.value;
                        colorDisplay.style.backgroundColor = newColor;
                        input.value = newColor;
                    });

                    // Sync text input with color picker
                    input.addEventListener('input', (e) => {
                        const newColor = e.target.value;
                        if (/^#[0-9A-Fa-f]{6}$/.test(newColor)) {
                            colorDisplay.style.backgroundColor = newColor;
                            colorInput.value = newColor;
                        }
                    });

                    colorPickerWrapper.appendChild(colorInput);
                    colorPickerWrapper.appendChild(colorDisplay);
                    colorPickerWrapper.appendChild(input);

                    formGroup.appendChild(colorPickerWrapper);

                    // Add color preview swatches
                    const swatchesContainer = document.createElement('div');
                    swatchesContainer.className = 'mt-3 flex items-center gap-2';

                    const swatchesLabel = document.createElement('span');
                    swatchesLabel.className = 'text-xs font-medium text-gray-600';
                    swatchesLabel.textContent = 'Quick colors:';
                    swatchesContainer.appendChild(swatchesLabel);

                    const defaultColors = ['#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f59e0b', '#10b981',
                        '#06b6d4', '#3b82f6'
                    ];
                    defaultColors.forEach(color => {
                        const swatch = document.createElement('button');
                        swatch.type = 'button';
                        swatch.className =
                            'w-8 h-8 rounded-lg border-2 border-gray-200 hover:border-gray-400 hover:scale-110 transition-all duration-150 cursor-pointer';
                        swatch.style.backgroundColor = color;
                        swatch.addEventListener('click', () => {
                            colorDisplay.style.backgroundColor = color;
                            input.value = color;
                            colorInput.value = color;
                        });
                        swatchesContainer.appendChild(swatch);
                    });

                    formGroup.appendChild(swatchesContainer);

                    return formGroup;
                } else {
                    input = document.createElement('input');
                    input.type = field.type || 'text';
                    input.className = baseClasses;
                }

                // Set common attributes (for non-checkbox/radio/file/image/color fields)
                if (field.type !== 'checkbox' && field.type !== 'radio' && field.type !== 'file' && field.type !==
                    'image' && field.type !== 'color') {
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

                    // Use old input if available, otherwise use field value
                    const fieldValue = this.oldInput && this.oldInput[field.name] !== undefined
                        ? this.oldInput[field.name]
                        : field.value;

                    if (fieldValue) {
                        input.value = fieldValue;
                    }

                    formGroup.appendChild(input);

                    // Add helper text if provided
                    if (field.helperText) {
                        const helperText = document.createElement('p');
                        helperText.className = 'mt-1.5 text-xs text-gray-500 flex items-start gap-1';
                        helperText.innerHTML = `
                            <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>${field.helperText}</span>
                        `;
                        formGroup.appendChild(helperText);
                    }
                } else if (field.type === 'checkbox') {
                    // For checkbox, set ID for the input inside wrapper
                    input.name = field.name;
                    input.id = field.name;
                }

                return formGroup;
            }

            // Display error message for a field
            showFieldError(fieldName, errorMessage) {
                const field = this.form.querySelector(`[name="${fieldName}"]`);
                if (!field) return;

                // Add error styling to the field
                field.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                field.classList.remove('border-gray-300');

                // Create or update error message element
                const formGroup = field.closest('.col-span-12, [class*="col-span-"]') || field.parentElement;
                let errorElement = formGroup.querySelector('.field-error-message');

                if (!errorElement) {
                    errorElement = document.createElement('div');
                    errorElement.className = 'field-error-message mt-1 text-sm text-red-600 flex items-center gap-1';
                    errorElement.innerHTML = `
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>${errorMessage}</span>
                    `;
                    formGroup.appendChild(errorElement);
                } else {
                    errorElement.querySelector('span').textContent = errorMessage;
                }
            }

            // Apply all server-side errors to fields
            applyServerErrors() {
                if (!this.errors || Object.keys(this.errors).length === 0) return;

                Object.keys(this.errors).forEach(fieldName => {
                    const errorMessages = this.errors[fieldName];
                    if (Array.isArray(errorMessages) && errorMessages.length > 0) {
                        this.showFieldError(fieldName, errorMessages[0]);
                    }
                });
            }

        }

        // ============================================
        // Initialize the form builder
        // ============================================
        const formElement = document.getElementById('dynamicForm');
        const formBuilder = new FormBuilder(formElement);

        // Get form configuration from component prop
        @if ($formConfig)
            const formConfig = @json($formConfig);

            // Check if it's grouped or flat
            if (formConfig.groups) {
                formBuilder.defineGroups(formConfig.groups).build();
            } else if (formConfig.fields) {
                formBuilder.defineFields(formConfig.fields).build();
            }

            // Apply server-side errors after form is built
            formBuilder.applyServerErrors();
        @else
            // Default example form if no config provided
            formBuilder.defineGroups(groupedFormFields).build();
        @endif
    </script>
@endpush
