<?php

return [
    // Module Name
    'module_name' => 'Stock Movements',
    'movements' => 'Movements',
    'movement' => 'Movement',

    // Page Titles
    'movement_list' => 'Movement List',
    'add_movement' => 'Add Movement',
    'edit_movement' => 'Edit Movement',
    'view_movement' => 'View Movement',
    'movement_details' => 'Movement Details',

    // Field Labels
    'reference_number' => 'Reference Number',
    'type' => 'Type',
    'movement_date' => 'Movement Date',
    'source_warehouse' => 'Source Warehouse',
    'destination_warehouse' => 'Destination Warehouse',
    'warehouses' => 'Warehouses',
    'notes' => 'Notes',
    'status' => 'Status',
    'total_items' => 'Total Items',
    'products' => 'Products',
    'product' => 'Product',
    'product_type' => 'Product Type',
    'quantity' => 'Quantity',
    'item_notes' => 'Notes',

    // Movement Types
    'type_in' => 'Stock In',
    'type_out' => 'Stock Out',
    'type_transfer' => 'Transfer',
    'type_adjustment' => 'Adjustment',

    // Status Options
    'status_pending' => 'Pending',
    'status_completed' => 'Completed',
    'status_cancelled' => 'Cancelled',

    // Statistics
    'total_movements' => 'Total Movements',
    'pending_movements' => 'Pending',
    'completed_movements' => 'Completed',
    'total_items_moved' => 'Items Moved',

    // Filters
    'all_types' => 'All Types',
    'all_statuses' => 'All Statuses',

    // Placeholders
    'select_type' => 'Select Type',
    'select_status' => 'Select Status',
    'select_product' => 'Select Product',
    'enter_reference_number' => 'Enter reference number',
    'search' => 'Search movements...',

    // Bulk Operations
    'add_multiple_products' => 'Add multiple products to this movement',
    'add_product' => 'Add Product',
    'products_in_movement' => 'products in this movement',

    // Messages
    'movement_added' => 'Movement added successfully',
    'movement_updated' => 'Movement updated successfully',
    'movement_deleted' => 'Movement deleted successfully',
    'movement_error' => 'Error processing movement',
    'cannot_edit_completed' => 'Cannot edit completed movement',
    'cannot_delete_completed' => 'Cannot delete completed movement',

    // Validation Messages
    'reference_number_required' => 'Reference number is required',
    'reference_number_unique' => 'Reference number already exists',
    'type_required' => 'Movement type is required',
    'type_invalid' => 'Invalid movement type',
    'movement_date_required' => 'Movement date is required',
    'movement_date_invalid' => 'Invalid movement date',
    'status_required' => 'Status is required',
    'status_invalid' => 'Invalid status',

    'items_required' => 'At least one product is required',
    'items_min' => 'At least one product is required',
    'item_product_required' => 'Product is required',
    'item_product_not_found' => 'Product not found',
    'item_quantity_required' => 'Quantity is required',
    'item_quantity_integer' => 'Quantity must be a number',
    'item_quantity_min' => 'Quantity must be at least 1',
];
