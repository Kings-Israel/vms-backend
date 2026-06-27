<?php

namespace App\Notifications;

use App\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class VisitorArrived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Visit $visit) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'visitor_arrived',
            'visit_id' => $this->visit->id,
            'visitor_name' => $this->visit->visitor->full_name,
            'unit' => $this->visit->unit?->name,
            'checked_in_at' => $this->visit->checked_in_at?->toIso8601String(),
        ];
    }
}
