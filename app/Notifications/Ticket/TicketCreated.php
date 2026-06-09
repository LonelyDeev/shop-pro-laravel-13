<?php

namespace App\Notifications\Ticket;

use App\Models\Contact;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class TicketCreated extends Notification implements ShouldQueue
{
    use Queueable;
    public $ticket;
    public $tries = 3;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', WebPushChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message'       => 'شما یک تیکت با موضوع "' . $this->ticket->subject . '" دارید.',
            'ticket_id'    => $this->ticket->id
        ];
    }

    public function databaseType()
    {
        return 'TicketCreated';
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('تیکت جدید در فروشگاه')
            ->icon(option('info_icon', asset('vendor/front-assets/images/favicon-32x32.png')))
            ->body($this->ticket->subject)
            ->options(['TTL' => 1000])
            ->data(['link' => route('admin.tickets.index')]);
    }
}
