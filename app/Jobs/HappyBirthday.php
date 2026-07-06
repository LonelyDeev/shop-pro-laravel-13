<?php

namespace App\Jobs;

use App\Models\Sms;
use App\Models\User;
use App\Services\Sms\SmsService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HappyBirthday implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries=10;
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (option('happy_birthday_sms')=="on"){
            $birthdayDate = Carbon::now()->format('m-d');
            $currentDate = Carbon::now()->format('Y-m-d');

            $users = User::where(function ($query) use ($birthdayDate, $currentDate) {
                $query->whereRaw("DATE_FORMAT(birthday, '%m-%d') = ?", [$birthdayDate])
                    ->where('birthday_at', '!=', $currentDate)
                    ->orWhere('birthday_at', null)
                    ->whereRaw("DATE_FORMAT(birthday, '%m-%d') = ?", [$birthdayDate]);
            })
                ->get();


            if (count($users)){
                foreach ($users as $user){
                    $smsService = new SmsService(
                        $user->mobile,
                        ['fullname'=>$user->full_name],
                        Sms::TYPES['SEND_HAPPY_BIRTHDAY'],
                        $user->id,
                    );

                    $smsService->sendSms();

                    $user->birthday_at=now();
                    $user->update();
                }

            }
        }

    }
}
