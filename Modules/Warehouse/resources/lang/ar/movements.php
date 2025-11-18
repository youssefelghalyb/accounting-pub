<?php

return [
    // Module Name
    'module_name' => 'حركات المخزون',
    'movements' => 'الحركات',
    'movement' => 'حركة',

    // Page Titles
    'movement_list' => 'قائمة الحركات',
    'add_movement' => 'إضافة حركة',
    'edit_movement' => 'تعديل الحركة',
    'view_movement' => 'عرض الحركة',
    'movement_details' => 'تفاصيل الحركة',

    // Field Labels
    'reference_number' => 'رقم المرجع',
    'type' => 'النوع',
    'movement_date' => 'تاريخ الحركة',
    'source_warehouse' => 'المستودع المصدر',
    'destination_warehouse' => 'المستودع الوجهة',
    'warehouses' => 'المستودعات',
    'notes' => 'ملاحظات',
    'status' => 'الحالة',
    'total_items' => 'إجمالي العناصر',
    'products' => 'المنتجات',
    'product' => 'المنتج',
    'product_type' => 'نوع المنتج',
    'quantity' => 'الكمية',
    'item_notes' => 'ملاحظات',

    // Movement Types
    'type_in' => 'إدخال مخزون',
    'type_out' => 'إخراج مخزون',
    'type_transfer' => 'نقل',
    'type_adjustment' => 'تعديل',

    // Status Options
    'status_pending' => 'قيد الانتظار',
    'status_completed' => 'مكتمل',
    'status_cancelled' => 'ملغى',

    // Statistics
    'total_movements' => 'إجمالي الحركات',
    'pending_movements' => 'قيد الانتظار',
    'completed_movements' => 'المكتملة',
    'total_items_moved' => 'العناصر المنقولة',

    // Filters
    'all_types' => 'جميع الأنواع',
    'all_statuses' => 'جميع الحالات',

    // Placeholders
    'select_type' => 'اختر النوع',
    'select_status' => 'اختر الحالة',
    'select_product' => 'اختر المنتج',
    'enter_reference_number' => 'أدخل رقم المرجع',
    'search' => 'البحث في الحركات...',

    // Bulk Operations
    'add_multiple_products' => 'إضافة منتجات متعددة لهذه الحركة',
    'add_product' => 'إضافة منتج',
    'products_in_movement' => 'منتجات في هذه الحركة',

    // Messages
    'movement_added' => 'تمت إضافة الحركة بنجاح',
    'movement_updated' => 'تم تحديث الحركة بنجاح',
    'movement_deleted' => 'تم حذف الحركة بنجاح',
    'movement_error' => 'خطأ في معالجة الحركة',
    'cannot_edit_completed' => 'لا يمكن تعديل حركة مكتملة',
    'cannot_delete_completed' => 'لا يمكن حذف حركة مكتملة',

    // Validation Messages
    'reference_number_required' => 'رقم المرجع مطلوب',
    'reference_number_unique' => 'رقم المرجع موجود بالفعل',
    'type_required' => 'نوع الحركة مطلوب',
    'type_invalid' => 'نوع الحركة غير صحيح',
    'movement_date_required' => 'تاريخ الحركة مطلوب',
    'movement_date_invalid' => 'تاريخ الحركة غير صحيح',
    'status_required' => 'الحالة مطلوبة',
    'status_invalid' => 'الحالة غير صحيحة',

    'items_required' => 'منتج واحد على الأقل مطلوب',
    'items_min' => 'منتج واحد على الأقل مطلوب',
    'item_product_required' => 'المنتج مطلوب',
    'item_product_not_found' => 'المنتج غير موجود',
    'item_quantity_required' => 'الكمية مطلوبة',
    'item_quantity_integer' => 'الكمية يجب أن تكون رقماً',
    'item_quantity_min' => 'الكمية يجب أن تكون على الأقل 1',
];
