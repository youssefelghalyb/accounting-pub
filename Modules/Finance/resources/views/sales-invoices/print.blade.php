<!DOCTYPE html>
<html lang="{{ $printLang }}" dir="{{ $printLang == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('finance::invoice.sales_invoices') }} #{{ $salesInvoice->invoice_number }}</title>
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
                height: 100%;
                background: white !important;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: A4;
                margin: 0 !important;
                /* ✅ remove all print margins */
            }
        }

        .invoice-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            min-height: 270mm;
            /* Minimum A4 height minus margins */
        }

        table {
            border-collapse: collapse;
        }

        .border-all {
            border: 1px solid #000;
        }

        .border-t {
            border-top: 1px solid #000;
        }

        .border-b {
            border-bottom: 1px solid #000;
        }

        .border-l {
            border-left: 1px solid #000;
        }

        .border-r {
            border-right: 1px solid #000;
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
    </style>
</head>

<body class="bg-gray-100">
    <!-- Print Toolbar -->
    <div class="no-print bg-white border-b sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <button onclick="window.print()"
                    class="px-3 py-1.5 text-sm bg-gray-900 text-white rounded hover:bg-gray-800 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    {{ $printLang == 'ar' ? 'طباعة' : 'Print' }}
                </button>
                <button onclick="window.close()"
                    class="px-3 py-1.5 text-sm border text-gray-700 rounded hover:bg-gray-50">
                    {{ $printLang == 'ar' ? 'إغلاق' : 'Close' }}
                </button>
            </div>
        </div>
    </div>

    <div class="p-4">
        <div class="invoice-container shadow-lg">
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-start justify-between mb-4 pb-3 border-b-2 border-black">
                    <div style="width: 60%;">
                        @if ($orgSettings->logo_path)
                            <img src="{{ asset('storage/'.$orgSettings->logo_path) }}"
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
                            @if ($orgSettings->website)
                                <p>{{ $orgSettings->website }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="text-{{ $printLang == 'ar' ? 'left' : 'right' }}">
                        <div class="border-2 border-black px-3 py-1 inline-block mb-2">
                            <h2 class="text-18 font-bold">{{ $printLang == 'ar' ? 'فاتورة' : 'INVOICE' }}</h2>
                        </div>
                        <table class="text-9" style="margin-{{ $printLang == 'ar' ? 'right' : 'left' }}: auto;">
                            <tr>
                                <td
                                    class="font-semibold {{ $printLang == 'ar' ? 'pl-3 text-left' : 'pr-3 text-right' }}">
                                    {{ $printLang == 'ar' ? 'رقم:' : 'No:' }}</td>
                                <td class="font-bold">{{ $salesInvoice->invoice_number }}</td>
                            </tr>
                            <tr>
                                <td
                                    class="font-semibold {{ $printLang == 'ar' ? 'pl-3 text-left' : 'pr-3 text-right' }}">
                                    {{ $printLang == 'ar' ? 'التاريخ:' : 'Date:' }}</td>
                                <td>{{ $salesInvoice->invoice_date->format('d/m/Y') }}</td>
                            </tr>
                            @if ($salesInvoice->due_date)
                                <tr>
                                    <td
                                        class="font-semibold {{ $printLang == 'ar' ? 'pl-3 text-left' : 'pr-3 text-right' }}">
                                        {{ $printLang == 'ar' ? 'الاستحقاق:' : 'Due:' }}</td>
                                    <td>{{ $salesInvoice->due_date->format('d/m/Y') }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td
                                    class="font-semibold {{ $printLang == 'ar' ? 'pl-3 text-left' : 'pr-3 text-right' }}">
                                    {{ $printLang == 'ar' ? 'الحالة:' : 'Status:' }}</td>
                                <td><span
                                        class="font-bold border border-black px-1.5 py-0.5 inline-block text-8">{{ strtoupper($salesInvoice->status_label) }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Bill To -->
                <div class="mb-3">
                    <div class="border border-black px-2 py-1.5 bg-gray">
                        <p class="text-8 font-bold uppercase mb-0.5">
                            {{ $printLang == 'ar' ? 'فاتورة إلى' : 'BILL TO' }}</p>
                        <p class="text-10 font-bold mb-0.5">{{ $salesInvoice->party->name }}</p>
                        <div class="text-8">
                            @if ($salesInvoice->party->address)
                                <p>{{ $salesInvoice->party->address }}</p>
                            @endif
                            <p>
                                @if ($salesInvoice->party->phone)
                                    <span>{{ $printLang == 'ar' ? 'ت:' : 'T:' }}
                                        {{ $salesInvoice->party->phone }}</span>
                                @endif
                                @if ($salesInvoice->party->email)
                                    <span class="ml-2">{{ $printLang == 'ar' ? 'بريد:' : 'E:' }}
                                        {{ $salesInvoice->party->email }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <table class="w-full text-9 mb-3">
                    <thead>
                        <tr class="bg-gray">
                            <th
                                class="border-all px-2 py-1.5 text-{{ $printLang == 'ar' ? 'right' : 'left' }} font-bold">
                                {{ $printLang == 'ar' ? 'البند' : 'Item Description' }}</th>
                            <th class="border-all px-2 py-1.5 text-center font-bold" style="width: 7%;">
                                {{ $printLang == 'ar' ? 'كمية' : 'Qty' }}</th>
                            <th class="border-all px-2 py-1.5 text-{{ $printLang == 'ar' ? 'right' : 'left' }} font-bold"
                                style="width: 11%;">{{ $printLang == 'ar' ? 'السعر' : 'Unit Price' }}</th>
                            @if ($salesInvoice->items->sum('discount_amount') > 0)
                                <th class="border-all px-2 py-1.5 text-{{ $printLang == 'ar' ? 'right' : 'left' }} font-bold"
                                    style="width: 10%;">{{ $printLang == 'ar' ? 'خصم' : 'Discount' }}</th>
                            @endif
                            <th class="border-all px-2 py-1.5 text-{{ $printLang == 'ar' ? 'right' : 'left' }} font-bold"
                                style="width: 12%;">{{ $printLang == 'ar' ? 'المبلغ' : 'Amount' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $minRows = 12; // Minimum rows to ensure full page
                            $itemCount = $salesInvoice->items->count();
                            $hasDiscount = $salesInvoice->items->sum('discount_amount') > 0;
                            $colspan = $hasDiscount ? 5 : 4;
                        @endphp

                        @foreach ($salesInvoice->items as $item)
                            <tr>
                                <td class="border-all px-2 py-1">
                                    <p class="font-semibold">{{ $item->product_name }}</p>
                                    @if ($item->product_sku)
                                        <p class="text-8">{{ $item->product_sku }}</p>
                                    @endif
                                    @if ($item->description)
                                        <p class="text-8">{{ $item->description }}</p>
                                    @endif
                                </td>
                                <td class="border-all px-2 py-1 text-center font-semibold">
                                    {{ number_format($item->quantity, 0) }}</td>
                                <td class="border-all px-2 py-1 text-{{ $printLang == 'ar' ? 'right' : 'left' }}">
                                    {{ number_format($item->unit_price, 2) }}</td>
                                @if ($hasDiscount)
                                    <td class="border-all px-2 py-1 text-{{ $printLang == 'ar' ? 'right' : 'left' }}">
                                        {{ $item->discount_amount > 0 ? number_format($item->discount_amount, 2) : '-' }}
                                    </td>
                                @endif
                                <td
                                    class="border-all px-2 py-1 text-{{ $printLang == 'ar' ? 'right' : 'left' }} font-bold">
                                    {{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach

                        {{-- Add empty rows to fill the page --}}
                        @if ($itemCount < $minRows)
                            @for ($i = $itemCount; $i < $minRows; $i++)
                                <tr>
                                    <td class="border-all px-2 py-1" style="height: 30px;">&nbsp;</td>
                                    <td class="border-all px-2 py-1">&nbsp;</td>
                                    <td class="border-all px-2 py-1">&nbsp;</td>
                                    @if ($hasDiscount)
                                        <td class="border-all px-2 py-1">&nbsp;</td>
                                    @endif
                                    <td class="border-all px-2 py-1">&nbsp;</td>
                                </tr>
                            @endfor
                        @endif
                    </tbody>
                </table>

                <!-- Summary -->
                <div class="flex justify-between items-start mb-3">
                    <!-- Notes -->
                    <div style="width: 52%;">
                        @if ($salesInvoice->notes)
                            <div class="border border-black px-2 py-1 mb-2">
                                <p class="text-8 font-bold mb-0.5">{{ $printLang == 'ar' ? 'ملاحظات:' : 'Notes:' }}</p>
                                <p class="text-9">{{ $salesInvoice->notes }}</p>
                            </div>
                        @endif
                        @if ($salesInvoice->payment_terms)
                            <div class="border border-black px-2 py-1">
                                <p class="text-8 font-bold mb-0.5">
                                    {{ $printLang == 'ar' ? 'شروط الدفع:' : 'Payment Terms:' }}</p>
                                <p class="text-9">{{ $salesInvoice->payment_terms }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Totals -->
                    <div style="width: 280px;">
                        <table class="w-full text-9">
                            <tr class="bg-gray">
                                <td class="border-all px-2 py-1 font-bold">
                                    {{ $printLang == 'ar' ? 'المجموع الفرعي:' : 'Subtotal:' }}</td>
                                <td class="border-all px-2 py-1 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-semibold"
                                    style="width: 35%;">{{ number_format($salesInvoice->subtotal, 2) }}</td>
                            </tr>
                            @if ($salesInvoice->discount_amount > 0)
                                <tr>
                                    <td class="border-all px-2 py-1 font-bold">
                                        {{ $printLang == 'ar' ? 'الخصم:' : 'Discount:' }}</td>
                                    <td
                                        class="border-all px-2 py-1 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-semibold">
                                        ({{ number_format($salesInvoice->discount_amount, 2) }})</td>
                                </tr>
                            @endif
                            @if ($salesInvoice->is_taxable && $salesInvoice->tax_amount > 0)
                                <tr class="bg-gray">
                                    <td class="border-all px-2 py-1 font-bold">
                                        {{ $printLang == 'ar' ? 'الضريبة' : 'Tax' }} ({{ $salesInvoice->tax_rate }}%):
                                    </td>
                                    <td
                                        class="border-all px-2 py-1 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-semibold">
                                        {{ number_format($salesInvoice->tax_amount, 2) }}</td>
                                </tr>
                            @endif
                            <tr class="bg-gray">
                                <td class="border-all border-thick px-2 py-1.5 font-bold text-10">
                                    {{ $printLang == 'ar' ? 'الإجمالي:' : 'TOTAL:' }}</td>
                                <td
                                    class="border-all border-thick px-2 py-1.5 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-bold text-11">
                                    {{ number_format($salesInvoice->total_amount, 2) }}
                                    {{ $orgSettings->currency_symbol }}</td>
                            </tr>
                            @if ($salesInvoice->paid_amount > 0)
                                <tr>
                                    <td class="border-all px-2 py-1 font-bold">
                                        {{ $printLang == 'ar' ? 'المدفوع:' : 'Amount Paid:' }}</td>
                                    <td
                                        class="border-all px-2 py-1 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-semibold">
                                        {{ number_format($salesInvoice->paid_amount, 2) }}</td>
                                </tr>
                            @endif
                            @if ($salesInvoice->outstanding_balance > 0)
                                <tr class="bg-gray">
                                    <td class="border-all border-thick px-2 py-1.5 font-bold text-10">
                                        {{ $printLang == 'ar' ? 'المبلغ المستحق:' : 'BALANCE DUE:' }}</td>
                                    <td
                                        class="border-all border-thick px-2 py-1.5 text-{{ $printLang == 'ar' ? 'left' : 'right' }} font-bold text-11">
                                        {{ number_format($salesInvoice->outstanding_balance, 2) }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Signatures -->
                <div class="grid grid-cols-2 gap-6 mt-8 pt-4 border-t-2 border-black">
                    <div class="text-center">
                        <div class="pt-12 border-t border-black mt-6">
                            <p class="text-9 font-bold">{{ $printLang == 'ar' ? 'المحاسب' : 'Accountant' }}</p>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="pt-12 border-t border-black mt-6">
                            <p class="text-9 font-bold">{{ $printLang == 'ar' ? 'العميل' : 'Customer' }}</p>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</body>

</html>
