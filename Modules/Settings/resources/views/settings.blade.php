<x-dashboard page-title="Organization Settings">
    
  

    {{-- Display current logo if exists --}}
    @if($settings->logo_path)
        <div class="max-w-7xl mx-auto px-6 py-8 bg-white rounded-lg shadow-sm border border-gray-200">
            <p class="text-sm font-semibold text-gray-700 mb-2">Current Logo:</p>
            <img src="{{  asset('storage/' . $settings->logo_path) }}" alt="Organization Logo" class="h-20 object-contain">
        </div>
    @endif

    <x-dashboard.packages.form-builder 
        :formConfig="$formConfig" 
        action="{{ route('settings.organization.update') }}"
        method="POST"
    />

</x-dashboard>