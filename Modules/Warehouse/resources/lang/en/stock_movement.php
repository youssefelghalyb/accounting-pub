<?php

return [
    // Page Titles
    'module_name' => 'Stock Movement Management',
    'stock_movements' => 'Stock Movements',
    'stock_movement_list' => 'Stock Movement List',
    'add_stock_movement' => 'Add Stock Movement',
    'create_movements' => 'Create Stock Movements',
    'edit_stock_movement' => 'Edit Stock Movement',
    'view_stock_movement' => 'View Stock Movement',
    'stock_movement_details' => 'Stock Movement Details',

    // Form Labels
    'product' => 'Product',
    'from_sub_warehouse' => 'From Sub-Warehouse',
    'to_sub_warehouse' => 'To Sub-Warehouse',
    'quantity' => 'Quantity',
    'movement_type' => 'Movement Type',
    'reason' => 'Reason',
    'reference_id' => 'Reference ID',
    'notes' => 'Notes',
    'user' => 'User',
    'date' => 'Date',

    // Movement Types
    'transfer' => 'Transfer',
    'inbound' => 'Inbound',
    'outbound' => 'Outbound',

    // Statistics
    'total_movements' => 'Total Movements',
    'total_transfers' => 'Total Transfers',
    'total_inbound' => 'Total Inbound',
    'total_outbound' => 'Total Outbound',

    // Actions
    'search' => 'Search movements...',
    'filter_by_type' => 'Filter by Type',
    'filter_by_warehouse' => 'Filter by Sub-Warehouse',
    'filter_by_product' => 'Filter by Product',
    'all_types' => 'All Types',
    'all_warehouses' => 'All Sub-Warehouses',
    'all_products' => 'All Products',
    'add_another_movement' => 'Add Another Movement',
    'remove_movement' => 'Remove',

    // Messages
    'movements_created' => 'Stock movements created successfully',
    'movement_updated' => 'Stock movement updated successfully',
    'movement_deleted' => 'Stock movement deleted successfully',
    'movements_failed' => 'Failed to create stock movements',
    'no_movements' => 'No stock movements found',
    'transfer_requires_both_warehouses' => 'Transfer requires both source and destination warehouses',
    'inbound_requires_destination' => 'Inbound movement requires a destination warehouse',
    'outbound_requires_source' => 'Outbound movement requires a source warehouse',
    'insufficient_stock' => 'Insufficient stock in source warehouse',

    // Validation Messages
    'movements_required' => 'At least one movement is required',
    'movements_invalid' => 'Invalid movements data',
    'movements_min' => 'At least one movement is required',
    'product_required' => 'Product is required',
    'product_not_found' => 'Selected product not found',
    'from_warehouse_not_found' => 'Source warehouse not found',
    'to_warehouse_not_found' => 'Destination warehouse not found',
    'quantity_required' => 'Quantity is required',
    'quantity_min' => 'Quantity must be at least 1',
    'type_required' => 'Movement type is required',
    'type_invalid' => 'Invalid movement type',

    // Placeholders
    'enter_quantity' => 'Enter quantity',
    'enter_reason' => 'Enter reason (optional)',
    'enter_reference_id' => 'Enter reference ID (optional)',
    'enter_notes' => 'Enter notes (optional)',
    'select_product' => 'Select product',
    'select_from_warehouse' => 'Select source warehouse',
    'select_to_warehouse' => 'Select destination warehouse',
    'select_type' => 'Select movement type',

    // Instructions
    'bulk_instructions' => 'You can add multiple stock movements at once. Click "Add Another Movement" to add more.',
    'transfer_instructions' => 'Transfer: Move stock from one sub-warehouse to another',
    'inbound_instructions' => 'Inbound: Receive new stock into a sub-warehouse',
    'outbound_instructions' => 'Outbound: Remove stock from a sub-warehouse',
];
