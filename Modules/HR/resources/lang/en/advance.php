<?php

return [
    // Main titles
    'employee_advances' => 'Employee Advances',
    'advance_details' => 'Advance Details',
    'add_advance' => 'Add Advance',
    'edit_advance' => 'Edit Advance',
    'manage_advances' => 'Manage employee cash advances and settlements',

    // Fields
    'advance_code' => 'Advance Code',
    'amount' => 'Advance Amount',
    'issue_date' => 'Issue Date',
    'expected_settlement_date' => 'Expected Settlement Date',
    'expected_settlement' => 'Expected Settlement',
    'actual_settlement_date' => 'Actual Settlement Date',
    'actual_settlement' => 'Actual Settlement',
    'type' => 'Advance Type',
    'status' => 'Status',
    'purpose' => 'Purpose',
    'notes' => 'Notes',
    'issued_by' => 'Issued By',
    'issued_to' => 'Issued To',
    'outstanding' => 'Outstanding Balance',
    'advance_amount' => 'Advance Amount',
    'cash_returned' => 'Cash Returned',
    'amount_spent' => 'Amount Spent (with receipts)',
    'total_accounted' => 'Total Accounted',
    'received_by' => 'Received By',
    'overdue' => 'Overdue',

    // Types
    'types' => [
        'cash' => 'Cash Advance',
        'salary_advance' => 'Salary Advance',
        'petty_cash' => 'Petty Cash',
        'travel' => 'Travel Advance',
        'purchase' => 'Purchase Advance',
    ],

    // Statuses
    'statuses' => [
    'pending' => 'Pending',
    'partial_settlement' => 'Partial Settlement',
    'settled' => 'Fully Settled',
    'settled_via_deduction' => 'Settled via Salary Deduction',
    ],

    // Settlements
    'settlements' => 'Settlements',
    'settlements_history' => 'Settlements History',
    'settlement_details' => 'Settlement Details',
    'settlement_code' => 'Settlement Code',
    'settlement_date' => 'Settlement Date',
    'settlement_notes' => 'Settlement Notes',
    'add_settlement' => 'Add Settlement',
    'edit_settlement' => 'Edit Settlement',
    'linked_advance' => 'Linked to Advance',
    'standalone_settlement' => 'Standalone Settlement (no advance)',
    'settlement_for_advance' => 'Settlement for Advance',
    'optional' => 'Optional',
    'receipt_file' => 'Receipt/Invoice File',
    'view_receipt' => 'View Receipt',
    'no_receipt' => 'No receipt uploaded',

    // Messages
    'created_successfully' => 'Advance created successfully',
    'updated_successfully' => 'Advance updated successfully',
    'deleted_successfully' => 'Advance deleted successfully',
    'settlement_created_successfully' => 'Settlement created successfully',
    'settlement_updated_successfully' => 'Settlement updated successfully',
    'settlement_deleted_successfully' => 'Settlement deleted successfully',
    'at_least_one_amount' => 'At least one amount (cash returned or amount spent) must be greater than 0',
    'cash_returned_exceeds_advance' => 'Cash returned cannot exceed the advance amount',
    'amount_spent_exceeds_advance' => 'Amount spent cannot exceed the advance amount',
    'total_exceeds_advance' => 'Total settlement amount (cash returned + amount spent) cannot exceed the advance amount of :amount',

    // Empty states
    'no_advances' => 'No Advances Found',
    'no_advances_description' => 'Get started by creating your first employee advance',
    'no_settlements' => 'No Settlements Yet',
    'no_settlements_description' => 'Record cash returns or expense receipts for this advance',

    // Filters
    'filter_status' => 'Filter by Status',
    'filter_type' => 'Filter by Type',
    'search_placeholder' => 'Search by code, employee, or purpose...',

    // Employee view sections
    'employee_advances_summary' => 'Advances Summary',
    'total_advances' => 'Total Advances',
    'total_outstanding' => 'Total Outstanding',
    'recent_advances' => 'Recent Advances',
    'recent_settlements' => 'Recent Settlements',
    // Actions
    'convert_to_deduction' => 'Convert to Deduction',
    'add_to_salary' => 'Add to Salary as Bonus',
    'confirm_convert_to_deduction' => 'Convert :amount to a deduction? This will deduct the amount from employee salary.',
    'confirm_add_to_salary' => 'Add :amount to employee salary as a bonus?',
    'converted_to_deduction_success' => 'Successfully converted :amount to deduction',
    'added_to_salary_success' => 'Successfully added :amount to employee salary',



    // Messages
    'no_outstanding_balance' => 'No outstanding balance to convert',
    'already_converted_to_deduction' => 'This advance has already been converted to a deduction',
    'no_overpayment' => 'No overpayment exists for this advance',
    'overpayment_already_added_to_salary' => 'This overpayment has already been added to salary',
    'deduction_from_advance' => 'Advance recovery from :code',
    'bonus_from_overpayment' => 'Overpayment bonus from :code',
    'converted_to_deduction' => 'Converted to salary deduction',
    'added_to_salary' => 'Added to salary as bonus',
    'deduction_info' => 'A deduction of :amount was created on :date and will be deducted from salary.',
    'view_deduction' => 'View Deduction Record',
];
