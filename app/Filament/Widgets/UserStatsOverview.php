<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\ChatBot;
use App\Models\BusinessData;

class UserStatsOverview extends StatsOverviewWidget
{

    protected static ?string $pollingInterval = null;
    protected static ?string $sorting = '99';

    protected function getCards(): array
    {
        $userId = auth()->id();
        $now = now();

        // All time
        $totalChats = \App\Models\ChatSession::where('user_id', $userId)->count();
        $totalMessages = \App\Models\ChatMessage::where('user_id', $userId)->count();

        // This month
        $chatsThisMonth = \App\Models\ChatSession::where('user_id', $userId)
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();
        $messagesThisMonth = \App\Models\ChatMessage::where('user_id', $userId)
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // Last month
        $lastMonth = $now->copy()->subMonth();
        $chatsLastMonth = \App\Models\ChatSession::where('user_id', $userId)
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();
        $messagesLastMonth = \App\Models\ChatMessage::where('user_id', $userId)
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();

        // Deltas
        $chatsDelta = $chatsThisMonth - $chatsLastMonth;
        $messagesDelta = $messagesThisMonth - $messagesLastMonth;

        $chatsDeltaText = ($chatsDelta >= 0 ? '+' : '') . $chatsDelta . ' ' . ($chatsDelta >= 0 ? 'increase' : 'decrease');
        $messagesDeltaText = ($messagesDelta >= 0 ? '+' : '') . $messagesDelta . ' ' . ($messagesDelta >= 0 ? 'increase' : 'decrease');

        $chatsDeltaIcon = $chatsDelta >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $messagesDeltaIcon = $messagesDelta >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';

        // Sparkline data for last 7 days
        $chatSparkline = [];
        $messageSparkline = [];
        $dateLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->startOfDay();
            $endOfDay = $date->copy()->endOfDay();
            $chatSparkline[] = \App\Models\ChatSession::where('user_id', $userId)
                ->whereBetween('created_at', [$date, $endOfDay])
                ->count();
            $messageSparkline[] = \App\Models\ChatMessage::where('user_id', $userId)
                ->whereBetween('created_at', [$date, $endOfDay])
                ->count();
            $dateLabels[] = $date->format('M d');
        }

        return [
            Card::make('Total Chats', $totalChats)
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('primary')
                ->description($chatsDeltaText)
                ->descriptionIcon($chatsDeltaIcon)
                ->chart($chatSparkline),
            Card::make('Total Messages', $totalMessages)
                ->icon('heroicon-o-envelope-open')
                ->color('info')
                ->description($messagesDeltaText)
                ->descriptionIcon($messagesDeltaIcon)
                ->chart($messageSparkline),
            Card::make('Chats This Month', $chatsThisMonth)
                ->icon('heroicon-o-calendar-days')
                ->color('success')
                ->description($chatsDeltaText)
                ->descriptionIcon($chatsDeltaIcon)
                ->chart($chatSparkline),
            Card::make('Messages This Month', $messagesThisMonth)
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->description($messagesDeltaText)
                ->descriptionIcon($messagesDeltaIcon)
                ->chart($messageSparkline),
        ];
    }

    protected static ?int $sort = 0; // Top of dashboard
}
