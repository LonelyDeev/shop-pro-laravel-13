<?php

namespace App\Notifications\Product;

use App\Models\Comment;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\SellerInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class SellerProductUpdate extends Notification implements ShouldQueue
{
    use Queueable;
    public $seller;
    public $product;
    public $seller_info;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Seller $seller,Product $product)
    {
        $this->seller = $seller;
        $this->product = $product;
        $this->seller_info = SellerInfo::where('seller_id',$seller->id)->first();
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
        return [
            'message' => 'فروشگاه "'.$this->seller_info->business_name.'" یک محصول، با عنوان "'.$this->product->title.'" ویرایش کرد.'
        ];
    }
    public function databaseType()
    {
        return 'SellerProductUpdate';
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('فروشگاه "'.$this->seller_info->business_name.'" یک محصول، با عنوان "'.$this->product->title.'" ویرایش کرد.')
            ->icon(option('info_icon', asset('vendor/front-assets/images/favicon-32x32.png')))
            ->body($this->product->title)
            ->options(['TTL' => 1000])
            ->data(['link' => route('admin.sellers.products')]);
    }
}
