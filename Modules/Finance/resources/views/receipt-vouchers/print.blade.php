<!DOCTYPE html>
<html lang="{{ $printLang }}" dir="{{ $printLang == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('finance::receipt.receipt_voucher') }} #{{ $receiptVoucher->voucher_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @if ($printLang == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Cairo', sans-serif;
            }
        </style>
    @else
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            }
        </style>
    @endif
    <style>
        @media print {
            body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100%;
                background: white !important;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: A4;
                margin: 0 !important;
            }
        }

        .receipt-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            min-height: 200mm;
        }

        table {
            border-collapse: collapse;
        }

        .border-all {
            border: 1px solid #000;
        }

        .border-thick {
            border-width: 2px;
        }

        .bg-gray {
            background-color: #e8e8e8;
        }

        .text-8 {
            font-size: 8pt;
            line-height: 1.2;
        }

        .text-9 {
            font-size: 9pt;
            line-height: 1.3;
        }

        .text-10 {
            font-size: 10pt;
            line-height: 1.3;
        }

        .text-11 {
            font-size: 11pt;
            line-height: 1.3;
        }

        .text-14 {
            font-size: 14pt;
            line-height: 1.2;
        }

        .text-18 {
            font-size: 18pt;
            line-height: 1.1;
        }

        .text-28 {
            font-size: 28pt;
            line-height: 1.1;
        }
    </style>
</head>

