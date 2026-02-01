<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SearchableSelectDrawer extends Component
{
    public $drawerId;
    public $title;
    public $apiUrl;
    public $filters;
    public $searchFields;
    public $displayFields;
    public $onSelectCallback;
    public $noResultsText;
    public $perPage;
    public $searchPlaceholder;
    public $triggerSelector;
    public $displaySelector;

    /**
     * Create a new component instance.
     *
     * @param string $drawerId - Unique ID for this drawer
     * @param string $title - Drawer title
     * @param string $apiUrl - API endpoint to fetch data
     * @param array $filters - Filter configuration
     * @param array $searchFields - Fields to search (for display purposes)
     * @param array $displayFields - Fields to display in card
     * @param string $onSelectCallback - JavaScript callback function name
     * @param string $noResultsText - Text to show when no results found
     * @param int $perPage - Items per page
     * @param string $searchPlaceholder - Search input placeholder
     * @param string $triggerSelector - CSS selector for element(s) that trigger drawer (e.g., '.product-select-btn')
     * @param string $displaySelector - CSS selector for element to display selected item name
     */
    public function __construct(
        string $drawerId = 'searchableDrawer',
        string $title = 'Select Item',
        string $apiUrl = '',
        array $filters = [],
        array $searchFields = ['name'],
        array $displayFields = [],
        string $onSelectCallback = 'onItemSelected',
        string $noResultsText = 'No items found',
        int $perPage = 20,
        string $searchPlaceholder = 'Search...',
        string $triggerSelector = '',
        string $displaySelector = ''
    ) {
        $this->drawerId = $drawerId;
        $this->title = $title;
        $this->apiUrl = $apiUrl;
        $this->filters = $filters;
        $this->searchFields = $searchFields;
        $this->displayFields = $displayFields;
        $this->onSelectCallback = $onSelectCallback;
        $this->noResultsText = $noResultsText;
        $this->perPage = $perPage;
        $this->searchPlaceholder = $searchPlaceholder;
        $this->triggerSelector = $triggerSelector;
        $this->displaySelector = $displaySelector;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.dashboard.packages.searchable-select-drawer');
    }
}