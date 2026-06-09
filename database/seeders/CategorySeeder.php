<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'کالای دیجیتال' => [
                'گوشی موبایل' => ['اپل', 'سامسونگ', 'شیائومی', 'نوکیا', 'هواوی', 'انر'],
                'لوازم جانبی گوشی' => [],
                'واقعیت مجازی' => [],
                'مچ بند و ساعت هوشمند' => [],
                'هدفون، هدست، هندزفری' => [],
                'اسپیکر بلوتوث و با سیم' => [],
                'دوربین' => [],
                'لپ تاپ' => ['ایسوس', 'ایسر'],
            ],
            'خودرو، ابزار و تجهیزات صنعتی' => [
                'خودرو ایرانی و خارجی' => [],
                'متورسیکلت' => [],
            ],
            'مد و پوشاک' => [],
            'کالاهای سوپرمارکتی' => [],
            'اسباب بازی، کودک و نوزاد' => [],
            'محصولات بومی و محلی' => [],
            'زیبایی و سلامت' => [],
            'خانه و آشپزخانه' => [],
            'کتاب، لوازم تحریر و هنر' => [],
            'ورزش و سفر' => [],
        ];

        $ordering = 1;
        $this->createCategories($categories, null, $ordering);

        $this->seedPostCategories();
    }

    private function createCategories(array $categories, $parentId = null, &$ordering)
    {
        foreach ($categories as $title => $children) {
            $slug = is_array($children) ? Str::slug($title) : Str::slug($children);

            $category = Category::create([
                'title' => $title,
                'slug' => sluggable_helper_function($title),
                'category_id' => $parentId,
                'type' => 'productcat',
                'ordering' => $ordering++,
                'show_in_index' => 1,
            ]);

            if (is_array($children) && !empty($children)) {
                $this->createCategories($children, $category->id, $ordering);
            }
        }
    }


    /**
     * ایجاد دسته‌بندی‌های پست (نسخه ساده)
     */
    private function seedPostCategories()
    {
        $postCategories = [
            'خانه' => [
                'راهنمای خرید',
                'نقد و بررسی',
                'ویدیو',
            ],
            'تکنولوژی و کالای دیجیتال' => [
                'کالای الکترونیک',
                'موبایل',
                'لپ‌تاپ',
                'ساعت و دستبند هوشمند',
                'دوربین عکاسی و فیلم‌برداری',
                'اپلیکیشن و نرم‌افزار',
                'آموزش و ترفند',
                'مقایسه گجت',
                'هوا فضا و نجوم',
                'خبر تکنولوژی',
            ],
            'بازی و سرگرمی' => [
                'بررسی بازی',
                'گجت بازی',
                'خبر بازی',
                'تریلر بازی',
                'موزه بازی',
                'اسباب بازی',
            ],
            'هنر و کتاب' => [
                'فیلم و سریال',
                'تئاتر و هنرهای تجسمی',
                'راهنمای خرید کتاب',
                'نقد کتاب',
                'لوازم التحریر',
                'موسیقی',
                'فرش و صنایع دستی',
            ],
            'مد و سبک زندگی' => [
                'مد و پوشاک',
                'آرایشی و بهداشتی',
                'طلا و جواهرات',
                'ورزش و تناسب اندام',
            ],
            'خانه و دکوراسیون' => [
                'خانه و آشپزخانه',
                'دکوراتیو',
                'مادر و کودک',
                'گل و گیاه',
                'خودرو و حمل‌و‌نقل',
            ],
            'سلامتی و تندرستی' => [
                'سلامت جسم',
                'سلامت روان',
                'دارو و مکمل',
                'ورزش و تناسب اندام',
                'آشپزی و تغذیه',
            ],
            'سرمایه‌گذاری' => [],
        ];

        $ordering = 1;

        foreach ($postCategories as $parentTitle => $children) {
            // ایجاد دسته اصلی
            $parent = Category::create([
                'title' => $parentTitle,
                'slug' => sluggable_helper_function($parentTitle),
                'type' => 'postcat',
                'image' => '',
                'ordering' => $ordering++,
            ]);

            // ایجاد زیردسته‌ها
            foreach ($children as $childTitle) {
                Category::create([
                    'title' => $childTitle,
                    'slug' => sluggable_helper_function($childTitle),
                    'category_id' => $parent->id,
                    'type' => 'postcat',
                    'image' => '',
                    'show_in_index' => 1,
                    'ordering' => $ordering++,
                ]);
            }
        }
    }

}
