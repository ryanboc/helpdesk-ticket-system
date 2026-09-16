<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
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
                ->color('danger')
                ->url(TicketResource::getUrl('index', [
                    'tableFilters' => ['status' => ['value' => 'open']],
                ])),

            Stat::make('Needs Attention', Ticket::whereNotIn('status', ['closed', 'finished'])->count())
                ->description('All active tickets')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color('warning')
                ->url(TicketResource::getUrl('index', [
                    'tableFilters' => ['needs_attention' => ['isActive' => true]],
                ])),

            Stat::make('In Progress', Ticket::where('status', 'in_progress')->count())
                ->description('Currently being worked on')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
