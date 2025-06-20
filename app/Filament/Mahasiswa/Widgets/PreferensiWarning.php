<?php

namespace App\Filament\Mahasiswa\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\HtmlString;

class PreferensiWarning extends Widget
{
    protected static string $view = 'filament.widgets.preferensi-warning';

    public static function canView(): bool
    {
        return !auth()->user()->mahasiswa?->preferensi()->exists();
    }
    
}
