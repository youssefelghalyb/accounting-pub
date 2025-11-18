<x-dashboard :pageTitle="$author->full_name">
    <div class="max-w-5xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                <li>
                    <a href="{{ route('product.authors.index') }}" class="text-gray-500 hover:text-gray-700">
                        {{ __('product::author.authors') }}
                    </a>
                </li>
                <li>
                    <svg class="w-5 h-5 text-gray-400 {{ app()->getLocale() == 'ar' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">{{ $author->full_name }}</span>
                </li>
            </ol>
        </nav>

        <!-- Header with Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        @php
                            $initials = strtoupper(substr($author->full_name, 0, 2));
                        @endphp
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl">
                            {{ $initials }}
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $author->full_name }}</h1>
                            @if($author->occupation)
                                <p class="text-sm text-gray-500 mt-1">{{ $author->occupation }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('product.authors.edit', $author) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ __('common.edit') }}
                        </a>
                        <form action="{{ route('product.authors.destroy', $author) }}" method="POST" onsubmit="return confirm('{{ __('common.are_you_sure') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                {{ __('common.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::author.total_books') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $author->books()->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::author.total_contracts') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $author->contracts()->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ __('product::author.total_contract_value') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($author->contracts()->sum('contract_price'), 2) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::author.personal_info') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.full_name') }}</label>
                        <p class="text-gray-900 font-medium">{{ $author->full_name }}</p>
                    </div>

                    @if($author->nationality)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.nationality') }}</label>
                        <p class="text-gray-900">{{ $author->nationality }}</p>
                    </div>
                    @endif

                    @if($author->country_of_residence)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.country_of_residence') }}</label>
                        <p class="text-gray-900">{{ $author->country_of_residence }}</p>
                    </div>
                    @endif

                    @if($author->occupation)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.occupation') }}</label>
                        <p class="text-gray-900">{{ $author->occupation }}</p>
                    </div>
                    @endif

                    @if($author->bio)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.bio') }}</label>
                        <p class="text-gray-900">{{ $author->bio }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::author.contact_info') }}</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($author->email)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.email') }}</label>
                        <a href="mailto:{{ $author->email }}" class="text-blue-600 hover:text-blue-700">{{ $author->email }}</a>
                    </div>
                    @endif

                    @if($author->phone_number)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.phone_number') }}</label>
                        <a href="tel:{{ $author->phone_number }}" class="text-blue-600 hover:text-blue-700">{{ $author->phone_number }}</a>
                    </div>
                    @endif

                    @if($author->whatsapp_number)
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('product::author.whatsapp_number') }}</label>
                        <a href="https://wa.me/{{ $author->whatsapp_number }}" target="_blank" class="text-green-600 hover:text-green-700">{{ $author->whatsapp_number }}</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($author->id_image)
        <!-- Additional Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">{{ __('product::author.additional_info') }}</h2>
            </div>
            <div class="p-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">{{ __('product::author.id_image') }}</label>
                    <img src="{{ asset('storage/' . $author->id_image) }}" alt="ID Image" class="max-w-md rounded-lg border border-gray-300">
                </div>
            </div>
        </div>
        @endif
    </div>
</x-dashboard>
