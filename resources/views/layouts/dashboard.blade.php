<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ App\Helpers\LocaleHelper::getDirection() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }} - {{ config('app.name', __('sidebar.organization_name')) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if (App\Helpers\LocaleHelper::isRtl())
        <link rel="stylesheet" href="{{ asset('css/rtl.css') }}">
    @endif
    <style>
        :root {
            --primary-color: {{ $orgSettings->primary_color ?? '#3490dc' }};
            --secondary-color: {{ $orgSettings->secondary_color ?? '#ffed4e' }};
            --primary-dark: {{ isset($orgSettings->primary_color) ? adjustBrightness($orgSettings->primary_color, -20) : '#2779bd' }};
            --primary-light: {{ isset($orgSettings->primary_color) ? adjustBrightness($orgSettings->primary_color, 20) : '#6cb2eb' }};
        }

        .bg-primary {
            background-color: var(--primary-color) !important;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .border-primary {
            border-color: var(--primary-color) !important;
        }

        .hover\:bg-primary:hover {
            background-color: var(--primary-color) !important;
        }

        .bg-primary-dark {
            background-color: var(--primary-dark) !important;
        }

        .bg-primary-light {
            background-color: var(--primary-light) !important;
        }

        /* RTL Layout Support */
        [dir="rtl"] {
            text-align: right;
        }

        [dir="rtl"] body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
        }

        /* Sidebar RTL positioning */
        [dir="rtl"] .sidebar {
            left: auto;
            right: 0;
        }

        [dir="rtl"] .sidebar-tooltip {
            left: auto;
            right: 100%;
            margin-left: 0;
            margin-right: 0.5rem;
        }

        [dir="rtl"] .submenu-item {
            padding-left: 0.75rem;
            padding-right: 3rem;
        }

        [dir="rtl"] .sidebar.collapsed .submenu-item {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        [dir="rtl"] .group-divider {
            margin-left: 0;
            margin-right: 0.75rem;
        }

        [dir="rtl"] .sidebar-item::before {
            left: auto;
            right: 0;
        }

        /* Main content RTL adjustment */
        [dir="rtl"] .main-wrapper {
            margin-left: 0;
            margin-right: 0;
        }

        @media (min-width: 768px) {
            [dir="rtl"] .main-wrapper {
                margin-right: 0;
            }
        }

        /* Notification badge RTL */
        [dir="rtl"] .notification-badge {
            left: 0;
            right: auto;
        }

        /* Chevron icon rotation for RTL */
        [dir="rtl"] .chevron-right {
            transform: rotate(180deg);
        }

        /* Dropdown arrow in RTL */
        [dir="rtl"] .dropdown-arrow {
            transform: rotate(180deg);
        }

        /* Text alignment */
        [dir="rtl"] .text-left {
            text-align: right !important;
        }

        [dir="rtl"] .text-right {
            text-align: left !important;
        }

        /* Border radius for RTL */
        [dir="rtl"] .rounded-l {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-top-right-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
        }

        [dir="rtl"] .rounded-r {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-top-left-radius: 0.375rem;
            border-bottom-left-radius: 0.375rem;
        }

        /* Form inputs RTL */
        [dir="rtl"] input,
        [dir="rtl"] textarea,
        [dir="rtl"] select {
            text-align: right;
        }

        /* Scrollbar for RTL */
        [dir="rtl"] .custom-scrollbar::-webkit-scrollbar {
            left: 0;
            right: auto;
        }

        /* Sidebar styles */
        .sidebar {
            transition: width 0.3s ease-in-out, transform 0.3s ease-in-out;
            width: 16rem;
        }

        .sidebar.collapsed {
            width: 4.5rem;
        }

        .sidebar.collapsed .sidebar-text,
        .sidebar.collapsed .org-name,
        .sidebar.collapsed .user-info,
        .sidebar.collapsed .group-title {
            opacity: 0;
            visibility: hidden;
            width: 0;
            overflow: hidden;
        }

        .sidebar.collapsed .chevron-icon {
            transform: rotate(180deg);
        }

        [dir="rtl"] .sidebar.collapsed .chevron-icon {
            transform: rotate(0deg);
        }

        [dir="rtl"] .chevron-icon {
            transform: rotate(180deg);
        }

        .sidebar-text,
        .org-name,
        .user-info,
        .group-title {
            transition: opacity 0.2s ease, visibility 0.2s ease, width 0.2s ease;
            white-space: nowrap;
        }

        .chevron-icon {
            transition: transform 0.3s ease;
        }

        /* Submenu styles */
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-in-out;
        }

        .submenu.open {
            max-height: 500px;
        }

        .submenu-item {
            padding-left: 3rem;
        }

        .sidebar.collapsed .submenu-item {
            padding-left: 0.75rem;
        }

        /* Group header */
        .group-header {
            display: flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }

        .sidebar.collapsed .group-header {
            justify-content: center;
            padding: 0.5rem 0.25rem;
        }

        .group-divider {
            flex-grow: 1;
            height: 1px;
            background-color: #e5e7eb;
            margin-left: 0.75rem;
        }

        .sidebar.collapsed .group-divider {
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            [dir="rtl"] .sidebar {
                transform: translateX(100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .sidebar.collapsed {
                width: 16rem;
            }
        }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Sidebar item hover effect */
        .sidebar-item {
            position: relative;
            overflow: hidden;
        }

        .sidebar-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: var(--primary-color);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .sidebar-item:hover::before,
        .sidebar-item.active::before {
            transform: scaleY(1);
        }

        /* Tooltip for collapsed sidebar */
        .sidebar-tooltip {
            position: absolute;
            left: 100%;
            margin-left: 0.5rem;
            padding: 0.5rem 0.75rem;
            background-color: #1f2937;
            color: white;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            z-index: 1000;
        }

        .sidebar.collapsed .sidebar-item:hover .sidebar-tooltip {
            opacity: 1;
        }

        .sidebar:not(.collapsed) .sidebar-tooltip {
            display: none;
        }

        /* Smooth transitions */
        * {
            transition-property: background-color, color, border-color;
            transition-duration: 0.2s;
            transition-timing-function: ease;
        }

        /* Main content adjustment */
        .main-wrapper {
            transition: margin-left 0.3s ease-in-out;
        }

        @media (min-width: 768px) {
            .sidebar.collapsed~.main-wrapper {
                margin-left: 0;
            }

            [dir="rtl"] .main-wrapper {
                margin-left: 0;
                margin-right: 0;
            }
        }

        /* Language switcher styles */
        .lang-switcher {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .lang-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .lang-btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .lang-btn:not(.active) {
            background-color: #f3f4f6;
            color: #6b7280;
            border-color: #e5e7eb;
        }

        .lang-btn:not(.active):hover {
            background-color: #e5e7eb;
            border-color: #d1d5db;
        }
    </style>
    {{ $styles ?? '' }}
    @stack('styles')
    @stack('head-scripts')
</head>

<body class="bg-gray-50 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="sidebar fixed inset-y-0 {{ app()->getLocale() == 'ar' ? 'right-0' : 'left-0' }} z-50 bg-white shadow-xl md:static md:translate-x-0">
            <div class="flex h-full flex-col">
                <!-- Sidebar Header -->
                <div class="flex h-16 items-center justify-between border-b border-gray-200 px-4">
                    <div
                        class="flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} overflow-hidden">
                        @if ($orgSettings && $orgSettings->logo_path)
                            <img src="{{ asset('storage/' . $orgSettings->logo_path) }}"
                                alt="{{ $orgSettings->organization_name }}"
                                class="h-10 w-10 rounded-lg object-contain flex-shrink-0">
                        @else
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-white font-bold text-lg flex-shrink-0">
                                {{ $orgSettings ? strtoupper(substr($orgSettings->organization_name ?? __('sidebar.organization_name'), 0, 1)) : 'D' }}
                            </div>
                        @endif
                        <span
                            class="org-name text-lg font-bold text-gray-800 truncate">{{ $orgSettings->organization_name ?? __('sidebar.organization_name') }}</span>
                    </div>
                    <button id="closeSidebar" class="text-gray-500 hover:text-gray-700 md:hidden flex-shrink-0">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto custom-scrollbar px-3 py-4">
                    @if (isset($navigation))
                        {{ $navigation }}
                    @else
                        <!-- Main Menu Group -->
                        <div class="group-header">
                            <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                </path>
                            </svg>
                            <span
                                class="group-title text-xs font-semibold text-gray-500 uppercase tracking-wider {{ app()->getLocale() == 'ar' ? 'mr-3' : 'ml-3' }}">{{ __('sidebar.main_menu') }}</span>
                            <div class="group-divider"></div>
                        </div>

                        <div class="space-y-1">
                            <a href="{{ route('dashboard') }}"
                                class="sidebar-item flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} rounded-lg px-3 py-2.5 text-gray-700 hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'active bg-gray-100' : '' }}">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                    </path>
                                </svg>
                                <span class="sidebar-text font-medium">{{ __('sidebar.dashboard') }}</span>
                                <span class="sidebar-tooltip">{{ __('sidebar.dashboard') }}</span>
                            </a>

                            <a href="#"
                                class="sidebar-item flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} rounded-lg px-3 py-2.5 text-gray-700 hover:bg-gray-100">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                                <span class="sidebar-text font-medium">{{ __('sidebar.analytics') }}</span>
                                <span class="sidebar-tooltip">{{ __('sidebar.analytics') }}</span>
                            </a>

                            <!-- Dropdown Menu Item -->
                            <div class="dropdown-item">
                                <button
                                    class="sidebar-item w-full flex items-center justify-between rounded-lg px-3 py-2.5 text-gray-700 hover:bg-gray-100"
                                    onclick="toggleSubmenu(this)">
                                    <div
                                        class="flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                            </path>
                                        </svg>
                                        <span class="sidebar-text font-medium">{{ __('sidebar.users') }}</span>
                                    </div>
                                    <svg class="h-4 w-4 transition-transform sidebar-text" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <span class="sidebar-tooltip">{{ __('sidebar.users') }}</span>
                                </button>
                                <div class="submenu">
                                    <a href="#"
                                        class="submenu-item sidebar-item flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} rounded-lg px-3 py-2 text-sm text-gray-600 hover:bg-gray-100">
                                        <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span class="sidebar-text">{{ __('sidebar.all_users') }}</span>
                                        <span class="sidebar-tooltip">{{ __('sidebar.all_users') }}</span>
                                    </a>
                                    <a href="#"
                                        class="submenu-item sidebar-item flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} rounded-lg px-3 py-2 text-sm text-gray-600 hover:bg-gray-100">
                                        <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span class="sidebar-text">{{ __('sidebar.add_user') }}</span>
                                        <span class="sidebar-tooltip">{{ __('sidebar.add_user') }}</span>
                                    </a>
                                    <a href="#"
                                        class="submenu-item sidebar-item flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} rounded-lg px-3 py-2 text-sm text-gray-600 hover:bg-gray-100">
                                        <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span class="sidebar-text">{{ __('sidebar.roles') }}</span>
                                        <span class="sidebar-tooltip">{{ __('sidebar.roles') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- HR Group -->
                        <div class="group-header">
                            <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                            <span
                                class="group-title text-xs font-semibold text-gray-500 uppercase tracking-wider {{ app()->getLocale() == 'ar' ? 'mr-3' : 'ml-3' }}">{{ __('sidebar.hr') }}</span>
                            <div class="group-divider"></div>
                        </div>

                        <div class="space-y-1">
                            <!-- Another Dropdown -->
                            {{-- <div class="dropdown-item">
                                <button class="sidebar-item w-full flex items-center justify-between rounded-lg px-3 py-2.5 text-gray-700 hover:bg-gray-100" onclick="toggleSubmenu(this)">
                                    <div class="flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="sidebar-text font-medium">{{ __('sidebar.finance') }}</span>
                                    </div>
                                    <svg class="h-4 w-4 transition-transform sidebar-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <span class="sidebar-tooltip">{{ __('sidebar.finance') }}</span>
                                </button>
                                <div class="submenu">
                                    <a href="#" class="submenu-item sidebar-item flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} rounded-lg px-3 py-2 text-sm text-gray-600 hover:bg-gray-100">
                                        <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span class="sidebar-text">{{ __('sidebar.invoices') }}</span>
                                        <span class="sidebar-tooltip">{{ __('sidebar.invoices') }}</span>
                                    </a>
                                    <a href="#" class="submenu-item sidebar-item flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} rounded-lg px-3 py-2 text-sm text-gray-600 hover:bg-gray-100">
                                        <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span class="sidebar-text">{{ __('sidebar.payments') }}</span>
                                        <span class="sidebar-tooltip">{{ __('sidebar.payments') }}</span>
                                    </a>
                                    <a href="#" class="submenu-item sidebar-item flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} rounded-lg px-3 py-2 text-sm text-gray-600 hover:bg-gray-100">
                                        <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        <span class="sidebar-text">{{ __('sidebar.reports') }}</span>
                                        <span class="sidebar-tooltip">{{ __('sidebar.reports') }}</span>
                                    </a>
                                </div>
                            </div> --}}

                            <a href="{{ route('hr.departments.index') }}"
                                class="sidebar-item flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} rounded-lg px-3 py-2.5 text-gray-700 hover:bg-gray-100">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <span class="sidebar-text font-medium">{{ __('sidebar.departments') }}</span>
                                <span class="sidebar-tooltip">{{ __('sidebar.departments') }}</span>
                            </a>
                            <a href="{{ route('hr.employees.index') }}"
                                class="sidebar-item flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} rounded-lg px-3 py-2.5 text-gray-700 hover:bg-gray-100">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <span class="sidebar-text font-medium">{{ __('sidebar.employees') }}</span>
                                <span class="sidebar-tooltip">{{ __('sidebar.employees') }}</span>
                            </a>
                            <a href="{{ route('hr.leave-types.index') }}"
                                class="sidebar-item flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} rounded-lg px-3 py-2.5 text-gray-700 hover:bg-gray-100">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <span class="sidebar-text font-medium">{{ __('sidebar.leave_types') }}</span>
                                <span class="sidebar-tooltip">{{ __('sidebar.leave_types') }}</span>
                            </a>
                            <a href="{{ route('hr.leaves.index') }}"
                                class="sidebar-item flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} rounded-lg px-3 py-2.5 text-gray-700 hover:bg-gray-100">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <span class="sidebar-text font-medium">{{ __('sidebar.leaves') }}</span>
                                <span class="sidebar-tooltip">{{ __('sidebar.leaves') }}</span>
                            </a>
                            <a href="{{ route('hr.deductions.index') }}"
                                class="sidebar-item flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} rounded-lg px-3 py-2.5 text-gray-700 hover:bg-gray-100">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <span class="sidebar-text font-medium">{{ __('sidebar.deductions') }}</span>
                                <span class="sidebar-tooltip">{{ __('sidebar.deductions') }}</span>
                            </a>
                        </div>



                        <!-- Support Group -->
                        <div class="group-header">
                            <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                            <span
                                class="group-title text-xs font-semibold text-gray-500 uppercase tracking-wider {{ app()->getLocale() == 'ar' ? 'mr-3' : 'ml-3' }}">{{ __('sidebar.support') }}</span>
                            <div class="group-divider"></div>
                        </div>

                        <div class="space-y-1">
                            <a href="#"
                                class="sidebar-item flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} rounded-lg px-3 py-2.5 text-gray-700 hover:bg-gray-100">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                                <span class="sidebar-text font-medium">{{ __('sidebar.help_support') }}</span>
                                <span class="sidebar-tooltip">{{ __('sidebar.help_support') }}</span>
                            </a>

                            <a href="#"
                                class="sidebar-item flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} rounded-lg px-3 py-2.5 text-gray-700 hover:bg-gray-100">
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                    </path>
                                </svg>
                                <span class="sidebar-text font-medium">{{ __('sidebar.documentation') }}</span>
                                <span class="sidebar-tooltip">{{ __('sidebar.documentation') }}</span>
                            </a>
                        </div>
                    @endif
                </nav>

                <!-- Sidebar Footer -->
                <div class="border-t border-gray-200 p-4">
                    <!-- Toggle Button -->
                    <button id="toggleSidebar"
                        class="hidden md:flex items-center justify-center w-full mb-3 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        <svg class="h-5 w-5 chevron-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                        </svg>
                        <span
                            class="sidebar-text {{ app()->getLocale() == 'ar' ? 'mr-2' : 'ml-2' }}">{{ __('sidebar.collapse') }}</span>
                    </button>

                    <div
                        class="flex items-center space-x-3 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=random"
                            alt="User" class="h-10 w-10 rounded-full flex-shrink-0">
                        <div class="user-info flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ auth()->user()->name ?? 'User Name' }}</p>
                            <p class="text-xs text-gray-500 truncate">
                                {{ auth()->user()->email ?? 'user@example.com' }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-gray-600"
                                title="{{ __('sidebar.logout') }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                    </path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-wrapper flex flex-1 flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div
                        class="flex items-center space-x-4 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">
                        <button id="openSidebar" class="text-gray-500 hover:text-gray-700 md:hidden">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <h1 class="text-xl font-semibold text-gray-800">{{ $pageTitle }}</h1>
                    </div>

                    <div
                        class="flex items-center space-x-4 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }}">


                        <!-- Organization Logo Badge -->
                        @if ($orgSettings && $orgSettings->logo_path)
                            <div
                                class="hidden mx-2 sm:flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} px-3 py-1.5 bg-gray-50 rounded-lg border border-gray-200">
                                <img src="{{ asset('storage/' . $orgSettings->logo_path) }}"
                                    alt="{{ $orgSettings->organization_name }}" class="h-6 w-6 object-contain">
                                <span
                                    class="text-sm font-medium text-gray-700">{{ $orgSettings->organization_name }}</span>
                            </div>
                        @endif

                        <!-- Notifications -->
                        <button class="relative text-gray-500 hover:text-gray-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                </path>
                            </svg>
                            <span
                                class="absolute top-0 {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                        </button>

                        <!-- User Menu -->
                        <div class="relative">
                            <button
                                class="flex items-center space-x-2 {{ app()->getLocale() == 'ar' ? 'space-x-reverse' : '' }} text-gray-700 hover:text-gray-900">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=random"
                                    alt="User" class="h-8 w-8 rounded-full">
                                <span
                                    class="hidden sm:block text-sm font-medium">{{ auth()->user()->name ?? 'User' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8 custom-scrollbar">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div id="sidebarOverlay" class="fixed inset-0 z-40 bg-black bg-opacity-50 hidden md:hidden"></div>

    <script>
        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');
        const toggleBtn = document.getElementById('toggleSidebar');

        // Load saved state
        const sidebarState = localStorage.getItem('sidebarCollapsed');
        if (sidebarState === 'true') {
            sidebar.classList.add('collapsed');
        }

        // Mobile sidebar open
        openBtn?.addEventListener('click', () => {
            sidebar.classList.add('active');
            overlay.classList.remove('hidden');
        });

        // Mobile sidebar close
        closeBtn?.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.add('hidden');
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.add('hidden');
        });

        // Desktop sidebar collapse/expand
        toggleBtn?.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);

            // Close all submenus when collapsing
            if (isCollapsed) {
                document.querySelectorAll('.submenu').forEach(submenu => {
                    submenu.classList.remove('open');
                });
            }
        });

        // Submenu toggle function
        function toggleSubmenu(button) {
            const submenu = button.nextElementSibling;
            const chevron = button.querySelector('svg:last-of-type');
            const isCollapsed = sidebar.classList.contains('collapsed');

            // Don't allow submenu expansion when sidebar is collapsed
            if (isCollapsed) return;

            // Close other submenus
            document.querySelectorAll('.submenu').forEach(otherSubmenu => {
                if (otherSubmenu !== submenu) {
                    otherSubmenu.classList.remove('open');
                    const otherButton = otherSubmenu.previousElementSibling;
                    const otherChevron = otherButton.querySelector('svg:last-of-type');
                    otherChevron?.classList.remove('rotate-180');
                }
            });

            // Toggle current submenu
            submenu.classList.toggle('open');
            chevron?.classList.toggle('rotate-180');
        }
    </script>
    {{ $scripts ?? '' }}
    @stack('scripts')
</body>

</html>
