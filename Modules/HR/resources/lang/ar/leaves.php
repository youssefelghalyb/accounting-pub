<?php

return [
    // Page Titles
    'leave_management' => 'إدارة الإجازات',
    'leave_list' => 'قائمة الإجازات',
    'leaves' => 'الإجازات',
    'add_leave' => 'طلب إجازة',
    'edit_leave' => 'تعديل طلب الإجازة',
    'leave_details' => 'تفاصيل الإجازة',
    'manage_leaves' => 'إدارة وتتبع طلبات إجازات الموظفين',

    // Form Labels
    'employee' => 'الموظف',
    'leave_type' => 'نوع الإجازة',
    'start_date' => 'تاريخ البداية',
    'end_date' => 'تاريخ النهاية',
    'duration' => 'المدة',
    'days' => 'أيام',
    'days_label' => 'يوم',
    'reason' => 'السبب',
    'notes' => 'ملاحظات',
    'status' => 'الحالة',
    'leave_information' => 'معلومات الإجازة',

    // Status
    'status_pending' => 'قيد الانتظار',
    'status_approved' => 'موافق عليها',
    'status_rejected' => 'مرفوضة',

    // Actions
    'approve_leave' => 'الموافقة',
    'reject_leave' => 'الرفض',
    'cancel_leave' => 'إلغاء',

    // Messages
    'leave_created' => 'تم إنشاء طلب الإجازة بنجاح',
    'leave_updated' => 'تم تحديث طلب الإجازة بنجاح',
    'leave_deleted' => 'تم حذف طلب الإجازة بنجاح',
    'leave_approved' => 'تمت الموافقة على طلب الإجازة بنجاح',
    'leave_rejected' => 'تم رفض طلب الإجازة بنجاح',
    'confirm_delete' => 'هل أنت متأكد من حذف طلب الإجازة؟',
    'confirm_approve' => 'هل أنت متأكد من الموافقة على طلب الإجازة؟',
    'confirm_reject' => 'هل أنت متأكد من رفض طلب الإجازة؟',
    'cannot_edit_processed' => 'لا يمكن تعديل طلبات الإجازات المعالجة',

    // Information
    'employee_information' => 'معلومات الموظف',
    'approval_information' => 'معلومات الموافقة',
    'approved_by' => 'تمت الموافقة بواسطة',
    'approval_date' => 'تاريخ الموافقة',
    'leave_reference' => 'مرجع الإجازة',

    // Search & Filter
    'search_leaves' => 'البحث باسم الموظف...',
    'filter_by_status' => 'تصفية حسب الحالة',
    'filter_by_type' => 'تصفية حسب النوع',
    'all_statuses' => 'جميع الحالات',
    'all_types' => 'جميع الأنواع',

    // Empty States
    'no_leaves' => 'لا توجد طلبات إجازات',
    'no_leaves_description' => 'لم يتم العثور على طلبات إجازات. قم بإنشاء واحدة للبدء.',

    // Validation
    'validation' => [
        'employee_required' => 'يرجى اختيار الموظف',
        'type_required' => 'يرجى اختيار نوع الإجازة',
        'start_date_required' => 'تاريخ البداية مطلوب',
        'end_date_required' => 'تاريخ النهاية مطلوب',
        'end_date_after_start' => 'يجب أن يكون تاريخ النهاية بعد تاريخ البداية',
        'reason_required' => 'يرجى تقديم سبب للإجازة',
        'days_invalid' => 'يجب أن تكون المدة يوماً واحداً على الأقل',
    ],

    // Placeholders
    'enter_reason' => 'أدخل سبب طلب الإجازة',
    'enter_notes' => 'أدخل ملاحظات إضافية (اختياري)',

    // Statistics
    'total_leaves' => 'إجمالي الإجازات',
    'pending_leaves' => 'الطلبات قيد الانتظار',
    'approved_leaves' => 'الإجازات الموافق عليها',
    'rejected_leaves' => 'الإجازات المرفوضة',
    'leaves_this_month' => 'هذا الشهر',
];