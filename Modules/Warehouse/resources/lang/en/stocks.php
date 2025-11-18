<?php

return [
    // Module Name
    'module_name' => 'Warehouse Stocks',
    'stocks' => 'Stocks',
    'stock' => 'Stock',

    // Page Titles
    'stock_list' => 'Stock List',
    'add_stock' => 'Add Stock',
    'edit_stock' => 'Edit Stock',
    'view_stock' => 'View Stock',
    'stock_details' => 'Stock Details',
    'product_details' => 'Product Details',
    'book_details' => 'Book Details',

    // Field Labels
    'product' => 'Product',
    'product_name' => 'Product Name',
    'product_type' => 'Product Type',
    'product_sku' => 'Product SKU',
    'warehouse' => 'Warehouse',
    'warehouse_name' => 'Warehouse Name',
    'location' => 'Location',
    'description' => 'Description',
    'quantity' => 'Quantity',
    'available_quantity' => 'Available Quantity',
    'reserved_quantity' => 'Reserved Quantity',
    'total_quantity' => 'Total Quantity',
    'minimum_quantity' => 'Minimum Quantity',
    'status' => 'Status',
    'stock_level' => 'Stock Level',
    'base_price' => 'Base Price',

    // Book Fields
    'author' => 'Author',
    'category' => 'Category',
    'isbn' => 'ISBN',
    'publisher' => 'Publisher',
    'publication_year' => 'Publication Year',
    'language' => 'Language',
    'pages' => 'Pages',

    // Status Options
    'active' => 'Active',
    'inactive' => 'Inactive',

    // Stock Level Options
    'in_stock' => 'In Stock',
    'low_stock' => 'Low Stock',
    'out_of_stock' => 'Out of Stock',

    // Statistics
    'total_stocks' => 'Total Stocks',
    'active_stocks' => 'Active Stocks',
    'low_stock_items' => 'Low Stock Items',
    'out_of_stock_items' => 'Out of Stock',

    // Filters
    'all_warehouses' => 'All Warehouses',
    'all_levels' => 'All Levels',
    'all_statuses' => 'All Statuses',

    // Placeholders
    'select_product' => 'Select Product',
    'select_status' => 'Select Status',
    'enter_warehouse_name' => 'Enter warehouse name',
    'enter_location' => 'Enter location',
    'enter_quantity' => 'Enter quantity',
    'enter_minimum_quantity' => 'Enter minimum quantity',
    'enter_description' => 'Enter description',
    'search' => 'Search stocks...',

    // Messages
    'stock_added' => 'Stock added successfully',
    'stock_updated' => 'Stock updated successfully',
    'stock_deleted' => 'Stock deleted successfully',

    // Validation Messages
    'product_required' => 'Product is required',
    'product_not_found' => 'Product not found',
    'warehouse_name_required' => 'Warehouse name is required',
    'warehouse_name_max' => 'Warehouse name must not exceed 255 characters',
    'quantity_required' => 'Quantity is required',
    'quantity_integer' => 'Quantity must be a number',
    'quantity_min' => 'Quantity must be at least 0',
    'status_required' => 'Status is required',
    'status_invalid' => 'Invalid status',
    'minimum_quantity_required' => 'Minimum quantity is required',
    'minimum_quantity_integer' => 'Minimum quantity must be a number',
    'minimum_quantity_min' => 'Minimum quantity must be at least 0',
];
