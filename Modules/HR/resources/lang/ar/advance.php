<?php

return [
    // Main titles
    'employee_advances' => 'سلف الموظفين',
    'advance_details' => 'تفاصيل السلفة',
    'add_advance' => 'إضافة سلفة',
    'edit_advance' => 'تعديل السلفة',
    'manage_advances' => 'إدارة السلف النقدية والتسويات للموظفين',

    // Fields
    'advance_code' => 'رمز السلفة',
    'amount' => 'مبلغ السلفة',
    'issue_date' => 'تاريخ الإصدار',
    'expected_settlement_date' => 'تاريخ التسوية المتوقع',
    'expected_settlement' => 'التسوية المتوقعة',
    'actual_settlement_date' => 'تاريخ التسوية الفعلي',
    'actual_settlement' => 'التسوية الفعلية',
    'type' => 'نوع السلفة',
    'status' => 'الحالة',
    'purpose' => 'الغرض',
    'notes' => 'ملاحظات',
    'issued_by' => 'صدرت بواسطة',
    'issued_to' => 'صدرت لـ',
    'outstanding' => 'الرصيد المتبقي',
    'advance_amount' => 'مبلغ السلفة',
    'cash_returned' => 'النقد المُرجع',
    'amount_spent' => 'المبلغ المُنفق (بإيصالات)',
    'total_accounted' => 'الإجمالي المحسوب',
    'received_by' => 'استلمها',
    'overdue' => 'متأخرة',

    // Types
    'types' => [
        'cash' => 'سلفة نقدية',
        'salary_advance' => 'سلفة راتب',
        'petty_cash' => 'مصروفات نثرية',
        'travel' => 'سلفة سفر',
        'purchase' => 'سلفة شراء',
    ],

    // Statuses
    'statuses' => [
        'pending' => 'قيد الانتظار',
        'partial_settlement' => 'تسوية جزئية',
        'settled' => 'مُسددة بالكامل',
        'settled_via_deduction' => 'مُسددة عبر خصم من الراتب',
    ],

    // Settlements
    'settlements' => 'التسويات',
    'settlements_history' => 'سجل التسويات',
    'settlement_details' => 'تفاصيل التسوية',
    'settlement_code' => 'رمز التسوية',
    'settlement_date' => 'تاريخ التسوية',
    'settlement_notes' => 'ملاحظات التسوية',
    'add_settlement' => 'إضافة تسوية',
    'edit_settlement' => 'تعديل التسوية',
    'linked_advance' => 'مرتبطة بسلفة',
    'standalone_settlement' => 'تسوية مستقلة (بدون سلفة)',
    'settlement_for_advance' => 'تسوية للسلفة',
    'optional' => 'اختياري',
    'receipt_file' => 'ملف الإيصال/الفاتورة',
    'view_receipt' => 'عرض الإيصال',
    'no_receipt' => 'لم يتم رفع إيصال',

    // Messages
    'created_successfully' => 'تم إنشاء السلفة بنجاح',
    'updated_successfully' => 'تم تحديث السلفة بنجاح',
    'deleted_successfully' => 'تم حذف السلفة بنجاح',
    'settlement_created_successfully' => 'تم إنشاء التسوية بنجاح',
    'settlement_updated_successfully' => 'تم تحديث التسوية بنجاح',
    'settlement_deleted_successfully' => 'تم حذف التسوية بنجاح',
    'at_least_one_amount' => 'يجب أن يكون أحد المبالغ (النقد المُرجع أو المبلغ المُنفق) أكبر من 0',
    'cash_returned_exceeds_advance' => 'لا يمكن أن يتجاوز النقد المُرجع مبلغ السلفة',
    'amount_spent_exceeds_advance' => 'لا يمكن أن يتجاوز المبلغ المُنفق مبلغ السلفة',
    'total_exceeds_advance' => 'لا يمكن أن يتجاوز إجمالي التسوية (النقد المُرجع + المبلغ المُنفق) مبلغ السلفة :amount',

    // Empty states
    'no_advances' => 'لا توجد سلف',
    'no_advances_description' => 'ابدأ بإنشاء أول سلفة للموظفين',
    'no_settlements' => 'لا توجد تسويات بعد',
    'no_settlements_description' => 'سجل إرجاع النقد أو إيصالات المصروفات لهذه السلفة',

    // Filters
    'filter_status' => 'تصفية حسب الحالة',
    'filter_type' => 'تصفية حسب النوع',
    'search_placeholder' => 'بحث برمز، موظف، أو الغرض...',

    // Employee view sections
    'employee_advances_summary' => 'ملخص السلف',
    'total_advances' => 'إجمالي السلف',
    'total_outstanding' => 'إجمالي المتبقي',
    'recent_advances' => 'السلف الأخيرة',
    'recent_settlements' => 'التسويات الأخيرة',
    // Actions
    'convert_to_deduction' => 'تحويل إلى خصم',
    'add_to_salary' => 'إضافة للراتب كمكافأة',
    'confirm_convert_to_deduction' => 'تحويل :amount إلى خصم؟ سيتم خصم المبلغ من راتب الموظف.',
    'confirm_add_to_salary' => 'إضافة :amount إلى راتب الموظف كمكافأة؟',
    'converted_to_deduction_success' => 'تم تحويل :amount إلى خصم بنجاح',
    'added_to_salary_success' => 'تم إضافة :amount إلى راتب الموظف بنجاح',

    // Messages
    'no_outstanding_balance' => 'لا يوجد رصيد متبقي للتحويل',
    'already_converted_to_deduction' => 'تم تحويل هذه السلفة إلى خصم بالفعل',
    'no_overpayment' => 'لا يوجد دفع زائد لهذه السلفة',
    'overpayment_already_added_to_salary' => 'تمت إضافة هذا الدفع الزائد إلى الراتب بالفعل',
    'deduction_from_advance' => 'استرداد سلفة من :code',
    'bonus_from_overpayment' => 'مكافأة دفع زائد من :code',
    'converted_to_deduction' => 'تم التحويل إلى خصم من الراتب',
    'added_to_salary' => 'تمت الإضافة للراتب كمكافأة',
    'deduction_info' => 'تم إنشاء خصم بمبلغ :amount في :date وسيتم خصمه من الراتب.',
    'view_deduction' => 'عرض سجل الخصم',
];
