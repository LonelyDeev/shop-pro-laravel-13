<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) کش اطلاعات پکیج‌ها از API
        Schema::create('packages_cache', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();          // شناسه یکتا پکیج در API
            $table->string('name');                    // نام نمایشی
            $table->text('description')->nullable();
            $table->string('latest_version', 50);      // آخرین نسخه
            $table->string('author')->nullable();
            $table->string('category')->nullable();
            $table->string('thumbnail')->nullable();   // URL تصویر
            $table->unsignedBigInteger('price')->default(0); // به تومان
            $table->boolean('is_free')->default(false);
            $table->json('meta')->nullable();          // سایر اطلاعات (مثل changelog, requires)
            $table->json('versions')->nullable();      // لیست نسخه‌ها
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
        });

        // 2) ماژول‌های نصب‌شده
        Schema::create('installed_modules', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();          // slug پکیج در API
            $table->string('name');                    // نام ماژول (نام پوشه در Modules/)
            $table->string('version', 50);
            $table->string('license_key')->nullable(); // لایسنس دریافتی از API
            $table->timestamp('license_expires_at')->nullable(); // تاریخ انقضای لایسنس
            $table->string('integrity_hash')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamp('installed_at');
            $table->timestamp('updated_app')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status', 20)->default('installed'); // installed | updating | failed
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['slug', 'version']);
            $table->index('license_expires_at');
        });

        // 3) تاریخچه خریدها
        Schema::create('package_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->string('package_slug');
            $table->string('package_name');
            $table->string('version', 50);
            $table->unsignedBigInteger('amount')->default(0);
            $table->string('currency', 10)->default('IRT');
            $table->string('gateway')->nullable();
            $table->string('transaction_id')->nullable();  // شناسه تراکنش در پروژه مدیریت
            $table->string('license_key')->nullable();
            $table->timestamp('license_expires_at')->nullable();
            $table->string('status', 20)->default('pending'); // pending | paid | failed | refunded
            $table->string('payment_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'status']);
            $table->index('package_slug');
            $table->index('transaction_id');
        });

        // 4) لاگ نصب/آپدیت/حذف
        Schema::create('module_install_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('module_slug');
            $table->string('module_name');
            $table->string('action', 20);   // install | update | uninstall | activate | deactivate
            $table->string('from_version', 50)->nullable();
            $table->string('to_version', 50)->nullable();
            $table->string('status', 20)->default('running'); // running | success | failed
            $table->text('message')->nullable();
            $table->json('details')->nullable(); // شامل مراحل اجراشده و خطاها
            $table->timestamps();

            $table->index(['module_slug', 'action']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_install_logs');
        Schema::dropIfExists('package_purchases');
        Schema::dropIfExists('installed_modules');
        Schema::dropIfExists('packages_cache');
    }
};
