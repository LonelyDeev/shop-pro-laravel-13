<?php

namespace App\Events;

use App\Models\Message;
use App\Models\MessageUser;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendMessagePopUp implements  ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $messageUser;
    public function __construct(Message $message,MessageUser $messageUser)
    {
        $this->message=$message;
        $this->messageUser=$messageUser;
    }

    public function broadcastOn()
    {
        return ['inbox-user-' . $this->messageUser->user_id];
    }

    public function broadcastAs()
    {
        return 'send-message-user';
    }
}
