<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\ChatBot;
use App\Models\BusinessData;

class UserStatsOverview extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        $userId = auth()->id();

        return [
            Card::make('My Chat Bots', ChatBot::where('user_id', $userId)->count()),

        ];
    }

    protected static ?int $sort = 0; // Top of dashboard
}
