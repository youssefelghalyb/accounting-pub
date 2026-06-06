<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class SearchableSelect extends Component
{
    public string $name;
    public string $url;
    public string $placeholder;
    public bool $required;

    public function __construct(string $name, string $url, string $placeholder, bool $required = false)
    {
        $this->name = $name;
        $this->url = $url;
        $this->placeholder = $placeholder;
        $this->required = $required;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('components.dashboard.packages.searchable-select');
    }
}
