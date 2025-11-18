<?php

return [
    // Page Titles
    'deduction_management' => 'إدارة الخصومات',
    'deduction_list' => 'قائمة الخصومات',
    'deductions' => 'الخصومات',
    'add_deduction' => 'إضافة خصم',
    'edit_deduction' => 'تعديل الخصم',
    'deduction_details' => 'تفاصيل الخصم',
    'manage_deductions' => 'إدارة وتتبع خصومات الموظفين',

    // Form Labels
    'employee' => 'الموظف',
    'deduction_type' => 'نوع الخصم',
    'deduction_date' => 'تاريخ الخصم',
    'days' => 'الأيام',
    'days_label' => 'يوم',
    'amount' => 'المبلغ',
    'reason' => 'السبب',
    'notes' => 'ملاحظات',
    'deduction_information' => 'معلومات الخصم',
    'employee_information' => 'معلومات الموظف',

    // Deduction Types
    'type_days' => 'خصم أيام',
    'type_amount' => 'مبلغ ثابت',
    'type_unpaid_leave' => 'إجازة بدون أجر',
    'type_advance_deduction' => 'خصم سلفة',

    // Messages
    'deduction_created' => 'تم إنشاء الخصم بنجاح',
    'deduction_updated' => 'تم تحديث الخصم بنجاح',
    'deduction_deleted' => 'تم حذف الخصم بنجاح',
    'confirm_delete' => 'هل أنت متأكد من حذف هذا الخصم؟',

    // Source
    'source' => 'المصدر',
    'from_leave' => 'من إجازة',
    'manual' => 'يدوي',
    'linked_to_leave' => 'هذا الخصم مرتبط بطلب إجازة',
    'leave_reference' => 'مرجع الإجازة',
    'deduction_reference' => 'مرجع الخصم',

    // Search & Filter
    'search_deductions' => 'البحث باسم الموظف...',
    'filter_by_type' => 'تصفية حسب النوع',

    // Empty States
    'no_deductions' => 'لا توجد خصومات',
    'no_deductions_description' => 'لم يتم العثور على خصومات. قم بإنشاء واحدة للبدء.',

    // Validation
    'validation' => [
        'employee_required' => 'يرجى اختيار الموظف',
        'type_required' => 'يرجى اختيار نوع الخصم',
        'days_invalid' => 'يجب أن تكون الأيام على الأقل 1',
        'amount_invalid' => 'يجب أن يكون المبلغ أكبر من 0',
        'date_required' => 'تاريخ الخصم مطلوب',
        'reason_required' => 'يرجى تقديم سبب للخصم',
        'days_required_for_type' => 'الأيام مطلوبة لنوع الخصم هذا',
        'amount_required_for_type' => 'المبلغ مطلوب لنوع الخصم هذا',
    ],

    // Placeholders
    'enter_days' => 'أدخل عدد الأيام',
    'enter_amount' => 'أدخل مبلغ الخصم',
    'enter_reason' => 'أدخل سبب الخصم',
    'enter_notes' => 'أدخل ملاحظات إضافية (اختياري)',

    // Statistics
    'total_deductions' => 'إجمالي الخصومات',
    'total_amount' => 'المبلغ الإجمالي',
    'unpaid_leave_count' => 'إجازات بدون أجر',
    'this_month' => 'هذا الشهر',
];