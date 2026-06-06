@props([
    'name',                        // form field name, e.g. "party_id"
    'id'        => null,           // optional id override
    'url',                         // API endpoint, e.g. route('search.parties')
    'value'     => null,           // pre-selected value (id)
    'label'     => null,           // pre-selected display label
    'placeholder' => __('common.select_or_search'),
    'required'  => false,
    'disabled'  => false,
    'clearable' => true,
    'perPage'   => 10,
    'extraParams' => [],           // array of extra static query params
    'onSelect'  => null,           // JS callback name to call on selection
    'onClear'   => null,           // JS callback name to call on clear
    'class'     => '',             // extra CSS classes for wrapper
])

@php
    $fieldId    = $id ?? 'ss_' . Str::random(8);
    $isRtl      = app()->getLocale() === 'ar';
    $extraJson  = json_encode($extraParams);
    $initValue  = $value  ? json_encode((string) $value) : 'null';
    $initLabel  = $label  ? json_encode((string) $label) : 'null';
@endphp

{{-- Hidden real input that gets submitted with the form --}}
<input
    type="hidden"
    name="{{ $name }}"
    id="{{ $fieldId }}_hidden"
    value="{{ $value ?? '' }}"
    @if($required) data-ss-required="true" @endif
>

{{-- Visible widget wrapper --}}
<div
    id="{{ $fieldId }}_wrapper"
    class="ss-wrapper relative {{ $class }}"
    dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
    data-ss-id="{{ $fieldId }}"
    data-ss-url="{{ $url }}"
    data-ss-per-page="{{ $perPage }}"
    data-ss-extra-params="{{ htmlspecialchars($extraJson) }}"
    data-ss-on-select="{{ $onSelect ?? '' }}"
    data-ss-on-clear="{{ $onClear ?? '' }}"
    data-ss-clearable="{{ $clearable ? 'true' : 'false' }}"
    data-ss-rtl="{{ $isRtl ? 'true' : 'false' }}"
>
    {{-- Trigger button --}}
    <button
        type="button"
        id="{{ $fieldId }}_trigger"
        @disabled($disabled)
        class="ss-trigger w-full flex items-center justify-between gap-2 px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm
               hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
               disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        aria-haspopup="listbox"
        aria-expanded="false"
        aria-controls="{{ $fieldId }}_dropdown"
    >
        <span class="ss-label truncate text-gray-500" id="{{ $fieldId }}_label">
            {{ $label ?? $placeholder }}
        </span>
        <span class="flex items-center gap-1 flex-shrink-0">
            {{-- Clear button --}}
            @if($clearable)
            <span
                id="{{ $fieldId }}_clear"
                class="ss-clear hidden p-0.5 rounded text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                role="button"
                tabindex="0"
                aria-label="{{ __('common.clear') }}"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </span>
            @endif
            {{-- Chevron --}}
            <svg class="ss-chevron w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </span>
    </button>

    {{-- Dropdown --}}
    <div
        id="{{ $fieldId }}_dropdown"
        role="listbox"
        class="ss-dropdown hidden absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden"
        style="min-width: 100%;"
    >
        {{-- Search input --}}
        <div class="p-2 border-b border-gray-100 bg-gray-50">
            <div class="relative">
                <svg class="absolute {{ $isRtl ? 'right-3' : 'left-3' }} top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input
                    type="text"
                    id="{{ $fieldId }}_search"
                    class="w-full {{ $isRtl ? 'pr-9 pl-3' : 'pl-9 pr-3' }} py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                    placeholder="{{ __('common.search') }}..."
                    autocomplete="off"
                >
            </div>
        </div>

        {{-- Options list --}}
        <div
            id="{{ $fieldId }}_list"
            class="ss-list overflow-y-auto"
            style="max-height: 260px;"
            role="listbox"
        >
            {{-- Populated by JS --}}
        </div>

        {{-- Loading / sentinel --}}
        <div id="{{ $fieldId }}_sentinel" class="ss-sentinel h-1"></div>

        {{-- State messages --}}
        <div id="{{ $fieldId }}_loading" class="hidden px-4 py-3 text-sm text-gray-500 text-center">
            <svg class="animate-spin w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ __('common.loading') }}...
        </div>
        <div id="{{ $fieldId }}_empty" class="hidden px-4 py-6 text-sm text-gray-400 text-center">
            <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ __('common.no_results') }}
        </div>
    </div>
