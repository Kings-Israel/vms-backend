<?php

namespace App\Notifications;

use App\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewVisitorExpected extends Notification implements ShouldQueue
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
            'type' => 'new_visitor_expected',
            'visit_id' => $this->visit->id,
            'visitor_name' => $this->visit->visitor->full_name,
            'unit' => $this->visit->unit?->name,
            'expected_arrival' => $this->visit->expected_arrival?->toIso8601String(),
            'purpose' => $this->visit->purpose,
        ];
    }
}
