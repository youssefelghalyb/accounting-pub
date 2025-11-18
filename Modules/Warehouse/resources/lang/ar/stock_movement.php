<?php

return [
    // Page Titles
    'module_name' => 'إدارة حركة المخزون',
    'stock_movements' => 'حركات المخزون',
    'stock_movement_list' => 'قائمة حركات المخزون',
    'add_stock_movement' => 'إضافة حركة مخزون',
    'create_movements' => 'إنشاء حركات مخزون',
    'edit_stock_movement' => 'تعديل حركة المخزون',
    'view_stock_movement' => 'عرض حركة المخزون',
    'stock_movement_details' => 'تفاصيل حركة المخزون',

    // Form Labels
    'product' => 'المنتج',
    'from_sub_warehouse' => 'من المخزن الفرعي',
    'to_sub_warehouse' => 'إلى المخزن الفرعي',
    'quantity' => 'الكمية',
    'movement_type' => 'نوع الحركة',
    'reason' => 'السبب',
    'reference_id' => 'رقم المرجع',
    'notes' => 'ملاحظات',
    'user' => 'المستخدم',
    'date' => 'التاريخ',

    // Movement Types
    'transfer' => 'تحويل',
    'inbound' => 'وارد',
    'outbound' => 'صادر',

    // Statistics
    'total_movements' => 'إجمالي الحركات',
    'total_transfers' => 'إجمالي التحويلات',
    'total_inbound' => 'إجمالي الوارد',
    'total_outbound' => 'إجمالي الصادر',

    // Actions
    'search' => 'البحث في الحركات...',
    'filter_by_type' => 'تصفية حسب النوع',
    'filter_by_warehouse' => 'تصفية حسب المخزن الفرعي',
    'filter_by_product' => 'تصفية حسب المنتج',
    'all_types' => 'جميع الأنواع',
    'all_warehouses' => 'جميع المخازن الفرعية',
    'all_products' => 'جميع المنتجات',
    'add_another_movement' => 'إضافة حركة أخرى',
    'remove_movement' => 'إزالة',

    // Messages
    'movements_created' => 'تم إنشاء حركات المخزون بنجاح',
    'movement_updated' => 'تم تحديث حركة المخزون بنجاح',
    'movement_deleted' => 'تم حذف حركة المخزون بنجاح',
    'movements_failed' => 'فشل إنشاء حركات المخزون',
    'no_movements' => 'لم يتم العثور على حركات مخزون',
    'transfer_requires_both_warehouses' => 'التحويل يتطلب كلاً من المخزن المصدر والمخزن الوجهة',
    'inbound_requires_destination' => 'الحركة الواردة تتطلب مخزن وجهة',
    'outbound_requires_source' => 'الحركة الصادرة تتطلب مخزن مصدر',
    'insufficient_stock' => 'مخزون غير كافٍ في المخزن المصدر',

    // Validation Messages
    'movements_required' => 'مطلوب حركة واحدة على الأقل',
    'movements_invalid' => 'بيانات الحركات غير صالحة',
    'movements_min' => 'مطلوب حركة واحدة على الأقل',
    'product_required' => 'المنتج مطلوب',
    'product_not_found' => 'المنتج المحدد غير موجود',
    'from_warehouse_not_found' => 'المخزن المصدر غير موجود',
    'to_warehouse_not_found' => 'مخزن الوجهة غير موجود',
    'quantity_required' => 'الكمية مطلوبة',
    'quantity_min' => 'يجب أن تكون الكمية 1 على الأقل',
    'type_required' => 'نوع الحركة مطلوب',
    'type_invalid' => 'نوع الحركة غير صالح',

    // Placeholders
    'enter_quantity' => 'أدخل الكمية',
    'enter_reason' => 'أدخل السبب (اختياري)',
    'enter_reference_id' => 'أدخل رقم المرجع (اختياري)',
    'enter_notes' => 'أدخل الملاحظات (اختياري)',
    'select_product' => 'اختر المنتج',
    'select_from_warehouse' => 'اختر المخزن المصدر',
    'select_to_warehouse' => 'اختر مخزن الوجهة',
    'select_type' => 'اختر نوع الحركة',

    // Instructions
    'bulk_instructions' => 'يمكنك إضافة حركات مخزون متعددة في وقت واحد. انقر على "إضافة حركة أخرى" لإضافة المزيد.',
    'transfer_instructions' => 'تحويل: نقل المخزون من مخزن فرعي إلى آخر',
    'inbound_instructions' => 'وارد: استلام مخزون جديد في مخزن فرعي',
    'outbound_instructions' => 'صادر: إزالة المخزون من مخزن فرعي',
];
