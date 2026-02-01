<?php

return [
    // Module
    'purchase_invoices' => 'Purchase Invoices',
    'purchase_invoice' => 'Purchase Invoice',
    'manage_purchases' => 'Manage purchase invoices from vendors',

    // Actions
    'create_invoice' => 'Create Invoice',
    'edit_invoice' => 'Edit Invoice',
    'update_invoice' => 'Update Invoice',
    'view_invoice' => 'View Invoice',
    'delete_invoice' => 'Delete Invoice',
    'cancel_invoice' => 'Cancel Invoice',

    // Fields
    'invoice_number' => 'Invoice Number',
    'vendor' => 'Vendor',
    'select_vendor' => 'Select Vendor',
    'all_vendors' => 'All Vendors',
    'invoice_date' => 'Invoice Date',
    'due_date' => 'Due Date',
    'date' => 'Date',
    'reference_number' => 'Reference Number',
    'vendor_invoice_number' => 'Vendor Invoice Number',
    'invoice_info' => 'Invoice Information',

    // Items
    'items' => 'Items',
    'add_item' => 'Add Item',
    'product' => 'Product',
    'select_product' => 'Select Product',
    'quantity' => 'Quantity',
    'unit_price' => 'Unit Price',
    'item_discount' => 'Discount',
    'line_total' => 'Line Total',
    'description' => 'Description',

    // Amounts
    'amount_breakdown' => 'Amount Breakdown',
    'subtotal' => 'Subtotal',
    'tax_rate' => 'Tax Rate (%)',
    'tax_amount' => 'Tax Amount',
    'discount_amount' => 'Discount Amount',
    'total_amount' => 'Total Amount',
    'total' => 'Total',
    'outstanding' => 'Outstanding',

    // Payment
    'payment_info' => 'Payment Information',
    'initial_payment' => 'Optional initial payment',
    'paid_amount' => 'Paid Amount',
    'payment_account' => 'Payment Account',
    'select_account' => 'Select Account',
    'pay' => 'Pay',

    // Statistics
    'total_purchases' => 'Total Purchases',
    'unpaid_invoices' => 'Unpaid Invoices',
    'paid_invoices' => 'Paid Invoices',

    // Status
    'status' => 'Status',
    'all_statuses' => 'All Statuses',

    // Actions
    'actions' => 'Actions',
    'view' => 'View',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'cancel' => 'Cancel',
    'filter' => 'Filter',
    'reset' => 'Reset',
    'search' => 'Search',
    'search_placeholder' => 'Search invoices...',

    // Notes
    'notes' => 'Notes',
    'enter_notes' => 'Enter any notes or additional information...',

    // Messages
    'created_successfully' => 'Purchase invoice created successfully',
    'updated_successfully' => 'Purchase invoice updated successfully',
    'deleted_successfully' => 'Purchase invoice deleted successfully',
    'cancelled_successfully' => 'Purchase invoice cancelled successfully',
    'cannot_edit_status' => 'Cannot edit :status invoice',
    'cannot_delete_has_payments' => 'Cannot delete invoice with payments',
    'cannot_cancel_has_payments' => 'Cannot cancel invoice with payments',

    // Empty States
    'no_invoices' => 'No purchase invoices',
    'no_invoices_desc' => 'Get started by creating your first purchase invoice',

    // Additional fields
    'print' => 'Print',
    'quick_actions' => 'Quick Actions',
    'invoice_details' => 'Invoice Details',
    'payment_history' => 'Payment History',
    'paid' => 'Paid',
    'tax' => 'Tax',
    'discount' => 'Discount',
    'created_by' => 'Created By',
    'created_at' => 'Created At',
    'last_updated' => 'Last Updated',
    'confirm_cancel' => 'Are you sure you want to cancel this invoice?',
    'service_expense_invoice' => 'Service or Expense Invoice',
    'manual_amount_description' => 'For service or expense invoices without product items, enter the total amount here',
    'manual_amount' => 'Manual Amount',
    'leave_zero_for_items' => 'Leave at 0 to use product items below',
    'manual_amount_mode' => 'Manual Amount Mode Active',
    'items_disabled_info' => 'Product items are disabled. Clear the manual amount to add products.',
    'items_or_amount_required' => 'Either add product items OR enter a manual amount',

    'add' => 'Add',
    'quick_add_isbn' => 'Quick Add by ISBN',
    'scan_or_enter_isbn' => 'Scan barcode or enter ISBN to quickly add products',
    'enter_isbn_placeholder' => 'Enter ISBN and press Enter...',
    'please_enter_isbn' => 'Please enter ISBN',
    'product_not_found' => 'Product not found with this ISBN',
    'product_added' => 'Product added',
    'at_least_one_item' => 'At least one item is required',
    'items_optional' => 'Items (Optional)',
    'no_items_yet' => 'No items yet',
    'is_taxable' => 'Is Taxable?'
];
