<?php

namespace App\Notifications\Post;

use App\Models\Comment;
use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class CommentPostCreated extends Notification implements ShouldQueue
{
    use Queueable;
    public $comment;
    public $tries = 3;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
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
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $name=$this->comment->user->full_name;
        if ($name==null){
            $name=$this->comment->user->username;
        }
        return [
            'message' => 'شما یک نظر جدید از بخش مقالات،از کاربر "'. $name . '" دارید.'
        ];
    }
    public function databaseType()
    {
        return 'CommentPostCreated';
    }


    public function toWebPush($notifiable, $notification)
    {
        $name=$this->comment->user->full_name;
        if ($name==null){
            $name=$this->comment->user->username;
        }
        return (new WebPushMessage)
            ->title('نظر جدید در سایت')
            ->icon(option('info_icon', asset('vendor/front-assets/images/favicon-32x32.png')))
            ->body($name)
            ->options(['TTL' => 1000])
            ->data(['link' => route('admin.comments.posts')]);
    }
}