</div>

@once
@push('styles')
<style>
/* =============================================
   Searchable Select Component Styles
   ============================================= */
.ss-wrapper { position: relative; }

.ss-dropdown {
    /* Smooth open/close */
    transform-origin: top center;
    animation: ss-open 0.15s ease-out;
}
@keyframes ss-open {
    from { opacity: 0; transform: scaleY(0.95) translateY(-4px); }
    to   { opacity: 1; transform: scaleY(1)    translateY(0);    }
}

.ss-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    color: #374151;
    cursor: pointer;
    transition: background-color 0.1s;
    user-select: none;
}
.ss-option:hover,
.ss-option.ss-focused {
    background-color: #eff6ff;
    color: #1d4ed8;
}
.ss-option.ss-selected {
    background-color: #dbeafe;
    color: #1e40af;
    font-weight: 500;
}
.ss-option .ss-check {
    margin-inline-start: auto;
    flex-shrink: 0;
    color: #3b82f6;
}
/* Sub-label */
.ss-option-sublabel {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.1rem;
}

/* Trigger open state */
.ss-trigger[aria-expanded="true"] {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
}
.ss-trigger[aria-expanded="true"] .ss-chevron {
    transform: rotate(180deg);
}

/* RTL adjustments */
[dir="rtl"] .ss-option .ss-check {
    margin-inline-start: auto;
    margin-inline-end: 0;
}
</style>
@endpush
@endonce

@once
@push('scripts')
<script>
/**
 * SearchableSelect — Reusable infinite-scroll select component
 * Usage: initialized automatically for all .ss-wrapper elements
 */
