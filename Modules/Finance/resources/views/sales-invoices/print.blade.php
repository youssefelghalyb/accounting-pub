<!DOCTYPE html>
<html lang="{{ $printLang }}" dir="{{ $printLang == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $printLang == 'ar' ? 'فاتورة مبيعات' : 'Sales Invoice' }} #{{ $salesInvoice->invoice_number }}</title>

    @if ($printLang == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet">
    @else
        <link
            href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap"
            rel="stylesheet">
    @endif

    <style>
        /* ─── Variables ─────────────────────────────── */
        :root {
            --ink: #0d0d0d;
            --ink-mid: #3a3a3a;
            --ink-light: #6b6b6b;
            --rule: #c8c8c8;
            --rule-heavy: #0d0d0d;
            --accent: #0d0d0d;
            --bg-band: #f2f2f2;
            --bg-total: #0d0d0d;
            --white: #ffffff;
            --page-w: 210mm;
        }

        /* ─── Reset ──────────────────────────────────── */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* ─── Base Typography ────────────────────────── */
        body {
            font-family: {{ $printLang == 'ar' ? "'Cairo', sans-serif" : "'IBM Plex Sans', sans-serif" }};
            font-size: 9pt;
            color: var(--ink);
            background: #e5e5e5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ─── Screen toolbar ─────────────────────────── */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--ink);
            color: var(--white);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toolbar h3 {
            font-size: 11pt;
            font-weight: 600;
            flex: 1;
            letter-spacing: .02em;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            font-size: 8.5pt;
            font-weight: 600;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            letter-spacing: .03em;
            transition: opacity .15s;
            font-family: inherit;
        }

        .btn:hover {
            opacity: .8;
        }

        .btn-print {
            background: var(--white);
            color: var(--ink);
        }

        .btn-close {
            background: transparent;
            color: #ccc;
            border: 1px solid #444;
        }

        /* ─── Page wrapper ───────────────────────────── */
        .page-wrap {
            padding: 20px 0 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        /* ─── Invoice sheet ──────────────────────────── */
        .invoice {
            width: var(--page-w);
            background: var(--white);
            box-shadow: 0 4px 24px rgba(0, 0, 0, .18);
            padding: 14mm 12mm 12mm;
        }

        /* ─── Header ─────────────────────────────────── */
        .inv-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 8px;
            border-bottom: 2.5px solid var(--rule-heavy);
            margin-bottom: 10px;
        }

        .inv-header-left {}

        .org-logo {
            height: 44px;
            object-fit: contain;
            margin-bottom: 5px;
            filter: grayscale(100%) contrast(1.2);
        }

        .org-name {
            font-size: 13pt;
            font-weight: 800;
            letter-spacing: -.01em;
            line-height: 1.1;
            margin-bottom: 3px;
        }

        .org-meta {
            font-size: 7.5pt;
            color: var(--ink-mid);
            line-height: 1.6;
        }

        .inv-header-right {
            text-align: {{ $printLang == 'ar' ? 'left' : 'right' }};
        }

        .inv-title-box {
            display: inline-block;
            border: 2.5px solid var(--ink);
            padding: 3px 14px;
            margin-bottom: 8px;
        }

        .inv-title {
            font-size: 20pt;
            font-weight: 800;
            letter-spacing: .06em;
            line-height: 1;
        }

        .inv-meta-table {
            font-size: 8.5pt;
        }

        .inv-meta-table td {
            padding: 1px 0;
        }

        .inv-meta-table .label {
            font-weight: 600;
            padding-{{ $printLang == 'ar' ? 'left' : 'right' }}: 10px;
            color: var(--ink-mid);
            white-space: nowrap;
        }

        .inv-meta-table .value {
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            border: 1.5px solid var(--ink);
            padding: 1px 6px;
            font-size: 7.5pt;
            font-weight: 700;
            letter-spacing: .05em;
        }

        /* ─── Bill To ────────────────────────────────── */
        .bill-to {
            border: 1px solid var(--rule);
            border-{{ $printLang == 'ar' ? 'right' : 'left' }}: 3px solid var(--ink);
            padding: 7px 10px;
            margin-bottom: 10px;
            background: var(--bg-band);
        }

        .bill-to-label {
            font-size: 7pt;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--ink-light);
            margin-bottom: 2px;
        }

        .bill-to-name {
            font-size: 11pt;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .bill-to-meta {
            font-size: 7.5pt;
            color: var(--ink-mid);
            line-height: 1.5;
        }

        /* ─── Items Table ────────────────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8.5pt;
        }

        .items-table thead tr {
            background: var(--ink);
            color: var(--white);
        }

        .items-table thead th {
            padding: 6px 8px;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            font-size: 7.5pt;
            white-space: nowrap;
        }

        .items-table thead th.text-center {
            text-align: center;
        }

        .items-table thead th.text-start {
            text-align: {{ $printLang == 'ar' ? 'right' : 'left' }};
        }

        .items-table thead th.text-end {
            text-align: {{ $printLang == 'ar' ? 'left' : 'right' }};
        }

        .items-table tbody tr {
            border-bottom: 1px solid var(--rule);
        }

        .items-table tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .items-table tbody tr.empty-row {
            background: var(--white) !important;
        }

        /* CRITICAL: prevent rows from splitting across printed pages */
        .items-table tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .items-table thead {
            display: table-header-group;
            /* repeat on every page */
        }

        .items-table tbody td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .items-table tbody td.text-center {
            text-align: center;
        }

        .items-table tbody td.text-start {
            text-align: {{ $printLang == 'ar' ? 'right' : 'left' }};
        }

        .items-table tbody td.text-end {
            text-align: {{ $printLang == 'ar' ? 'left' : 'right' }};
        }

        .item-no {
            color: var(--ink-light);
            font-size: 8pt;
            {{ $printLang == 'ar' ? "'IBM Plex Mono'" : "'IBM Plex Mono'" }};
        }

        .item-name {
            font-weight: 600;
            line-height: 1.3;
        }

        .item-sku {
            font-size: 7.5pt;
            color: var(--ink-light);
            margin-top: 1px;
        }

        .item-desc {
            font-size: 7.5pt;
            color: var(--ink-mid);
            margin-top: 1px;
        }

        .item-qty {
            font-weight: 700;
        }

        .item-price {
            color: var(--ink-mid);
        }

        .item-disc {
            color: var(--ink-mid);
        }

        .item-total {
            font-weight: 700;
        }

        .empty-cell {
            height: 26px;
        }

        /* ─── Footer section ─────────────────────────── */
        .inv-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }

        .inv-footer-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        /* Notes / terms */
        .note-box {
            border: 1px solid var(--rule);
            padding: 6px 9px;
        }

        .note-label {
            font-size: 7pt;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--ink-light);
            margin-bottom: 2px;
        }

        .note-text {
            font-size: 8pt;
            color: var(--ink-mid);
            line-height: 1.5;
        }

        /* Totals block */
        .totals-table {
            width: 260px;
            border-collapse: collapse;
            font-size: 8.5pt;
            flex-shrink: 0;
        }

        .totals-table tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .totals-table td {
            padding: 5px 9px;
            border: 1px solid var(--rule);
        }

        .totals-table .t-label {
            font-weight: 600;
            color: var(--ink-mid);
        }

        .totals-table .t-value {
            text-align: {{ $printLang == 'ar' ? 'left' : 'right' }};
            font-weight: 600;
            white-space: nowrap;
        }

        .totals-table .t-band {
            background: var(--bg-band);
        }

        .totals-table .t-total-row td {
            background: var(--bg-total);
            color: var(--white);
            font-weight: 800;
            font-size: 10pt;
            border-color: var(--bg-total);
        }

        .totals-table .t-balance-row td {
            background: var(--bg-band);
            font-weight: 800;
            font-size: 10pt;
            border: 1.5px solid var(--ink);
        }

        /* ─── Signatures ─────────────────────────────── */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1.5px solid var(--rule-heavy);
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .sig-block {
            text-align: center;
        }

        .sig-line {
            height: 44px;
            border-bottom: 1px solid var(--ink);
            margin-bottom: 4px;
        }

        .sig-label {
            font-size: 8pt;
            font-weight: 700;
            letter-spacing: .04em;
            color: var(--ink-mid);
        }

        /* ─── Page number watermark (screen only) ──── */
        .page-stamp {
            font-size: 7pt;
            color: var(--rule);
            text-align: center;
            margin-top: 6px;
            letter-spacing: .08em;
        }

        /* ─── PRINT OVERRIDES ────────────────────────── */
        @media print {
            body {
                background: white !important;
            }

            .toolbar,
            .page-stamp {
                display: none !important;
            }

            .page-wrap {
                padding: 0;
                background: white;
            }

            .invoice {
                width: 100%;
                box-shadow: none;
                padding: 10mm 10mm 8mm;
                /* Let browser handle page breaks naturally */
            }

            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }
        }
    </style>
