<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Post;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Str;

class ActivityLogController extends Controller
{

    public function index(Request $request)
    {
        $this->authorize('activity-log.index');
        $query = Activity::with(['causer', 'subject'])
            ->latest();

        // فیلتر بر اساس نوع موضوع (مقالات، محصولات و...)
        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        // فیلتر بر اساس نوع رویداد
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // فیلتر بر اساس کاربر
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        // فیلتر بر اساس بازه زمانی
        if ($request->filled('from_date')) {
            $dateFrom = convertPersianToEnglish($request->from_date);
            $dateFrom = Jalalian::fromFormat('Y-m-d', $dateFrom)->toCarbon()->startOfDay();
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($request->filled('to_date')) {
            $dateTo = convertPersianToEnglish($request->to_date);
            $dateTo = Jalalian::fromFormat('Y-m-d', $dateTo)->toCarbon()->endOfDay();
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // جستجو در توضیحات
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                    ->orWhere('log_name', 'like', '%' . $request->search . '%');
            });
        }

        // دریافت آمارها بر اساس فیلترهای اعمال شده
        $statsQuery = clone $query;

        // آمار پایه
        $stats = [
            'total' => $statsQuery->count(),
            'created' => (clone $statsQuery)->where('event', 'created')->count(),
            'updated' => (clone $statsQuery)->where('event', 'updated')->count(),
            'deleted' => (clone $statsQuery)->where('event', 'deleted')->count(),
        ];

        // آمار امروز
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $stats['today'] = [
            'total' => (clone $statsQuery)->whereBetween('created_at', [$todayStart, $todayEnd])->count(),
            'created' => (clone $statsQuery)->whereBetween('created_at', [$todayStart, $todayEnd])->where('event', 'created')->count(),
            'updated' => (clone $statsQuery)->whereBetween('created_at', [$todayStart, $todayEnd])->where('event', 'updated')->count(),
            'deleted' => (clone $statsQuery)->whereBetween('created_at', [$todayStart, $todayEnd])->where('event', 'deleted')->count(),
        ];

        // آمار این هفته
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $stats['this_week'] = [
            'total' => (clone $statsQuery)->whereBetween('created_at', [$weekStart, $weekEnd])->count(),
            'created' => (clone $statsQuery)->whereBetween('created_at', [$weekStart, $weekEnd])->where('event', 'created')->count(),
            'updated' => (clone $statsQuery)->whereBetween('created_at', [$weekStart, $weekEnd])->where('event', 'updated')->count(),
            'deleted' => (clone $statsQuery)->whereBetween('created_at', [$weekStart, $weekEnd])->where('event', 'deleted')->count(),
        ];

        // آمار این ماه
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $stats['this_month'] = [
            'total' => (clone $statsQuery)->whereBetween('created_at', [$monthStart, $monthEnd])->count(),
            'created' => (clone $statsQuery)->whereBetween('created_at', [$monthStart, $monthEnd])->where('event', 'created')->count(),
            'updated' => (clone $statsQuery)->whereBetween('created_at', [$monthStart, $monthEnd])->where('event', 'updated')->count(),
            'deleted' => (clone $statsQuery)->whereBetween('created_at', [$monthStart, $monthEnd])->where('event', 'deleted')->count(),
        ];

        // آمار بیشترین فعالیت کاربران
        $filteredForTop = clone $statsQuery;
        $filteredForTop->getQuery()->orders = [];

        $topUsers = $filteredForTop
            ->select('causer_id', 'causer_type', DB::raw('count(*) as total'))
            ->whereNotNull('causer_id')
            ->groupBy('causer_id', 'causer_type')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $causer = null;
                if ($item->causer_type === 'App\Models\Admin') {
                    $causer = Admin::find($item->causer_id);
                } elseif ($item->causer_type === 'App\Models\User') {
                    $causer = User::find($item->causer_id);
                } elseif ($item->causer_type === 'App\Models\Seller') {
                    $causer = Seller::find($item->causer_id);
                }

