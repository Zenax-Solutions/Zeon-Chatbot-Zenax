<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class CurrentPlanWidget extends Widget
{
    protected static string $view = 'filament.widgets.current-plan-widget';
    protected static ?int $sort = 102;
    protected static ?string $pollingInterval = null;
}
