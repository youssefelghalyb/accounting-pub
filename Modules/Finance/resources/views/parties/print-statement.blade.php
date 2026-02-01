<!DOCTYPE html>
<html lang="{{ $printLang }}" dir="{{ $printLang == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('finance::party.account_statement') }} - {{ $party->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @if($printLang == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>body { font-family: 'Cairo', sans-serif; }</style>
    @endif
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 1.5cm 1cm; size: A4; }
        }
        .text-8 { font-size: 8pt; line-height: 1.2; }
        .text-9 { font-size: 9pt; line-height: 1.3; }
        .text-10 { font-size: 10pt; line-height: 1.3; }
        .text-12 { font-size: 12pt; line-height: 1.3; }
        .text-14 { font-size: 14pt; line-height: 1.2; }
        .text-18 { font-size: 18pt; line-height: 1.1; }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Print Toolbar -->
    <div class="no-print bg-white border-b sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-3 py-1.5 text-sm bg-gray-900 text-white rounded hover:bg-gray-800">
                    {{ $printLang == 'ar' ? 'طباعة' : 'Print' }}
                </button>
                <button onclick="window.close()" class="px-3 py-1.5 text-sm border text-gray-700 rounded hover:bg-gray-50">
                    {{ $printLang == 'ar' ? 'إغلاق' : 'Close' }}
                </button>
            </div>
        </div>
    </div>

    <div class="p-4">
        <div class="bg-white shadow-lg" style="max-width: 210mm; margin: 0 auto;">
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-start justify-between mb-4 pb-3 border-b-2 border-black">
                    <div style="width: 60%;">
                        @if($orgSettings->logo_path)
                            <img src="{{ Storage::url($orgSettings->logo_path) }}" alt="{{ $orgSettings->organization_name }}" 
                                 class="h-12 mb-1.5 object-contain" style="filter: grayscale(100%) contrast(1.2);">
                        @endif
                        <h1 class="text-14 font-bold mb-0.5">{{ $orgSettings->organization_name }}</h1>
                        <div class="text-8">
                            @if($orgSettings->address)<p>{{ $orgSettings->address }}</p>@endif
                            <p>
                                @if($orgSettings->phone)<span>{{ $printLang == 'ar' ? 'ت:' : 'T:' }} {{ $orgSettings->phone }}</span>@endif
                                @if($orgSettings->email)<span class="ml-2">{{ $printLang == 'ar' ? 'بريد:' : 'E:' }} {{ $orgSettings->email }}</span>@endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="text-{{ $printLang == 'ar' ? 'left' : 'right' }}">
                        <div class="border-2 border-black px-3 py-1 inline-block mb-2">
                            <h2 class="text-18 font-bold">{{ $printLang == 'ar' ? 'كشف حساب' : 'ACCOUNT STATEMENT' }}</h2>
                        </div>
                        <table class="text-9" style="margin-{{ $printLang == 'ar' ? 'right' : 'left' }}: auto;">
                            <tr>
                                <td class="font-semibold {{ $printLang == 'ar' ? 'pl-3 text-left' : 'pr-3 text-right' }}">{{ $printLang == 'ar' ? 'من:' : 'From:' }}</td>
                                <td>{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold {{ $printLang == 'ar' ? 'pl-3 text-left' : 'pr-3 text-right' }}">{{ $printLang == 'ar' ? 'إلى:' : 'To:' }}</td>
                                <td>{{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Party Info -->
                <div class="mb-3">
                    <div class="border border-black px-2 py-1.5 bg-gray-100">
                        <p class="text-8 font-bold uppercase mb-0.5">{{ $printLang == 'ar' ? 'العميل' : 'PARTY' }}</p>
                        <p class="text-10 font-bold mb-0.5">{{ $party->name }}</p>
                        <div class="text-8">
                            @if($party->phone)<p>{{ $printLang == 'ar' ? 'ت:' : 'T:' }} {{ $party->phone }}</p>@endif
                            @if($party->email)<p>{{ $printLang == 'ar' ? 'بريد:' : 'E:' }} {{ $party->email }}</p>@endif
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="grid grid-cols-4 gap-2 mb-3">
                    <div class="border border-black px-2 py-1.5 text-center">
                        <p class="text-8 text-gray-600">{{ $printLang == 'ar' ? 'مدين' : 'Debit' }}</p>
                        <p class="text-10 font-bold text-red-600">{{ number_format($stats['total_debit'], 2) }}</p>
                    </div>
                    <div class="border border-black px-2 py-1.5 text-center">
                        <p class="text-8 text-gray-600">{{ $printLang == 'ar' ? 'دائن' : 'Credit' }}</p>
                        <p class="text-10 font-bold text-green-600">{{ number_format($stats['total_credit'], 2) }}</p>
                    </div>
                    <div class="border border-black px-2 py-1.5 text-center bg-gray-100">
                        <p class="text-8 text-gray-600">{{ $printLang == 'ar' ? 'الرصيد' : 'Balance' }}</p>
                        <p class="text-10 font-bold {{ $stats['closing_balance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($stats['closing_balance'], 2) }}
                        </p>
                    </div>
                    <div class="border border-black px-2 py-1.5 text-center">
                        <p class="text-8 text-gray-600">{{ $printLang == 'ar' ? 'العمليات' : 'Trans.' }}</p>
                        <p class="text-10 font-bold">{{ $stats['transaction_count'] }}</p>
                    </div>
                </div>

                <!-- Transactions Table -->
                <table class="w-full text-9 mb-3">
                    <thead>
                        <tr class="bg-gray-800 text-white">
                            <th class="border border-gray-700 px-2 py-1.5 text-{{ $printLang == 'ar' ? 'right' : 'left' }} font-bold" style="width: 10%;">{{ $printLang == 'ar' ? 'التاريخ' : 'Date' }}</th>
                            <th class="border border-gray-700 px-2 py-1.5 text-{{ $printLang == 'ar' ? 'right' : 'left' }} font-bold" style="width: 12%;">{{ $printLang == 'ar' ? 'المرجع' : 'Ref' }}</th>
                            <th class="border border-gray-700 px-2 py-1.5 text-{{ $printLang == 'ar' ? 'right' : 'left' }} font-bold">{{ $printLang == 'ar' ? 'الوصف' : 'Description' }}</th>
                            <th class="border border-gray-700 px-2 py-1.5 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-bold" style="width: 12%;">{{ $printLang == 'ar' ? 'مدين' : 'Debit' }}</th>
                            <th class="border border-gray-700 px-2 py-1.5 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-bold" style="width: 12%;">{{ $printLang == 'ar' ? 'دائن' : 'Credit' }}</th>
                            <th class="border border-gray-700 px-2 py-1.5 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-bold" style="width: 12%;">{{ $printLang == 'ar' ? 'الرصيد' : 'Balance' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $index => $transaction)
                        <tr class="{{ $index % 2 == 0 ? '' : 'bg-gray-50' }}">
                            <td class="border border-gray-200 px-2 py-1">{{ $transaction['date']->format('d/m/Y') }}</td>
                            <td class="border border-gray-200 px-2 py-1 font-medium">{{ $transaction['reference'] }}</td>
                            <td class="border border-gray-200 px-2 py-1">{{ $transaction['description'] }}</td>
                            <td class="border border-gray-200 px-2 py-1 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-semibold text-red-600">
                                {{ $transaction['debit'] > 0 ? number_format($transaction['debit'], 2) : '-' }}
                            </td>
                            <td class="border border-gray-200 px-2 py-1 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-semibold text-green-600">
                                {{ $transaction['credit'] > 0 ? number_format($transaction['credit'], 2) : '-' }}
                            </td>
                            <td class="border border-gray-200 px-2 py-1 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-bold {{ $transaction['balance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($transaction['balance'], 2) }}
                            </td>
                        </tr>
                        @endforeach
                        
                        <!-- Totals Row -->
                        <tr class="bg-gray-800 text-white">
                            <td colspan="3" class="border border-gray-700 px-2 py-2 font-bold">{{ $printLang == 'ar' ? 'الإجمالي' : 'TOTAL' }}</td>
                            <td class="border border-gray-700 px-2 py-2 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-bold">{{ number_format($stats['total_debit'], 2) }}</td>
                            <td class="border border-gray-700 px-2 py-2 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-bold">{{ number_format($stats['total_credit'], 2) }}</td>
                            <td class="border border-gray-700 px-2 py-2 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-bold">{{ number_format($stats['closing_balance'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Footer -->
                <div class="mt-4 pt-2 border-t border-black text-center">
                    <p class="text-8">{{ $printLang == 'ar' ? 'طُبعت:' : 'Printed:' }} {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>