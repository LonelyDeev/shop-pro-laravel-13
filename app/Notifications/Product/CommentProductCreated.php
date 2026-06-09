<?php

namespace App\Notifications\Product;

use App\Models\Comment;
use App\Models\Contact;
use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class CommentProductCreated extends Notification implements ShouldQueue
{
    use Queueable;
    public $review;
    public $tries = 3;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Review $review)
    {
        $this->review = $review;
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
        $name=$this->review->user->full_name;
        if ($name==null){
            $name=$this->review->user->username;
        }
        return [
            'message' => 'شما یک نظر جدید از بخش محصولات،از کاربر "'. $name . '" برای محصول "'.$this->review->product->title.'" دارید.'
        ];

    }
    public function databaseType()
    {
        return 'CommentProductCreated';
    }

    public function toWebPush($notifiable, $notification)
    {
        $name=$this->review->user->full_name;
        if ($name==null){
            $name=$this->review->user->username;
        }
        return (new WebPushMessage)
            ->title('نظر جدید در سایت')
            ->icon(option('info_icon', asset('vendor/front-assets/images/favicon-32x32.png')))
            ->body($name)
            ->options(['TTL' => 1000])
            ->data(['link' => route('admin.reviews.index')]);
    }
}
