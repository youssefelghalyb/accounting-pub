<?php

return [
    'sales_invoices' => 'Sales Invoices',
    'sales_invoice' => 'Sales Invoice',
    'add_invoice' => 'Add Invoice',
    'create_invoice' => 'Create Sales Invoice',
    'edit_invoice' => 'Edit Invoice',
    'invoice_details' => 'Invoice Details',
    'invoice_list' => 'Invoice List',
    'invoice_management' => 'Sales Invoice Management',

    'invoice_number' => 'Invoice Number',
    'invoice_date' => 'Invoice Date',
    'due_date' => 'Due Date',
    'party' => 'Customer',
    'select_party' => 'Select Customer',
    'payment_terms' => 'Payment Terms',
    'is_taxable' => 'Taxable',
    'tax_rate' => 'Tax Rate (%)',
    'tax_amount' => 'Tax Amount',
    'discount_type' => 'Discount Type',
    'discount_value' => 'Discount Value',
    'discount_amount' => 'Discount Amount',
    'subtotal' => 'Subtotal',
    'total_amount' => 'Total Amount',
    'paid_amount' => 'Paid Amount',
    'outstanding_balance' => 'Outstanding Balance',
    'notes' => 'Notes',
    'terms_conditions' => 'Terms & Conditions',

    'statuses' => [
        'draft' => 'Draft',
        'pending' => 'Pending',
        'unpaid' => 'Unpaid',
        'partial' => 'Partially Paid',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
    ],

    'discount_types' => [
        'fixed' => 'Fixed Amount',
        'percentage' => 'Percentage',
    ],

    // Invoice Items
    'items' => 'Invoice Items',
    'add_item' => 'Add Item',
    'product' => 'Product',
    'select_product' => 'Select Product',
    'quantity' => 'Quantity',
    'unit_price' => 'Unit Price',
    'item_discount' => 'Discount',
    'line_total' => 'Line Total',
    'description' => 'Description',

    // Statistics
    'total_invoices' => 'Total Invoices',
    'unpaid_invoices' => 'Unpaid Invoices',
    'partial_invoices' => 'Partial Payments',
    'paid_invoices' => 'Paid Invoices',
    'overdue_invoices' => 'Overdue Invoices',
    'total_sales' => 'Total Sales',
    'total_outstanding' => 'Total Outstanding',

    // Payment
    'payment_info' => 'Payment Information',
    'initial_payment' => 'Initial Payment',
    'payment_account' => 'Payment Account',
    'select_account' => 'Select Account',
    'record_payment' => 'Record Payment',

    // Actions
    'view_invoice' => 'View Invoice',
    'print_invoice' => 'Print Invoice',
    'download_pdf' => 'Download PDF',
    'send_email' => 'Send Email',
    'cancel_invoice' => 'Cancel Invoice',
    'activate_invoice' => 'Activate Invoice',
    'activated_successfully' => 'Invoice activated successfully',

    // Messages
    'created_successfully' => 'Invoice created successfully',
    'updated_successfully' => 'Invoice updated successfully',
    'deleted_successfully' => 'Invoice deleted successfully',
    'cancelled_successfully' => 'Invoice cancelled successfully',

    'cannot_delete_has_payments' => 'Cannot delete invoice with payments',
    'cannot_cancel_has_payments' => 'Cannot cancel invoice with payments',
    'cannot_edit_status' => 'Cannot edit invoice with status: :status',

    'no_invoices' => 'No invoices found',
    'search_placeholder' => 'Search by invoice number or customer...',
    'confirm_delete' => 'Are you sure you want to delete this invoice?',
    'confirm_cancel' => 'Are you sure you want to cancel this invoice?',

    // Info
    'invoice_info' => 'Invoice Information',
    'customer_info' => 'Customer Information',
    'amount_breakdown' => 'Amount Breakdown',
    'overdue' => 'Overdue',
    'days_overdue' => ':days days overdue',

    'enter_notes' => 'Enter any additional notes...',
    'enter_terms' => 'Enter terms and conditions...',
    'quick_add_isbn' => 'Quick Add by ISBN',
    'scan_or_enter_isbn' => 'Scan barcode or enter ISBN to quickly add products',
    'enter_isbn_placeholder' => 'Enter ISBN and press Enter...',
    'please_enter_isbn' => 'Please enter an ISBN',
    'product_not_found' => 'Product not found with this ISBN',
    'product_added' => 'Product added',
    'at_least_one_item' => 'At least one item is required',
    'payment_history' => 'Payment History',

    // Product Drawer
    'search' => 'Search',
    'search_by_name_isbn' => 'Search by name, ISBN, or SKU...',
    'category' => 'Category',
    'all_categories' => 'All Categories',
    'sub_category' => 'Sub Category',
    'all_sub_categories' => 'All Sub Categories',
    'author' => 'Author',
    'all_authors' => 'All Authors',
    'no_products_found' => 'No products found',
    'stock' => 'Stock',
    'add' => 'Add',

    'sub_warehouse' => 'Sub-Warehouse',
    'select_sub_warehouse' => 'Select Sub-Warehouse',
    'please_select_sub_warehouse' => 'Please select a sub-warehouse first',
    'please_select_sub_warehouse_first' => 'Please select a sub-warehouse before adding products',
    'insufficient_stock_warning' => 'Insufficient Stock Warning',
    'insufficient_stock_for' => 'Insufficient stock for',
    'available' => 'Available',
    'requested' => 'Requested',
    'please_check_stock_warnings' => 'Please check stock warnings before submitting',


    // 
    'draft_found' => 'Draft Found',
    'draft_saved' => 'Draft saved',
    'restore_draft' => 'Restore Draft',
    'start_fresh' => 'Start Fresh',
    'draft_restored' => 'Draft restored successfully',
    'draft_discarded' => 'Draft discarded',
    'failed_to_restore' => 'Failed to restore draft',
    'save_failed' => 'Save failed',
    'just_now' => 'just now',
    'minutes_ago' => 'minutes ago',
    'hours_ago' => 'hours ago',
    'days_ago' => 'days ago',
    'party_change_warning' => 'Party Change Warning',
    'party_change_receipt_warning' => 'This invoice has :count receipt voucher(s). If you change the party, these receipts will be reassigned to the new party. Do you want to continue?',

];
