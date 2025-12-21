<?php

return [
    // Page Titles
    'customer_management' => 'Customer Management',
    'customers' => 'Customers',
    'add_customer' => 'Add Customer',
    'edit_customer' => 'Edit Customer',
    'customer_details' => 'Customer Details',
    
    // Form Fields
    'name' => 'Name',
    'type' => 'Type',
    'phone' => 'Phone',
    'email' => 'Email',
    'address' => 'Address',
    'tax_number' => 'Tax Number / VAT',
    'status' => 'Status',
    
    // Placeholders
    'enter_name' => 'Enter customer name',
    'enter_phone' => 'Enter phone number',
    'enter_email' => 'Enter email address',
    'enter_address' => 'Enter address',
    'enter_tax_number' => 'Enter tax/VAT number',
    'select_type' => 'Select customer type',
    
    // Customer Types
    'types' => [
        'individual' => 'Individual',
        'company' => 'Company',
        'online' => 'Online',
    ],
    
    // Status
    'active' => 'Active',
    'inactive' => 'Inactive',
    'all_types' => 'All Types',
    'all_statuses' => 'All Statuses',
    
    // Statistics
    'total_customers' => 'Total Customers',
    'active_customers' => 'Active Customers',
    'individual' => 'Individual',
    'company' => 'Company',
    'online' => 'Online',
    
    // Customer Info
    'customer_list' => 'Customer List',
    'basic_info' => 'Basic Information',
    'contact_info' => 'Contact Information',
    'tax_info' => 'Tax Information',
    
    // Metrics
    'total_orders' => 'Total Orders',
    'total_spent' => 'Total Spent',
    'avg_order_value' => 'Avg Order Value',
    'outstanding_balance' => 'Outstanding Balance',
    'customer_timeline' => 'Customer Timeline',
    'first_order' => 'First Order',
    'last_order' => 'Last Order',
    
    // Activity
    'recent_orders' => 'Recent Orders',
    'recent_payments' => 'Recent Payments',
    'order_number' => 'Order #',
    'payment_number' => 'Payment #',
    'total' => 'Total',
    'amount' => 'Amount',
    'method' => 'Payment Method',
    
    // Actions
    'create_order' => 'Create Order',
    'create_invoice' => 'Create Invoice',
    'new_order_for_customer' => 'Create a new order for this customer',
    'new_invoice_for_customer' => 'Generate an invoice for this customer',
    'activate' => 'Activate',
    'deactivate' => 'Deactivate',
    
    // Messages
    'customer_added' => 'Customer added successfully',
    'customer_updated' => 'Customer updated successfully',
    'customer_deleted' => 'Customer deleted successfully',
    'customer_activated' => 'Customer activated successfully',
    'customer_deactivated' => 'Customer deactivated successfully',
    'cannot_delete_has_orders' => 'Cannot delete customer with existing orders',
    
    // Empty States
    'no_customers' => 'No customers found',
    'no_orders' => 'No orders found',
    'no_payments' => 'No payments found',
    
    // Search
    'search' => 'Search customers...',
    
    // Confirmations
    'confirm_delete' => 'Are you sure you want to delete this customer?',
    'confirm_activate' => 'Are you sure you want to activate this customer?',
    'confirm_deactivate' => 'Are you sure you want to deactivate this customer?',
    
    // Validation
    'validation' => [
        'name_required' => 'Customer name is required',
        'type_required' => 'Customer type is required',
        'type_invalid' => 'Invalid customer type selected',
        'email_invalid' => 'Please enter a valid email address',
        'email_unique' => 'This email is already registered',
        'tax_number_unique' => 'This tax number is already registered',
    ],

    // Customer Details Page
    'customer_since' => 'Customer Since',
    'days_active' => 'Days Active',
    'last_updated' => 'Last Updated',
    'customer_notes' => 'Customer Notes',
    'notes_feature_coming_soon' => 'Notes feature is coming soon. You will be able to add notes about this customer.',
    'activity_log' => 'Activity Log',
    'customer_created' => 'Customer Created',
    'customer_updated' => 'Customer Updated',
    'current_status' => 'Current Status',
    'status_can_be_changed' => 'Use the button above to activate or deactivate this customer',
];