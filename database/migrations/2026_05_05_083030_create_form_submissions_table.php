<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->json('data'); // داده‌های ارسال شده
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->boolean('has_photo')->nullable()->default(false);
            $table->timestamps();

            // 1. ایندکس برای جستجوی ارسال‌های یک فرم (پرکاربردترین)
            $table->index('form_id');

            // 2. ایندکس برای مرتب‌سازی بر اساس زمان ارسال
            $table->index('submitted_at');

            // 3. ایندکس برای جستجوی ارسال‌های یک کاربر
            $table->index('user_id');

            // 4. ایندکس ترکیبی برای ارسال‌های یک فرم در بازه زمانی خاص
            $table->index(['form_id', 'submitted_at']);

            // 5. ایندکس برای فیلتر ارسال‌های دارای عکس
            $table->index('has_photo');

            // 6. ایندکس برای جستجوی آی پی
            $table->index('ip_address');

            // 7. ایندکس ترکیبی برای کاربر و فرم
            $table->index(['user_id', 'form_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
