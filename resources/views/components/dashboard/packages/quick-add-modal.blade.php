@props([
    'id' => 'quick_add_' . Str::random(6), // unique modal ID
    'url', // POST endpoint
    'title', // Modal heading
    'fields' => [], // Array of field definitions (see below)
    'submitLabel' => __('common.save'),
    'size' => 'md', // sm | md | lg
])

@php
    /*
     * Field definition shape:
     * [
     *   'name'        => 'phone',          // required — POST field name
     *   'label'       => 'Phone',          // required — visible label
     *   'type'        => 'text',           // text | email | number | tel | textarea | select | hidden
     *   'required'    => false,
     *   'placeholder' => '',
     *   'value'       => '',               // default value
     *   'options'     => [],               // for type=select: [['value'=>'', 'label'=>''] …]
     *   'rows'        => 3,                // for textarea
     *   'class'       => '',               // extra CSS
     *   'span'        => 1,                // 1 = half width, 2 = full width (in a 2-col grid)
     * ]
     */
    $targetSsId = $attributes->get('target-ss-id', '');

    $sizeClass = match ($size) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-2xl',
        default => 'max-w-md',
    };

    $isRtl = app()->getLocale() === 'ar';
    $fieldsJson = json_encode($fields);
@endphp

{{-- ═══════════════════════════════════════════
     MODAL OVERLAY
     ═══════════════════════════════════════════ --}}