(function() {

class SearchableSelect {
    constructor(wrapper) {
        this.id          = wrapper.dataset.ssId;
        this.url         = wrapper.dataset.ssUrl;
        this.perPage     = parseInt(wrapper.dataset.ssPerPage) || 10;
        this.clearable   = wrapper.dataset.ssClearable === 'true';
        this.isRtl       = wrapper.dataset.ssRtl === 'true';
        this.onSelectCb  = wrapper.dataset.ssOnSelect || null;
        this.onClearCb   = wrapper.dataset.ssOnClear  || null;

        // Parse extra static params
        try {
            this.extraParams = JSON.parse(wrapper.dataset.ssExtraParams || '{}');
        } catch(e) {
            this.extraParams = {};
        }

        // State
        this.page        = 1;
        this.hasMore     = true;
        this.loading     = false;
        this.query       = '';
        this.selectedVal = null;
        this.selectedLbl = null;
        this.debounceTimer = null;
        this.observer    = null;

        // DOM refs
        this.wrapper   = wrapper;
        this.hidden    = document.getElementById(`${this.id}_hidden`);
        this.trigger   = document.getElementById(`${this.id}_trigger`);
        this.labelEl   = document.getElementById(`${this.id}_label`);
        this.dropdown  = document.getElementById(`${this.id}_dropdown`);
        this.searchEl  = document.getElementById(`${this.id}_search`);
        this.list      = document.getElementById(`${this.id}_list`);
        this.sentinel  = document.getElementById(`${this.id}_sentinel`);
        this.loadingEl = document.getElementById(`${this.id}_loading`);
        this.emptyEl   = document.getElementById(`${this.id}_empty`);
        this.clearBtn  = document.getElementById(`${this.id}_clear`);

        // Restore pre-selected value
        if (this.hidden.value) {
            this.selectedVal = this.hidden.value;
            this.selectedLbl = this.labelEl.textContent.trim();
            this.labelEl.classList.remove('text-gray-500');
            if (this.clearable && this.clearBtn) this.clearBtn.classList.remove('hidden');
        }

        this._bindEvents();
        this._initObserver();
    }

    _bindEvents() {
        // Toggle open/close
        this.trigger.addEventListener('click', (e) => {
            // Ignore clicks on the clear button
            if (this.clearBtn && this.clearBtn.contains(e.target)) return;
            this.isOpen() ? this.close() : this.open();
        });

        // Clear button
        if (this.clearBtn) {
            this.clearBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.clear();
            });
            this.clearBtn.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.clear();
                }
            });
        }

        // Search input
        this.searchEl.addEventListener('input', () => {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.query = this.searchEl.value.trim();
                this._reset();
                this._fetchPage();
            }, 300);
        });

        // Keyboard nav inside list
        this.list.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown') { e.preventDefault(); this._moveFocus(1); }
            if (e.key === 'ArrowUp')   { e.preventDefault(); this._moveFocus(-1); }
            if (e.key === 'Enter')     { e.preventDefault(); const f = this.list.querySelector('.ss-focused'); if (f) f.click(); }
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!this.wrapper.contains(e.target)) this.close();
        });

        // Esc key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') this.close();
        });
    }

    _initObserver() {
        this.observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && this.hasMore && !this.loading) {
                this._fetchPage();
            }
        }, { root: this.list, threshold: 0.1 });
    }

    isOpen() {
        return !this.dropdown.classList.contains('hidden');
    }

    open() {
        // Close all other open instances
        document.querySelectorAll('.ss-wrapper').forEach(w => {
            if (w !== this.wrapper) {
                const otherId = w.dataset.ssId;
                const otherDrop = document.getElementById(`${otherId}_dropdown`);
                const otherTrig = document.getElementById(`${otherId}_trigger`);
                if (otherDrop) otherDrop.classList.add('hidden');
                if (otherTrig) otherTrig.setAttribute('aria-expanded', 'false');
            }
        });

        this.dropdown.classList.remove('hidden');
        this.trigger.setAttribute('aria-expanded', 'true');
        this.searchEl.value = '';
        this.query = '';

        // Position dropdown - flip up if no room below
        const rect = this.wrapper.getBoundingClientRect();
        const spaceBelow = window.innerHeight - rect.bottom;
        if (spaceBelow < 300) {
            this.dropdown.style.bottom = '100%';
            this.dropdown.style.top    = 'auto';
            this.dropdown.style.marginBottom = '4px';
            this.dropdown.style.marginTop    = '0';
        } else {
            this.dropdown.style.top    = '100%';
            this.dropdown.style.bottom = 'auto';
        }

        // Load first page
        this._reset();
        this._fetchPage();

        setTimeout(() => this.searchEl.focus(), 50);
    }

    close() {
        this.dropdown.classList.add('hidden');
        this.trigger.setAttribute('aria-expanded', 'false');
        if (this.observer) this.observer.disconnect();
    }

    clear() {
        this.selectedVal = null;
        this.selectedLbl = null;
        this.hidden.value = '';
        this.labelEl.textContent = this.trigger.closest('[data-placeholder]')?.dataset.placeholder
            || this.searchEl.placeholder.replace('...', '');

        // Reset label to placeholder style
        const placeholder = this.wrapper.dataset.ssPlaceholder
            || this.searchEl.getAttribute('placeholder')?.replace('...','')
            || '---';
        this.labelEl.textContent = placeholder;
        this.labelEl.classList.add('text-gray-500');

        if (this.clearBtn) this.clearBtn.classList.add('hidden');

        // Dispatch change event
        this.hidden.dispatchEvent(new Event('change', { bubbles: true }));

        // Custom callback
        if (this.onClearCb && typeof window[this.onClearCb] === 'function') {
            window[this.onClearCb]();
        }

        this.close();
    }

    select(value, label) {
        this.selectedVal = value;
        this.selectedLbl = label;
        this.hidden.value = value;
        this.labelEl.textContent = label;
        this.labelEl.classList.remove('text-gray-500');

        if (this.clearable && this.clearBtn) {
            this.clearBtn.classList.remove('hidden');
        }

        // Update visual selection in list
        this.list.querySelectorAll('.ss-option').forEach(opt => {
            const isSelected = opt.dataset.value == value;
            opt.classList.toggle('ss-selected', isSelected);
            const check = opt.querySelector('.ss-check');
            if (check) check.classList.toggle('hidden', !isSelected);
        });

        // Dispatch change event on the hidden input
        this.hidden.dispatchEvent(new Event('change', { bubbles: true }));

        // Custom callback
        if (this.onSelectCb && typeof window[this.onSelectCb] === 'function') {
            window[this.onSelectCb](value, label, this.id);
        }

        this.close();
    }

    _reset() {
        this.page    = 1;
        this.hasMore = true;
        this.list.innerHTML = '';
        this.emptyEl.classList.add('hidden');
        if (this.observer) this.observer.unobserve(this.sentinel);
    }

    async _fetchPage() {
        if (this.loading || !this.hasMore) return;
        this.loading = true;
        this.loadingEl.classList.remove('hidden');

        const params = new URLSearchParams({
            q:        this.query,
            page:     this.page,
            per_page: this.perPage,
            ...this.extraParams
        });

        try {
            const resp = await fetch(`${this.url}?${params}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
            const data = await resp.json();

            /*
             * Expected API response shape:
             * {
             *   data: [{ id, text, sublabel? }],
             *   meta: { current_page, last_page }  — OR — has_more: bool
             * }
             */
            const items   = data.data || data.results || [];
            const hasMore = data.meta
                ? data.meta.current_page < data.meta.last_page
                : (data.has_more ?? items.length >= this.perPage);

            this.hasMore = hasMore;
            this._appendItems(items);
            this.page++;

            // Show empty state
            if (this.page === 2 && items.length === 0) {
                this.emptyEl.classList.remove('hidden');
            }

            // Observe sentinel for infinite scroll
            if (this.hasMore) {
                this.observer.observe(this.sentinel);
            } else {
                this.observer.unobserve(this.sentinel);
            }

        } catch (err) {
            console.error('[SearchableSelect] fetch error:', err);
        } finally {
            this.loading = false;
            this.loadingEl.classList.add('hidden');
        }
    }

    _appendItems(items) {
        items.forEach(item => {
            const opt = document.createElement('div');
            opt.className = 'ss-option' + (item.id == this.selectedVal ? ' ss-selected' : '');
            opt.setAttribute('role', 'option');
            opt.setAttribute('tabindex', '0');
            opt.dataset.value = item.id;
            opt.dataset.label = item.text;
            console.log(item)
            opt.innerHTML = `
                <div class="flex-1 min-w-0">
                    <div class="truncate">${this._esc(item.text)}</div>
                    ${item.sublabel ? `<div class="ss-option-sublabel truncate">${this._esc(item.sublabel)}</div>` : ''}
                </div>
                <svg class="ss-check w-4 h-4 flex-shrink-0 ${item.id == this.selectedVal ? '' : 'hidden'}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            `;

            opt.addEventListener('click', () => this.select(item.id, item.text));
            opt.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.select(item.id, item.text);
                }
            });

            this.list.appendChild(opt);
        });
    }

    _moveFocus(direction) {
        const opts = [...this.list.querySelectorAll('.ss-option')];
        const current = this.list.querySelector('.ss-focused');
        let idx = current ? opts.indexOf(current) : -1;
        if (current) current.classList.remove('ss-focused');
        idx = Math.max(0, Math.min(opts.length - 1, idx + direction));
        if (opts[idx]) {
            opts[idx].classList.add('ss-focused');
            opts[idx].scrollIntoView({ block: 'nearest' });
        }
    }

    _esc(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    /**
     * Programmatically set a value from outside
     * e.g. SearchableSelect.getInstance('my_id').setValue(5, 'John Doe')
     */
    setValue(value, label) {
        this.selectedVal  = value;
        this.selectedLbl  = label;
        this.hidden.value = value;
        this.labelEl.textContent = label;
        this.labelEl.classList.remove('text-gray-500');
        if (this.clearable && this.clearBtn) this.clearBtn.classList.remove('hidden');
    }

    /**
     * Dynamically update extra params (useful when another select changes)
     * e.g. SearchableSelect.getInstance('invoice_id').setExtraParams({ party_id: 5 })
     */
    setExtraParams(params) {
        this.extraParams = { ...this.extraParams, ...params };
    }
}

// Registry
window.SearchableSelectInstances = {};

function SearchableSelectGetInstance(id) {
    return window.SearchableSelectInstances[id] || null;
}
window.SearchableSelectGetInstance = SearchableSelectGetInstance;

// Auto-init all wrappers in DOM (and future ones via MutationObserver)
function initSearchableSelects(root = document) {
    root.querySelectorAll('.ss-wrapper:not([data-ss-init])').forEach(wrapper => {
        wrapper.setAttribute('data-ss-init', '1');
        const instance = new SearchableSelect(wrapper);
        window.SearchableSelectInstances[wrapper.dataset.ssId] = instance;
    });
}

document.addEventListener('DOMContentLoaded', () => initSearchableSelects());

// Support dynamically injected components
const _mutObs = new MutationObserver(() => initSearchableSelects());
_mutObs.observe(document.body, { childList: true, subtree: true });

})();
</script>
@endpush
@endonce