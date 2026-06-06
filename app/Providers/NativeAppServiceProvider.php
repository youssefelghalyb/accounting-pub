<?php

namespace App\Providers;

use Native\Laravel\Facades\Window;

class NativeAppServiceProvider
{
    public function boot(): void
    {
        Window::open()
            ->title(config('app.name'))
            ->width(1200)
            ->height(800);
    }
}