<body class="bg-gray-100">

    <!-- Print Toolbar -->
    <div class="no-print bg-white border-b sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-2 flex items-center gap-2">
            <button onclick="window.print()"
                class="px-3 py-1.5 text-sm bg-gray-900 text-white rounded hover:bg-gray-800 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                    </path>
                </svg>
                {{ $printLang == 'ar' ? 'طباعة' : 'Print' }}
            </button>
            <button onclick="window.close()" class="px-3 py-1.5 text-sm border text-gray-700 rounded hover:bg-gray-50">
                {{ $printLang == 'ar' ? 'إغلاق' : 'Close' }}
            </button>
        </div>
    </div>

    <div class="p-4">
        <div class="receipt-container shadow-lg">
            <div class="p-6">

                {{-- ══ HEADER ═══════════════════════════════════════════ --}}
                <div class="flex items-start justify-between mb-4 pb-3 border-b-2 border-black">
                    {{-- Left: org info --}}
                    <div style="width: 60%;">
                        @if ($orgSettings->logo_path)
                            <img src="{{ asset('storage/' . $orgSettings->logo_path) }}"
                                alt="{{ $orgSettings->organization_name }}" class="h-12 mb-1.5 object-contain"
                                style="filter: grayscale(100%) contrast(1.2);">
                        @endif
                        <h1 class="text-14 font-bold mb-0.5">{{ $orgSettings->organization_name }}</h1>
                        <div class="text-8">
                            @if ($orgSettings->address)
                                <p>{{ $orgSettings->address }}</p>
                            @endif
                            <p>
                                @if ($orgSettings->phone)
                                    <span>{{ $printLang == 'ar' ? 'ت:' : 'T:' }} {{ $orgSettings->phone }}</span>
                                @endif
                                @if ($orgSettings->email)
                                    <span class="ml-2">{{ $printLang == 'ar' ? 'بريد:' : 'E:' }}
                                        {{ $orgSettings->email }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Right: voucher meta --}}
                    <div class="text-{{ $printLang == 'ar' ? 'left' : 'right' }}">
                        <div class="border-2 border-black px-3 py-1 inline-block mb-2">
                            <h2 class="text-14 font-bold">
                                {{ $printLang == 'ar' ? 'سند قبض' : 'RECEIPT VOUCHER' }}
                            </h2>
                        </div>
                        <table class="text-9" style="margin-{{ $printLang == 'ar' ? 'right' : 'left' }}: auto;">
                            <tr>
                                <td
                                    class="font-semibold {{ $printLang == 'ar' ? 'pl-3 text-left' : 'pr-3 text-right' }}">
                                    {{ $printLang == 'ar' ? 'رقم:' : 'No:' }}
                                </td>
                                <td class="font-bold">{{ $receiptVoucher->voucher_number }}</td>
                            </tr>
                            <tr>
                                <td
                                    class="font-semibold {{ $printLang == 'ar' ? 'pl-3 text-left' : 'pr-3 text-right' }}">
                                    {{ $printLang == 'ar' ? 'التاريخ:' : 'Date:' }}
                                </td>
                                <td>{{ $receiptVoucher->voucher_date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td
                                    class="font-semibold {{ $printLang == 'ar' ? 'pl-3 text-left' : 'pr-3 text-right' }}">
                                    {{ $printLang == 'ar' ? 'طريقة الدفع:' : 'Method:' }}
                                </td>
                                <td>
                                    <span class="font-bold border border-black px-1.5 py-0.5 inline-block text-8">
                                        {{ strtoupper($receiptVoucher->payment_method_label) }}
                                    </span>
                                </td>
                            </tr>
                            @if ($receiptVoucher->reference_number)
                                <tr>
                                    <td
                                        class="font-semibold {{ $printLang == 'ar' ? 'pl-3 text-left' : 'pr-3 text-right' }}">
                                        {{ $printLang == 'ar' ? 'المرجع:' : 'Ref:' }}
                                    </td>
                                    <td>{{ $receiptVoucher->reference_number }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- ══ AMOUNT BANNER ════════════════════════════════════ --}}
                <div class="border-2 border-black bg-gray mb-4 px-4 py-3 text-center">
                    <p class="text-9 font-semibold mb-1">
                        {{ $printLang == 'ar' ? 'المبلغ المستلم' : 'AMOUNT RECEIVED' }}
                    </p>
                    <p class="text-28 font-bold">
                        {{ number_format($receiptVoucher->amount, 2) }}
                        {{ $orgSettings->currency_symbol }}
                    </p>
                </div>

                {{-- ══ RECEIVED FROM / ACCOUNT ══════════════════════════ --}}
                <div class="grid grid-cols-2 gap-3 mb-4">
                    {{-- Received from --}}
                    <div class="border border-black px-2 py-1.5">
                        <p class="text-8 font-bold uppercase mb-0.5">
                            {{ $printLang == 'ar' ? 'استُلم من' : 'RECEIVED FROM' }}
                        </p>
                        <p class="text-10 font-bold mb-0.5">{{ $receiptVoucher->party->name }}</p>
                        <div class="text-8">
                            @if ($receiptVoucher->party->address)
                                <p>{{ $receiptVoucher->party->address }}</p>
                            @endif
                            <p>
                                @if ($receiptVoucher->party->phone)
                                    <span>{{ $printLang == 'ar' ? 'ت:' : 'T:' }}
                                        {{ $receiptVoucher->party->phone }}</span>
                                @endif
                                @if ($receiptVoucher->party->email)
                                    <span class="ml-2">{{ $receiptVoucher->party->email }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Deposited to account --}}
                    <div class="border border-black px-2 py-1.5">
                        <p class="text-8 font-bold uppercase mb-0.5">
                            {{ $printLang == 'ar' ? 'إيداع في حساب' : 'DEPOSITED TO' }}
                        </p>
                        <p class="text-10 font-bold mb-0.5">{{ $receiptVoucher->account->account_name }}</p>
                        <div class="text-8">
                            @if ($receiptVoucher->account->account_number)
                                <p>{{ $printLang == 'ar' ? 'رقم الحساب:' : 'Account No:' }}
                                    {{ $receiptVoucher->account->account_number }}</p>
                            @endif
                            @if ($receiptVoucher->account->bank_name)
                                <p>{{ $receiptVoucher->account->bank_name }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ══ LINKED INVOICE ═══════════════════════════════════ --}}
                @if ($receiptVoucher->salesInvoice)
                    <div class="border border-black px-2 py-1.5 mb-4">
                        <p class="text-8 font-bold uppercase mb-1">
                            {{ $printLang == 'ar' ? 'الفاتورة المرتبطة' : 'LINKED INVOICE' }}
                        </p>
                        <table class="w-full text-9">
                            <tr class="bg-gray">
                                <th
                                    class="border-all px-2 py-1 text-{{ $printLang == 'ar' ? 'right' : 'left' }} font-bold">
                                    {{ $printLang == 'ar' ? 'رقم الفاتورة' : 'Invoice No.' }}
                                </th>
                                <th class="border-all px-2 py-1 text-center font-bold">
                                    {{ $printLang == 'ar' ? 'تاريخ الفاتورة' : 'Invoice Date' }}
                                </th>
                                <th
                                    class="border-all px-2 py-1 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-bold">
                                    {{ $printLang == 'ar' ? 'إجمالي الفاتورة' : 'Invoice Total' }}
                                </th>
                                <th
                                    class="border-all px-2 py-1 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-bold">
                                    {{ $printLang == 'ar' ? 'المبلغ المدفوع' : 'Amount Paid' }}
                                </th>
                                <th
                                    class="border-all px-2 py-1 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-bold">
                                    {{ $printLang == 'ar' ? 'الرصيد المتبقي' : 'Balance Due' }}
                                </th>
                            </tr>
                            <tr>
                                <td class="border-all px-2 py-1 font-semibold">
                                    {{ $receiptVoucher->salesInvoice->invoice_number }}
                                </td>
                                <td class="border-all px-2 py-1 text-center">
                                    {{ $receiptVoucher->salesInvoice->invoice_date->format('d/m/Y') }}
                                </td>
                                <td class="border-all px-2 py-1 text-{{ $printLang == 'ar' ? 'left' : 'right' }}">
                                    {{ number_format($receiptVoucher->salesInvoice->total_amount, 2) }}
                                </td>
                                <td class="border-all px-2 py-1 text-{{ $printLang == 'ar' ? 'left' : 'right' }}">
                                    {{ number_format($receiptVoucher->salesInvoice->paid_amount, 2) }}
                                </td>
                                <td
                                    class="border-all px-2 py-1 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-bold">
                                    {{ number_format($receiptVoucher->salesInvoice->outstanding_balance, 2) }}
                                </td>
                            </tr>
                        </table>
                    </div>
                @endif

                {{-- ══ DESCRIPTION / NOTES ══════════════════════════════ --}}
                @if ($receiptVoucher->description || $receiptVoucher->notes)
                    <div class="mb-4 space-y-2">
                        @if ($receiptVoucher->description)
                            <div class="border border-black px-2 py-1">
                                <p class="text-8 font-bold mb-0.5">
                                    {{ $printLang == 'ar' ? 'البيان:' : 'Description:' }}</p>
                                <p class="text-9">{{ $receiptVoucher->description }}</p>
                            </div>
                        @endif
                        @if ($receiptVoucher->notes)
                            <div class="border border-black px-2 py-1">
                                <p class="text-8 font-bold mb-0.5">{{ $printLang == 'ar' ? 'ملاحظات:' : 'Notes:' }}</p>
                                <p class="text-9">{{ $receiptVoucher->notes }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ══ SIGNATURES ═══════════════════════════════════════ --}}
                <div class="grid grid-cols-3 gap-6 mt-10 pt-4 border-t-2 border-black">
                    <div class="text-center">
                        <div class="pt-10 border-t border-black mt-6">
                            <p class="text-9 font-bold">{{ $printLang == 'ar' ? 'المحاسب' : 'Accountant' }}</p>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="pt-10 border-t border-black mt-6">
                            <p class="text-9 font-bold">{{ $printLang == 'ar' ? 'المدير المالي' : 'Finance Manager' }}
                            </p>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="pt-10 border-t border-black mt-6">
                            <p class="text-9 font-bold">{{ $printLang == 'ar' ? 'المستلم' : 'Received By' }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>

</html>