                return [
                    'name' => $causer->full_name ?? $causer->name ?? 'ناشناس',
                    'type' => class_basename($item->causer_type),
                    'total' => $item->total,
                ];
            });

        $stats['top_users'] = $topUsers;

        // آمار بر اساس روزهای هفته
        $filteredForDaily = clone $statsQuery;
        $filteredForDaily->getQuery()->orders = [];

        $dailyStats = $filteredForDaily
            ->select(DB::raw('DAYNAME(created_at) as day'), DB::raw('count(*) as total'))
            ->groupBy('day')
            ->orderBy(DB::raw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')"))
            ->get()
            ->map(function ($item) {
                $dayNames = [
                    'Monday' => 'دوشنبه',
                    'Tuesday' => 'سه‌شنبه',
                    'Wednesday' => 'چهارشنبه',
                    'Thursday' => 'پنج‌شنبه',
                    'Friday' => 'جمعه',
                    'Saturday' => 'شنبه',
                    'Sunday' => 'یک‌شنبه',
                ];
                return [
                    'day' => $dayNames[$item->day] ?? $item->day,
                    'total' => $item->total,
                ];
            });

        $stats['daily'] = $dailyStats;

        $activities = $query->paginate(20)->withQueryString();

        // فرمت کردن لاگ‌ها برای نمایش در جدول
        $formattedActivities = $activities->map(function ($activity) {
            $report = $this->formatActivityReportWithLinks($activity);
            $description = $activity->description;

            $extraDescription = null;

            if ($activity->subject_type === 'App\Models\Comment' && $description && !in_array($description, ['created', 'updated', 'deleted', 'restored'])) {
                $extraDescription = $description;
            }

            return (object)[
                'id' => $activity->id,
                'report' => $report,
                'date' => $this->formatActivityDate($activity->created_at),
                'raw_activity' => $activity,
                'extra_description' => $description
            ];
        });

        // دریافت لیست منحصر به فرد subject_type ها
        $subjectTypes = Activity::select('subject_type')
            ->distinct()
            ->whereNotNull('subject_type')
            ->pluck('subject_type')
            ->map(function ($item) {
                return [
                    'value' => $item,
                    'label' => $this->getModelLabel($item)
                ];
            });

        // دریافت لیست رویدادها
        $events = Activity::select('event')
            ->distinct()
            ->whereNotNull('event')
            ->pluck('event');

        // دریافت لیست کاربران فعال
        $causers = Activity::with('causer')
            ->select('causer_id', 'causer_type')
            ->distinct()
            ->whereNotNull('causer_id')
            ->get()
            ->map(function ($item) {
                $type = '';
                $typeLabel = '';

                switch ($item->causer_type) {
                    case 'App\Models\Admin':
                        $type = 'admin';
                        $typeLabel = 'مدیر';
                        break;
                    case 'App\Models\User':
                        $type = 'user';
                        $typeLabel = 'کاربر';
                        break;
                    case 'App\Models\Seller':
                        $type = 'seller';
                        $typeLabel = 'فروشنده';
                        break;
                    default:
                        $type = 'other';
                        $typeLabel = class_basename($item->causer_type);
                }

                return [
                    'id' => $item->causer_id,
                    'name' => $item->causer ? $item->causer->full_name : 'ناشناس',
                    'type' => $type,
                    'type_label' => $typeLabel,
                    'full_display' => $item->causer ? "{$typeLabel} - {$item->causer->full_name}" : 'ناشناس'
                ];
            })
            ->unique('id')
            ->sortBy(function ($item) {
                return $item['full_display'];
            })
            ->values();

        return view('back.activity-log.index', compact(
            'formattedActivities',
            'activities',
            'subjectTypes',
            'events',
            'causers',
            'stats'
        ));
    }

    /**
     * فرمت کردن گزارش فعالیت با لینک
     */
    private function formatActivityReportWithLinks($activity)
    {
        $actorLink = $this->getActorLink($activity);
        $subjectLink = $this->getSubjectLink($activity);

        // استفاده از متد کمکی برای دریافت متن گزارش بر اساس action
        return $this->getActionReportText($activity, $actorLink, $subjectLink);
    }
    /**
     * دریافت لینک انجام‌دهنده
     */
    private function getActorLink($activity)
    {
        if (!$activity->causer) {
            return 'سیستم';
        }

        $causer = $activity->causer;
        $role = '';
        $route = '';

        switch ($activity->causer_type) {
            case 'App\Models\Admin':
                $role = 'مدیریت';
                $route = route('admin.admins.show', $causer->id);
                break;
            case 'App\Models\User':
                $role = 'کاربر';
                $route = route('admin.users.show', $causer->id);
                break;
            case 'App\Models\Seller':
                $role = 'فروشنده';
                $route = route('admin.sellers.show', $causer->id);
                break;
            default:
                $role = class_basename($activity->causer_type);
                $route = '#';
        }

        $full_name = $causer->full_name ?? $causer->username ?? $causer->name ?? 'کاربر';

        return $role . ' <a href="' . $route . '" class="actor-link" data-user-id="' . $causer->id . '" data-user-type="' . $activity->causer_type . '">'
            . e($full_name) . '</a>';
    }

    /**
     * دریافت متن دقیق عملیات انجام شده
     */

    /**
     * دریافت متن دقیق عملیات انجام شده (فقط برای لاگ‌های خودکار)
     */
    private function getDetailedActionText($activity)
    {

        // بررسی برای کامنت
        if ($activity->subject_type === 'App\Models\Comment') {
            $properties = $activity->properties;
            if ($properties && isset($properties['action'])) {
                if ($properties['action'] === 'add_comment') {
                    return 'ثبت کرد';
                }
                if ($properties['action'] === 'reply_to_comment') {
                    return 'پاسخ داد';
                }
                if ($properties['action'] === 'like_comment') {
                    return 'لایک کرد';
                }
                if ($properties['action'] === 'dislike_comment') {
                    return 'دیسلایک کرد';
                }
                if ($properties['action'] === 'delete_comment') {
                    return 'حذف کرد';
                }
                if ($properties['action'] === 'update_gateways') {
                    return 'ویرایش کرد';
                }

            }
            if ($activity->event === 'created') {
                return 'ثبت کرد';
            }
            if ($activity->event === 'deleted') {
                return 'حذف کرد';
            }
            if ($activity->event === 'updated') {
                return 'ویرایش کرد';
            }
        }

        // عملیات استاندارد
        $actionMap = [
            'created'   => 'ایجاد کرد',
            'updated'   => 'ویرایش کرد',
            'deleted'   => 'حذف کرد',
            'restored'  => 'بازیابی کرد',
        ];

        if (isset($actionMap[$activity->event])) {
            return $actionMap[$activity->event];
        }

        $properties = $activity->properties;

        if ($properties && isset($properties['action'])) {
            return $properties['action'];
        }

        return $activity->event ?? 'عملیاتی انجام داد';
    }

    /**
     * دریافت لینک موضوع (مقاله، محصول و ...)
     */
    private function getSubjectLink($activity)
    {
        if (!$activity->subject) {
            return $this->getModelLabel($activity->subject_type);
        }

        $subject = $activity->subject;
        $subjectType = $activity->subject_type;

        // بررسی ویژه برای کامنت‌ها
        if ($subjectType === 'App\Models\Comment') {
            $commentable = $subject->commentable;
            if ($commentable) {
                if ($commentable instanceof \App\Models\Post) {
                    $route = route('admin.comments.posts');
                    $displayName = "نظر در مقاله «{$commentable->title}»";
                } elseif ($commentable instanceof \App\Models\Product) {
                    $route = route('front.products.show', $commentable->slug);
                    $productName = $commentable->name ?? $commentable->title ?? 'محصول';
                    $displayName = "نظر در محصول «{$productName}»";
                } else {
                    $route = '#';
                    $displayName = "نظر #{$subject->body}";
                }
            } else {
                $route = '#';
                $displayName = "نظر #{$subject->body}";
            }

            return '<a href="' . $route . '" target="_blank" class="subject-link" data-subject-id="' . $subject->id . '" data-subject-type="' . $subjectType . '">'
                . e($displayName) . '</a>';
        }

        // لینک پیشفرض (به صفحه ویرایش در ادمین) برای سایر مدل‌ها
        $route = $this->getDefaultEditRoute($subject, $subjectType);
        $displayName = $this->getSubjectDisplayName($activity, $subject);

        return '<a href="' . $route . '" target="_blank" class="subject-link" data-subject-id="' . $subject->id . '" data-subject-type="' . $subjectType . '">'
            . e($displayName) . '</a>';
    }

    /**
     * دریافت نام نمایشی موضوع
     */
    private function getSubjectDisplayName($activity, $subject): string
    {
        $subjectType = $activity->subject_type;

        $displayExceptions = [
            'App\Models\Post' => function($subject) {
                return "مقاله «{$subject->title}»";
            },
            'App\Models\Product' => function($subject) {
                $productName = $subject->name ?? $subject->title ?? 'محصول';
                $details = [];

                $detailsText = !empty($details) ? ' ' . implode(' - ', $details) : '';
                return "محصول {$productName}{$detailsText}";
            },
            'App\Models\User' => function($subject) {
                return "کاربر «{$subject->name}»";
            },
            'App\Models\Category' => function($subject) {
                return "دسته‌بندی «{$subject->name}»";
            },
            'App\Models\Order' => function($subject) {
                $total = isset($subject->total) ? number_format($subject->total) . ' تومان' : '';
                return "سفارش #{$subject->id}" . ($total ? " به مبلغ {$total}" : '');
            },
            'App\Models\Comment' => function($subject) {
                $commentText = mb_substr($subject->body ?? $subject->comment ?? '', 0, 50);
                return "«{$commentText}»";
            },
        ];

        if (isset($displayExceptions[$subjectType])) {
            return $displayExceptions[$subjectType]($subject);
        }

        $nameField = $subject->name ?? $subject->title ?? null;
        $modelLabel = $this->getModelLabel($subjectType);

        if ($nameField) {
            return "{$modelLabel} «{$nameField}»";
        }

        return "{$modelLabel} #{$subject->id}";
    }

    /**
     * دریافت لینک پیشفرض ویرایش برای هر مدل
     */
    private function getDefaultEditRoute($subject, string $subjectType): string
    {
        $exceptions = [
            'App\Models\Post' => [
                'route' => 'front.articles.show',
                'params' => ['slug' => $subject->slug ?? $subject->id]
            ],
            'App\Models\Product' => [
                'route' => 'front.products.show',
                'params' => ['slug' => $subject->slug ?? $subject->id]
            ],
            'App\Models\User' => [
                'route' => 'admin.users.edit',
                'params' => ['user' => $subject->id]
            ],
            'App\Models\Category' => [
                'route' => 'admin.categories.edit',
                'params' => ['category' => $subject->id]
            ],
            'App\Models\Order' => [
                'route' => 'admin.orders.show',
                'params' => ['order' => $subject->id]
            ],
        ];

        if (isset($exceptions[$subjectType])) {
            try {
                return route($exceptions[$subjectType]['route'], $exceptions[$subjectType]['params']);
            } catch (\Exception $e) {
                return '#';
            }
        }

        $routeName = $this->getRouteNameForModel($subject);

        if (\Route::has($routeName)) {
            try {
                $paramName = $this->getRouteParameterName($subject);
                return route($routeName, [$paramName => $subject->id]);
            } catch (\Exception $e) {
                return '#';
            }
        }

        return '#';
    }

    private function getRouteParameterName($subject): string
    {
        $baseName = class_basename($subject);
        $baseNameLower = strtolower($baseName);

        $paramMap = [
            'story' => 'story',
            'category' => 'category',
            'activity' => 'activity',
            'product' => 'product',
            'post' => 'post',
            'user' => 'user',
            'order' => 'order',
            'comment' => 'comment',
        ];

        return $paramMap[$baseNameLower] ?? $baseNameLower;
    }

    private function getRouteNameForModel($subject): string
    {
        $baseName = class_basename($subject);
        $baseNameLower = strtolower($baseName);

        $pluralMap = [
            'story' => 'stories',
            'category' => 'categories',
            'activity' => 'activities',
            'entity' => 'entities',
            'country' => 'countries',
            'city' => 'cities',
            'file' => 'files',
            'photo' => 'photos',
            'video' => 'videos',
            'news' => 'news',
            'information' => 'information',
        ];

        if (isset($pluralMap[$baseNameLower])) {
            $pluralName = $pluralMap[$baseNameLower];
        } else {
            $pluralName = $baseNameLower . 's';
        }

        $possibleRoutes = [
            "admin.{$pluralName}.edit",
            "admin.{$pluralName}.show",
            "admin.{$baseNameLower}.edit",
            "admin.{$baseNameLower}.show",
        ];

        foreach ($possibleRoutes as $routeName) {
            if (\Route::has($routeName)) {
                return $routeName;
            }
        }

        return "admin.{$pluralName}.edit";
    }

    /**
     * فرمت کردن تاریخ
     */
    private function formatActivityDate($date)
    {
        if (function_exists('jdate')) {
            return jdate($date)->format('H:i - Y/m/d');
        }
        return $date->format('H:i - Y/m/d');
    }

    /**
     * دریافت جزئیات فعالیت برای مودال
     */
    public function show($id)
    {
        $activity = Activity::with(['causer', 'subject'])->findOrFail($id);

        $eventConfig = [
            'created' => ['class' => 'event-created', 'icon' => 'bi-plus-circle-fill', 'text' => 'ایجاد'],
            'updated' => ['class' => 'event-updated', 'icon' => 'bi-pencil-fill', 'text' => 'ویرایش'],
            'deleted' => ['class' => 'event-deleted', 'icon' => 'bi-trash-fill', 'text' => 'حذف'],
            'restored' => ['class' => 'event-restored', 'icon' => 'bi-arrow-counterclockwise', 'text' => 'بازیابی'],
        ];

        $event = $eventConfig[$activity->event] ?? ['class' => 'event-default', 'icon' => 'bi-activity', 'text' => $activity->event];

        $actorHtml = $this->getActorLink($activity);
        $subjectHtml = $this->getSubjectLink($activity);

        $causerLink = null;
        $causerName = $activity->causer?->full_name ?? 'سیستم';
        $causerType = $this->getModelLabel($activity->causer_type);

        if ($activity->causer) {
            if ($activity->causer_type === 'App\Models\Admin') {
                $causerLink = route('admin.admins.show', $activity->causer_id);
            } elseif ($activity->causer_type === 'App\Models\User') {
                $causerLink = route('admin.users.show', $activity->causer_id);
            } elseif ($activity->causer_type === 'App\Models\Seller') {
                $causerLink = route('admin.sellers.show', $activity->causer_id);
            }
        }

        $subjectLink = null;
        $subjectName = null;
        if ($activity->subject) {
            if ($activity->subject_type === 'App\Models\Post') {
                $slug = Post::find($activity->subject_id)->slug ?? null;
                if ($slug) {
                    $subjectLink = route('front.articles.show', $slug);
                }
                $subjectName = $activity->subject->title ?? 'مقاله';
            } elseif ($activity->subject_type === 'App\Models\Product') {
                $subjectLink = route('front.products.show', $activity->subject_id);
                $subjectName = $activity->subject->name ?? $activity->subject->title ?? 'محصول';
            } elseif ($activity->subject_type === 'App\Models\User') {
                $subjectLink = route('admin.users.show', $activity->subject_id);
                $subjectName = $activity->subject->name ?? 'کاربر';
            } elseif ($activity->subject_type === 'App\Models\Category') {
                $subjectLink = route('admin.products.categories.index', $activity->subject_id);
                $subjectName = $activity->subject->name ?? 'دسته‌بندی';
            } elseif ($activity->subject_type === 'App\Models\Order') {
                $subjectLink = route('admin.orders.show', $activity->subject_id);
                $subjectName = 'سفارش #' . $activity->subject_id;
            }
        }

        $data = [
            'id' => $activity->id,
            'event' => $event['text'],
            'event_class' => $event['class'],
            'event_icon' => $event['icon'],
            'subject_type' => $this->getModelLabel($activity->subject_type),
            'subject_id' => $activity->subject_id,
            'subject_name' => $subjectName,
            'subject_link' => $subjectLink,
            'subject_html' => $subjectHtml,
            'causer_name' => $causerName,
            'causer_type' => $causerType,
            'causer_link' => $causerLink,
            'causer_html' => $actorHtml,
            'description' => $activity->description,
            'created_at' => jdate($activity->created_at)->format('Y/m/d - H:i'),
            'created_at_diff' => jdate($activity->created_at)->ago(),
            'properties' => $this->formatProperties($activity->properties),
            'translateFieldName' => function($key) {
                return $this->translateFieldName($key);
            }

        ];

        $html = view('back.activity-log.partials.modal-details', [
            'activity' => $data,
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * فرمت کردن properties
     */
    private function formatProperties($properties)
    {
        if (empty($properties)) {
            return null;
        }

        $formatted = [
            'attributes' => [],
            'old' => []
        ];

        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $formatted['attributes'][$this->translateFieldName($key)] = $this->formatValue($value, $key);
            }
        }

        if (isset($properties['old'])) {
            foreach ($properties['old'] as $key => $value) {
                $formatted['old'][$this->translateFieldName($key)] = $this->formatValue($value, $key);
            }
        }

        return $formatted;
    }

    /**
     * فرمت کردن مقادیر برای نمایش
     */
    private function formatValue($value, $fieldName = null)
    {
        if (is_null($value)) {
            return '—';
        }

        $booleanFields = [
            'published', 'is_editor_pick', 'allow_comments', 'is_active',
            'is_verified', 'is_featured', 'is_popular'
        ];

        if (in_array($fieldName, $booleanFields)) {
            if ($value === 0 || $value === '0' || $value === false) {
                return 'خیر';
            }
            if ($value === 1 || $value === '1' || $value === true) {
                return 'بله';
            }
        }

        if ($value === 0 || $value === '0') {
            return 'خیر';
        }
        if ($value === 1 || $value === '1') {
            return 'بله';
        }
        if (is_bool($value)) {
            return $value ? 'بله' : 'خیر';
        }

        $dateFields = [
            'publish_date', 'published_at', 'created_at', 'updated_at',
            'deleted_at', 'expires_at', 'date', 'start_date', 'expiry_date', 'end_date'
        ];

        if (in_array($fieldName, $dateFields) && !empty($value)) {
            try {
                if (is_numeric($value)) {
                    return jdate($value)->format('Y/m/d - H:i');
                }
                $carbon = \Carbon\Carbon::parse($value);
                return jdate($carbon)->format('Y/m/d - H:i');
            } catch (\Exception $e) {
                return $value;
            }
        }

        $imageFields = [
            'image', 'images', 'avatar', 'picture', 'photo', 'thumbnail', 'banner', 'logo'
        ];

        if (in_array($fieldName, $imageFields) && !empty($value)) {
            if (is_array($value)) {
                $firstImage = $value[0] ?? null;
                if ($firstImage) {
                    return '<img src="' . e(asset($firstImage)) . '" class="activity-thumbnail" style="max-width: 50px; max-height: 50px; border-radius: 8px; cursor: pointer;" onclick="window.open(this.src)">';
                }
            }
            if (is_string($value) && !empty($value)) {
                return '<img src="' . e(asset($value)) . '" class="activity-thumbnail" style="max-width: 50px; max-height: 50px; border-radius: 8px; cursor: pointer;" onclick="window.open(this.src)">';
            }
        }

        if ($value === '' || $value === null) {
            return '—';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return $value;
    }

    /**
     * ترجمه نام فیلدها به فارسی
     */
    private function translateFieldName($key)
    {
        $fieldTitles = [
            'title' => 'عنوان',
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'post_type' => 'نوع پست',
            'ticket_id' => 'شناسه تیکت',
            'message' => 'پیغام',
            'category_id' => 'دسته‌بندی',
            'slug' => 'نامک',
            'image' => 'تصویر',
            'images' => 'تصاویر',
            'admin_id' => 'نویسنده',
            'published' => 'وضعیت انتشار',
            'view' => 'تعداد بازدید',
            'summary' => 'خلاصه',
            'content' => 'محتوا',
            'video_url' => 'لینک ویدئو',
            'podcast_url' => 'لینک پادکست',
            'created_by' => 'ایجاد شده توسط',
            'status' => 'وضعیت',
            'more' => 'اطلاعات بیشتر',
            'source' => 'منبع',
            'is_editor_pick' => 'انتخاب سردبیر',
            'allow_comments' => 'مجوز نظرات',
            'publish_date' => 'تاریخ انتشار',
            'published_at' => 'تاریخ انتشار',
            'expiry_date' => 'تاریخ انقضاء',
            'name' => 'نام',
            'email' => 'ایمیل',
            'password' => 'رمز عبور',
            'phone' => 'تلفن',
            'address' => 'آدرس',
            'price' => 'قیمت',
            'stock' => 'موجودی',
            'brand' => 'برند',
            'model' => 'مدل',
            'ram' => 'رم',
            'storage' => 'حافظه',
            'color' => 'رنگ',
            'description' => 'توضیحات',
            'user_id' => 'کاربر',
            'parent_id' => 'والد',
            'order' => 'ترتیب',
            'icon' => 'آیکون',
            'meta_title' => 'عنوان سئو',
            'meta_description' => 'توضیحات سئو',
            'meta_keywords' => 'کلمات کلیدی',
            'expires_at' => 'تاریخ انقضا',
            'code' => 'کد',
            'type' => 'نوع',
            'role' => 'نقش',
            'permissions' => 'دسترسی‌ها',
            'is_active' => 'فعال',
            'is_verified' => 'تایید شده',
            'is_featured' => 'ویژه',
            'is_popular' => 'محبوب',
            'lang' => 'زبان',
            'Seller' => 'فروشنده',
            'Warehouse' => 'انبار',
            'update_variation' => 'تنوع محصول',
            'percent' => 'درصد',
            'amount' => 'مقدار',
            'end_date' => 'تاریخ پایان',
            'quantity' => 'تعداد',
            'start_date' => 'تاریخ شروع',
            'least_price' => 'حداقل قیمت',
            'exclude_type' => 'نوع حذف',
            'include_type' => 'نوع شامل',
            'discount_ceiling' => 'سقف تخفیف',
            'quantity_per_user' => 'تعداد برای هر کاربر',
            'only_first_purchase' => 'فقط اولین خرید',
            'least_products_count' => 'حداقل تعداد محصولات',
            'not_discount_products' => 'محصولات بدون تخفیف',
        ];

        return $fieldTitles[$key] ?? $key;
    }

    /**
     * دریافت برچسب مدل
     */
    private function getModelLabel($modelClass)
    {
        if (empty($modelClass)) {
            return '';
        }

        $modelNames = [
            'App\Models\Post' => 'مقاله',
            'App\Models\Product' => 'محصول',
            'App\Models\User' => 'کاربر',
            'App\Models\Admin' => 'مدیر',
            'App\Models\Category' => 'دسته‌بندی',
            'App\Models\Comment' => 'نظر',
            'App\Models\Order' => 'سفارش',
            'App\Models\Warehouse' => 'انبار',
            'App\Models\StockNotify' => 'اطلاع از موجودی',
            'App\Models\SpecificationGroup' => 'لیست انواع مشخصات',
        ];

        return $modelNames[$modelClass] ?? class_basename($modelClass);
    }

    /**
     * حذف لاگ‌های قدیمی
     */
    public function deleteOld(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1'
        ]);

        $date = now()->subDays($request->days);
        $count = Activity::where('created_at', '<', $date)->delete();

        return response()->json([
            'success' => true,
            'message' => "$count فعالیت حذف شد"
        ]);
    }

    /**
     * دریافت متن خلاصه برای گزارش فعالیت‌ها بر اساس action
     */
    /**
     * دریافت متن خلاصه برای گزارش فعالیت‌ها بر اساس action
     */
    private function getActionReportText($activity, $actorLink, $subjectLink)
    {
        $properties = $activity->properties;
        $action = $properties['action'] ?? null;
        $event = $activity->event ?? null;

        // ========== نظرات (Comments) ==========
        if ($activity->subject_type === 'App\Models\Comment') {
            if ($action === 'update_comment') {
                return "{$actorLink}, {$subjectLink} را ویرایش کرد";
            }
            if ($action === 'reply_to_comment') {
                return "{$actorLink}, {$subjectLink} پاسخ داد";
            }
            if ($action === 'delete_comment') {
                return "{$actorLink}, {$subjectLink} را حذف کرد";
            }
            if ($action === 'add_comment') {
                return "{$actorLink}, {$subjectLink} ثبت کرد";
            }
            if ($action === 'like_comment') {
                return "{$actorLink}, {$subjectLink} را لایک کرد";
            }
            if ($action === 'dislike_comment') {
                return "{$actorLink}, {$subjectLink} را دیسلایک کرد";
            }
            if ($action === 'unlike_comment') {
                return "{$actorLink}, لایک {$subjectLink} را لغو کرد";
            }
            return "{$actorLink}, {$subjectLink}";
        }
        if ($action === 'unlike_post') {
            return "{$actorLink}, {$subjectLink} را دیسلایک کرد";
        }
        if ($action === 'like_post') {
            return "{$actorLink}, {$subjectLink} را لایک کرد";
        }
        if ($action === 'view_order_item') {
            return "{$actorLink}, {$subjectLink} را مشاهده کرد";
        }


        // ========== محصولات (Products) ==========
        if ($action === 'create_product') {
            return "{$actorLink}, {$subjectLink} را ایجاد کرد";
        }
        if ($action === 'update_product') {
            return "{$actorLink}, {$subjectLink} را ویرایش کرد";
        }
        if ($action === 'delete_product') {
            return "{$actorLink}, {$subjectLink} را حذف کرد";
        }
        if ($action === 'update_products_prices') {
            $updatedProducts = $properties['updated_products'] ?? [];
            $count = count($updatedProducts);
            return "{$actorLink}, قیمت و موجودی {$count} محصول را ویرایش کرد";
        }
        if ($action === 'update_product_variations') {
            return "{$actorLink}, {$subjectLink} را ویرایش کرد";
        }
        if ($action === 'update_prices_group') {
            $affectedCount = $properties['affected_count'] ?? 0;
            $changeType = $properties['change_type'] ?? 'تغییر';
            $amountText = $properties['amount_text'] ?? '';
            return "{$actorLink}, {$changeType} گروهی قیمت {$amountText} روی {$affectedCount} محصول انجام داد";
        }
        if ($action === 'export_products') {
            $count = $properties['products_count'] ?? 0;
            return "{$actorLink}, خروجی {$count} محصول را دریافت کرد";
        }

        // ========== انبارها (Warehouses) ==========
        if ($action === 'create_warehouse') {
            return "{$actorLink}, {$subjectLink} را ایجاد کرد";
        }
        if ($action === 'update_warehouse') {
            return "{$actorLink}, {$subjectLink} را ویرایش کرد";
        }
        if ($action === 'delete_warehouse') {
            return "{$actorLink}, {$subjectLink} را حذف کرد";
        }
        if ($action === 'toggle_warehouse_status') {
            return "{$actorLink}, وضعیت {$subjectLink} را تغییر داد";
        }
        if ($action === 'stock_take') {
            $updatedCount = $properties['updated_count'] ?? 0;
            $totalDifference = $properties['total_difference'] ?? 0;
            if ($updatedCount > 0) {
                $sign = $totalDifference > 0 ? '+' : '';
                return "{$actorLink}, سرشماری {$subjectLink} را انجام داد ({$updatedCount} آیتم تغییر کرد، مغایرت: {$sign}{$totalDifference})";
            }
            return "{$actorLink}, سرشماری {$subjectLink} را انجام داد";
        }
        if ($action === 'bulk_stock_update') {
            $updatedCount = $properties['updated_count'] ?? 0;
            $totalDifference = $properties['total_difference'] ?? 0;
            $sign = $totalDifference > 0 ? '+' : '';
            return "{$actorLink}, بروزرسانی گروهی موجودی {$subjectLink} را انجام داد ({$updatedCount} آیتم، تغییر کل: {$sign}{$totalDifference})";
        }
        if ($action === 'create_variation') {
            $attributes = $properties['attributes'] ?? [];
            $price = $attributes['قیمت'] ?? '';
            $stock = $attributes['موجودی اولیه'] ?? '';
            return "{$actorLink}, تنوع جدیدی برای {$subjectLink} ایجاد کرد (قیمت: {$price}, موجودی: {$stock})";
        }
        if ($action === 'update_variation') {
            return "{$actorLink}, تنوع {$subjectLink} را ویرایش کرد";
        }
        if ($action === 'delete_variation') {
            $deletedPrice = $properties['deleted_price'] ?? '';
            $deletedStock = $properties['deleted_stock'] ?? '';
            return "{$actorLink}, تنوع {$subjectLink} را حذف کرد (قیمت: {$deletedPrice}, موجودی: {$deletedStock})";
        }
        if ($action === 'export_warehouse_products') {
            $count = $properties['products_count'] ?? 0;
            $format = $properties['format'] ?? 'excel';
            return "{$actorLink}, خروجی {$format} از {$count} محصول انبار {$subjectLink} را دریافت کرد";
        }
        if ($action === 'export_stock_history') {
            $count = $properties['records_count'] ?? 0;
            return "{$actorLink}, تاریخچه موجودی {$subjectLink} را با {$count} رکورد خروجی گرفت";
        }

        // ========== تنظیمات (Settings) ==========
        if ($action === 'update_information_settings') {
            return "{$actorLink}, تنظیمات اطلاعات کلی سایت را به‌روزرسانی کرد";
        }
        if ($action === 'update_socials') {
            return "{$actorLink}, تنظیمات شبکه‌های اجتماعی را به‌روزرسانی کرد";
        }
        if ($action === 'update_gateways') {
            $changedGateways = $properties['changed_gateways'] ?? [];
            $count = count($changedGateways);
            return "{$actorLink}, تنظیمات {$count} درگاه پرداخت را به‌روزرسانی کرد";
        }
        if ($action === 'update_others_settings') {
            return "{$actorLink}, تنظیمات عمومی سایت را به‌روزرسانی کرد";
        }
        if ($action === 'update_sms_settings') {
            return "{$actorLink}, تنظیمات پیامک را به‌روزرسانی کرد";
        }
        if ($action === 'update_theme_settings') {
            return "{$actorLink}, تنظیمات قالب را به‌روزرسانی کرد";
        }

        // ========== ابزارک‌ها (Widgets) ==========
        if ($action === 'update_widget') {
            return "{$actorLink}, {$subjectLink} را ویرایش کرد";
        }
        if ($action === 'update_home_widget') {
            return "{$actorLink}, {$subjectLink} را ویرایش کرد";
        }
        if ($action === 'create_widget') {
            return "{$actorLink}, {$subjectLink} را ایجاد کرد";
        }
        if ($action === 'delete_widget') {
            return "{$actorLink}, {$subjectLink} را حذف کرد";
        }

        // ========== نظرات محصولات (Reviews) ==========
        if ($action === 'create_review') {
            $rating = $properties['attributes']['امتیاز'] ?? '';
            return "{$actorLink}, برای {$subjectLink} نظر ثبت کرد (امتیاز: {$rating})";
        }
        if ($action === 'update_review') {
            return "{$actorLink}, نظر خود را برای {$subjectLink} ویرایش کرد";
        }
        if ($action === 'like_review') {
            return "{$actorLink}, به نظر {$subjectLink} لایک داد";
        }
        if ($action === 'dislike_review') {
            return "{$actorLink}, به نظر {$subjectLink} دیسلایک داد";
        }

        // ========== سوالات و پاسخ‌ها (Q&A) ==========
        if ($action === 'ask_question') {
            return "{$actorLink}, سوال جدیدی برای {$subjectLink} مطرح کرد";
        }
        if ($action === 'reply_question') {
            return "{$actorLink}, به سوال {$subjectLink} پاسخ داد";
        }

        // ========== علاقه‌مندی‌ها (Favorites) ==========
        if ($action === 'add_to_favorites') {
            return "{$actorLink}, {$subjectLink} را به علاقه‌مندی‌ها اضافه کرد";
        }
        if ($action === 'remove_from_favorites') {
            return "{$actorLink}, {$subjectLink} را از علاقه‌مندی‌ها حذف کرد";
        }

        // ========== راهنمای سایز (Size Type) ==========
        if ($action === 'update_size_type_values') {
            return "{$actorLink}, مقادیر راهنمای سایز {$subjectLink} را ویرایش کرد";
        }

        // ========== رویدادهای استاندارد (Events) ==========
        if ($event === 'created') {
            return "{$actorLink}, {$subjectLink} را ایجاد کرد";
        }
        if ($event === 'updated') {
            return "{$actorLink}, {$subjectLink} را ویرایش کرد";
        }
        if ($event === 'deleted') {
            return "{$actorLink}, {$subjectLink} را حذف کرد";
        }
        if ($event === 'restored') {
            return "{$actorLink}, {$subjectLink} را بازیابی کرد";
        }
        if ($event === 'liked') {
            return "{$actorLink}, {$subjectLink} را لایک کرد";
        }
        if ($event === 'disliked') {
            return "{$actorLink}, {$subjectLink} را دیسلایک کرد";
        }
        if ($event === 'unliked') {
            return "{$actorLink}, لایک {$subjectLink} را لغو کرد";
        }
        if ($event === 'replied') {
            return "{$actorLink}, به {$subjectLink} پاسخ داد";
        }
        if ($event === 'favorited') {
            return "{$actorLink}, {$subjectLink} را به علاقه‌مندی‌ها اضافه کرد";
        }
        if ($event === 'unfavorited') {
            return "{$actorLink}, {$subjectLink} را از علاقه‌مندی‌ها حذف کرد";
        }
        if ($event === 'asked') {
            return "{$actorLink}, سوال جدیدی برای {$subjectLink} مطرح کرد";
        }

        // ========== پیشفرض ==========
        $actionText = $this->getDetailedActionText($activity);
        return "{$actorLink}, {$subjectLink} {$actionText}";
    }
}