<div id="{{ $id }}_modal" class="qam-modal fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true"
    aria-labelledby="{{ $id }}_title">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
        onclick="QuickAddModal.close('{{ $id }}')"></div>

    {{-- Panel --}}
    <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div id="{{ $id }}_panel"
            class="qam-panel pointer-events-auto w-full {{ $sizeClass }} bg-white rounded-2xl shadow-2xl
                   transform transition-all scale-95 opacity-0">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 id="{{ $id }}_title" class="text-lg font-bold text-gray-900">
                    {{ $title }}
                </h3>
                <button type="button" onclick="QuickAddModal.close('{{ $id }}')"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                    aria-label="{{ __('common.close') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Error Banner --}}
            <div id="{{ $id }}_errors"
                class="hidden mx-6 mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <ul id="{{ $id }}_error_list" class="text-sm text-red-700 space-y-1 list-disc list-inside">
                </ul>
            </div>

            {{-- Form --}}
            <form id="{{ $id }}_form" class="p-6"
                onsubmit="QuickAddModal.submit(event, '{{ $id }}')" novalidate>
                <div class="grid grid-cols-2 gap-4">
                    @foreach ($fields as $field)
                        @php
                            $span = $field['span'] ?? 1;
                            $colClass = $span >= 2 ? 'col-span-2' : 'col-span-2 sm:col-span-1';
                            $fType = $field['type'] ?? 'text';
                            $fName = $field['name'];
                            $fLabel = $field['label'];
                            $fReq = $field['required'] ?? false;
                            $fPlaceholder = $field['placeholder'] ?? '';
                            $fValue = $field['value'] ?? '';
                            $baseInputClass =
                                'w-full px-3 py-2 text-sm border border-gray-300 rounded-lg
                                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                               disabled:opacity-50 ' .
                                ($field['class'] ?? '');
                        @endphp

                        @if ($fType === 'hidden')
                            <input type="hidden" name="{{ $fName }}" value="{{ $fValue }}">
                        @else
                            <div class="{{ $colClass }}">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    {{ $fLabel }}
                                    @if ($fReq)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>

                                @if ($fType === 'textarea')
                                    <textarea name="{{ $fName }}" rows="{{ $field['rows'] ?? 3 }}" placeholder="{{ $fPlaceholder }}"
                                        @if ($fReq) required @endif class="{{ $baseInputClass }} resize-none">{{ $fValue }}</textarea>
                                @elseif($fType === 'select')
                                    <select name="{{ $fName }}"
                                        @if ($fReq) required @endif
                                        class="{{ $baseInputClass }}">
                                        @if (!$fReq)
                                            <option value="">—</option>
                                        @endif
                                        @foreach ($field['options'] ?? [] as $opt)
                                            <option value="{{ $opt['value'] }}"
                                                @if ($fValue !== '' && $fValue !== null && $fValue == $opt['value']) selected @endif>{{ $opt['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $fType }}" name="{{ $fName }}"
                                        value="{{ $fValue }}" placeholder="{{ $fPlaceholder }}"
                                        @if ($fReq) required @endif
                                        class="{{ $baseInputClass }}">
                                @endif
                            </div>
                        @endif
                    @endforeach

                    {{-- Slot for extra custom content if needed --}}
                    @if ($slot->isNotEmpty())
                        <div class="col-span-2">{{ $slot }}</div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-gray-100">
                    <button type="button" onclick="QuickAddModal.close('{{ $id }}')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit" id="{{ $id }}_submit"
                        class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700
                               disabled:opacity-60 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
                        <svg id="{{ $id }}_spinner" class="hidden animate-spin w-4 h-4" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        <span id="{{ $id }}_submit_label">{{ $submitLabel }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            /* Quick Add Modal animations */
            .qam-modal:not(.hidden) .qam-panel {
                animation: qam-enter 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }

            @keyframes qam-enter {
                from {
                    opacity: 0;
                    transform: scale(0.94) translateY(8px);
                }

                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }

            .qam-exit .qam-panel {
                animation: qam-leave 0.15s ease-in forwards;
            }

            @keyframes qam-leave {
                from {
                    opacity: 1;
                    transform: scale(1);
                }

                to {
                    opacity: 0;
                    transform: scale(0.96);
                }
            }
        </style>
    @endpush
@endonce

@once
    @push('scripts')
        <script>
            /**
             * QuickAddModal — Reusable quick-add modal that integrates with SearchableSelect.
             *
             * After a successful POST, the server must return JSON in the shape:
             * {
             *   "success": true,
             *   "item": { "id": 42, "text": "John Doe", "sublabel": "optional" },
             *   "message": "Created successfully"   // optional toast message
             * }
             *
             * On error the server can return:
             * { "success": false, "errors": { "field": ["msg"] } | ["msg1", "msg2"] }
             * OR a 422 with Laravel validation errors JSON.
             */
            window.QuickAddModal = (function() {

                // Registry: modalId → { targetSsId, url, onSuccess? }
                const _registry = {};

                function register(modalId, targetSsId, url) {
                    _registry[modalId] = {
                        targetSsId,
                        url
                    };
                }

                function open(modalId, prefill = {}) {
                    const modal = document.getElementById(`${modalId}_modal`);
                    if (!modal) return;
                    modal.querySelectorAll('[required]').forEach(el => el.disabled = false);
                    // Pre-fill any fields
                    Object.entries(prefill).forEach(([name, value]) => {
                        const el = modal.querySelector(`[name="${name}"]`);
                        if (el) el.value = value;
                    });

                    _clearErrors(modalId);
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';

                    // Focus first visible input
                    setTimeout(() => {
                        const first = modal.querySelector('input:not([type=hidden]), textarea, select');
                        if (first) first.focus();
                    }, 80);
                }

                function close(modalId) {
                    const modal = document.getElementById(`${modalId}_modal`);
                    if (!modal) return;
                    modal.querySelectorAll('[required]').forEach(el => el.disabled = true);

                    modal.classList.add('qam-exit');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        modal.classList.remove('qam-exit');
                        document.body.style.overflow = '';
                        // Reset form
                        const form = document.getElementById(`${modalId}_form`);
                        if (form) form.reset();
                        _clearErrors(modalId);
                    }, 150);
                }

                async function submit(event, modalId) {
                    event.preventDefault();

                    const cfg = _registry[modalId];
                    if (!cfg) {
                        console.error(`[QuickAddModal] "${modalId}" not registered`);
                        return;
                    }

                    const form = document.getElementById(`${modalId}_form`);
                    const btn = document.getElementById(`${modalId}_submit`);
                    const spinner = document.getElementById(`${modalId}_spinner`);
                    const btnLabel = document.getElementById(`${modalId}_submit_label`);

                    // Native HTML5 validation
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    // Loading state
                    btn.disabled = true;
                    spinner.classList.remove('hidden');
                    const savedLabel = btnLabel.textContent;
                    btnLabel.textContent = '...';
                    _clearErrors(modalId);

                    const formData = new FormData(form);

                    try {
                        const resp = await fetch(cfg.url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    ?.content || '',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        const data = await resp.json();

                        if (resp.ok && data.success) {
                            // ✅ Inject into the target SearchableSelect
                            if (cfg.targetSsId) {
                                const ss = window.SearchableSelectGetInstance(cfg.targetSsId);
                                if (ss && data.item) {
                                    ss.setValue(data.item.id, data.item.text);
                                    // Dispatch change so dependent logic (e.g. filter invoices by party) runs
                                    ss.hidden.dispatchEvent(new Event('change', {
                                        bubbles: true
                                    }));
                                    // Call the onSelect callback if configured
                                    if (ss.onSelectCb && typeof window[ss.onSelectCb] === 'function') {
                                        window[ss.onSelectCb](data.item.id, data.item.text, cfg.targetSsId);
                                    }
                                }
                            }

                            // Custom success hook
                            if (typeof cfg.onSuccess === 'function') {
                                cfg.onSuccess(data);
                            }

                            // Toast
                            if (typeof window.showToast === 'function') {
                                window.showToast(data.message || '✓ Saved');
                            }

                            close(modalId);

                        } else {
                            // Show validation errors
                            _showErrors(modalId, data.errors || data.message);
                        }

                    } catch (err) {
                        console.error('[QuickAddModal] submit error:', err);
                        _showErrors(modalId, ['An unexpected error occurred. Please try again.']);
                    } finally {
                        btn.disabled = false;
                        spinner.classList.add('hidden');
                        btnLabel.textContent = savedLabel;
                    }
                }

                /** Attach a custom success callback */
                function onSuccess(modalId, fn) {
                    if (_registry[modalId]) _registry[modalId].onSuccess = fn;
                }

                function _clearErrors(modalId) {
                    const banner = document.getElementById(`${modalId}_errors`);
                    const list = document.getElementById(`${modalId}_error_list`);
                    if (banner) banner.classList.add('hidden');
                    if (list) list.innerHTML = '';
                }

                function _showErrors(modalId, errors) {
                    const banner = document.getElementById(`${modalId}_errors`);
                    const list = document.getElementById(`${modalId}_error_list`);
                    if (!banner || !list) return;

                    let messages = [];

                    if (typeof errors === 'string') {
                        messages = [errors];
                    } else if (Array.isArray(errors)) {
                        messages = errors;
                    } else if (typeof errors === 'object') {
                        // Laravel validation shape: { field: ["msg1"] }
                        messages = Object.values(errors).flat();
                    }

                    list.innerHTML = messages.map(m => `<li>${_esc(m)}</li>`).join('');
                    banner.classList.remove('hidden');

                    // Scroll to error
                    banner.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }

                function _esc(str) {
                    const d = document.createElement('div');
                    d.textContent = str;
                    return d.innerHTML;
                }

                // Close on Escape
                document.addEventListener('keydown', (e) => {
                    if (e.key !== 'Escape') return;
                    document.querySelectorAll('.qam-modal:not(.hidden)').forEach(modal => {
                        close(modal.id.replace('_modal', ''));
                    });
                });

                return {
                    register,
                    open,
                    close,
                    submit,
                    onSuccess
                };

            })();
        </script>
    @endpush
@endonce

{{-- Auto-register this instance --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.qam-modal [required]').forEach(el => el.disabled = true);
        QuickAddModal.register(
            '{{ $id }}',
            '{{ $targetSsId }}',
            '{{ $url }}'
        );
    });
</script>
