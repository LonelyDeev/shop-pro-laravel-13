<?php
// app/Traits/StoryInteractionTrait.php

namespace App\Traits;

use App\Models\StoryInteraction;
use Illuminate\Support\Facades\Auth;

trait StoryInteractionTrait
{
    /**
     * ثبت یا بروزرسانی تعامل استوری (با شمارش تعداد)
     *
     * @param int $storyId
     * @param string $type
     * @param array $data
     * @return StoryInteraction
     */
    protected function logOrUpdateStoryInteraction($storyId, $type, $data = [])
    {
        // پیدا کردن رکورد موجود بر اساس کلیدهای یکتا
        $query = StoryInteraction::where('story_id', $storyId)
            ->where('type', $type);

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', session()->getId());
        }

        // برای نوع comment، بر اساس element_text هم باید چک کنیم
        if ($type === 'comment' && isset($data['element_text'])) {
            $query->where('element_text', $data['element_text']);
        }

        $interaction = $query->first();

        $interactionData = [
            'story_id' => $storyId,
            'type' => $type,
            'element_id' => $data['element_id'] ?? null,
            'element_text' => $data['element_text'] ?? null,
            'target_url' => $data['target_url'] ?? null,
            'additional_data' => !empty($data['additional_data']) ? json_encode($data['additional_data']) : null,
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'device_type' => $this->getDeviceType(request()->userAgent()),
            'interacted_at' => now(),
            'last_interacted_at' => now()
        ];

