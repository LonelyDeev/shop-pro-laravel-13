<?php

use App\Models\Banner;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Widget;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;
use App\Services\WidgetRegistry;
use App\Models\Tag;

function get_widget($widget)
{
    if (WidgetRegistry::has($widget->key)) {
        return WidgetRegistry::handle($widget->key, $widget);
    }

    $variables = [];

    switch ($widget->key) {

        case 'fullscreen-slider': {
            $variables['fullscreen_slider'] = Slider::detectLang()
                ->whereJsonContains('groups', 'fullscreen_slider')
                ->whereJsonContains('pages', $widget->page)
                ->where('published', true)
                ->orderBy('ordering', $widget->option('ordering', 'asc'))
                ->take($widget->option('number', 5))
                ->get();

            $variables['mobile_sliders'] = Slider::detectLang()
                ->whereJsonContains('groups', 'mobile_sliders')
                ->whereJsonContains('pages', $widget->page)
                ->where('published', true)
                ->orderBy('ordering', $widget->option('ordering', 'asc'))
                ->take($widget->option('number', 5))
                ->get();

            break;
        }

        case 'main-slider': {
            $variables['main_sliders'] = Slider::whereJsonContains('groups', 'main_sliders')
                ->whereJsonContains('pages', $widget->page)
                ->where('published', true)
                ->orderBy('ordering', $widget->option('ordering', 'asc'))
                ->take($widget->option('number', 5))
                ->get();

            $variables['mobile_sliders'] = Slider::whereJsonContains('groups', 'mobile_sliders')
                ->whereJsonContains('pages', $widget->page)
                ->where('published', true)
                ->orderBy('ordering', $widget->option('ordering', 'asc'))
                ->take($widget->option('number', 5))
                ->get();

            $variables['index_slider_banners'] = Banner::whereJsonContains('groups', 'index_slider_banners')
                ->whereJsonContains('pages', $widget->page)
                ->where('published', true)
                ->orderBy('ordering', $widget->option('ordering', 'asc'))
                ->take(2)
                ->get();

            break;
        }

        case 'middle-banners': {
            $variables['index_middle_banners'] = Banner::whereJsonContains('groups', 'index_middle_banners')
                ->whereJsonContains('pages', $widget->page)
                ->where('published', true)
                ->orderBy('ordering', $widget->option('ordering', 'asc'))
                ->get();

            break;
        }

        case 'middle-banners-2': {
            $variables['index_middle_2_banners'] = Banner::whereJsonContains('groups', 'index_middle_2_banners')
                ->whereJsonContains('pages', $widget->page)
                ->where('published', true)
                ->orderBy('ordering', $widget->option('ordering', 'asc'))
                ->get();

            break;
        }

        case 'middle-banners-4': {
            $variables['index_middle_4_banners'] = Banner::whereJsonContains('groups', 'index_middle_4_banners')
                ->whereJsonContains('pages', $widget->page)
                ->where('published', true)
                ->orderBy('ordering', $widget->option('ordering', 'asc'))
                ->get();

            break;
        }

        case 'coworker-sliders': {
            $variables['coworker_sliders'] = Slider::whereJsonContains('groups', 'coworker_sliders')
                ->whereJsonContains('pages', $widget->page)
                ->where('published', true)
                ->orderBy('ordering')
                ->take($widget->option('number', 2))
                ->get();

            break;
        }

        case 'sevices-sliders': {
            $variables['sevices_sliders'] = Slider::whereJsonContains('groups', 'sevices_sliders')
                ->whereJsonContains('pages', $widget->page)
                ->where('published', true)
                ->orderBy('ordering')
                ->take($widget->option('number', 2))
                ->get();

            break;
        }

        case 'categories': {
            $ids             = [];
            $category_filter = $widget->options->where('key', 'categories')->first();

            if ($category_filter && $category_filter->hasCategory()) {
                $ids = $category_filter->categories()->pluck('categories.id');
            }

            $variables['categories'] = Category::published()
                ->orderBy('ordering')
                ->whereIn('id', $ids)
                ->get();

            break;
        }

        case 'post-categories': {
            $ids             = [];
            $category_filter = $widget->options->where('key', 'post_categories')->first();

            if ($category_filter && $category_filter->hasCategory()) {
                $ids = $category_filter->categories()->pluck('categories.id');
            }
            $variables['categories'] = Category::published()
                ->orderBy('ordering')
                ->whereIn('id', $ids)
                ->get();

            break;
        }

        case 'posts': {
            $posts = Post::query();

            $category_filter = $widget->options->where('key', 'categories')->first();

            if ($category_filter && $category_filter->hasCategory()) {
                $ids = $category_filter->categories()->pluck('categories.id');
                $categories = [];

                foreach ($ids as $id) {
                    $category = Category::find($id);

                    if ($category) {
                        $categories = array_merge($categories, $category->allChildCategories());
                    }
                }

                $posts->whereIn('category_id', $categories);
            }

            switch ($widget->option('sort_type', 'latest')) {
                case 'latest': {
                    $posts->latest();
                    break;
                }
                case 'view': {
                    $posts->orderBy('view', 'desc');
                    break;
                }
            }

            $posts->published()->latest()->take($widget->option('number', 10));

            $variables['posts'] = $posts->get();

            break;
        }

        case 'products-default-block':
        case 'products-moment-block':
        case 'products-colorful-block': {
            $products = Product::query()
                ->with('lowestPrice', 'category:id,title,slug,type')
                ->select(
                    'id',
                    'title',
                    'type',
                    'category_id',
                    'slug',
                    'image',
                    'special',
                    'image_alt'
                );

            $category_filter = $widget->options->where('key', 'categories')->first();
            if ($category_filter && $category_filter->hasCategory()) {
                $ids = $category_filter->categories()->pluck('categories.id');
                $categories = [];

                foreach ($ids as $id) {
                    $category = Category::find($id);

                    if ($category && $widget->option('sub_category_products', 'yes') == 'yes') {
                        $categories = array_merge($categories, $category->allChildCategories());
                    } else if ($category) {
                        $categories = array_merge($categories, [$category->id]);
                    }
                }

                $products->whereIn('category_id', $categories);
            }

            switch ($widget->option('order_by_stock', 'yes')) {
                case 'yes': {
                    $products->orderByStock();
                    break;
                }
            }

            switch ($widget->option('products_type', 'all')) {
                case 'discount': {
                    $products->discount();
                    break;
                }
                case 'special': {
                    $products->special();
                    break;
                }
                case 'moment': {
                    $products->special();
                    break;
                }
            }

            switch ($widget->option('inventory_status', 'all')) {
                case 'available': {
                    $products->available();
                    break;
                }
                case 'unavailable': {
                    $products->unavailable();
                    break;
                }
            }

            switch ($widget->option('sort_type', 'latest')) {
                case 'latest': {
                    $products->latest();
                    break;
                }
                case 'sell': {
                    $products->orderBy('sell', 'desc');
                    break;
                }
                case 'view': {
                    $products->orderBy('view', 'desc');
                    break;
                }
                case 'cheapest': {
                    $products->orderByPrice('asc');
                    break;
                }
                case 'expensivest': {
                    $products->orderByPrice('desc');
                    break;
                }
            }

            $products->published()->latest()->take($widget->option('number', 10));

            $variables['products'] = $products->get();

            break;
        }

        case 'posts-default-block': {
            $posts = Post::query()
                ->with('category:id,title,slug');

            $category_filter = $widget->options->where('key', 'post_categories')->first();
            if ($category_filter && $category_filter->hasCategory()) {
                $ids = $category_filter->categories()->pluck('categories.id');
                $categories = [];

                foreach ($ids as $id) {
                    $category = Category::find($id);

                    if ($category && $widget->option('sub_category_post', 'yes') == 'yes') {
                        $categories = array_merge($categories, $category->allChildCategories());
                    } else if ($category) {
                        $categories = array_merge($categories, [$category->id]);
                    }
                }

                $posts->whereIn('category_id', $categories);
            }

            switch ($widget->option('posts_type', 'all')) {
                case 'is_editor_pick': {
                    $posts->where('is_editor_pick', true);
                    break;
                }
            }

            switch ($widget->option('sort_type', 'latest')) {
                case 'latest': {
                    $posts->latest();
                    break;
                }
                case 'oldest': {
                    $posts->oldest();
                    break;
                }
                case 'view': {
                    $posts->orderBy('view', 'desc');
                    break;
                }
                case 'random': {
                    $posts->inRandomOrder();
                    break;
                }
            }

            $posts->published()->take($widget->option('number', 10));

            $variables['posts'] = $posts->get();

            break;
        }

        case 'posts-three-box-block': {
            $posts = Post::query()
                ->with('category:id,title,slug');

            $category_filter = $widget->options->where('key', 'post_categories')->first();
            if ($category_filter && $category_filter->hasCategory()) {
                $ids = $category_filter->categories()->pluck('categories.id');
                $categories = [];

                foreach ($ids as $id) {
                    $category = Category::find($id);

                    if ($category && $widget->option('sub_category_post', 'yes') == 'yes') {
                        $categories = array_merge($categories, $category->allChildCategories());
                    } else if ($category) {
                        $categories = array_merge($categories, [$category->id]);
                    }
                }

                $posts->whereIn('category_id', $categories);
            }

            switch ($widget->option('posts_type', 'all')) {
                case 'is_editor_pick': {
                    $posts->where('is_editor_pick', true);
                    break;
                }
            }

            switch ($widget->option('sort_type', 'latest')) {
                case 'latest': {
                    $posts->latest();
                    break;
                }
                case 'oldest': {
                    $posts->oldest();
                    break;
                }
                case 'view': {
                    $posts->orderBy('view', 'desc');
                    break;
                }
                case 'random': {
                    $posts->inRandomOrder();
                    break;
                }
            }

            $posts->published()->take(3);

            $variables['posts'] = $posts->get();

            break;
        }

        case 'posts-big-box-block': {
            $posts = Post::query()
                ->with('category:id,title,slug');

            $category_filter = $widget->options->where('key', 'post_categories')->first();
            if ($category_filter && $category_filter->hasCategory()) {
                $ids = $category_filter->categories()->pluck('categories.id');
                $categories = [];

                foreach ($ids as $id) {
                    $category = Category::find($id);

                    if ($category && $widget->option('sub_category_post', 'yes') == 'yes') {
                        $categories = array_merge($categories, $category->allChildCategories());
                    } else if ($category) {
                        $categories = array_merge($categories, [$category->id]);
                    }
                }

                $posts->whereIn('category_id', $categories);
            }

            switch ($widget->option('posts_type', 'all')) {
                case 'is_editor_pick': {
                    $posts->where('is_editor_pick', true);
                    break;
                }
            }

            switch ($widget->option('sort_type', 'latest')) {
                case 'latest': {
                    $posts->latest();
                    break;
                }
                case 'oldest': {
                    $posts->oldest();
                    break;
                }
                case 'view': {
                    $posts->orderBy('view', 'desc');
                    break;
                }
                case 'random': {
                    $posts->inRandomOrder();
                    break;
                }
            }

            $posts->published()->take(6);

            $variables['posts'] = $posts->get();

            break;
        }

        case 'posts-tags': {
            $tags = Tag::query()
                ->select('tags.*')
                ->whereHas('posts', function ($query) {
                    $query->published();
                })
                ->withCount(['posts' => function ($query) {
                    $query->published();
                }]);

            switch ($widget->option('sort_type', 'most_used')) {
                case 'most_used': {
                    $tags->orderBy('posts_count', 'desc');
                    break;
                }
                case 'latest': {
                    $tags->latest();
                    break;
                }
                case 'oldest': {
                    $tags->oldest();
                    break;
                }
                case 'view': {
                    $tags->orderBy('view_count', 'desc');
                    break;
                }
                case 'random': {
                    $tags->inRandomOrder();
                    break;
                }
            }

            $ordering = $widget->option('ordering', 'asc');
            if ($widget->option('sort_type', 'most_used') != 'random') {
                $tags->orderBy('name', $ordering);
            }

            $tags->take($widget->option('number', 10));

            $variables['tags'] = $tags->get();

            break;
        }

        case 'faqs':
            $variables['faqs'] = \App\Models\Faq::where('published', true)
                ->take($widget->option('number', 10))
                ->orderBy('order', 'asc')
                ->get();
            break;
    }

    return $variables;
}

