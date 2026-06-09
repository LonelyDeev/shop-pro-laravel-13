<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Helpers\ShortcodeHelper;

class ShortcodeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // ثبت یک Blade directive جدید
        Blade::directive('shortcode', function ($expression) {
            return "<?php echo App\Helpers\ShortcodeHelper::parse($expression); ?>";
        });
    }

    public function register()
    {
        //
    }
}
