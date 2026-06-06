<?php

return [
    // Page Titles
    'books' => 'الكتب',
    'book_list' => 'قائمة الكتب',
    'add_book' => 'إضافة كتاب',
    'edit_book' => 'تعديل كتاب',
    'view_book' => 'عرض الكتاب',
    'book_details' => 'تفاصيل الكتاب',
    'register_book' => 'تسجيل كتاب جديد',

    // Form Labels
    'author' => 'المؤلف',
    'category' => 'الفئة',
    'sub_category' => 'الفئة الفرعية',
    'isbn' => 'الترقيم الدولي',
    'num_of_pages' => 'عدد الصفحات',
    'cover_type' => 'نوع الغلاف',
    'published_at' => 'تاريخ النشر',
    'language' => 'اللغة',
    'is_translated' => 'مترجم',
    'translated_from' => 'مترجم من',
    'translated_to' => 'مترجم إلى',
    'translator_name' => 'اسم المترجم',

    // Cover Types
    'hard' => 'غلاف كرتوني',
    'soft' => 'غلاف ورقي',

    // Sections
    'book_info' => 'معلومات الكتاب',
    'product_info' => 'معلومات المنتج',
    'translation_info' => 'معلومات الترجمة',

    // Statistics
    'total_books' => 'إجمالي الكتب',
    'total_pages' => 'إجمالي الصفحات',
    'translated_books' => 'الكتب المترجمة',

    // Actions
    'search' => 'البحث في الكتب...',
    'filter_by_author' => 'تصفية حسب المؤلف',
    'filter_by_category' => 'تصفية حسب الفئة',
    'all_authors' => 'جميع المؤلفين',
    'all_categories' => 'جميع الفئات',
    'view_contracts' => 'عرض العقود',

    // Messages
    'book_added' => 'تم إضافة الكتاب بنجاح',
    'book_updated' => 'تم تحديث الكتاب بنجاح',
    'book_deleted' => 'تم حذف الكتاب بنجاح',
    'no_books' => 'لم يتم العثور على كتب',
    'cannot_delete_has_contracts' => 'لا يمكن حذف كتاب له عقود موجودة',

    // Validation
    'name_required' => 'اسم الكتاب مطلوب',
    'isbn_required' => 'الترقيم الدولي مطلوب',
    'isbn_unique' => 'الترقيم الدولي مستخدم بالفعل',
    'author_invalid' => 'المؤلف غير صحيح',
    'category_invalid' => 'الفئة غير صحيحة',
    'sub_category_invalid' => 'الفئة الفرعية غير صحيحة',
    'cover_type_required' => 'نوع الغلاف مطلوب',
    'base_price_required' => 'السعر الأساسي مطلوب',
    'base_price_positive' => 'يجب أن يكون السعر الأساسي رقماً موجباً',

    // Placeholders
    'select_author' => 'اختر المؤلف (اختياري)',
    'select_category' => 'اختر الفئة (اختياري)',
    'select_sub_category' => 'اختر الفئة الفرعية (اختياري)',
    'enter_isbn' => 'أدخل الترقيم الدولي',
    'enter_pages' => 'أدخل عدد الصفحات',
    'select_cover_type' => 'اختر نوع الغلاف',
    'select_published_date' => 'اختر تاريخ النشر',
    'enter_language' => 'أدخل اللغة',
    'enter_translated_from' => 'أدخل اللغة الأصلية',
    'enter_translated_to' => 'أدخل اللغة المستهدفة',
    'enter_translator_name' => 'أدخل اسم المترجم',
    'bulk_price_update' => 'تحديث الأسعار جماعياً',
    'bulk_price_desc' => 'ضبط أسعار جميع الكتب دفعة واحدة',
    'operation' => 'العملية',
    'increment' => 'زيادة',
    'decrement' => 'تخفيض',
    'update_type' => 'نوع التحديث',
    'fixed_amount' => 'مبلغ ثابت',
    'percentage' => 'نسبة مئوية',
    'amount' => 'المبلغ',
    'percentage_amount' => 'النسبة المئوية (%)',
    'apply_update' => 'تطبيق التحديث',
    'update_again' => 'تحديث مجدداً',
    'updating_prices' => 'جاري تحديث الأسعار...',
    'please_wait' => 'يرجى الانتظار',
    'update_complete' => 'اكتمل التحديث',
    'books_updated_successfully' => 'كتب تم تحديثها بنجاح',
    'total' => 'الإجمالي',
    'updated' => 'محدّثة',
    'failed' => 'فشلت',
    'errors_found' => 'أخطاء',

    // Sales History
    'sales_overview' => 'سجل المبيعات',
    'total_orders' => 'إجمالي الطلبات',
    'total_qty_sold' => 'الكمية المباعة',
    'total_revenue' => 'إجمالي الإيرادات',
    'total_discount' => 'إجمالي الخصومات',
    'price_range' => 'نطاق السعر',
    'avg_price' => 'متوسط السعر',
    'invoice_number' => 'رقم الفاتورة',
    'date' => 'التاريخ',
    'customer' => 'العميل',
    'qty' => 'الكمية',
    'unit_price' => 'سعر الوحدة',
    'discount' => 'الخصم',
    'line_total' => 'إجمالي السطر',
    'status' => 'الحالة',
    'page_total' => 'مجموع الصفحة',
    'no_sales_yet' => 'لم يتم استخدام هذا الكتاب في أي فاتورة مبيعات بعد.',
    'gift' => 'هدية',
    // Invoice discount clarification
    'before_invoice_discount' => 'قبل خصم الفاتورة',
    'invoice_discount_note'   => 'خصم الفاتورة',
    'invoice_discount_tooltip'=> 'هذا خصم على مستوى الفاتورة بأكملها وليس على هذا البند فقط',

];