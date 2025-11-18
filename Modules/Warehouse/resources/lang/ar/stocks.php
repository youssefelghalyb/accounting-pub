<?php

return [
    // Module Name
    'module_name' => 'مخزون المستودعات',
    'stocks' => 'المخزون',
    'stock' => 'مخزون',

    // Page Titles
    'stock_list' => 'قائمة المخزون',
    'add_stock' => 'إضافة مخزون',
    'edit_stock' => 'تعديل المخزون',
    'view_stock' => 'عرض المخزون',
    'stock_details' => 'تفاصيل المخزون',
    'product_details' => 'تفاصيل المنتج',
    'book_details' => 'تفاصيل الكتاب',

    // Field Labels
    'product' => 'المنتج',
    'product_name' => 'اسم المنتج',
    'product_type' => 'نوع المنتج',
    'product_sku' => 'رمز المنتج',
    'warehouse' => 'المستودع',
    'warehouse_name' => 'اسم المستودع',
    'location' => 'الموقع',
    'description' => 'الوصف',
    'quantity' => 'الكمية',
    'available_quantity' => 'الكمية المتاحة',
    'reserved_quantity' => 'الكمية المحجوزة',
    'total_quantity' => 'الكمية الإجمالية',
    'minimum_quantity' => 'الحد الأدنى للكمية',
    'status' => 'الحالة',
    'stock_level' => 'مستوى المخزون',
    'base_price' => 'السعر الأساسي',

    // Book Fields
    'author' => 'المؤلف',
    'category' => 'التصنيف',
    'isbn' => 'الرقم الدولي',
    'publisher' => 'الناشر',
    'publication_year' => 'سنة النشر',
    'language' => 'اللغة',
    'pages' => 'الصفحات',

    // Status Options
    'active' => 'نشط',
    'inactive' => 'غير نشط',

    // Stock Level Options
    'in_stock' => 'متوفر',
    'low_stock' => 'كمية منخفضة',
    'out_of_stock' => 'نفذت الكمية',

    // Statistics
    'total_stocks' => 'إجمالي المخزون',
    'active_stocks' => 'المخزون النشط',
    'low_stock_items' => 'منتجات بكمية منخفضة',
    'out_of_stock_items' => 'نفذت الكمية',

    // Filters
    'all_warehouses' => 'جميع المستودعات',
    'all_levels' => 'جميع المستويات',
    'all_statuses' => 'جميع الحالات',

    // Placeholders
    'select_product' => 'اختر المنتج',
    'select_status' => 'اختر الحالة',
    'enter_warehouse_name' => 'أدخل اسم المستودع',
    'enter_location' => 'أدخل الموقع',
    'enter_quantity' => 'أدخل الكمية',
    'enter_minimum_quantity' => 'أدخل الحد الأدنى للكمية',
    'enter_description' => 'أدخل الوصف',
    'search' => 'البحث في المخزون...',

    // Messages
    'stock_added' => 'تمت إضافة المخزون بنجاح',
    'stock_updated' => 'تم تحديث المخزون بنجاح',
    'stock_deleted' => 'تم حذف المخزون بنجاح',

    // Validation Messages
    'product_required' => 'المنتج مطلوب',
    'product_not_found' => 'المنتج غير موجود',
    'warehouse_name_required' => 'اسم المستودع مطلوب',
    'warehouse_name_max' => 'اسم المستودع يجب ألا يتجاوز 255 حرف',
    'quantity_required' => 'الكمية مطلوبة',
    'quantity_integer' => 'الكمية يجب أن تكون رقماً',
    'quantity_min' => 'الكمية يجب أن تكون على الأقل 0',
    'status_required' => 'الحالة مطلوبة',
    'status_invalid' => 'الحالة غير صحيحة',
    'minimum_quantity_required' => 'الحد الأدنى للكمية مطلوب',
    'minimum_quantity_integer' => 'الحد الأدنى للكمية يجب أن يكون رقماً',
    'minimum_quantity_min' => 'الحد الأدنى للكمية يجب أن يكون على الأقل 0',
];
