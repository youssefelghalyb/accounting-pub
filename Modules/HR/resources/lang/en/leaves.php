<?php

return [
    // Page Titles
    'leave_management' => 'Leave Management',
    'leave_list' => 'Leave List',
    'leaves' => 'Leaves',
    'add_leave' => 'Request Leave',
    'edit_leave' => 'Edit Leave Request',
    'leave_details' => 'Leave Details',
    'manage_leaves' => 'Manage and track employee leave requests',

    // Form Labels
    'employee' => 'Employee',
    'leave_type' => 'Leave Type',
    'start_date' => 'Start Date',
    'end_date' => 'End Date',
    'duration' => 'Duration',
    'days' => 'Days',
    'days_label' => 'days',
    'reason' => 'Reason',
    'notes' => 'Notes',
    'status' => 'Status',
    'leave_information' => 'Leave Information',

    // Status
    'status_pending' => 'Pending',
    'status_approved' => 'Approved',
    'status_rejected' => 'Rejected',

    // Actions
    'approve' => 'Approve',
    'reject' => 'Reject',
    'approve_leave' => 'Approve',
    'reject_leave' => 'Reject Leave',
    'cancel_leave' => 'Cancel',

    // Messages
    'leave_created' => 'Leave request created successfully',
    'leave_updated' => 'Leave request updated successfully',
    'leave_deleted' => 'Leave request deleted successfully',
    'leave_approved' => 'Leave request approved successfully',
    'leave_rejected' => 'Leave request rejected successfully',
    'approval_failed' => 'Failed to approve leave request',
    'already_processed' => 'This leave request has already been processed',
    'insufficient_balance' => 'Insufficient leave balance',
    'cannot_delete_with_deduction' => 'Cannot delete leave with associated deduction',
    'confirm_delete' => 'Are you sure you want to delete this leave request?',
    'confirm_approve' => 'Are you sure you want to approve this leave request?',
    'confirm_reject' => 'Are you sure you want to reject this leave request?',
    'cannot_edit_processed' => 'Cannot edit processed leave requests',

    // Information
    'employee_information' => 'Employee Information',
    'approval_information' => 'Approval Information',
    'approved_by' => 'Approved By',
    'rejected_by' => 'Rejected By',
    'approval_date' => 'Approval Date',
    'leave_reference' => 'Leave Reference',
    'rejection_reason' => 'Rejection Reason',
    'enter_rejection_reason' => 'Enter reason for rejection',
    'unpaid' => 'Unpaid',
    'deduction_applied' => 'Deduction Applied',
    'deduction_amount' => 'Deduction Amount',
    'view_deduction' => 'View Deduction',

    // Search & Filter
    'search_leaves' => 'Search by employee name...',
    'filter_by_status' => 'Filter by Status',
    'filter_by_type' => 'Filter by Type',
    'all_statuses' => 'All Statuses',
    'all_types' => 'All Types',

    // Empty States
    'no_leaves' => 'No Leave Requests',
    'no_leaves_description' => 'No leave requests found. Create one to get started.',

    // Validation
    'validation' => [
        'employee_required' => 'Please select an employee',
        'type_required' => 'Please select leave type',
        'start_date_required' => 'Start date is required',
        'end_date_required' => 'End date is required',
        'end_date_after_start' => 'End date must be after start date',
        'reason_required' => 'Please provide a reason for leave',
        'days_invalid' => 'Duration must be at least 1 day',
        'rejection_reason_required' => 'Please provide a reason for rejection',
    ],

    // Placeholders
    'enter_reason' => 'Enter reason for leave request',
    'enter_notes' => 'Enter additional notes (optional)',

    // Deduction Messages
    'unpaid_leave_deduction' => 'Unpaid leave deduction: :type',
    'leave_period' => 'Leave period from :start to :end',

    // Others
    'not_available' => 'Not Available',

    // Statistics
    'total_leaves' => 'Total Leaves',
    'pending_leaves' => 'Pending Requests',
    'approved_leaves' => 'Approved Leaves',
    'rejected_leaves' => 'Rejected Leaves',
    'leaves_this_month' => 'This Month',
];