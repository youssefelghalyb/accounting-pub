<?php

return [
    // Page Titles
    'deduction_management' => 'Deduction Management',
    'deduction_list' => 'Deduction List',
    'deductions' => 'Deductions',
    'add_deduction' => 'Add Deduction',
    'edit_deduction' => 'Edit Deduction',
    'deduction_details' => 'Deduction Details',
    'manage_deductions' => 'Manage and track employee deductions',

    // Form Labels
    'employee' => 'Employee',
    'deduction_type' => 'Deduction Type',
    'deduction_date' => 'Deduction Date',
    'days' => 'Days',
    'days_label' => 'days',
    'amount' => 'Amount',
    'reason' => 'Reason',
    'notes' => 'Notes',
    'deduction_information' => 'Deduction Information',
    'employee_information' => 'Employee Information',

    // Deduction Types
    'type_days' => 'Days Deduction',
    'type_amount' => 'Fixed Amount',
    'type_unpaid_leave' => 'Unpaid Leave',
    'type_advance_deduction' => 'Advance Deduction',

    // Messages
    'deduction_created' => 'Deduction created successfully',
    'deduction_updated' => 'Deduction updated successfully',
    'deduction_deleted' => 'Deduction deleted successfully',
    'confirm_delete' => 'Are you sure you want to delete this deduction?',
    'unpaid_leave_deduction' => 'Unpaid leave deduction: :type',
    'leave_period' => 'Leave period from :start to :end',

    // Source
    'source' => 'Source',
    'from_leave' => 'From Leave',
    'manual' => 'Manual',
    'linked_to_leave' => 'This deduction is linked to a leave request',
    'leave_reference' => 'Leave Reference',
    'deduction_reference' => 'Deduction Reference',

    // Search & Filter
    'search_deductions' => 'Search by employee name...',
    'filter_by_type' => 'Filter by Type',

    // Empty States
    'no_deductions' => 'No Deductions',
    'no_deductions_description' => 'No deductions found. Create one to get started.',

    // Validation
    'validation' => [
        'employee_required' => 'Please select an employee',
        'type_required' => 'Please select deduction type',
        'days_invalid' => 'Days must be at least 1',
        'amount_invalid' => 'Amount must be greater than 0',
        'date_required' => 'Deduction date is required',
        'reason_required' => 'Please provide a reason for deduction',
        'days_required_for_type' => 'Days are required for this deduction type',
        'amount_required_for_type' => 'Amount is required for this deduction type',
    ],

    // Placeholders
    'enter_days' => 'Enter number of days',
    'enter_amount' => 'Enter deduction amount',
    'enter_reason' => 'Enter reason for deduction',
    'enter_notes' => 'Enter additional notes (optional)',

    // Statistics
    'total_deductions' => 'Total Deductions',
    'total_amount' => 'Total Amount',
    'unpaid_leave_count' => 'Unpaid Leave',
    'this_month' => 'This Month',
];