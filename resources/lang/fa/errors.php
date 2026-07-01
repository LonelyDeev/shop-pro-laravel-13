<?php

return [
    // Authentication
    'Invalid credentials' => 'اطلاعات ورود نامعتبر',
    'Unauthorized access' => 'دسترسی غیرمجاز',
    'Access forbidden' => 'دسترسی ممنوع',
    'Token has expired' => 'توکن منقضی شده است',
    'Invalid token' => 'توکن نامعتبر',
    'Account is inactive' => 'حساب کاربری غیرفعال است',
    'Account has been suspended' => 'حساب کاربری تعلیق شده است',
    'Incorrect password' => 'رمز عبور اشتباه است',
    'User not found' => 'کاربر یافت نشد',

    // Validation
    'The :attribute field is required' => 'فیلد :attribute الزامی است',
    'The :attribute must be at least :min characters' => ':attribute باید حداقل :min کاراکتر باشد',
    'The :attribute must not exceed :max characters' => ':attribute نباید بیشتر از :max کاراکتر باشد',
    'The :attribute must be a number' => ':attribute باید عددی باشد',
    'The :attribute must be an integer' => ':attribute باید عدد صحیح باشد',
    'The :attribute must be a valid email' => ':attribute باید ایمیل معتبر باشد',
    'The :attribute must be a valid URL' => ':attribute باید آدرس اینترنتی معتبر باشد',
    'The :attribute must be unique' => ':attribute باید یکتا باشد',
    'The :attribute confirmation does not match' => 'تاییدیه :attribute مطابقت ندارد',
    'The :attribute must be a valid date' => ':attribute باید تاریخ معتبر باشد',
    'The :attribute must be a valid IP address' => ':attribute باید آدرس IP معتبر باشد',
    'The :attribute must be boolean' => ':attribute باید مقدار بولین باشد',
    'The :attribute must be an array' => ':attribute باید آرایه باشد',
    'The :attribute must be a string' => ':attribute باید رشته باشد',
    'The :attribute must exist in the database' => ':attribute باید در پایگاه داده وجود داشته باشد',

    // Database
    'Database error occurred' => 'خطای پایگاه داده رخ داده است',
    'Duplicate entry' => 'ورودی تکراری',
    'Column not found' => 'ستون یافت نشد',
    'Table not found' => 'جدول یافت نشد',
    'Foreign key constraint violation' => 'نقض کلید خارجی',
    'SQL syntax error' => 'خطای نحوی در دستور SQL',
    'Query execution error' => 'خطا در اجرای پرس و جو',
    'Database connection refused' => 'اتصال به پایگاه داده رد شد',

    // Files
    'File not found' => 'فایل یافت نشد',
    'Permission denied' => 'دسترسی مجاز نیست',
    'Invalid file type' => 'نوع فایل نامعتبر',
    'File is too large' => 'حجم فایل بیش از حد مجاز است',
    'File upload error' => 'خطا در آپلود فایل',
    'Disk is full' => 'فضای دیسک کامل است',

    // HTTP
    'Resource not found' => 'منبع مورد نظر یافت نشد',
    'Method not allowed' => 'متود مجاز نیست',
    'Bad request' => 'درخواست نامعتبر',
    'Internal server error' => 'خطای داخلی سرور',
    'Too many requests' => 'تعداد درخواست‌ها بیش از حد مجاز است',
    'Page not found' => 'صفحه یافت نشد',
    'Route not found' => 'مسیر یافت نشد',
    'View not found' => 'نمایش یافت نشد',

    // API
    'Invalid API key' => 'کلید API نامعتبر',
    'API rate limit exceeded' => 'محدودیت درخواست API رعایت نشده است',
    'Invalid request format' => 'فرمت درخواست نامعتبر',
    'Missing required parameter' => 'پارامتر الزامی وجود ندارد',
    'Invalid parameter value' => 'مقدار پارامتر نامعتبر',

    // Subscription & Payment
    'Subscription has expired' => 'اشتراک منقضی شده است',
    'Subscription is inactive' => 'اشتراک غیرفعال است',
    'Payment failed' => 'پرداخت ناموفق بود',
    'Invalid payment method' => 'روش پرداخت نامعتبر',
    'Insufficient balance' => 'موجودی کافی نیست',
    'Transaction failed' => 'تراکنش ناموفق بود',

    // System
    'Memory limit exceeded' => 'محدودیت حافظه رد شده است',
    'Execution timeout' => 'زمان اجرا به پایان رسید',
    'Class not found' => 'کلاس یافت نشد',
    'Method not found' => 'متد یافت نشد',
    'Property not found' => 'خصوصیت یافت نشد',
    'Undefined variable' => 'متغیر تعریف نشده',
    'Undefined index' => 'ایندکس تعریف نشده',
    'Division by zero' => 'تقسیم بر صفر',
    'Type error' => 'خطای نوع داده',
    'Operation timeout' => 'زمان عملیات به پایان رسید',

    // Custom Application
    'Project not found' => 'پروژه یافت نشد',
    'Customer not found' => 'مشتری یافت نشد',
    'Subscription not found' => 'اشتراک یافت نشد',
    'Update not found' => 'آپدیت یافت نشد',
    'Invalid domain' => 'دامنه نامعتبر',
    'Domain is blocked' => 'دامنه مسدود شده است',
    'Version mismatch' => 'عدم تطابق نسخه',
    'Update already applied' => 'آپدیت قبلاً اعمال شده است',
    'Download failed' => 'دانلود ناموفق بود',
    'Installation failed' => 'نصب ناموفق بود',

    // Network
    'Network error' => 'خطای شبکه',
    'Connection timeout' => 'زمان اتصال به پایان رسید',
    'SSL certificate error' => 'خطای گواهی SSL',
    'DNS resolution error' => 'خطای تفکیک DNS',

    // Security
    'CSRF token mismatch' => 'توکن CSRF مطابقت ندارد',
    'XSS attack detected' => 'حمله XSS شناسایی شد',
    'SQL injection detected' => 'حمله تزریق SQL شناسایی شد',
    'Brute force attempt detected' => 'حمله بروت‌فورس شناسایی شد',

    // General
    'An error occurred' => 'خطایی رخ داده است',
    'Unknown error occurred' => 'خطای ناشناخته رخ داده است',
    'Trying to access array offset on null' => 'دسترسی به ایندکس آرایه بر روی مقدار null',
    // سایر خطاهای مرتبط با آرایه
    'Undefined array key' => 'کلید آرایه تعریف نشده',
    'Array offset error' => 'خطای آفست آرایه',
    'Cannot use a scalar value as an array' => 'نمی‌توان از مقدار اسکالر به عنوان آرایه استفاده کرد',
    'Cannot access empty property' => 'دسترسی به خصوصیت خالی امکان‌پذیر نیست',
];
