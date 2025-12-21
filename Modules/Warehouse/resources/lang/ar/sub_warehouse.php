<?php

return [
    // Page Titles
    'module_name' => 'إدارة المخازن الفرعية',
    'sub_warehouses' => 'المخازن الفرعية',
    'sub_warehouse_list' => 'قائمة المخازن الفرعية',
    'add_sub_warehouse' => 'إضافة مخزن فرعي',
    'edit_sub_warehouse' => 'تعديل المخزن الفرعي',
    'view_sub_warehouse' => 'عرض المخزن الفرعي',
    'sub_warehouse_details' => 'تفاصيل المخزن الفرعي',
    'stock_details' => 'تفاصيل المخزون',
    'add_stock' => 'إضافة مخزون',
    'edit_stock' => 'تعديل المخزون',

    // Form Labels
    'warehouse' => 'المخزن الرئيسي',
    'name' => 'اسم المخزن الفرعي',
    'type' => 'النوع',
    'address' => 'العنوان',
    'country' => 'الدولة',
    'notes' => 'ملاحظات',
    'product' => 'المنتج',
    'quantity' => 'الكمية',
    'current_quantity' => 'الكمية الحالية',
    'new_quantity' => 'الكمية الجديدة',

    // Type Values
    'main' => 'رئيسي',
    'branch' => 'فرع',
    'book_fair' => 'معرض كتاب',
    'temporary' => 'مؤقت',
    'other' => 'أخرى',

    // Statistics
    'total_sub_warehouses' => 'إجمالي المخازن الفرعية',
    'total_products' => 'إجمالي المنتجات',
    'total_quantity' => 'إجمالي الكمية',

    // Actions
    'search' => 'البحث في المخازن الفرعية...',
    'filter_by_warehouse' => 'تصفية حسب المخزن',
    'filter_by_type' => 'تصفية حسب النوع',
    'all_warehouses' => 'جميع المخازن',
    'all_types' => 'جميع الأنواع',
    'select_product' => 'اختر المنتج',
    'add_another_product' => 'إضافة منتج آخر',
    'remove_product' => 'إزالة',

    // Messages
    'sub_warehouse_added' => 'تمت إضافة المخزن الفرعي بنجاح',
    'sub_warehouse_updated' => 'تم تحديث المخزن الفرعي بنجاح',
    'sub_warehouse_deleted' => 'تم حذف المخزن الفرعي بنجاح',
    'stock_added' => 'تمت إضافة المخزون بنجاح',
    'stock_updated' => 'تم تحديث المخزون بنجاح',
    'edit_stock_notice' => 'يمكنك تحديث الكمية لهذا المنتج. سيقوم النظام بإنشاء سجل حركة مخزون لتتبع هذا التغيير.',
    'cannot_delete_has_products' => 'لا يمكن حذف مخزن فرعي يحتوي على منتجات',
    'no_sub_warehouses' => 'لم يتم العثور على مخازن فرعية',
    'no_products' => 'لا توجد منتجات في المخزون',

    // Validation Messages
    'warehouse_required' => 'المخزن الرئيسي مطلوب',
    'warehouse_not_found' => 'المخزن المحدد غير موجود',
    'name_required' => 'اسم المخزن الفرعي مطلوب',
    'name_max' => 'يجب ألا يتجاوز اسم المخزن الفرعي 255 حرفًا',
    'type_required' => 'النوع مطلوب',
    'type_invalid' => 'النوع المحدد غير صالح',
    'country_max' => 'يجب ألا تتجاوز الدولة 255 حرفًا',

    // Placeholders
    'enter_name' => 'أدخل اسم المخزن الفرعي',
    'enter_address' => 'أدخل العنوان (اختياري)',
    'enter_country' => 'أدخل الدولة (اختياري)',
    'enter_notes' => 'أدخل الملاحظات (اختياري)',
    'enter_quantity' => 'أدخل الكمية',
    'select_warehouse' => 'اختر المخزن الرئيسي',
    'select_type' => 'اختر النوع',

    // Book Details
    'book_details' => 'تفاصيل الكتاب',
    'isbn' => 'الرقم الدولي',
    'author' => 'المؤلف',
    'category' => 'التصنيف',
    'cover_type' => 'نوع الغلاف',
    'pages' => 'عدد الصفحات',
];
