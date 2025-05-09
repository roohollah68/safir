<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;

class NotificationService
{
    public function create(User $user, string $message, ?string $link = null)
    {
        $user->notify(new class($message, $link) extends Notification {
            public function __construct(
                private string $message,
                private ?string $link
            ) {}

            public function via($notifiable)
            {
                return ['database'];
            }

            public function toDatabase($notifiable)
            {
                return [
                    'message' => $this->message,
                    'link' => $this->link
                ];
            }
        });
    }
}