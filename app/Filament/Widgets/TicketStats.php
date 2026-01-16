<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketStats extends BaseWidget
{
    // Optional: Refresh data every 15 seconds
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Tickets', Ticket::count())
                ->description('All tickets in database')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),

            Stat::make('Open Tickets', Ticket::where('status', 'open')->count())
                ->description('Tickets needing attention')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),

            Stat::make('In Progress', Ticket::where('status', 'in_progress')->count())
                ->description('Currently being worked on')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}