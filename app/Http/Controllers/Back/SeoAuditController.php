<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SeoAuditController extends Controller
{
    // ─── صفحه اصلی ───────────────────────────────────────────────
    public function index()
    {
        $this->authorize('seo.audit');

      /*  $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';
        activity()->causedBy(auth()->user())->event('view')
            ->withProperties(['action' => 'view_seo_audit', 'ip' => request()->ip()])
            ->log("مدیر {$adminName} صفحه آدیت سئو سایت را مشاهده کرد");*/

        // ── آمار کلی از دیتابیس ──────────────────────────────────
        $dbStats = Cache::remember('seo_audit_db_stats', 300, function () {
            return $this->getDbStats();
        });

        return view('back.seo.audit', compact('dbStats'));
    }

    // ─── آمار دیتابیس ─────────────────────────────────────────────
    private function getDbStats(): array
    {
        // ── محصولات ──
        $products = Product::select('id','title','slug','image','image_alt','meta_title','meta_description','description','status','publish_date','brand_id','category_id')->get();

        $productStats = [
            'total'               => $products->count(),
            'no_meta_title'       => $products->filter(fn($p) => empty($p->meta_title))->count(),
            'no_meta_desc'        => $products->filter(fn($p) => empty($p->meta_description))->count(),
            'no_image_alt'        => $products->filter(fn($p) => empty($p->image_alt))->count(),
            'no_description'      => $products->filter(fn($p) => empty($p->description))->count(),
            'no_image'            => $products->filter(fn($p) => empty($p->image))->count(),
            'short_meta_title'    => $products->filter(fn($p) => !empty($p->meta_title) && mb_strlen($p->meta_title) < 30)->count(),
            'long_meta_title'     => $products->filter(fn($p) => !empty($p->meta_title) && mb_strlen($p->meta_title) > 65)->count(),
            'short_meta_desc'     => $products->filter(fn($p) => !empty($p->meta_description) && mb_strlen($p->meta_description) < 100)->count(),
            'long_meta_desc'      => $products->filter(fn($p) => !empty($p->meta_description) && mb_strlen($p->meta_description) > 165)->count(),
            'no_slug'             => $products->filter(fn($p) => empty($p->slug))->count(),
            'long_slug'           => $products->filter(fn($p) => !empty($p->slug) && mb_strlen($p->slug) > 75)->count(),
            'no_category'         => $products->filter(fn($p) => empty($p->category_id))->count(),
            'no_brand'            => $products->filter(fn($p) => empty($p->brand_id))->count(),
            'no_publish_date'     => $products->filter(fn($p) => empty($p->publish_date))->count(),
            // لیست محصولات مشکل‌دار (تا ۱۵ مورد)
            'items_no_meta_title' => $products->filter(fn($p) => empty($p->meta_title))->take(15)->values(),
            'items_no_alt'        => $products->filter(fn($p) => empty($p->image_alt))->take(15)->values(),
            'items_no_desc'       => $products->filter(fn($p) => empty($p->description))->take(15)->values(),
        ];

        // ── مقالات ──
        $posts = Post::select('id','title','slug','image','meta_title','meta_description','summary','content','status','publish_date','category_id','admin_id')->get();

        $postStats = [
            'total'            => $posts->count(),
            'no_meta_title'    => $posts->filter(fn($p) => empty($p->meta_title))->count(),
            'no_meta_desc'     => $posts->filter(fn($p) => empty($p->meta_description))->count(),
            'no_image'         => $posts->filter(fn($p) => empty($p->image))->count(),
            'no_summary'       => $posts->filter(fn($p) => empty($p->summary))->count(),
            'no_content'       => $posts->filter(fn($p) => empty($p->content))->count(),
            'short_content'    => $posts->filter(fn($p) => empty($p->content) || ( !empty($p->content) && str_word_count(strip_tags($p->content)) < 300 ))->count(),
            'no_category'      => $posts->filter(fn($p) => empty($p->category_id))->count(),
            'no_publish_date'  => $posts->filter(fn($p) => empty($p->publish_date))->count(),
            'items_no_meta'    => $posts->filter(fn($p) => empty($p->meta_title))->take(15)->values(),
            'items_no_image'   => $posts->filter(fn($p) => empty($p->image))->take(15)->values(),
            'items_short' => $posts->filter(fn($p) => empty($p->content) || str_word_count(strip_tags($p->content)) < 300)->take(15)->values(),
        ];

        // ── دسته‌بندی‌ها ──
        $categories = Category::select('id','title','slug','image','category_id','meta_title','meta_description')->get();
        $categoryStats = [
            'total'         => $categories->count(),
            'no_meta_title' => $categories->filter(fn($c) => empty($c->meta_title))->count(),
            'no_meta_desc'  => $categories->filter(fn($c) => empty($c->meta_description))->count(),
            'no_image'      => $categories->filter(fn($c) => empty($c->image))->count(),
            'no_slug'       => $categories->filter(fn($c) => empty($c->slug))->count(),
            'items_no_meta' => $categories->filter(fn($c) => empty($c->meta_title))->take(15)->values(),
        ];

        // ── تصاویر بدون Alt (محصولات) ──
        $imagesNoAlt = Product::whereNotNull('image')->where(fn($q) => $q->whereNull('image_alt')->orWhere('image_alt', ''))->select('id','title','slug','image')->take(30)->get();

        // ── محتوای تکراری Meta Title ──
        $duplicateMetaTitles = DB::table('products')
            ->select('meta_title', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('meta_title')->where('meta_title', '!=', '')
            ->groupBy('meta_title')->having('cnt', '>', 1)
            ->orderByDesc('cnt')->limit(10)->get();

        $duplicatePostMetaTitles = DB::table('posts')
            ->select('meta_title', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('meta_title')->where('meta_title', '!=', '')
            ->groupBy('meta_title')->having('cnt', '>', 1)
            ->orderByDesc('cnt')->limit(10)->get();

        // ── امتیاز کلی ──
        $totalIssues = $productStats['no_meta_title'] + $productStats['no_meta_desc']
            + $productStats['no_image_alt'] + $productStats['no_description']
            + $postStats['no_meta_title'] + $postStats['no_meta_desc']
            + $postStats['no_content'] + $postStats['no_image']
            + $categoryStats['no_meta_title'] + $categoryStats['no_meta_desc'];

        $totalItems  = max(1, $productStats['total'] * 5 + $postStats['total'] * 4 + $categoryStats['total'] * 2);
        $overallScore = max(0, min(100, (int) round((1 - ($totalIssues / $totalItems)) * 100)));

        return compact(
            'productStats', 'postStats', 'categoryStats',
            'imagesNoAlt', 'duplicateMetaTitles', 'duplicatePostMetaTitles',
            'overallScore', 'totalIssues'
        );
    }

    // ─── کراول زنده URL ──────────────────────────────────────────
    public function crawl(Request $request)
    {
        $this->authorize('seo.audit');

        $request->validate(['url' => 'required|url']);
        $url = $request->input('url');

        try {
            $start    = microtime(true);
            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'SeoAuditBot/1.0'])
                ->get($url);
            $loadTime = round((microtime(true) - $start) * 1000); // ms

            $html       = $response->body();
            $statusCode = $response->status();

            $result = $this->analyzeHtml($html, $url, $statusCode, $loadTime);

            return response()->json(['success' => true, 'data' => $result]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'خطا در دسترسی به URL: ' . $e->getMessage()]);
        }
    }

    // ─── آنالیز HTML ──────────────────────────────────────────────
    private function analyzeHtml(string $html, string $url, int $status, int $loadTime): array
    {
        $issues   = [];
        $warnings = [];
        $good     = [];

        // ── متا‌ها ──
        preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $titleMatch);
        $title    = trim(strip_tags($titleMatch[1] ?? ''));
        $titleLen = mb_strlen($title);

        if (empty($title)) {
            $issues[] = ['field' => 'title', 'msg' => 'تگ <title> وجود ندارد یا خالی است.', 'fix' => 'Title تگ ضروری‌ترین فاکتور SEO است — حتماً اضافه کنید.'];
        } elseif ($titleLen < 30) {
            $warnings[] = ['field' => 'title', 'msg' => "Title خیلی کوتاه است ({$titleLen} کاراکتر).", 'fix' => 'بین ۵۰–۶۰ کاراکتر باشد.'];
        } elseif ($titleLen > 65) {
            $warnings[] = ['field' => 'title', 'msg' => "Title خیلی بلند است ({$titleLen} کاراکتر) — در گوگل قطع می‌شود.", 'fix' => 'به کمتر از ۶۰ کاراکتر کاهش دهید.'];
        } else {
            $good[] = "Title مناسب ({$titleLen} کاراکتر): {$title}";
        }

        preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)["\'][^>]*>/is', $html, $descMatch);
        if (empty($descMatch[1])) {
            preg_match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']description["\']/is', $html, $descMatch);
        }
        $metaDesc    = trim($descMatch[1] ?? '');
        $metaDescLen = mb_strlen($metaDesc);

        if (empty($metaDesc)) {
            $issues[] = ['field' => 'meta_description', 'msg' => 'Meta Description وجود ندارد.', 'fix' => '۱۴۰–۱۶۰ کاراکتر با کلیدواژه و CTA.'];
        } elseif ($metaDescLen < 100) {
            $warnings[] = ['field' => 'meta_description', 'msg' => "Meta Description کوتاه است ({$metaDescLen} کاراکتر).", 'fix' => 'به ۱۴۰–۱۶۰ کاراکتر برسانید.'];
        } elseif ($metaDescLen > 165) {
            $warnings[] = ['field' => 'meta_description', 'msg' => "Meta Description بلند است ({$metaDescLen} کاراکتر).", 'fix' => 'به کمتر از ۱۶۰ کاراکتر کاهش دهید.'];
        } else {
            $good[] = "Meta Description مناسب ({$metaDescLen} کاراکتر)";
        }

        // ── Canonical ──
        preg_match('/<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']+)["\']/is', $html, $canonicalMatch);
        $canonical = $canonicalMatch[1] ?? '';
        if (empty($canonical)) {
            $warnings[] = ['field' => 'canonical', 'msg' => 'تگ Canonical وجود ندارد.', 'fix' => '<link rel="canonical" href="..."> اضافه کنید تا محتوای تکراری مدیریت شود.'];
        } else {
            $good[] = "Canonical تنظیم شده: {$canonical}";
        }

        // ── Hreflang ──
        $hreflangCount = preg_match_all('/rel=["\']alternate["\'][^>]+hreflang/is', $html);
        if ($hreflangCount === 0) {
            $warnings[] = ['field' => 'hreflang', 'msg' => 'Hreflang تعریف نشده.', 'fix' => 'اگر سایت چندزبانه دارید، hreflang اضافه کنید.'];
        } else {
            $good[] = "{$hreflangCount} تگ Hreflang یافت شد";
        }

        // ── H1 ──
        preg_match_all('/<h1[^>]*>(.*?)<\/h1>/is', $html, $h1Matches);
        $h1Count = count($h1Matches[0]);
        $h1Text  = strip_tags($h1Matches[1][0] ?? '');
        if ($h1Count === 0) {
            $issues[] = ['field' => 'h1', 'msg' => 'H1 وجود ندارد.', 'fix' => 'هر صفحه باید دقیقاً یک H1 داشته باشد که کلیدواژه اصلی را در بر بگیرد.'];
        } elseif ($h1Count > 1) {
            $warnings[] = ['field' => 'h1', 'msg' => "{$h1Count} تگ H1 وجود دارد — باید فقط ۱ باشد.", 'fix' => 'تنها یک H1 نگه دارید.'];
        } else {
            $good[] = "H1 مناسب: {$h1Text}";
        }

        // ── تصاویر ──
        preg_match_all('/<img[^>]+>/is', $html, $imgMatches);
        $totalImages    = count($imgMatches[0]);
        $imagesNoAlt    = 0;
        $imagesNoWidth  = 0;
        $imagesNoHeight = 0;
        $largeImgSrcs   = [];

        foreach ($imgMatches[0] as $img) {
            if (!preg_match('/alt=["\'][^"\']+["\']/i', $img)) $imagesNoAlt++;
            if (!preg_match('/width=["\']?\d+["\']?/i', $img)) $imagesNoWidth++;
            if (!preg_match('/height=["\']?\d+["\']?/i', $img)) $imagesNoHeight++;
            // تصاویر بدون lazy loading
            if (!preg_match('/loading=["\']lazy["\']/i', $img) && preg_match('/src=["\']([^"\']+)["\']/i', $img, $sm)) {
                if (!str_contains($sm[1], 'data:')) {
                    $largeImgSrcs[] = $sm[1];
                }
            }
        }

        if ($imagesNoAlt > 0) {
            $issues[] = ['field' => 'img_alt', 'msg' => "{$imagesNoAlt} از {$totalImages} تصویر Alt ندارد.", 'fix' => 'برای همه تصاویر alt توصیفی اضافه کنید.'];
        } else {
            $good[] = "همه {$totalImages} تصویر Alt دارند";
        }

        if ($imagesNoWidth + $imagesNoHeight > 0) {
            $warnings[] = ['field' => 'img_dimensions', 'msg' => "{$imagesNoWidth} تصویر بدون width/height — باعث CLS می‌شود.", 'fix' => 'width و height را روی تگ img تعریف کنید تا از Layout Shift جلوگیری شود.'];
        }

        $lazyMissing = count($largeImgSrcs);
        if ($lazyMissing > 3) {
            $warnings[] = ['field' => 'lazy_load', 'msg' => "{$lazyMissing} تصویر بدون loading=\"lazy\".", 'fix' => 'برای تصاویر زیر fold، loading="lazy" اضافه کنید.'];
        } else {
            $good[] = 'اکثر تصاویر Lazy Load دارند';
        }

        // ── لینک‌ها ──
        preg_match_all('/<a[^>]+href=["\']([^"\'#]+)["\'][^>]*>(.*?)<\/a>/is', $html, $linkMatches);
        $appUrl         = rtrim(config('app.url'), '/');
        $internalLinks  = [];
        $externalLinks  = [];
        $noTextLinks    = 0;
        $nofollowExt    = 0;

        foreach ($linkMatches[1] as $i => $href) {
            $text = trim(strip_tags($linkMatches[2][$i]));
            if (empty($text)) $noTextLinks++;
            if (str_starts_with($href, '/') || str_contains($href, $appUrl)) {
                $internalLinks[] = $href;
            } elseif (str_starts_with($href, 'http')) {
                $externalLinks[] = $href;
                if (preg_match('/rel=["\'][^"\']*nofollow/i', $linkMatches[0][$i])) $nofollowExt++;
            }
        }

        if ($noTextLinks > 0) {
            $warnings[] = ['field' => 'link_text', 'msg' => "{$noTextLinks} لینک بدون متن anchor.", 'fix' => 'هر لینک باید متن توصیفی داشته باشد (نه «کلیک کنید»).'];
        }

        // ── Open Graph ──
        $ogTags = [];
        preg_match_all('/<meta[^>]+property=["\']og:([^"\']+)["\'][^>]+content=["\']([^"\']*)["\'][^>]*>/is', $html, $ogMatches);
        foreach ($ogMatches[1] as $i => $prop) $ogTags[$prop] = $ogMatches[2][$i];

        $requiredOg = ['title', 'description', 'image', 'url', 'type'];
        $missingOg  = array_filter($requiredOg, fn($k) => empty($ogTags[$k]));
        if (count($missingOg) > 0) {
            $warnings[] = ['field' => 'open_graph', 'msg' => 'Open Graph ناقص است: og:' . implode(', og:', $missingOg) . ' ندارد.', 'fix' => 'تگ‌های OG برای اشتراک‌گذاری در شبکه‌های اجتماعی ضروری‌اند.'];
        } else {
            $good[] = 'Open Graph کامل است';
        }

        // ── Twitter Card ──
        preg_match('/<meta[^>]+name=["\']twitter:card["\'][^>]+content=["\']([^"\']*)["\'][^>]*>/is', $html, $tcMatch);
        if (empty($tcMatch[1])) {
            $warnings[] = ['field' => 'twitter_card', 'msg' => 'Twitter Card تعریف نشده.', 'fix' => '<meta name="twitter:card" content="summary_large_image"> اضافه کنید.'];
        } else {
            $good[] = "Twitter Card: {$tcMatch[1]}";
        }

        // ── Schema ──
        $schemaCount = preg_match_all('/application\/ld\+json/i', $html);
        if ($schemaCount === 0) {
            $warnings[] = ['field' => 'schema', 'msg' => 'هیچ Schema (JSON-LD) در صفحه وجود ندارد.', 'fix' => 'Schema markup برای Rich Results در گوگل ضروری است.'];
        } else {
            $good[] = "{$schemaCount} بلوک Schema JSON-LD یافت شد";
        }

        // ── Viewport ──
        if (!preg_match('/name=["\']viewport["\']/i', $html)) {
            $issues[] = ['field' => 'viewport', 'msg' => 'Meta Viewport وجود ندارد — سایت موبایل‌فرندلی نیست.', 'fix' => '<meta name="viewport" content="width=device-width, initial-scale=1"> اضافه کنید.'];
        } else {
            $good[] = 'Meta Viewport تنظیم شده';
        }

        // ── Robots ──
        preg_match('/<meta[^>]+name=["\']robots["\'][^>]+content=["\']([^"\']*)["\'][^>]*>/is', $html, $robotsMatch);
        $robotsContent = strtolower($robotsMatch[1] ?? '');
        if (str_contains($robotsContent, 'noindex')) {
            $issues[] = ['field' => 'robots', 'msg' => "⚠️ صفحه noindex دارد: {$robotsContent}", 'fix' => 'اگر می‌خواهید صفحه ایندکس شود، noindex را حذف کنید.'];
        } elseif (!empty($robotsContent)) {
            $good[] = "Meta Robots: {$robotsContent}";
        }

        // ── Charset ──
        if (!preg_match('/charset/i', $html)) {
            $warnings[] = ['field' => 'charset', 'msg' => 'Charset تعریف نشده.', 'fix' => '<meta charset="UTF-8"> باید اولین تگ داخل <head> باشد.'];
        } else {
            $good[] = 'Charset تعریف شده';
        }

        // ── سرعت بارگذاری ──
        if ($loadTime > 3000) {
            $issues[] = ['field' => 'load_time', 'msg' => "زمان پاسخ سرور {$loadTime}ms است — خیلی کند.", 'fix' => 'هدف زیر ۲۰۰ms باشد. کشینگ، CDN، و بهینه‌سازی دیتابیس را بررسی کنید.'];
        } elseif ($loadTime > 1000) {
            $warnings[] = ['field' => 'load_time', 'msg' => "زمان پاسخ سرور {$loadTime}ms است.", 'fix' => 'زیر ۲۰۰ms ایده‌آل است.'];
        } else {
            $good[] = "سرعت پاسخ سرور عالی: {$loadTime}ms";
        }

        // ── HTML Size ──
        $htmlSizeKb = round(strlen($html) / 1024, 1);
        if ($htmlSizeKb > 100) {
            $warnings[] = ['field' => 'html_size', 'msg' => "حجم HTML: {$htmlSizeKb}KB — زیاد است.", 'fix' => 'حجم HTML باید زیر ۵۰KB باشد. کامنت‌ها و کدهای اضافه را حذف کنید.'];
        } else {
            $good[] = "حجم HTML: {$htmlSizeKb}KB";
        }

        // ── Status Code ──
        if ($status >= 400) {
            $issues[] = ['field' => 'status_code', 'msg' => "HTTP Status: {$status}", 'fix' => 'صفحه دسترسی‌پذیر نیست یا ریدایرکت مشکل دارد.'];
        } elseif ($status >= 300) {
            $warnings[] = ['field' => 'status_code', 'msg' => "Redirect: {$status}", 'fix' => 'Redirect باید ۳۰۱ Permanent باشد نه ۳۰۲.'];
        } else {
            $good[] = "HTTP Status: {$status} OK";
        }

        // ── امتیاز ──
        $total    = count($issues) * 2 + count($warnings);
        $maxScore = 20;
        $score    = max(0, min(100, (int) round((1 - $total / $maxScore) * 100)));

        return [
            'url'            => $url,
            'status'         => $status,
            'load_time'      => $loadTime,
            'html_size_kb'   => $htmlSizeKb,
            'score'          => $score,
            'issues'         => $issues,
            'warnings'       => $warnings,
            'good'           => $good,
            'title'          => $title,
            'title_len'      => $titleLen,
            'meta_desc'      => $metaDesc,
            'meta_desc_len'  => $metaDescLen,
            'canonical'      => $canonical,
            'h1_count'       => $h1Count,
            'h1_text'        => $h1Text,
            'total_images'   => $totalImages,
            'images_no_alt'  => $imagesNoAlt,
            'internal_links' => count($internalLinks),
            'external_links' => count($externalLinks),
            'og_tags'        => $ogTags,
            'schema_count'   => $schemaCount,
        ];
    }

    // ─── بررسی Robots.txt ────────────────────────────────────────
    public function checkRobots()
    {
        $this->authorize('seo.audit');

        $url = rtrim(config('app.url'), '/') . '/robots.txt';
        try {
            $response = Http::timeout(10)->get($url);
            $content  = $response->body();
            $status   = $response->status();

            $issues   = [];
            $warnings = [];
            $good     = [];

            if ($status !== 200) {
                $issues[] = "robots.txt در دسترس نیست (HTTP {$status})";
            } else {
                $good[] = 'robots.txt موجود است';
            }

            if (!str_contains($content, 'Sitemap:')) {
                $warnings[] = 'آدرس Sitemap در robots.txt تعریف نشده';
            } else {
                $good[] = 'آدرس Sitemap در robots.txt وجود دارد';
            }

            if (str_contains($content, 'Disallow: /')) {
                $warnings[] = 'Disallow: / تمام سایت را از ایندکس خارج می‌کند — بررسی کنید';
            }

            if (str_contains($content, 'User-agent: *')) {
                $good[] = 'User-agent: * تعریف شده';
            } else {
                $warnings[] = 'User-agent: * تعریف نشده';
            }

            return response()->json([
                'success'  => true,
                'content'  => $content,
                'status'   => $status,
                'issues'   => $issues,
                'warnings' => $warnings,
                'good'     => $good,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── بررسی Sitemap ───────────────────────────────────────────
    public function checkSitemap()
    {
        $this->authorize('seo.audit');

        $url = rtrim(config('app.url'), '/') . '/sitemap.xml';
        try {
            $response = Http::timeout(15)->get($url);
            $content  = $response->body();
            $status   = $response->status();

            $urlCount = substr_count($content, '<loc>');
            $good     = [];
            $warnings = [];
            $issues   = [];

            if ($status !== 200) {
                $issues[] = "sitemap.xml در دسترس نیست (HTTP {$status})";
            } else {
                $good[] = "sitemap.xml موجود است ({$urlCount} URL)";
            }

            if ($urlCount > 50000) {
                $warnings[] = "تعداد URL‌ها ({$urlCount}) از ۵۰,۰۰۰ بیشتر است — Sitemap را تقسیم کنید";
            }

            if (!str_contains($content, '<lastmod>')) {
                $warnings[] = 'lastmod در Sitemap وجود ندارد';
            } else {
                $good[] = 'lastmod در Sitemap موجود است';
            }

            if (!str_contains($content, '<priority>')) {
                $warnings[] = 'priority در Sitemap وجود ندارد';
            }

            return response()->json([
                'success'   => true,
                'url_count' => $urlCount,
                'status'    => $status,
                'issues'    => $issues,
                'warnings'  => $warnings,
                'good'      => $good,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── بررسی لینک‌های شکسته ───────────────────────────────────
    public function checkBrokenLinks(Request $request)
    {
        $this->authorize('seo.audit');

        $urls   = $request->input('urls', []);
        $appUrl = rtrim(config('app.url'), '/');
        $broken = [];
        $ok     = 0;

        foreach (array_slice($urls, 0, 30) as $url) {
            if (!str_starts_with($url, 'http')) $url = $appUrl . $url;
            try {
                $status = Http::timeout(8)->head($url)->status();
                if ($status >= 400) {
                    $broken[] = ['url' => $url, 'status' => $status];
                } else {
                    $ok++;
                }
            } catch (\Exception $e) {
                $broken[] = ['url' => $url, 'status' => 'timeout'];
            }
        }

        return response()->json(['success' => true, 'broken' => $broken, 'ok' => $ok]);
    }
}
