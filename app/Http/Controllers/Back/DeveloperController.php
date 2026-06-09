<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Codedge\Updater\UpdaterManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class DeveloperController extends Controller
{
    public function showSettings()
    {
        $schedule_last_work = option('schedule_run');
        $schedule_run       = false;
        $random_str         = str_random(15);

        if ($schedule_last_work) {
            if (!is_object($schedule_last_work)) {
                $schedule_last_work = Carbon::createFromDate($schedule_last_work);
            }

            $diff = $schedule_last_work->diffInMinutes(now());
            $schedule_run = ($diff <= 2);
        }

        return view('back.developer.settings', compact('schedule_run', 'random_str'));
    }

    public function updateSettings(Request $request)
    {
        $developer_options = $request->except(['SELF_UPDATER_HTTP_PRIVATE_ACCESS_TOKEN']);

        foreach ($developer_options as $option => $value) {
            option_update($option, $value);
        }

        if ($request->app_debug_mode) {
            change_env('APP_DEBUG', 'true');
        } else {
            change_env('APP_DEBUG', 'false');
        }

        change_env('SELF_UPDATER_HTTP_PRIVATE_ACCESS_TOKEN', $request->SELF_UPDATER_HTTP_PRIVATE_ACCESS_TOKEN);

        if ($request->debugbar_enabled) {
            change_env('DEBUGBAR_ENABLED', 'true');
        } else {
            change_env('DEBUGBAR_ENABLED', 'false');
        }

        return response('success');
    }

    public function downApplication(Request $request)
    {
        $request->validate([
            'secret' => 'required|string'
        ]);

        $down_options = $request->except(['secret']);

        foreach ($down_options as $option => $value) {
            option_update($option, $value);
        }

        Artisan::call("down --render='errors::503' --secret='$request->secret'");

        return response()->json(['secret' => $request->secret]);
    }

    public function upApplication()
    {
        Artisan::call("up");

        return response('success');
    }

    public function webpushNotification()
    {
        Artisan::call('webpush:vapid');

        return response('success');
    }

    public function showUpdater(UpdaterManager $updater)
    {
        $token = config('self-update.updater_token');

        if (!$token) {
            session()->put('toast-error', 'برای بروزرسانی نرم افزار لطفا شماره سفارش راست چین را وارد کنید.');
            return redirect()->route('admin.developer.settings');
        }

        $isNewVersionAvailable = $updater->source()->isNewVersionAvailable();
        $versionAvailable = $updater->source()->getVersionAvailable();
        $versionInstalled = $updater->source()->getVersionInstalled();

        return view('back.developer.updater', compact(
            'isNewVersionAvailable',
            'versionAvailable',
            'versionInstalled'
        ));
    }

    public function updateApplication(UpdaterManager $updater)
    {
        $versionAvailable = $updater->source()->getVersionAvailable();
        $versionInstalled = $updater->source()->getVersionInstalled();

        try {
            Http::withHeaders([
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json'
            ])->get(config('general.api_url') . '/shop/update', [
                'host'                => url('/') ?: config('app.url'),
                'time'                => now(),
                'script'              => 'shop',
                'versionAvailable'    => $versionAvailable,
                'versionInstalled'    => $versionInstalled,
                'server_ip'           => request()->server('SERVER_ADDR'),
                'updater_ip'          => request()->ip(),
            ]);
        } catch (Exception $e) {
            // just continue
        }


        Artisan::call('updater:update');

        return response('success');
    }

    public function updaterAfter()
    {
        Artisan::call('updater:after');

        return response('success');
    }
}
