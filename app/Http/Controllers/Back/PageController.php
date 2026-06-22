<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\Menu;
use App\Models\Page;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Page::class, 'page');
    }

    public function index()
    {
        $pages = Page::detectLang()->latest()->paginate(10);

        return view('back.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('back.pages.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|max:191',
            'content' => 'required',
            'slug' => 'nullable|unique:pages,slug'
        ]);

        Page::create([
            'title'      => $request->title,
            'content'    => $request->input('content'),
            'slug'       => $request->slug ?: $request->title,
            'meta_title'       => $request->meta_title,
            'meta_description' => $request->meta_description,
            'published'  => $request->published ? true : false,
            'lang'       => app()->getLocale(),
        ]);

        session()->put('toast-success','صفحه با موفقیت ایجاد شد.');
        return response("success", 200);
    }

    public function edit(Page $page)
    {
        return view('back.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $this->validate($request, [
            'title' => 'required|string|max:191',
            'content' => 'required',
        ]);

        $slug = $page->slug;

        $page->update([
            'title'     => $request->title,
            'content'   => $request->input('content'),
            'slug'      => $request->slug ?: $request->title,
            'published' => $request->published ? true : false,
        ]);

        Menu::where('link', '/pages/' . $slug)->update([
            'link' => '/pages/' . $page->slug,
        ]);

        Link::where('link', '/pages/' . $slug)->update([
            'link' => '/pages/' . $page->slug,
        ]);

        session()->put('toast-success','صفحه با موفقیت ویرایش شد.');
        return response("success", 200);
    }

    public function destroy(Page $page)
    {
        $page->tags()->detach();
        $page->delete();

        return response("success", 200);
    }

    public function show_details(Page $page)
    {
        $this->authorize('pages.details');


        // ── آمار محتوا ─────────────────────────────────────────────
        $rawContent   = $page->content ?? '';
        $plainContent = strip_tags($rawContent);
        $wordCount    = $plainContent ? str_word_count($plainContent) : 0;
        // تخمین زمان مطالعه: میانگین ۲۰۰ کلمه فارسی در دقیقه
        $readingTime  = $wordCount > 0 ? max(1, (int) ceil($wordCount / 200)) : 0;

        $contentStats = [
            'word_count'      => $wordCount,
            'char_count'      => mb_strlen($plainContent),
            'reading_time'    => $readingTime,
            'images_in_body'  => substr_count($rawContent, '<img'),
            'links_in_body'   => substr_count($rawContent, '<a '),
        ];

        // ── تحلیل SEO ──────────────────────────────────────────────
        $seoIssues   = [];
        $seoWarnings = [];
        $seoGood     = [];

        // 1. Meta Title
        $metaTitle    = $page->meta_title ?? '';
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
        $metaDesc    = $page->meta_description ?? '';
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
        $slug = $page->slug ?? '';
        if (empty($slug)) {
            $seoIssues[] = ['field' => 'slug', 'msg' => 'Slug تعریف نشده است.', 'fix' => 'یک slug کوتاه، خوانا و حاوی کلیدواژه اصلی تعریف کنید.'];
        } elseif (mb_strlen($slug) > 75) {
            $seoWarnings[] = ['field' => 'slug', 'msg' => 'Slug خیلی بلند است.', 'fix' => 'slug را به کمتر از ۷۵ کاراکتر کاهش دهید.'];
        } else {
            $seoGood[] = 'Slug تعریف شده';
        }

        // 4. محتوا
        if (empty($rawContent)) {
            $seoIssues[] = ['field' => 'content', 'msg' => 'محتوا (content) خالی است.', 'fix' => 'حداقل ۴۰۰ کلمه محتوای منحصربه‌فرد با ساختار صحیح بنویسید.'];
        } elseif ($wordCount < 200) {
            $seoIssues[] = ['field' => 'content', 'msg' => "محتوا خیلی کوتاه است ({$wordCount} کلمه).", 'fix' => 'برای صفحات، حداقل ۴۰۰–۸۰۰ کلمه توصیه می‌شود.'];
        } elseif ($wordCount < 400) {
            $seoWarnings[] = ['field' => 'content', 'msg' => "محتوا نسبتاً کوتاه است ({$wordCount} کلمه).", 'fix' => 'صفحات ۴۰۰+ کلمه رتبه بهتری می‌گیرند. محتوا را غنی‌تر کنید.'];
        } else {
            $seoGood[] = "محتوای کافی ({$wordCount} کلمه)";
        }

        // 5. وضعیت انتشار
        if (!$page->published) {
            $seoWarnings[] = ['field' => 'published', 'msg' => 'صفحه منتشر نشده است.', 'fix' => 'صفحه را منتشر کنید تا در سایت نمایش داده شود.'];
        } else {
            $seoGood[] = 'صفحه منتشر شده';
        }

        // 6. تگ‌های HTML ضروری در محتوا
        $missingTags = [];
        if (!empty($rawContent)) {
            if (!preg_match('/<h1/i', $rawContent))
                $missingTags[] = ['tag' => 'H1', 'reason' => 'هر صفحه باید یک H1 داشته باشد — عنوان اصلی صفحه.'];
            if (!preg_match('/<h2/i', $rawContent))
                $missingTags[] = ['tag' => 'H2', 'reason' => 'H2 برای بخش‌بندی اصلی صفحه ضروری است.'];
            if (!preg_match('/<img/i', $rawContent))
                $missingTags[] = ['tag' => 'IMG', 'reason' => 'تصویر داخل صفحه تجربه کاربر و سئو را بهبود می‌دهد (اختیاری).'];
            if ($contentStats['images_in_body'] > 0 && !preg_match('/alt=["\'][^"\']+["\']/i', $rawContent))
                $missingTags[] = ['tag' => 'IMG alt=""', 'reason' => 'تصاویر بدون alt هستند — alt را برای هر تصویر پر کنید.'];
        } else {
            $missingTags[] = ['tag' => 'همه تگ‌ها', 'reason' => 'محتوا خالی است — ابتدا محتوا اضافه کنید.'];
        }

        // 7. لینک‌های داخلی و خارجی
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
                    $hasNofollow = preg_match('/rel=["\'][^"\']*nofollow[^"\']*["\']/i', $lm[0][$i]);
                    $externalLinks[] = ['url' => $href, 'text' => $text, 'nofollow' => $hasNofollow];
                }
            }
            if (count($internalLinks) === 0)
                $seoWarnings[] = ['field' => 'internal_links', 'msg' => 'هیچ لینک داخلی در محتوا وجود ندارد.', 'fix' => 'به صفحات مرتبط سایت لینک دهید (حداقل ۱–۲ لینک).'];
            else
                $seoGood[] = count($internalLinks) . ' لینک داخلی در محتوا';
        }

        // 8. Schema checklist برای صفحه (WebPage)
        $schemaChecks = [
            ['label' => 'name (عنوان)',        'ok' => !empty($page->title)],
            ['label' => 'description',          'ok' => !empty($page->meta_description)],
            ['label' => 'text (محتوا)',         'ok' => !empty($page->content)],
            ['label' => 'dateModified',         'ok' => !empty($page->updated_at)],
        ];

        // 9. Open Graph checklist
        $ogChecks = [
            ['label' => 'og:title',       'ok' => !empty($page->meta_title ?: $page->title)],
            ['label' => 'og:description', 'ok' => !empty($page->meta_description)],
            ['label' => 'og:type',        'ok' => true],  // website
            ['label' => 'og:url',         'ok' => !empty($page->slug)],
        ];

        // امتیاز SEO
        $totalChecks = 8;
        $issueScore  = count($seoIssues) * 2;
        $warnScore   = count($seoWarnings) * 0.5;
        $seoScore    = max(0, min(100, (int) round((1 - ($issueScore + $warnScore) / $totalChecks) * 100)));

        return view('back.pages.details', compact(
            'page', 'contentStats', 'seoIssues', 'seoWarnings', 'seoGood',
            'missingTags', 'internalLinks', 'externalLinks',
            'schemaChecks', 'ogChecks', 'seoScore'
        ));
    }
}
