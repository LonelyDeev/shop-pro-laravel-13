<?php

namespace App\Listeners;
use App\Events\SendMessage as EventsSendMessage;
use App\Events\SendMessagePopUp;
use App\Models\Message;
use App\Models\Sms;
use App\Notifications\Contact\ContactCreated;
use App\Services\Sms\SmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Mail\SendMessage as SendMailMessage;
use App\Notifications\Message\SendMessage as SendNotificationMessage;
class SendMessage
{

    public function handle(EventsSendMessage $event)
    {
       $message=$event->message;

       if ($message->status_send=="pending"){
           $items=$message->items()->where('sent',0)->get();

           foreach ($items as $item){
               if ($message->email) {
                   // ارسال ایمیل
                   Mail::to($item->user->email)->send(new SendMailMessage($message->title,$message->description));
               }

               if ($message->sms) {
                   $sms_variables=json_decode($message->sms_variables);
                   if ($sms_variables){
                       $sms_variables = get_object_vars($sms_variables);
                   }else{
                        $sms_variables=null;
                    }
                   $smsService = new SmsService(
                       $item->user->mobile,
                       $sms_variables,
                       Sms::TYPES['SEND_MESSAGE_USERS'],
                       $item->user->id,
                   );

                   $smsService->sendSms();
               }

               if ($message->notification) {
                   Notification::send($item->user, new SendNotificationMessage($message));
                   event(new SendMessagePopUp($message,$item));
               }

//               if ($message->popup) {
//                   event(new SendMessagePopUp($message,$item));
//               }
           }

           Message::where('id',$message->id)->update(['status_send'=>'sent']);
       }
    }
}
