<?php

return [
    'sales_invoices' => 'فواتير المبيعات',
    'sales_invoice' => 'فاتورة مبيعات',
    'add_invoice' => 'إضافة فاتورة',
    'create_invoice' => 'إنشاء فاتورة مبيعات',
    'edit_invoice' => 'تعديل فاتورة',
    'invoice_details' => 'تفاصيل الفاتورة',
    'invoice_list' => 'قائمة الفواتير',
    'invoice_management' => 'إدارة فواتير المبيعات',

    'invoice_number' => 'رقم الفاتورة',
    'invoice_date' => 'تاريخ الفاتورة',
    'due_date' => 'تاريخ الاستحقاق',
    'party' => 'العميل',
    'select_party' => 'اختر العميل',
    'payment_terms' => 'شروط الدفع',
    'is_taxable' => 'خاضع للضريبة',
    'tax_rate' => 'معدل الضريبة (%)',
    'tax_amount' => 'قيمة الضريبة',
    'discount_type' => 'نوع الخصم',
    'discount_value' => 'قيمة الخصم',
    'discount_amount' => 'مبلغ الخصم',
    'subtotal' => 'المجموع الفرعي',
    'total_amount' => 'المبلغ الإجمالي',
    'paid_amount' => 'المبلغ المدفوع',
    'outstanding_balance' => 'الرصيد المستحق',
    'notes' => 'ملاحظات',
    'terms_conditions' => 'الشروط والأحكام',

    'statuses' => [
        'draft' => 'مسودة',
        'pending' => 'قيد الانتظار',
        'unpaid' => 'غير مدفوعة',
        'partial' => 'مدفوعة جزئياً',
        'paid' => 'مدفوعة',
        'cancelled' => 'ملغاة',
    ],

    'discount_types' => [
        'fixed' => 'مبلغ ثابت',
        'percentage' => 'نسبة مئوية',
    ],

    // Invoice Items
    'items' => 'عناصر الفاتورة',
    'add_item' => 'إضافة عنصر',
    'product' => 'المنتج',
    'select_product' => 'اختر المنتج',
    'quantity' => 'الكمية',
    'unit_price' => 'سعر الوحدة',
    'item_discount' => 'الخصم',
    'line_total' => 'المجموع',
    'description' => 'الوصف',

    // Statistics
    'total_invoices' => 'إجمالي الفواتير',
    'unpaid_invoices' => 'الفواتير غير المدفوعة',
    'partial_invoices' => 'الدفعات الجزئية',
    'paid_invoices' => 'الفواتير المدفوعة',
    'overdue_invoices' => 'الفواتير المتأخرة',
    'total_sales' => 'إجمالي المبيعات',
    'total_outstanding' => 'إجمالي المستحقات',

    // Payment
    'payment_info' => 'معلومات الدفع',
    'initial_payment' => 'الدفعة الأولى',
    'payment_account' => 'حساب الدفع',
    'select_account' => 'اختر الحساب',
    'record_payment' => 'تسجيل الدفع',

    // Actions
    'view_invoice' => 'عرض الفاتورة',
    'print_invoice' => 'طباعة الفاتورة',
    'download_pdf' => 'تحميل PDF',
    'send_email' => 'إرسال بريد إلكتروني',
    'cancel_invoice' => 'إلغاء الفاتورة',
    'activate_invoice' => 'تفعيل الفاتورة',
    'activated_successfully' => 'تم تفعيل الفاتورة بنجاح',

    // Messages
    'created_successfully' => 'تم إنشاء الفاتورة بنجاح',
    'updated_successfully' => 'تم تحديث الفاتورة بنجاح',
    'deleted_successfully' => 'تم حذف الفاتورة بنجاح',
    'cancelled_successfully' => 'تم إلغاء الفاتورة بنجاح',

    'cannot_delete_has_payments' => 'لا يمكن حذف فاتورة لها دفعات',
    'cannot_cancel_has_payments' => 'لا يمكن إلغاء فاتورة لها دفعات',
    'cannot_edit_status' => 'لا يمكن تعديل فاتورة بحالة: :status',

    'no_invoices' => 'لا توجد فواتير',
    'search_placeholder' => 'البحث برقم الفاتورة أو العميل...',
    'confirm_delete' => 'هل أنت متأكد من حذف هذه الفاتورة؟',
    'confirm_cancel' => 'هل أنت متأكد من إلغاء هذه الفاتورة؟',

    // Info
    'invoice_info' => 'معلومات الفاتورة',
    'customer_info' => 'معلومات العميل',
    'amount_breakdown' => 'تفاصيل المبلغ',
    'overdue' => 'متأخرة',
    'days_overdue' => 'متأخرة :days يوم',

    'enter_notes' => 'أدخل أي ملاحظات إضافية...',
    'enter_terms' => 'أدخل الشروط والأحكام...',
    'quick_add_isbn' => 'إضافة سريعة بواسطة ISBN',
    'scan_or_enter_isbn' => 'امسح الباركود أو أدخل ISBN لإضافة المنتجات بسرعة',
    'enter_isbn_placeholder' => 'أدخل ISBN واضغط Enter...',
    'please_enter_isbn' => 'الرجاء إدخال ISBN',
    'product_not_found' => 'لم يتم العثور على منتج بهذا ISBN',
    'product_added' => 'تمت إضافة المنتج',
    'at_least_one_item' => 'مطلوب عنصر واحد على الأقل',
    'payment_history' => 'سجل المدفوعات',

    // Product Drawer
    'search' => 'بحث',
    'search_by_name_isbn' => 'البحث بالاسم أو ISBN أو SKU...',
    'category' => 'التصنيف',
    'all_categories' => 'جميع التصنيفات',
    'sub_category' => 'التصنيف الفرعي',
    'all_sub_categories' => 'جميع التصنيفات الفرعية',
    'author' => 'المؤلف',
    'all_authors' => 'جميع المؤلفين',
    'no_products_found' => 'لم يتم العثور على منتجات',
    'stock' => 'المخزون',
    'add' => 'إضافة',


    'sub_warehouse' => 'المستودع الفرعي',
    'select_sub_warehouse' => 'اختر المستودع الفرعي',
    'please_select_sub_warehouse' => 'يرجى اختيار مستودع فرعي أولاً',
    'please_select_sub_warehouse_first' => 'يرجى اختيار مستودع فرعي قبل إضافة المنتجات',
    'insufficient_stock_warning' => 'تحذير من نقص المخزون',
    'insufficient_stock_for' => 'نقص المخزون لـ',
    'available' => 'متاح',
    'requested' => 'مطلوب',
    'please_check_stock_warnings' => 'يرجى التحقق من تحذيرات المخزون قبل الإرسال',



    // 
    'draft_found' => 'تم العثور على مسودة',
    'draft_saved' => 'تم حفظ المسودة',
    'restore_draft' => 'استعادة المسودة',
    'start_fresh' => 'بدء جديد',
    'draft_restored' => 'تم استعادة المسودة بنجاح',
    'draft_discarded' => 'تم تجاهل المسودة',
    'failed_to_restore' => 'فشل في استعادة المسودة',
    'save_failed' => 'فشل الحفظ',
    'just_now' => 'الآن',
    'minutes_ago' => 'دقائق مضت',
    'hours_ago' => 'ساعات مضت',
    'days_ago' => 'أيام مضت',
    'party_change_warning' => 'تحذير تغيير العميل',
    'party_change_receipt_warning' => 'هذه الفاتورة لديها :count سند(ات) قبض. إذا قمت بتغيير العميل، سيتم إعادة تعيين هذه السندات للعميل الجديد. هل تريد المتابعة؟',

];
