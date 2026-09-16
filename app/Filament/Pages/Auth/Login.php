<?php

namespace App\Filament\Pages\Auth;

use App\Models\Ticket;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;

class Login extends \Filament\Pages\Auth\Login
{
    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        if ($response !== null) {
            $this->sendWeeklyDeadlineNotification();
        }

        return $response;
    }

    private function sendWeeklyDeadlineNotification(): void
    {
        $user = Filament::auth()->user();
        $weekKey = today()->startOfWeek()->toDateString();

        $tickets = Ticket::query()
            ->whereBetween('deadline_date', [today(), today()->endOfWeek()])
            ->whereNotIn('status', ['closed', 'finished'])
            ->when(! $user->is_admin, function (Builder $query) use ($user): void {
                $query->where(function (Builder $query) use ($user): void {
                    $query->where('user_id', $user->id)
                        ->orWhere('assigned_to_id', $user->id);
                });
            })
            ->orderBy('deadline_date')
            ->get(['id', 'title', 'deadline_date']);

        if ($tickets->isEmpty()) {
            return;
        }

        $deadlineList = $tickets
            ->take(3)
            ->map(fn (Ticket $ticket): string => sprintf(
                '#%d %s (%s)',
                $ticket->id,
                e($ticket->title),
                $ticket->deadline_date->format('D, j M'),
            ))
            ->implode(' · ');

        if ($tickets->count() > 3) {
            $deadlineList .= sprintf(' · +%d more', $tickets->count() - 3);
        }

        $notification = Notification::make()
            ->title(sprintf('%d deadline%s this week', $tickets->count(), $tickets->count() === 1 ? '' : 's'))
            ->body($deadlineList)
            ->icon('heroicon-o-calendar-days')
            ->warning()
            ->persistent()
            ->viewData(['deadline_week' => $weekKey]);

        $existingNotification = $user->notifications()
            ->get()
            ->first(fn (DatabaseNotification $databaseNotification): bool => data_get($databaseNotification->data, 'viewData.deadline_week') === $weekKey);

        if ($existingNotification) {
            $existingNotification->update([
                'data' => $notification->getDatabaseMessage(),
                'read_at' => null,
            ]);
        } else {
            $notification->sendToDatabase($user);
        }

        // Also show the alert as soon as the user reaches the dashboard.
        $notification->send();
    }
}
