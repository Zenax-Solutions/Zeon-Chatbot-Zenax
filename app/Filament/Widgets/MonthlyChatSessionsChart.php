<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ChatSession;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MonthlyChatSessionsChart extends ChartWidget
{
    public static string $chartType = 'line';

    protected static ?string $heading = 'Monthly Chat Sessions';
    protected static ?string $maxHeight = '200px';
    protected static ?string $maxWidth = '50px';
    protected static ?int $sort = 100;
    protected static ?string $description = 'Number of chat sessions created in the last two months.';

    protected function getData(): array
    {
        $userId = Auth::id();
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();

        $currentMonthCount = ChatSession::where('user_id', $userId)
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        $lastMonthCount = ChatSession::where('user_id', $userId)
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Chat Sessions',
                    'data' => [$lastMonthCount, $currentMonthCount],
                    'backgroundColor' => ['#a5b4fc', '#2563eb'],
                ],
            ],
            'labels' => [
                $lastMonth->format('F'),
                $now->format('F'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line'; // You can use 'doughnut' or 'pie' if preferred
    }
}
