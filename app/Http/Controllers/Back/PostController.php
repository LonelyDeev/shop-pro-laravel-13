<?php

namespace App\Http\Controllers\Back;

use App\Imports\PostsImport;
use App\Imports\UsersImport;
use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Http\Requests\Back\Post\StorePostRequest;
use App\Http\Requests\Back\Post\UpdatePostRequest;
use App\Models\FieldValue;
use App\Models\Fild;
use App\Models\Post;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use Morilog\Jalali\Jalalian;
use Spatie\Activitylog\Models\Activity;

class PostController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Post::class, 'post');
    }

    public function index()
    {
        $posts = Post::detectLang()->latest()->paginate(10);

        return view('back.posts.index', compact('posts'));
    }

    public function create(Request $request)
    {
        $categories = Category::detectLang()->where('type', 'postcat')->orderBy('ordering')->get();
        $copy_product = $request->product ? Post::where('slug', $request->product)->first() : null;
        $filds = Fild::where('belongs_to', 'products')->orderBy('created_at', 'desc')->get();
        return view('back.posts.create', compact('categories', 'copy_product','filds'));
    }

    public function store(StorePostRequest $request)
    {
        // اعتبار سنجی فیلد های اختصاصی
        $requiredFilds = Fild::where('belongs_to', 'posts')->where('required', 1)->get();
        $validationRules = [];
        $messagesValidationRules = [];
        foreach ($requiredFilds as $requiredFild) {
            $validationRules["filds.$requiredFild->id"] = 'required';
            $messagesValidationRules["filds.$requiredFild->id.required"] = "فیلد {$requiredFild->title} اجباری است.";
        }
        $request->validate($validationRules, $messagesValidationRules);

        $posts = Post::where('slug', sluggable_helper_function($request['slug'] ?: $request['title']))->get();
        if (!count($posts)) {
            $data = $request->validated();
            $data['published'] = $request->has('published');
            $data['is_editor_pick'] = $request->has('is_editor_pick');
            $data['allow_comments'] = $request->has('allow_comments');
            $data['status'] = 'end';
            if ($request->created_by == "ai" or $request->created_by == "ai-pro") {
                $token = option('AI_TOKEN_KEY');
                if (!$token or $token == null) {
                    return response("enterAiToken");
                }
                $check_token = checkAiToken($token, $request->created_by);
                if ($check_token->status == 0) {
                    return response($check_token->message);
                }
                sleep(1);
                $response = Http::post('https://ai.webtpro.ir/api/create-post', [
                    'slug' => sluggable_helper_function($data['slug'] ?: $data['title']),
                    'token_key' => $token,
                    'title' => $data['title'],
                    'created_by' => $request->created_by,
                    'language' => $request->language,
                    'description' => null
                ]);
                if ($response->getStatusCode() != 200) {
                    if (@json_decode($response->body())->message == "unique-slug") {
                        return response("uniqueSlug");
                    }
                    return response('error');
                }
                $data['status'] = 'waiting';
                if ($request->created_by == "ai") {
                    $content = json_decode($response->body());
                    $data['content'] = $content->data;
                    $data['status'] = 'end';
                }

                $data['published'] = "0";
                $data['created_by'] = $request->created_by;
            }
            if ($data['publish_date']) {
                $data['publish_date'] = Jalalian::fromFormat('Y-m-d H:i:s', $request->publish_date)->toCarbon();
            }

            $data['slug']      = sluggable_helper_function($data['slug'] ?: $data['title']);
            $data['admin_id']   = auth('adminPanel')->user()->id;
            $data['lang']      = app()->getLocale();


            if ($request->hasFile('image')) {

                $data['image'] = uploadOptimizedImage($request->image, 'posts');
            }

            $post = Post::create($data);
            $post->Categories()->attach($request->categories);

            // store Filds
            if (isset($request->filds) and count($request->filds)) {
                saveFieldValues($request->filds, 'posts', $post->id);
            }

            session()->put('toast-success', 'نوشته با موفقیت ایجاد شد.');
            return response("success");
        } else {
            return response(["Repetition-slug", sluggable_helper_function($request['slug'] ?: $request['title'])]);
        }
    }

    public function storeWithAi(Request $request) {}

    public function edit(Post $post)
    {
        $categories = Category::detectLang()->where('type', 'postcat')->orderBy('ordering')->get();
        $filds = Fild::where('belongs_to', 'posts')->orderBy('created_at', 'desc')->get();
        $fieldValues = FieldValue::where('related_id', $post->id)->get();

        return view('back.posts.edit', compact('post', 'categories' ,'filds','fieldValues'));
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        // اعتبار سنجی فیلد های اختصاصی
        $requiredFilds = Fild::where('belongs_to', 'posts')->where('required', 1)->get();
        $validationRules = [];
        $messagesValidationRules = [];
        foreach ($requiredFilds as $requiredFild) {
            $validationRules["filds.$requiredFild->id"] = 'required';
            $messagesValidationRules["filds.$requiredFild->id.required"] = "فیلد {$requiredFild->title} اجباری است.";
        }
        $request->validate($validationRules, $messagesValidationRules);

        $data = $request->validated();

        if ($data['publish_date']) {
            $data['publish_date'] = Jalalian::fromFormat('Y-m-d H:i:s', $request->publish_date)->toCarbon();
        }

        $data['slug']      = $data['slug'] ?: $data['title'];
        $data['published'] = $request->has('published');
        $data['is_editor_pick'] = $request->has('is_editor_pick');
        $data['allow_comments'] = $request->has('allow_comments');

        if ($request->hasFile('image')) {
            $data['image'] = uploadOptimizedImage($request->image, 'posts', $post->id);
        } else {
            $data['image'] = $post->image;
        }

        $post->update($data);
        $post->Categories()->sync($request->categories);

        // store Filds
        if (isset($request->filds) and count($request->filds)) {
            saveFieldValues($request->filds, 'posts', $post->id);
        }

        session()->put('toast-success', 'نوشته با موفقیت ویرایش شد.');
        return response("success");
    }

    public function destroy(Post $post)
    {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $post->tags()->detach();
        $post->Categories()->sync([]);
        $post->delete();
    }

    public function generate_slug(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $slug = SlugService::createSlug(Post::class, 'slug', $request->title);

        return response()->json(['slug' => $slug]);
    }

    //------------- Category methods

    public function categories()
    {
        $this->authorize('posts.category');

        $categories = Category::detectLang()->where('type', 'postcat')->whereNull('category_id')
            ->with('childrenCategories')
            ->orderBy('ordering')
            ->get();

        return view('back.posts.categories', compact('categories'));
    }

    public function show_details(Post $post)
    {
        $this->authorize('posts.details');

        // ── روابط ──────────────────────────────────────────────────
        $post->load([
            'category',
            'admin',
            'tags',
            'comments' => fn($q) => $q->latest()->take(5),
        ]);

        // ── آمار محتوا ─────────────────────────────────────────────
        $rawContent   = $post->content ?? '';
        $plainContent = strip_tags($rawContent);
        $wordCount    = $plainContent ? str_word_count($plainContent) : 0;
        // تخمین زمان مطالعه: میانگین ۲۰۰ کلمه فارسی در دقیقه
        $readingTime  = $wordCount > 0 ? max(1, (int) ceil($wordCount / 200)) : 0;

        $contentStats = [
            'word_count'      => $wordCount,
            'char_count'      => mb_strlen($plainContent),
            'reading_time'    => $readingTime,
            'total_views'     => $post->view ?? 0,
            'comments_count'  => $post->comments ? $post->comments()->count() : 0,
            'images_in_body'  => substr_count($rawContent, '<img'),
            'links_in_body'   => substr_count($rawContent, '<a '),
            'has_video'       => !empty($post->video_url),
            'has_podcast'     => !empty($post->podcast_url),
        ];

        // ── تحلیل SEO ──────────────────────────────────────────────
        $seoIssues   = [];
        $seoWarnings = [];
        $seoGood     = [];

        // 1. Meta Title
        $metaTitle    = $post->meta_title ?? '';
        $metaTitleLen = mb_strlen($metaTitle);
        if (empty($metaTitle)) {
            $seoIssues[] = ['field' => 'meta_title', 'msg' => 'عنوان متا (Meta Title) تعریف نشده است.', 'fix' => 'عنوانی بین ۵۰–۶۰ کاراکتر با کلیدواژه اصلی در ابتدا بنویسید.'];
        } elseif ($metaTitleLen < 30) {
            $seoWarnings[] = ['field' => 'meta_title', 'msg' => "عنوان متا خیلی کوتاه است ({$metaTitleLen} کاراکتر).", 'fix' => 'عنوان را به ۵۰–۶۰ کاراکتر برسانید.'];
        } elseif ($metaTitleLen > 65) {
            $seoWarnings[] = ['field' => 'meta_title', 'msg' => "عنوان متا خیلی بلند است ({$metaTitleLen} کاراکتر) — در گوگل قطع می‌شود.", 'fix' => 'عنوان را به کمتر از ۶۰ کاراکتر کاهش دهید.'];
        } else {
            $seoGood[] = "عنوان متا مناسب ({$metaTitleLen} کاراکتر)";
        }

        // 2. Meta Description
        $metaDesc    = $post->meta_description ?? '';
        $metaDescLen = mb_strlen($metaDesc);
        if (empty($metaDesc)) {
            $seoIssues[] = ['field' => 'meta_description', 'msg' => 'توضیحات متا (Meta Description) تعریف نشده است.', 'fix' => 'توضیحاتی بین ۱۴۰–۱۶۰ کاراکتر با CTA (دعوت به اقدام) بنویسید.'];
        } elseif ($metaDescLen < 100) {
            $seoWarnings[] = ['field' => 'meta_description', 'msg' => "توضیحات متا خیلی کوتاه ({$metaDescLen} کاراکتر).", 'fix' => 'به ۱۴۰–۱۶۰ کاراکتر برسانید.'];
        } elseif ($metaDescLen > 165) {
            $seoWarnings[] = ['field' => 'meta_description', 'msg' => "توضیحات متا خیلی بلند ({$metaDescLen} کاراکتر).", 'fix' => 'به کمتر از ۱۶۰ کاراکتر کاهش دهید.'];
        } else {
            $seoGood[] = "توضیحات متا مناسب ({$metaDescLen} کاراکتر)";
        }

        // 3. Slug
        $slug = $post->slug ?? '';
        if (empty($slug)) {
            $seoIssues[] = ['field' => 'slug', 'msg' => 'Slug تعریف نشده است.', 'fix' => 'یک slug کوتاه، خوانا و حاوی کلیدواژه اصلی تعریف کنید.'];
        } elseif (mb_strlen($slug) > 75) {
            $seoWarnings[] = ['field' => 'slug', 'msg' => 'Slug خیلی بلند است.', 'fix' => 'slug را به کمتر از ۷۵ کاراکتر کاهش دهید.'];
        } else {
            $seoGood[] = 'Slug تعریف شده';
        }

        // 4. تصویر شاخص
        if (empty($post->image)) {
            $seoIssues[] = ['field' => 'image', 'msg' => 'تصویر شاخص (Featured Image) ندارد.', 'fix' => 'تصویر شاخص مرتبط با موضوع اضافه کنید (حداقل ۱۲۰۰×۶۳۰ پیکسل برای Open Graph).'];
        } else {
            $seoGood[] = 'تصویر شاخص دارد';
        }

        // 5. محتوا
        if (empty($rawContent)) {
            $seoIssues[] = ['field' => 'content', 'msg' => 'محتوا (content) خالی است.', 'fix' => 'حداقل ۸۰۰ کلمه محتوای منحصربه‌فرد با ساختار صحیح بنویسید.'];
        } elseif ($wordCount < 300) {
            $seoIssues[] = ['field' => 'content', 'msg' => "محتوا خیلی کوتاه است ({$wordCount} کلمه).", 'fix' => 'برای مقالات، حداقل ۸۰۰–۱۵۰۰ کلمه توصیه می‌شود.'];
        } elseif ($wordCount < 800) {
            $seoWarnings[] = ['field' => 'content', 'msg' => "محتوا نسبتاً کوتاه است ({$wordCount} کلمه).", 'fix' => 'مقالات ۸۰۰+ کلمه رتبه بهتری می‌گیرند. محتوا را غنی‌تر کنید.'];
        } else {
            $seoGood[] = "محتوای کافی ({$wordCount} کلمه)";
        }

        // 6. خلاصه (summary / OG description)
        if (empty($post->summary)) {
            $seoWarnings[] = ['field' => 'summary', 'msg' => 'خلاصه مقاله (summary) خالی است.', 'fix' => 'یک خلاصه ۱–۲ جمله‌ای بنویسید که برای Open Graph و RSS نیز استفاده می‌شود.'];
        } else {
            $seoGood[] = 'خلاصه مقاله دارد';
        }

        // 7. دسته‌بندی
        if (empty($post->category_id)) {
            $seoWarnings[] = ['field' => 'category', 'msg' => 'مقاله دسته‌بندی ندارد.', 'fix' => 'دسته‌بندی به ساختار سایت و breadcrumb کمک می‌کند.'];
        } else {
            $seoGood[] = 'دسته‌بندی تعریف شده';
        }

        // 8. تاریخ انتشار
        if (empty($post->publish_date)) {
            $seoWarnings[] = ['field' => 'publish_date', 'msg' => 'تاریخ انتشار ندارد (datePublished در Schema).', 'fix' => 'تاریخ انتشار را ثبت کنید تا گوگل محتوا را تازه بداند.'];
        } else {
            $seoGood[] = 'تاریخ انتشار دارد';
        }

        // 9. تگ‌های HTML ضروری در محتوا
        $missingTags = [];
        if (!empty($rawContent)) {
            if (!preg_match('/<h1/i', $rawContent))
                $missingTags[] = ['tag' => 'H1', 'reason' => 'هر مقاله باید یک H1 داشته باشد — عنوان اصلی صفحه.'];
            if (!preg_match('/<h2/i', $rawContent))
                $missingTags[] = ['tag' => 'H2', 'reason' => 'H2 برای بخش‌بندی اصلی مقاله ضروری است.'];
            if (!preg_match('/<h3/i', $rawContent))
                $missingTags[] = ['tag' => 'H3', 'reason' => 'H3 برای زیربخش‌ها — ساختار محتوا را برای گوگل شفاف می‌کند.'];
            if (!preg_match('/<ul|<ol/i', $rawContent))
                $missingTags[] = ['tag' => 'UL / OL', 'reason' => 'لیست‌ها خوانایی را بالا می‌برند و Featured Snippet ایجاد می‌کنند.'];
            if (!preg_match('/<strong|<b\b/i', $rawContent))
                $missingTags[] = ['tag' => 'STRONG', 'reason' => 'کلیدواژه‌های مهم را bold کنید تا گوگل اهمیت آن‌ها را بفهمد.'];
            if (!preg_match('/<img/i', $rawContent))
                $missingTags[] = ['tag' => 'IMG', 'reason' => 'تصویر داخل مقاله تجربه کاربر و سئو را بهبود می‌دهد.'];
            if (!preg_match('/<blockquote/i', $rawContent))
                $missingTags[] = ['tag' => 'BLOCKQUOTE', 'reason' => 'نقل‌قول‌های برجسته برای مقالات محتوایی بسیار مناسب‌اند.'];
            if (!preg_match('/<table/i', $rawContent))
                $missingTags[] = ['tag' => 'TABLE', 'reason' => 'جداول مقایسه‌ای شانس نمایش در Featured Snippet را بالا می‌برند.'];
            if ($contentStats['images_in_body'] > 0 && !preg_match('/alt=["\'][^"\']+["\']/i', $rawContent))
                $missingTags[] = ['tag' => 'IMG alt=""', 'reason' => 'تصاویر بدون alt هستند — alt را برای هر تصویر پر کنید.'];
        } else {
            $missingTags[] = ['tag' => 'همه تگ‌ها', 'reason' => 'محتوا خالی است — ابتدا محتوا اضافه کنید.'];
        }

        // 10. لینک‌های داخلی و خارجی
        $internalLinks = [];
        $externalLinks = [];
        $appUrl = rtrim(config('app.url'), '/');
        if (!empty($rawContent)) {
            preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $rawContent, $lm);
            foreach ($lm[1] as $i => $href) {
                $text = strip_tags($lm[2][$i]);
                if (str_starts_with($href, '/') || str_contains($href, $appUrl)) {
                    $internalLinks[] = ['url' => $href, 'text' => $text];
                } elseif (str_starts_with($href, 'http')) {
                    // بررسی rel=nofollow
                    $hasNofollow = preg_match('/rel=["\'][^"\']*nofollow[^"\']*["\']/i', $lm[0][$i]);
                    $externalLinks[] = ['url' => $href, 'text' => $text, 'nofollow' => $hasNofollow];
                }
            }
            if (count($internalLinks) === 0)
                $seoWarnings[] = ['field' => 'internal_links', 'msg' => 'هیچ لینک داخلی در محتوا وجود ندارد.', 'fix' => 'به مقالات و صفحات مرتبط سایت لینک دهید (حداقل ۲–۳ لینک).'];
            else
                $seoGood[] = count($internalLinks) . ' لینک داخلی در محتوا';

            if (count($externalLinks) === 0)
                $seoWarnings[] = ['field' => 'external_links', 'msg' => 'لینک خارجی معتبر وجود ندارد.', 'fix' => 'یک لینک خارجی به منابع معتبر (ویکی‌پدیا، gov، edu) اعتبار صفحه را نزد گوگل بالا می‌برد.'];
            else
                $seoGood[] = count($externalLinks) . ' لینک خارجی';
        }

        // 11. Schema checklist برای مقاله (Article / BlogPosting)
        $schemaChecks = [
            ['label' => 'headline (عنوان)',       'ok' => !empty($post->title)],
            ['label' => 'description (خلاصه)',    'ok' => !empty($post->summary)],
            ['label' => 'image (تصویر شاخص)',     'ok' => !empty($post->image)],
            ['label' => 'author (نویسنده)',        'ok' => !empty($post->admin_id)],
            ['label' => 'datePublished',           'ok' => !empty($post->publish_date)],
            ['label' => 'dateModified',            'ok' => !empty($post->updated_at)],
            ['label' => 'articleBody (محتوا)',     'ok' => !empty($post->content)],
            ['label' => 'articleSection (دسته)',   'ok' => !empty($post->category_id)],
            ['label' => 'keywords (تگ‌ها)',        'ok' => $post->tags && $post->tags->count() > 0],
            ['label' => 'commentCount',            'ok' => $contentStats['comments_count'] > 0],
        ];

        // 12. Open Graph checklist
        $ogChecks = [
            ['label' => 'og:title',       'ok' => !empty($post->meta_title ?: $post->title)],
            ['label' => 'og:description', 'ok' => !empty($post->meta_description ?: $post->summary)],
            ['label' => 'og:image',       'ok' => !empty($post->image)],
            ['label' => 'og:type',        'ok' => true],  // article — معمولاً در layout تعریف است
            ['label' => 'og:url',         'ok' => !empty($post->slug)],
            ['label' => 'twitter:card',   'ok' => !empty($post->image)],
        ];

        // امتیاز SEO
        $totalChecks = 12;
        $issueScore  = count($seoIssues) * 2;    // هر issue دو امتیاز کم می‌کند
        $warnScore   = count($seoWarnings) * 0.7;
        $seoScore    = max(0, min(100, (int) round((1 - ($issueScore + $warnScore) / $totalChecks) * 100)));

        // لاگ
     /*   $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';
        activity()
            ->causedBy(auth()->user())
            ->event('view')
            ->withProperties(['action' => 'view_post', 'post_id' => $post->id, 'ip' => request()->ip()])
            ->log("مدیر {$adminName} صفحه مقاله #{$post->id} را مشاهده کرد");*/

        return view('back.posts.details', compact(
            'post', 'contentStats', 'seoIssues', 'seoWarnings', 'seoGood',
            'missingTags', 'internalLinks', 'externalLinks',
            'schemaChecks', 'ogChecks', 'seoScore'
        ));
    }
}
