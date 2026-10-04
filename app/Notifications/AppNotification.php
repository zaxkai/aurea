<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppNotification extends Notification
{
    use Queueable;

    public string $title;

    public string $message;

    public string $icon;

    public string $type;

    public ?string $link;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, string $icon = 'bell', string $type = 'info', ?string $link = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->icon = $icon; // bell, star, fire, tree, heart
        $this->type = $type; // info, success, warning
        $this->link = $link;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'icon' => $this->icon,
            'type' => $this->type,
            'link' => $this->link,
        ];
    }
}
