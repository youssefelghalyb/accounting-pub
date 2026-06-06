<?php

return [
    // Page Titles
    'contracts'       => 'عقود المؤلفين',
    'contract_list'   => 'قائمة العقود',
    'add_contract'    => 'إضافة عقد',
    'edit_contract'   => 'تعديل عقد',
    'view_contract'   => 'عرض العقد',
    'contract_details'=> 'تفاصيل العقد',
    'create_contract' => 'إنشاء عقد جديد',
    'book_name'       => 'اسم الكتاب',
    'contract'        => 'عقد',

    // Form Labels
    'author'          => 'المؤلف',
    'authors'         => 'المؤلفون',
    'co_authors'      => 'المؤلفون المشتركون',
    'book'            => 'الكتاب',
    'contract_date'   => 'تاريخ العقد',
    'contract_price'  => 'قيمة العقد',
    'percentage_from_book_profit' => 'نسبة من أرباح الكتاب (%)',
    'contract_file'   => 'ملف العقد',
    'representative'  => 'المفوض',

    // Contract Status
    'payment_status'  => 'حالة الدفع',
    'paid'            => 'مدفوع بالكامل',
    'partial'         => 'مدفوع جزئياً',
    'pending'         => 'في انتظار الدفع',

    // Statistics
    'total_contracts'     => 'إجمالي العقود',
    'total_value'         => 'إجمالي قيمة العقود',
    'total_paid'          => 'إجمالي المدفوع',
    'outstanding'         => 'الرصيد المستحق',
    'payment_percentage'  => 'نسبة تقدم الدفع',
    'outstanding_balance' => 'الرصيد المستحق',

    // Actions
    'search'            => 'البحث في العقود...',
    'filter_by_author'  => 'تصفية حسب المؤلف',
    'filter_by_book'    => 'تصفية حسب الكتاب',
    'filter_by_status'  => 'تصفية حسب الحالة',
    'all_authors'       => 'جميع المؤلفين',
    'all_books'         => 'جميع الكتب',
    'all_statuses'      => 'جميع الحالات',
    'add_payment'       => 'إضافة دفعة',
    'view_payments'     => 'عرض سجل الدفعات',
    'download_contract' => 'تحميل العقد',
    'payment_history'   => 'سجل الدفعات',
    'save_contract'     => 'حفظ العقد',

    // Messages
    'contract_added'                  => 'تم إضافة العقد بنجاح',
    'contract_updated'                => 'تم تحديث العقد بنجاح',
    'contract_deleted'                => 'تم حذف العقد بنجاح',
    'no_contracts'                    => 'لم يتم العثور على عقود',
    'cannot_delete_has_transactions'  => 'لا يمكن حذف عقد له معاملات موجودة',
    'no_payments'                     => 'لم يتم العثور على دفعات لهذا العقد',
    'no_payments_description'         => 'لا توجد حالياً دفعات مرتبطة بهذا العقد.',

    // Validation
    'author_required'                 => 'يجب تحديد مؤلف واحد على الأقل',
    'author_invalid'                  => 'أحد المؤلفين المحددين غير صحيح',
    'representative_required'         => 'يجب تحديد المؤلف المفوض',
    'representative_must_be_author'   => 'يجب أن يكون المؤلف المفوض ضمن قائمة المؤلفين المحددين',
    'book_required'                   => 'الكتاب مطلوب',
    'book_invalid'                    => 'الكتاب غير صحيح',
    'contract_date_required'          => 'تاريخ العقد مطلوب',
    'contract_price_required'         => 'قيمة العقد مطلوبة',
    'contract_price_positive'         => 'يجب أن تكون قيمة العقد رقماً موجباً',
    'percentage_required'             => 'نسبة الربح مطلوبة',
    'percentage_min'                  => 'لا يمكن أن تكون النسبة سالبة',
    'percentage_max'                  => 'لا يمكن أن تتجاوز النسبة 100',
    'contract_file_invalid'           => 'يجب أن يكون ملف العقد بصيغة PDF أو DOC',
    'contract_file_max_size'          => 'يجب ألا يتجاوز حجم ملف العقد 5 ميجابايت',

    // Placeholders
    'select_author'         => 'اختر المؤلف',
    'search_add_author'     => 'ابحث عن مؤلف لإضافته...',
    'no_authors_selected'   => 'لم يتم إضافة أي مؤلف بعد',
    'set_as_representative' => 'تعيين كمفوض',
    'remove_author'         => 'إزالة المؤلف',
    'select_book'           => 'اختر الكتاب',
    'enter_contract_price'  => 'أدخل قيمة العقد',
    'enter_percentage'      => 'أدخل النسبة (0-100)',
    'upload_contract_file'  => 'رفع ملف العقد (PDF/DOC)',
];