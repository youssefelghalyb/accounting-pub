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
    'request_leave' => 'طلب إجازة',

    // Form Labels
    'employee' => 'الموظف',
    'leave_type' => 'نوع الإجازة',
    'start_date' => 'تاريخ البدء',
    'end_date' => 'تاريخ الانتهاء',
    'duration' => 'المدة',
    'days' => 'الأيام',
    'days_label' => 'يوم',
    'reason' => 'السبب',
    'notes' => 'ملاحظات',
    'status' => 'الحالة',
    'leave_information' => 'معلومات الإجازة',

    // Status
    'status_pending' => 'قيد المراجعة',
    'status_approved' => 'موافقة',
    'status_rejected' => 'مرفوضة',

    // Actions
    'approve' => 'موافقة',
    'reject' => 'رفض',
    'reject_reason' => 'سبب الرفض',
    'approve_leave' => 'الموافقة على الإجازة',
    'reject_leave' => 'رفض الإجازة',
    'cancel_leave' => 'إلغاء',

    // Messages
    'leave_created' => 'تم إنشاء طلب الإجازة بنجاح',
    'leave_updated' => 'تم تحديث طلب الإجازة بنجاح',
    'leave_deleted' => 'تم حذف طلب الإجازة بنجاح',
    'leave_approved' => 'تمت الموافقة على طلب الإجازة بنجاح',
    'leave_rejected' => 'تم رفض طلب الإجازة بنجاح',
    'approval_failed' => 'فشل في الموافقة على طلب الإجازة',
    'already_processed' => 'تم التعامل مع هذا الطلب مسبقاً',
    'insufficient_balance' => 'رصيد الإجازات غير كافٍ',
    'cannot_delete_with_deduction' => 'لا يمكن حذف إجازة مرتبطة بخصم',
    'confirm_delete' => 'هل أنت متأكد من رغبتك في حذف هذا الطلب؟',
    'confirm_approve' => 'هل أنت متأكد من رغبتك في الموافقة على هذا الطلب؟',
    'confirm_reject' => 'هل أنت متأكد من رغبتك في رفض هذا الطلب؟',
    'cannot_edit_processed' => 'لا يمكن تعديل طلبات الإجازة التي تم معالجتها',

    // Information
    'employee_information' => 'معلومات الموظف',
    'approval_information' => 'معلومات الموافقة',
    'approved_by' => 'تمت الموافقة بواسطة',
    'rejected_by' => 'تم الرفض بواسطة',
    'approval_date' => 'تاريخ الموافقة',
    'leave_reference' => 'مرجع الإجازة',
    'rejection_reason' => 'سبب الرفض',
    'enter_rejection_reason' => 'أدخل سبب الرفض',
    'unpaid' => 'غير مدفوعة',
    'deduction_applied' => 'تم تطبيق خصم',
    'deduction_amount' => 'قيمة الخصم',
    'view_deduction' => 'عرض الخصم',

    // Search & Filter
    'search_leaves' => 'ابحث باسم الموظف...',
    'filter_by_status' => 'تصفية حسب الحالة',
    'filter_by_type' => 'تصفية حسب النوع',
    'all_statuses' => 'كل الحالات',
    'all_types' => 'كل الأنواع',

    // Empty States
    'no_leaves' => 'لا توجد طلبات إجازة',
    'no_leaves_description' => 'لا توجد طلبات إجازة حالياً. ابدأ بإنشاء طلب جديد.',

    // Validation
    'validation' => [
        'employee_required' => 'يرجى اختيار الموظف',
        'type_required' => 'يرجى اختيار نوع الإجازة',
        'start_date_required' => 'تاريخ البدء مطلوب',
        'end_date_required' => 'تاريخ الانتهاء مطلوب',
        'end_date_after_start' => 'يجب أن يكون تاريخ الانتهاء بعد تاريخ البدء',
        'reason_required' => 'يرجى كتابة سبب الإجازة',
        'days_invalid' => 'يجب أن تكون المدة يوم واحد على الأقل',
        'rejection_reason_required' => 'يرجى كتابة سبب الرفض',
    ],

    // Placeholders
    'enter_reason' => 'أدخل سبب طلب الإجازة',
    'enter_notes' => 'أدخل ملاحظات إضافية (اختياري)',

    // Deduction Messages
    'unpaid_leave_deduction' => 'خصم إجازة غير مدفوعة: :type',
    'leave_period' => 'فترة الإجازة من :start إلى :end',

    // Others
    'not_available' => 'غير متوفر',

    // Statistics
    'total_leaves' => 'إجمالي الإجازات',
    'pending_leaves' => 'الطلبات قيد المراجعة',
    'approved_leaves' => 'الإجازات الموافق عليها',
    'rejected_leaves' => 'الإجازات المرفوضة',
    'leaves_this_month' => 'إجازات هذا الشهر',
];
