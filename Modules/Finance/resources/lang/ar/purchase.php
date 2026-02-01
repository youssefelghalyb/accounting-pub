<?php

return [
    // Module
    'purchase_invoices' => 'فواتير الشراء',
    'purchase_invoice' => 'فاتورة شراء',
    'manage_purchases' => 'إدارة فواتير الشراء من الموردين',

    // Actions
    'create_invoice' => 'إنشاء فاتورة',
    'edit_invoice' => 'تعديل الفاتورة',
    'update_invoice' => 'تحديث الفاتورة',
    'view_invoice' => 'عرض الفاتورة',
    'delete_invoice' => 'حذف الفاتورة',
    'cancel_invoice' => 'إلغاء الفاتورة',

    // Fields
    'invoice_number' => 'رقم الفاتورة',
    'vendor' => 'المورد',
    'select_vendor' => 'اختر المورد',
    'all_vendors' => 'جميع الموردين',
    'invoice_date' => 'تاريخ الفاتورة',
    'due_date' => 'تاريخ الاستحقاق',
    'date' => 'التاريخ',
    'reference_number' => 'الرقم المرجعي',
    'vendor_invoice_number' => 'رقم فاتورة المورد',
    'invoice_info' => 'معلومات الفاتورة',

    // Items
    'items' => 'الأصناف',
    'add_item' => 'إضافة صنف',
    'product' => 'المنتج',
    'select_product' => 'اختر المنتج',
    'quantity' => 'الكمية',
    'unit_price' => 'سعر الوحدة',
    'item_discount' => 'الخصم',
    'line_total' => 'إجمالي السطر',
    'description' => 'الوصف',

    // Amounts
    'amount_breakdown' => 'تفصيل المبالغ',
    'subtotal' => 'المجموع الفرعي',
    'tax_rate' => 'نسبة الضريبة (%)',
    'tax_amount' => 'مبلغ الضريبة',
    'discount_amount' => 'مبلغ الخصم',
    'total_amount' => 'المبلغ الإجمالي',
    'total' => 'الإجمالي',
    'outstanding' => 'المستحق',

    // Payment
    'payment_info' => 'معلومات الدفع',
    'initial_payment' => 'دفعة أولية اختيارية',
    'paid_amount' => 'المبلغ المدفوع',
    'payment_account' => 'حساب الدفع',
    'select_account' => 'اختر الحساب',
    'pay' => 'دفع',

    // Statistics
    'total_purchases' => 'إجمالي المشتريات',
    'unpaid_invoices' => 'الفواتير غير المدفوعة',
    'paid_invoices' => 'الفواتير المدفوعة',

    // Status
    'status' => 'الحالة',
    'all_statuses' => 'جميع الحالات',

    // Actions
    'actions' => 'الإجراءات',
    'view' => 'عرض',
    'edit' => 'تعديل',
    'delete' => 'حذف',
    'cancel' => 'إلغاء',
    'filter' => 'تصفية',
    'reset' => 'إعادة تعيين',
    'search' => 'بحث',
    'search_placeholder' => 'البحث في الفواتير...',

    // Notes
    'notes' => 'ملاحظات',
    'enter_notes' => 'أدخل أي ملاحظات أو معلومات إضافية...',

    // Messages
    'created_successfully' => 'تم إنشاء فاتورة الشراء بنجاح',
    'updated_successfully' => 'تم تحديث فاتورة الشراء بنجاح',
    'deleted_successfully' => 'تم حذف فاتورة الشراء بنجاح',
    'cancelled_successfully' => 'تم إلغاء فاتورة الشراء بنجاح',
    'cannot_edit_status' => 'لا يمكن تعديل الفاتورة :status',
    'cannot_delete_has_payments' => 'لا يمكن حذف فاتورة بها مدفوعات',
    'cannot_cancel_has_payments' => 'لا يمكن إلغاء فاتورة بها مدفوعات',

    // Empty States
    'no_invoices' => 'لا توجد فواتير شراء',
    'no_invoices_desc' => 'ابدأ بإنشاء فاتورة الشراء الأولى',

    // Additional fields
    'print' => 'طباعة',
    'quick_actions' => 'إجراءات سريعة',
    'invoice_details' => 'تفاصيل الفاتورة',
    'payment_history' => 'سجل المدفوعات',
    'paid' => 'مدفوع',
    'tax' => 'الضريبة',
    'discount' => 'الخصم',
    'created_by' => 'أنشئت بواسطة',
    'created_at' => 'تاريخ الإنشاء',
    'last_updated' => 'آخر تحديث',
    'confirm_cancel' => 'هل أنت متأكد من إلغاء هذه الفاتورة؟',
    'service_expense_invoice' => 'فاتورة خدمة أو مصروف',
    'manual_amount_description' => 'للفواتير الخدمية أو المصروفات بدون منتجات، أدخل المبلغ الإجمالي هنا',
    'manual_amount' => 'المبلغ اليدوي',
    'leave_zero_for_items' => 'اترك 0 لاستخدام المنتجات أدناه',
    'manual_amount_mode' => 'وضع المبلغ اليدوي نشط',
    'items_disabled_info' => 'المنتجات معطلة. امسح المبلغ اليدوي لإضافة منتجات.',
    'items_or_amount_required' => 'أضف منتجات أو أدخل مبلغ يدوي',

    'enter_terms' => 'أدخل الشروط والأحكام...',
    'quick_add_isbn' => 'إضافة سريعة بواسطة ISBN',
    'scan_or_enter_isbn' => 'امسح الباركود أو أدخل ISBN لإضافة المنتجات بسرعة',
    'enter_isbn_placeholder' => 'أدخل ISBN واضغط Enter...',
    'please_enter_isbn' => 'الرجاء إدخال ISBN',
    'product_not_found' => 'لم يتم العثور على منتج بهذا ISBN',
    'product_added' => 'تمت إضافة المنتج',
    'at_least_one_item' => 'مطلوب عنصر واحد على الأقل',
    'add' => 'إضافة',
    'items_optional' => 'الأصناف (اختياري)',
    'no_items_yet' => 'لا توجد أصناف بعد',
    'is_taxable' => 'هل هو خاضع للضريبة؟'
];