        if ($interaction) {
            // به‌روزرسانی رکورد موجود

            $newCount = $interaction->count + 1;

            $interaction->update([
                'count' => $newCount,
                'last_interacted_at' => now(),
                'additional_data' => $interactionData['additional_data'],
                'ip_address' => request()->ip(),
            ]);

            return $interaction;
        } else {
            // ایجاد رکورد جدید
            $interactionData['count'] = 1;
            $newInteraction = StoryInteraction::create($interactionData);
            return $newInteraction;
        }
    }

    /**
     * ثبت تعامل بازدید کامل استوری
     */
    protected function logStoryView($storyId, $isRealView = false, $viewsCount = 0, $realViewsCount = 0)
    {
        return $this->logOrUpdateStoryInteraction($storyId, 'view_full', [
            'additional_data' => [
                'is_real_view' => $isRealView,
                'views_count' => $viewsCount,
                'real_views_count' => $realViewsCount,
                'view_count_total' => ($this->getUserStoryViewCount($storyId) + 1)
            ]
        ]);
    }

    /**
     * گرفتن تعداد دفعات مشاهده استوری توسط کاربر فعلی
     */
    protected function getUserStoryViewCount($storyId)
    {
        $interaction = StoryInteraction::where('story_id', $storyId)
            ->where('type', 'view_full')
            ->where(function($query) {
                if (Auth::check()) {
                    $query->where('user_id', Auth::id());
                } else {
                    $query->where('session_id', session()->getId());
                }
            })
            ->first();

        return $interaction ? $interaction->count : 0;
    }

    /**
     * ثبت تعامل لایک استوری
     */
    protected function logStoryLike($storyId, $isLiked)
    {
        $type = $isLiked ? 'like_add' : 'like_remove';

        // برای لایک، اگر رکورد قبلی وجود داشت، آن را حذف نکن، فقط نوع را تغییر بده
        $existingLike = StoryInteraction::where('story_id', $storyId)
            ->where('type', 'like_add')
            ->where(function($query) {
                if (Auth::check()) {
                    $query->where('user_id', Auth::id());
                } else {
                    $query->where('session_id', session()->getId());
                }
            })
            ->first();

        if ($isLiked && !$existingLike) {
            // اضافه کردن لایک جدید
            return $this->logOrUpdateStoryInteraction($storyId, 'like_add', [
                'additional_data' => ['is_liked' => true]
            ]);
        } elseif (!$isLiked && $existingLike) {
            // حذف لایک
            return $this->logOrUpdateStoryInteraction($storyId, 'like_remove', [
                'additional_data' => ['is_liked' => false]
            ]);
        }

        return null;
    }

    /**
     * ثبت تعامل کامنت استوری (هر کامنت یک رکورد جدا)
     */
    protected function logStoryComment($storyId, $comment, $commentId = null)
    {
        // برای کامنت، همیشه یک رکورد جدید ایجاد می‌کنیم چون متن کامنت متفاوت است
        $interactionData = [
            'story_id' => $storyId,
            'type' => 'comment',
            'element_id' => $commentId,
            'element_text' => $comment,
            'additional_data' => json_encode([
                'comment_length' => strlen($comment),
                'user_authenticated' => Auth::check(),
                'user_id' => Auth::id()
            ]),
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'device_type' => $this->getDeviceType(request()->userAgent()),
            'interacted_at' => now(),
            'last_interacted_at' => now(),
            'count' => 1
        ];

        return StoryInteraction::create($interactionData);
    }

    /**
     * ثبت تعامل کلیک روی ویجت
     */
    protected function logStoryWidgetClick($storyId, $widgetTitle, $widgetUrl)
    {
        return $this->logOrUpdateStoryInteraction($storyId, 'widget_click', [
            'element_text' => $widgetTitle,
            'target_url' => $widgetUrl,
            'additional_data' => [
                'widget_title' => $widgetTitle,
                'widget_url' => $widgetUrl,
                'click_count' => 1
            ]
        ]);
    }

    /**
     * ثبت تعامل کلیک روی محصول
     */
    protected function logStoryProductClick($storyId, $productId, $productTitle, $productUrl)
    {
        return $this->logOrUpdateStoryInteraction($storyId, 'product_click', [
            'element_id' => $productId,
            'element_text' => $productTitle,
            'target_url' => $productUrl,
            'additional_data' => [
                'product_id' => $productId,
                'product_title' => $productTitle,
                'click_count' => 1
            ]
        ]);
    }

    /**
     * ثبت تعامل اشتراک‌گذاری استوری
     */
    protected function logStoryShare($storyId, $platform = null)
    {
        return $this->logOrUpdateStoryInteraction($storyId, 'share', [
            'additional_data' => [
                'platform' => $platform,
                'shared_at' => now()->toDateTimeString(),
                'share_count' => 1
            ]
        ]);
    }

    /**
     * ثبت تعامل باز شدن استوری
     */
    protected function logStoryOpen($storyId)
    {
        return $this->logOrUpdateStoryInteraction($storyId, 'story_open', [
            'additional_data' => [
                'opened_at' => now()->toDateTimeString()
            ]
        ]);
    }

    /**
     * ثبت تعامل بسته شدن استوری
     */
    protected function logStoryClose($storyId)
    {
        return $this->logOrUpdateStoryInteraction($storyId, 'story_close', [
            'additional_data' => [
                'closed_at' => now()->toDateTimeString()
            ]
        ]);
    }

    /**
     * ثبت تعامل پیشرفت مشاهده استوری
     */
    protected function logStoryProgress($storyId, $percentage)
    {
        $progressType = '';
        if ($percentage >= 100) {
            $progressType = 'progress_100';
        } elseif ($percentage >= 75) {
            $progressType = 'progress_75';
        } elseif ($percentage >= 50) {
            $progressType = 'progress_50';
        } elseif ($percentage >= 25) {
            $progressType = 'progress_25';
        } else {
            return;
        }

        return $this->logOrUpdateStoryInteraction($storyId, $progressType, [
            'additional_data' => [
                'percentage' => $percentage,
                'elapsed_time' => $this->storyCurrentElapsed ?? 0,
                'total_duration' => $this->storyCurrentDuration ?? 0
            ]
        ]);
    }

    /**
     * دریافت آمار تعاملات استوری
     */
    protected function getStoryInteractionStats($storyId)
    {
        $stats = [
            'total_views' => 0,
            'unique_viewers' => 0,
            'total_likes' => 0,
            'total_comments' => 0,
            'total_widget_clicks' => 0,
            'total_product_clicks' => 0,
            'total_shares' => 0,
        ];

        // مجموع بازدیدها (تعداد دفعات)
        $viewsInteractions = StoryInteraction::where('story_id', $storyId)
            ->where('type', 'view_full')
            ->get();

        foreach ($viewsInteractions as $interaction) {
            $stats['total_views'] += $interaction->count;
        }

        $stats['unique_viewers'] = StoryInteraction::where('story_id', $storyId)
            ->where('type', 'view_full')
            ->distinct('user_id', 'session_id')
            ->count();

        // لایک‌ها
        $stats['total_likes'] = StoryInteraction::where('story_id', $storyId)
            ->where('type', 'like_add')
            ->sum('count');

        // کامنت‌ها
        $stats['total_comments'] = StoryInteraction::where('story_id', $storyId)
            ->where('type', 'comment')
            ->count();

        // کلیک روی ویجت
        $stats['total_widget_clicks'] = StoryInteraction::where('story_id', $storyId)
            ->where('type', 'widget_click')
            ->sum('count');

        // کلیک روی محصول
        $stats['total_product_clicks'] = StoryInteraction::where('story_id', $storyId)
            ->where('type', 'product_click')
            ->sum('count');

        // اشتراک‌گذاری
        $stats['total_shares'] = StoryInteraction::where('story_id', $storyId)
            ->where('type', 'share')
            ->sum('count');

        return $stats;
    }

    /**
     * گرفتن دستگاه کاربر از User Agent
     */
    private function getDeviceType($userAgent)
    {
        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/iPad|Tablet|PlayBook|Kindle|Silk/i', $userAgent)) {
            return 'tablet';
        }
        return 'desktop';
    }
}
