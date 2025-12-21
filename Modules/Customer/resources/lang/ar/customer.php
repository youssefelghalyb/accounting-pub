<?php

return [
    // Page Titles
    'customer_management' => 'إدارة العملاء',
    'customers' => 'العملاء',
    'add_customer' => 'إضافة عميل',
    'edit_customer' => 'تعديل عميل',
    'customer_details' => 'تفاصيل العميل',
    
    // Form Fields
    'name' => 'الاسم',
    'type' => 'النوع',
    'phone' => 'الهاتف',
    'email' => 'البريد الإلكتروني',
    'address' => 'العنوان',
    'tax_number' => 'الرقم الضريبي',
    'status' => 'الحالة',
    
    // Placeholders
    'enter_name' => 'أدخل اسم العميل',
    'enter_phone' => 'أدخل رقم الهاتف',
    'enter_email' => 'أدخل البريد الإلكتروني',
    'enter_address' => 'أدخل العنوان',
    'enter_tax_number' => 'أدخل الرقم الضريبي',
    'select_type' => 'اختر نوع العميل',
    
    // Customer Types
    'types' => [
        'individual' => 'فردي',
        'company' => 'شركة',
        'online' => 'أونلاين',
    ],
    
    // Status
    'active' => 'نشط',
    'inactive' => 'غير نشط',
    'all_types' => 'كل الأنواع',
    'all_statuses' => 'كل الحالات',
    
    // Statistics
    'total_customers' => 'إجمالي العملاء',
    'active_customers' => 'العملاء النشطون',
    'individual' => 'فردي',
    'company' => 'شركة',
    'online' => 'أونلاين',
    
    // Customer Info
    'customer_list' => 'قائمة العملاء',
    'basic_info' => 'المعلومات الأساسية',
    'contact_info' => 'معلومات الاتصال',
    'tax_info' => 'المعلومات الضريبية',
    
    // Metrics
    'total_orders' => 'إجمالي الطلبات',
    'total_spent' => 'إجمالي الإنفاق',
    'avg_order_value' => 'متوسط قيمة الطلب',
    'outstanding_balance' => 'الرصيد المستحق',
    'customer_timeline' => 'الجدول الزمني للعميل',
    'first_order' => 'أول طلب',
    'last_order' => 'آخر طلب',
    
    // Activity
    'recent_orders' => 'الطلبات الأخيرة',
    'recent_payments' => 'المدفوعات الأخيرة',
    'order_number' => 'رقم الطلب',
    'payment_number' => 'رقم الدفعة',
    'total' => 'الإجمالي',
    'amount' => 'المبلغ',
    'method' => 'طريقة الدفع',
    
    // Actions
    'create_order' => 'إنشاء طلب',
    'create_invoice' => 'إنشاء فاتورة',
    'new_order_for_customer' => 'إنشاء طلب جديد لهذا العميل',
    'new_invoice_for_customer' => 'إنشاء فاتورة لهذا العميل',
    'activate' => 'تفعيل',
    'deactivate' => 'إلغاء التفعيل',
    
    // Messages
    'customer_added' => 'تمت إضافة العميل بنجاح',
    'customer_updated' => 'تم تحديث العميل بنجاح',
    'customer_deleted' => 'تم حذف العميل بنجاح',
    'customer_activated' => 'تم تفعيل العميل بنجاح',
    'customer_deactivated' => 'تم إلغاء تفعيل العميل بنجاح',
    'cannot_delete_has_orders' => 'لا يمكن حذف عميل لديه طلبات',
    
    // Empty States
    'no_customers' => 'لا توجد عملاء',
    'no_orders' => 'لا توجد طلبات',
    'no_payments' => 'لا توجد مدفوعات',
    
    // Search
    'search' => 'البحث عن عملاء...',
    
    // Confirmations
    'confirm_delete' => 'هل أنت متأكد من حذف هذا العميل؟',
    'confirm_activate' => 'هل أنت متأكد من تفعيل هذا العميل؟',
    'confirm_deactivate' => 'هل أنت متأكد من إلغاء تفعيل هذا العميل؟',
    
    // Validation
    'validation' => [
        'name_required' => 'اسم العميل مطلوب',
        'type_required' => 'نوع العميل مطلوب',
        'type_invalid' => 'نوع العميل المحدد غير صحيح',
        'email_invalid' => 'الرجاء إدخال بريد إلكتروني صحيح',
        'email_unique' => 'هذا البريد الإلكتروني مسجل بالفعل',
        'tax_number_unique' => 'هذا الرقم الضريبي مسجل بالفعل',
    ],

    // Customer Details Page
    'customer_since' => 'عميل منذ',
    'days_active' => 'أيام النشاط',
    'last_updated' => 'آخر تحديث',
    'customer_notes' => 'ملاحظات العميل',
    'notes_feature_coming_soon' => 'ميزة الملاحظات قادمة قريباً. ستتمكن من إضافة ملاحظات حول هذا العميل.',
    'activity_log' => 'سجل النشاط',
    'customer_created' => 'تم إنشاء العميل',
    'customer_updated' => 'تم تحديث العميل',
    'current_status' => 'الحالة الحالية',
    'status_can_be_changed' => 'استخدم الزر أعلاه لتفعيل أو إلغاء تفعيل هذا العميل',
];