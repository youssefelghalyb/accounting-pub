<?php

return [
    // Page titles
    'contractors' => 'Contractors',
    'contractor_list' => 'Contractor List',
    'add_contractor' => 'Add Contractor',
    'edit_contractor' => 'Edit Contractor',
    'view_contractor' => 'View Contractor',

    // Form labels
    'name' => 'Name',
    'phone' => 'Phone',
    'secondary_phone' => 'Secondary Phone',
    'email' => 'Email Address',
    'secondary_email' => 'Secondary Email',
    'nationality' => 'Nationality',
    'address' => 'Address',
    'national_id_file' => 'National ID',
    'upload_national_id' => 'Upload a photo or PDF of the national ID',

    // Sections
    'personal_info' => 'Personal Information',
    'contact_info' => 'Contact Information',
    'additional_info' => 'Additional Information',
    'contracted_books' => 'Contracted Books',
    'transactions' => 'Transactions',

    // Placeholders
    'enter_name' => 'Enter contractor name',
    'enter_nationality' => 'Enter nationality',
    'enter_address' => 'Enter address',
    'enter_email' => 'Enter email address',
    'enter_phone' => 'Enter phone number',

    // Stats
    'total_contractors' => 'Total Contractors',
    'total_contracted_books' => 'Total Contracted Books',
    'total_books' => 'Contracted Books',
    'total_royalty_accrued' => 'Royalty Accrued',
    'total_royalty_paid' => 'Royalty Paid',
    'outstanding_royalty' => 'Outstanding Royalty',
    'gift_copies' => 'Gift Copies',

    // ContractorBook
    'profit_percentage' => 'Profit %',
    'percentage_basis' => 'Basis',
    'basis_sale_price' => 'Sale Price',
    'basis_base_price' => 'Base Price',
    'contract_date' => 'Contract Date',
    'end_contract_date' => 'Contract End Date',
    'contract_file' => 'Contract File',

    // Transactions
    'record_transaction' => 'Record Transaction',
    'type' => 'Type',
    'type_publishing_fee' => 'Publishing Fee',
    'type_royalty_payment' => 'Royalty Payment',
    'type_advance_payment' => 'Advance Payment',
    'type_refund' => 'Refund',
    'type_adjustment' => 'Adjustment',
    'direction' => 'Direction',
    'direction_in' => 'Received from contractor',
    'direction_out' => 'Paid to contractor',
    'amount' => 'Amount',
    'date' => 'Date',
    'notes' => 'Notes',
    'receipt_file' => 'Supporting Document',
    'transaction_recorded' => 'Transaction recorded successfully.',
    'transaction_deleted' => 'Transaction deleted successfully.',

    // Gift flow
    'add_gift' => 'Add Gift',
    'confirm_gift' => 'Confirm Gift',
    'sub_warehouse' => 'Warehouse',
    'gift_recorded' => 'Gift recorded successfully.',

    // Register as client
    'register_as_client_title' => 'Create Invoice for Contractor',
    'register_as_client_description' => 'This contractor is not yet registered as a client. Registering will create a client record so you can issue an invoice to them.',
    'confirm_and_create_invoice' => 'Confirm & Create Invoice',

    // Messages
    'contractor_added' => 'Contractor added successfully.',
    'contractor_updated' => 'Contractor updated successfully.',
    'contractor_deleted' => 'Contractor deleted successfully.',
    'cannot_delete_has_books' => 'Cannot delete: this contractor still has books under contract.',
    'cannot_unassign_has_sales' => 'Cannot remove this contract: royalty sales have already been recorded against it.',
    'no_contractors' => 'No contractors found.',
    'search' => 'Search by name, email, or nationality...',

    // Validation
    'name_required' => 'Contractor name is required.',
    'email_invalid' => 'Please enter a valid email address.',
];
