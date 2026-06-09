<?php

namespace App\Notifications\Product;

use App\Models\Comment;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class QuestionProductCreated extends Notification implements ShouldQueue
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
        $product=Product::find($this->comment->commentable_id);
        return [
            'message' => 'شما یک پرسش  جدید از بخش محصولات،از کاربر "'. $name . '" برای محصول "'.$product->title.'" دارید.'
        ];

    }
    public function databaseType()
    {
        return 'QuestionProductCreated';
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
            ->data(['link' => route('admin.comments.products')]);
    }
}