</head>

<body>

    {{-- ── Screen Toolbar ── --}}
    <div class="toolbar no-print">
        <h3>
            {{ $printLang == 'ar' ? 'معاينة الطباعة — فاتورة' : 'Print Preview — Invoice' }}
            #{{ $salesInvoice->invoice_number }}
        </h3>

        {{-- Language switcher --}}
        <a href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}"
            style="color: #aaa; font-size: 8.5pt; text-decoration: none; margin-inline-end: 6px;">AR</a>
        <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}"
            style="color: #aaa; font-size: 8.5pt; text-decoration: none; margin-inline-end: 14px;">EN</a>

        <button class="btn btn-print" onclick="window.print()">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            {{ $printLang == 'ar' ? 'طباعة' : 'Print' }}
        </button>
        <button class="btn btn-close" onclick="window.close()">
            {{ $printLang == 'ar' ? 'إغلاق' : 'Close' }}
        </button>
    </div>

    <div class="page-wrap">
        <div class="invoice">

            {{-- ── HEADER ── --}}
            <div class="inv-header">
                <div class="inv-header-left">
                    @if ($orgSettings->logo_path)
                        <img src="{{ asset('storage/' . $orgSettings->logo_path) }}"
                            alt="{{ $orgSettings->organization_name }}" class="org-logo">
                    @endif
                    <div class="org-name">{{ $orgSettings->organization_name }}</div>
                    <div class="org-meta">
                        @if ($orgSettings->address)
                            <div>{{ $orgSettings->address }}</div>
                        @endif
                        @if ($orgSettings->phone)
                            <span>{{ $printLang == 'ar' ? 'ت: ' : 'T: ' }}{{ $orgSettings->phone }}</span>
                        @endif
                        @if ($orgSettings->phone && $orgSettings->email)
                            &nbsp;·&nbsp;
                        @endif
                        @if ($orgSettings->email)
                            <span>{{ $orgSettings->email }}</span>
                        @endif
                        @if ($orgSettings->website)
                            <div>{{ $orgSettings->website }}</div>
                        @endif
                    </div>
                </div>

                <div class="inv-header-right">
                    <div class="inv-title-box">
                        <div class="inv-title">{{ $printLang == 'ar' ? 'فاتورة' : 'INVOICE' }}</div>
                    </div>
                    <table class="inv-meta-table">
                        <tr>
                            <td class="label">{{ $printLang == 'ar' ? 'رقم الفاتورة' : 'Invoice No.' }}</td>
                            <td class="value">{{ $salesInvoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td class="label">{{ $printLang == 'ar' ? 'التاريخ' : 'Date' }}</td>
                            <td class="value">{{ $salesInvoice->invoice_date->format('d / m / Y') }}</td>
                        </tr>
                        @if ($salesInvoice->due_date)
                            <tr>
                                <td class="label">{{ $printLang == 'ar' ? 'تاريخ الاستحقاق' : 'Due Date' }}</td>
                                <td class="value">{{ $salesInvoice->due_date->format('d / m / Y') }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="label">{{ $printLang == 'ar' ? 'الحالة' : 'Status' }}</td>
                            <td class="value">
                                <span class="status-badge">{{ strtoupper($salesInvoice->status_label) }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- ── BILL TO ── --}}
            <div class="bill-to">
                <div class="bill-to-label">{{ $printLang == 'ar' ? 'فاتورة إلى' : 'Bill To' }}</div>
                <div class="bill-to-name">{{ $salesInvoice->party->name }}</div>
                <div class="bill-to-meta">
                    @if ($salesInvoice->party->address)
                        {{ $salesInvoice->party->address }}<br>
                    @endif
                    @if ($salesInvoice->party->phone)
                        {{ $printLang == 'ar' ? 'ت: ' : 'T: ' }}{{ $salesInvoice->party->phone }}
                    @endif
                    @if ($salesInvoice->party->phone && $salesInvoice->party->email)
                        &nbsp;·&nbsp;
                    @endif
                    @if ($salesInvoice->party->email)
                        {{ $salesInvoice->party->email }}
                    @endif
                </div>
            </div>

            {{-- ── ITEMS TABLE ── --}}
            @php
                $hasDiscount = $salesInvoice->items->sum('discount_amount') > 0;
                $itemCount = $salesInvoice->items->count();
                $minRows = 10;
            @endphp

            <table class="items-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width:4%;">#</th>
                        <th class="text-start">{{ $printLang == 'ar' ? 'البند' : 'Description' }}</th>
                        <th class="text-center" style="width:7%;">{{ $printLang == 'ar' ? 'كمية' : 'Qty' }}</th>
                        <th class="text-end" style="width:12%;">{{ $printLang == 'ar' ? 'سعر الوحدة' : 'Unit Price' }}
                        </th>
                        @if ($hasDiscount)
                            <th class="text-end" style="width:10%;">{{ $printLang == 'ar' ? 'خصم' : 'Discount' }}</th>
                        @endif
                        <th class="text-end" style="width:13%;">{{ $printLang == 'ar' ? 'الإجمالي' : 'Total' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salesInvoice->items as $i => $item)
                        <tr>
                            <td class="text-center item-no">{{ $i + 1 }}</td>
                            <td class="text-start">
                                <div class="item-name">{{ $item->product_name }}</div>
                                @if ($item->product_sku)
                                    <div class="item-sku">{{ $item->product_sku }}</div>
                                @endif
                                @if ($item->description)
                                    <div class="item-desc">{{ $item->description }}</div>
                                @endif
                            </td>
                            <td class="text-center item-qty">{{ number_format($item->quantity, 0) }}</td>
                            <td class="text-end item-price">{{ number_format($item->unit_price, 2) }}</td>
                            @if ($hasDiscount)
                                <td class="text-end item-disc">
                                    {{ $item->discount_amount > 0 ? '(' . number_format($item->discount_amount, 2) . ')' : '—' }}
                                </td>
                            @endif
                            <td class="text-end item-total">{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach

                    {{-- Padding rows --}}
                    @for ($j = $itemCount; $j < $minRows; $j++)
                        <tr class="empty-row">
                            <td class="empty-cell"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            @if ($hasDiscount)
                                <td></td>
                            @endif
                            <td></td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            {{-- ── FOOTER (notes + totals) ── --}}
            <div class="inv-footer">

                {{-- Left: notes & payment terms --}}
                <div class="inv-footer-left">
                    @if ($salesInvoice->notes)
                        <div class="note-box">
                            <div class="note-label">{{ $printLang == 'ar' ? 'ملاحظات' : 'Notes' }}</div>
                            <div class="note-text">{{ $salesInvoice->notes }}</div>
                        </div>
                    @endif
                    @if ($salesInvoice->payment_terms)
                        <div class="note-box">
                            <div class="note-label">{{ $printLang == 'ar' ? 'شروط الدفع' : 'Payment Terms' }}</div>
                            <div class="note-text">{{ $salesInvoice->payment_terms }}</div>
                        </div>
                    @endif
                </div>

                {{-- Right: totals --}}
                <table class="totals-table">
                    <tr class="t-band">
                        <td class="t-label">{{ $printLang == 'ar' ? 'المجموع الفرعي' : 'Subtotal' }}</td>
                        <td class="t-value">{{ number_format($salesInvoice->subtotal, 2) }}</td>
                    </tr>
                    @if ($salesInvoice->discount_amount > 0)
                        <tr>
                            <td class="t-label">{{ $printLang == 'ar' ? 'الخصم' : 'Discount' }}</td>
                            <td class="t-value">({{ number_format($salesInvoice->discount_amount, 2) }})</td>
                        </tr>
                    @endif
                    @if ($salesInvoice->is_taxable && $salesInvoice->tax_amount > 0)
                        <tr class="t-band">
                            <td class="t-label">
                                {{ $printLang == 'ar' ? 'ضريبة' : 'Tax' }} ({{ $salesInvoice->tax_rate }}%)
                            </td>
                            <td class="t-value">{{ number_format($salesInvoice->tax_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="t-total-row">
                        <td class="t-label">{{ $printLang == 'ar' ? 'الإجمالي' : 'TOTAL' }}</td>
                        <td class="t-value">
                            {{ number_format($salesInvoice->total_amount, 2) }}
                            {{ $orgSettings->currency_symbol }}
                        </td>
                    </tr>
                    @if ($salesInvoice->paid_amount > 0)
                        <tr>
                            <td class="t-label">{{ $printLang == 'ar' ? 'المدفوع' : 'Amount Paid' }}</td>
                            <td class="t-value">{{ number_format($salesInvoice->paid_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if ($salesInvoice->outstanding_balance > 0)
                        <tr class="t-balance-row">
                            <td class="t-label">{{ $printLang == 'ar' ? 'المبلغ المستحق' : 'BALANCE DUE' }}</td>
                            <td class="t-value">
                                {{ number_format($salesInvoice->outstanding_balance, 2) }}
                                {{ $orgSettings->currency_symbol }}
                            </td>
                        </tr>
                    @endif
                </table>
            </div>

            {{-- ── SIGNATURES ── --}}
            <div class="signatures">
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-label">{{ $printLang == 'ar' ? 'المحاسب' : 'Accountant' }}</div>
                </div>
                <div class="sig-block">
                    <div class="sig-line"></div>
                    <div class="sig-label">{{ $printLang == 'ar' ? 'العميل' : 'Customer' }}</div>
                </div>
            </div>

        </div>{{-- .invoice --}}

        <div class="page-stamp">
            {{ $orgSettings->organization_name }} &nbsp;·&nbsp; {{ $salesInvoice->invoice_number }}
        </div>
    </div>{{-- .page-wrap --}}

</body>

</html>