function highest_banner()
{
    return Banner::whereJsonContains('groups', 'index_highest_banner')
        ->where('published', true)
        ->orderBy('ordering')
        ->get();
}


function widget_seeder()
{
    $theme = current_theme_name();

    if (Widget::where('theme', $theme)->exists()) {
        return;
    }

    // ویجت‌های صفحه اصلی
    $homeWidgets = [
        [
            'widget' => ['title' => 'بنر تکی', 'key' => 'middle-banners', 'theme' => $theme, 'ordering' => 1],
            'options' => [['key' => 'number', 'value' => '1'], ['key' => 'place', 'value' => 'index_banners_place1']]
        ],
        [
            'widget' => ['title' => 'اسلایدر اصلی و بنر کناری', 'key' => 'main-slider', 'theme' => $theme, 'ordering' => 2],
            'options' => [['key' => 'number', 'value' => '5'], ['key' => 'banner_position', 'value' => 'left']]
        ],
        [
            'widget' => ['title' => 'استوری و هایلایت ها', 'key' => 'main-story', 'theme' => $theme, 'ordering' => 3],
            'options' => [['key' => 'number', 'value' => '10']]
        ],
        [
            'widget' => ['title' => 'محصولات ویژه', 'key' => 'products-colorful-block', 'theme' => $theme, 'ordering' => 4],
            'options' => array_merge(
                [['key' => 'title', 'value' => 'محصولات ویژه'], ['key' => 'block_color', 'value' => '#2ba5da']],
                baseProductOptions()
            )
        ],
        [
            'widget' => ['title' => 'بنر چهارتایی', 'key' => 'middle-banners-4', 'theme' => $theme, 'ordering' => 5],
            'options' => [['key' => 'place', 'value' => 'index_banners_place1'], ['key' => 'number', 'value' => '4']]
        ],
        [
            'widget' => ['title' => 'محصولات ویژه دو', 'key' => 'products-colorful-block', 'theme' => $theme, 'ordering' => 6],
            'options' => array_merge(
                [['key' => 'title', 'value' => 'محصولات ویژه'], ['key' => 'block_color', 'value' => '#53c83c']],
                baseProductOptions()
            )
        ],
        [
            'widget' => ['title' => 'کادر محصولات با پیشنهاد لحظه ای', 'key' => 'products-moment-block', 'theme' => $theme, 'ordering' => 7],
            'options' => [
                ['key' => 'products_type', 'value' => 'all'],
                ['key' => 'inventory_status', 'value' => 'all'],
                ['key' => 'sort_type', 'value' => 'latest'],
                ['key' => 'order_by_stock', 'value' => 'yes'],
                ['key' => 'number', 'value' => '10'],
                ['key' => 'categories', 'value' => 'off'],
                ['key' => 'sub_category_products', 'value' => 'yes'],
            ]
        ],
        [
            'widget' => ['title' => 'دسته بندی ها', 'key' => 'categories', 'theme' => $theme, 'ordering' => 8],
            'options' => [
                ['key' => 'categories', 'value' => 'on', 'type' => 'product_categories', 'categories' => Category::where(['show_in_index' => 1, 'type' => 'productcat'])->pluck('id')]
            ]
        ],
        [
            'widget' => ['title' => 'محصولات تخفیف دار', 'key' => 'products-default-block', 'theme' => $theme, 'ordering' => 9],
            'options' => array_merge([['key' => 'title', 'value' => 'محصولات تخفیف دار'], ['key' => 'products_type', 'value' => 'discount']], baseProductOptions('no'))
        ],
        [
            'widget' => ['title' => 'بنر 4 تایی', 'key' => 'middle-banners-4', 'theme' => $theme, 'ordering' => 10],
            'options' => [['key' => 'place', 'value' => 'index_banners_place2'], ['key' => 'number', 'value' => '4']]
        ],
        [
            'widget' => ['title' => 'جدید ترین محصولات', 'key' => 'products-default-block', 'theme' => $theme, 'ordering' => 11],
            'options' => array_merge([['key' => 'title', 'value' => 'جدید ترین محصولات']], baseProductOptions('no'))
        ],
        [
            'widget' => ['title' => 'بنر دوتایی', 'key' => 'middle-banners-2', 'theme' => $theme, 'ordering' => 12],
            'options' => [['key' => 'place', 'value' => 'index_banners_place1'], ['key' => 'number', 'value' => '2']]
        ],
        [
            'widget' => ['title' => 'اسلایدر لوگو همکاران', 'key' => 'coworker-sliders', 'theme' => $theme, 'ordering' => 13],
            'options' => [['key' => 'number', 'value' => '10']]
        ],
        [
            'widget' => ['title' => 'بنر تکی', 'key' => 'middle-banners', 'theme' => $theme, 'ordering' => 14],
            'options' => [['key' => 'place', 'value' => 'index_banners_place2'], ['key' => 'number', 'value' => '10']]
        ],
    ];

    // ویجت‌های صفحه مقالات
    $postWidgets = [
        [
            'widget' => ['title' => 'دسته بندی ها', 'key' => 'post-categories', 'theme' => $theme, 'ordering' => 1, 'page' => 'posts'],
            'options' => [
                ['key' => 'post_categories', 'value' => 'on', 'type' => 'post_categories', 'categories' => Category::where(['show_in_index' => 1, 'type' => 'postcat'])->pluck('id')]
            ]
        ],
        [
            'widget' => ['title' => 'برگزیده ها', 'key' => 'posts-three-box-block', 'theme' => $theme, 'ordering' => 2, 'page' => 'posts'],
            'options' => [['key' => 'title', 'value' => 'برگزیده ها'], ['key' => 'sort_type', 'value' => 'latest'], ['key' => 'sub_category_post', 'value' => 'yes']]
        ],
        [
            'widget' => ['title' => 'مقالات تصادفی', 'key' => 'posts-big-box-block', 'theme' => $theme, 'ordering' => 3, 'page' => 'posts'],
            'options' => [['key' => 'title', 'value' => 'مقالات تصادفی'], ['key' => 'sort_type', 'value' => 'random'], ['key' => 'sub_category_post', 'value' => 'yes']]
        ],
        [
            'widget' => ['title' => 'مقالات', 'key' => 'posts-default-block', 'theme' => $theme, 'ordering' => 4, 'page' => 'posts'],
            'options' => [
                ['key' => 'title', 'value' => 'پربازدید ترین ها'],
                ['key' => 'sort_type', 'value' => 'latest'],
                ['key' => 'link_title', 'value' => 'بیشتر'],
                ['key' => 'number', 'value' => '10'],
                ['key' => 'sub_category_post', 'value' => 'yes'],
            ]
        ],
        [
            'widget' => ['title' => 'بنر چهارتایی', 'key' => 'middle-banners-4', 'theme' => $theme, 'ordering' => 5, 'page' => 'posts'],
            'options' => [['key' => 'place', 'value' => 'index_banners_place1'], ['key' => 'number', 'value' => '4']]
        ],
        [
            'widget' => ['title' => 'هشتگ های داغ', 'key' => 'posts-tags', 'theme' => $theme, 'ordering' => 6, 'page' => 'posts'],
            'options' => [['key' => 'title', 'value' => 'هشتگ های داغ'], ['key' => 'sort_type', 'value' => 'most_used'], ['key' => 'ordering', 'value' => 'asc']]
        ],
    ];

    // ایجاد ویجت‌ها
    createWidgets($homeWidgets);
    createWidgets($postWidgets);
}

