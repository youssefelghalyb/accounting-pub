<?php

return [
    // Page Titles
    'contracts' => 'Author Contracts',
    'contract_list' => 'Contract List',
    'add_contract' => 'Add Contract',
    'edit_contract' => 'Edit Contract',
    'view_contract' => 'View Contract',
    'contract_details' => 'Contract Details',
    'create_contract' => 'Create New Contract',

    // Form Labels
    'author' => 'Author',
    'book' => 'Book',
    'contract_date' => 'Contract Date',
    'contract_price' => 'Contract Price',
    'percentage_from_book_profit' => 'Percentage from Book Profit (%)',
    'contract_file' => 'Contract File',

    // Contract Status
    'payment_status' => 'Payment Status',
    'paid' => 'Fully Paid',
    'partial' => 'Partially Paid',
    'pending' => 'Pending Payment',

    // Statistics
    'total_contracts' => 'Total Contracts',
    'total_value' => 'Total Contract Value',
    'total_paid' => 'Total Paid',
    'outstanding' => 'Outstanding Balance',
    'payment_percentage' => 'Payment Progress',

    // Actions
    'search' => 'Search contracts...',
    'filter_by_author' => 'Filter by Author',
    'filter_by_book' => 'Filter by Book',
    'filter_by_status' => 'Filter by Status',
    'all_authors' => 'All Authors',
    'all_books' => 'All Books',
    'all_statuses' => 'All Statuses',
    'add_payment' => 'Add Payment',
    'view_payments' => 'View Payment History',
    'download_contract' => 'Download Contract',

    // Messages
    'contract_added' => 'Contract added successfully',
    'contract_updated' => 'Contract updated successfully',
    'contract_deleted' => 'Contract deleted successfully',
    'no_contracts' => 'No contracts found',
    'cannot_delete_has_transactions' => 'Cannot delete contract with existing transactions',

    // Validation
    'author_required' => 'Author is required',
    'author_invalid' => 'Invalid author',
    'book_required' => 'Book is required',
    'book_invalid' => 'Invalid book',
    'contract_date_required' => 'Contract date is required',
    'contract_price_required' => 'Contract price is required',
    'contract_price_positive' => 'Contract price must be a positive number',
    'percentage_required' => 'Profit percentage is required',
    'percentage_min' => 'Percentage cannot be negative',
    'percentage_max' => 'Percentage cannot exceed 100',
    'contract_file_invalid' => 'Contract file must be PDF or DOC format',
    'contract_file_max_size' => 'Contract file must not exceed 5MB',

    // Placeholders
    'select_author' => 'Select author',
    'select_book' => 'Select book',
    'select_contract_date' => 'Select contract date',
    'enter_contract_price' => 'Enter contract price',
    'enter_percentage' => 'Enter percentage (0-100)',
    'upload_contract_file' => 'Upload contract file (PDF/DOC)',
];