// گزینه‌های پایه محصولات
function baseProductOptions($orderByStock = 'yes')
{
    return [
        ['key' => 'inventory_status', 'value' => 'all'],
        ['key' => 'sort_type', 'value' => 'latest'],
        ['key' => 'order_by_stock', 'value' => $orderByStock],
        ['key' => 'link', 'value' => '/product/specials'],
        ['key' => 'link_title', 'value' => 'مشاهده همه'],
        ['key' => 'image', 'value' => theme_asset("images/amazing/amazing.png")],
        ['key' => 'sub_category_products', 'value' => 'yes'],
        ['key' => 'number', 'value' => '10'],
    ];
}

// ایجاد ویجت‌ها و آپشن‌ها
function createWidgets($widgets)
{
    foreach ($widgets as $data) {
        $widget = Widget::create($data['widget']);

        foreach ($data['options'] as $option) {
            if (isset($option['type']) && in_array($option['type'], ['product_categories', 'post_categories'])) {
                $opt = $widget->options()->create(['key' => $option['key'], 'value' => $option['value']]);
                if (!empty($option['categories'])) {
                    $opt->categories()->sync($option['categories']);
                }
            } else {
                $widget->options()->create($option);
            }
        }
    }
}

function theme_first_config()
{
    widget_seeder();
}

function Get_All_Tags($tag_name)
{
    $ids=[];
    if ($tag_name=="post"){
        $tagName='App\Models\Post';
    }elseif($tag_name=="product"){
        $tagName='App\Models\Product';
    }
    $taggables=DB::table('taggables')->where('taggable_type',$tagName)->get();
    foreach ($taggables as $taggable){
        array_push($ids,$taggable->tag_id);
    }
    return \App\Models\Tag::whereIn('id',$ids)->get();
}
