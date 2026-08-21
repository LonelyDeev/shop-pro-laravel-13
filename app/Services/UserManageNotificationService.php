<?php

namespace App\Services;

use App\Models\NotificationManage;
use App\Models\NotificationManageUser;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserManageNotificationService
{
    // ---------- لیست ادغام‌شده + صفحه‌بندی ----------
    public function paginate(User $user, string $filter = 'all', int $perPage = 15): array
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $base = DB::query()->fromSub($this->unionQuery($user), 't');

        $total  = (clone $base)->count();
        $unread = (clone $base)->where('is_read', 0)->count();

        $query = clone $base;
        if ($filter === 'unread') $query->where('is_read', 0);
        if ($filter === 'read')   $query->where('is_read', 1);

        $rows = $query->orderByDesc('created_at')
            ->forPage($page, $perPage)
            ->get();

        $items = $rows->map(fn ($r) => $this->mapRow($r));

        $paginator = new LengthAwarePaginator(
            $items, $total, $perPage, $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [$paginator, ['total' => $total, 'unread' => $unread, 'read' => $total - $unread]];
    }

    // ---------- کوئری UNION ----------
    private function unionQuery(User $user)
    {
        // ===== بخش ۱: اعلان‌های پنل مدیریت (notification_manages) =====
        // گروهی‌ها (allUsers=1) ردیف pivot ندارند → LEFT JOIN برای وضعیت خواندن
        $manage = DB::table('notification_manages as nm')
            ->leftJoin('notification_manage_users as nmu', function ($join) use ($user) {
                $join->on('nmu.notification_manage_id', '=', 'nm.id')
                    ->where('nmu.user_id', $user->id)
                    ->whereNull('nmu.seller_id');
            })
            ->where(function ($q) {
                $q->where(fn ($qq) => $qq->whereIn('nm.private', ['all', 'user'])->where('nm.allUsers', 1))
                    ->orWhereNotNull('nmu.id'); // + انتخابی‌هایی که ردیف دارند
            })
            ->selectRaw(implode(', ', [
                "CONCAT('manage_', nm.id)  AS uid",
                "'manage'                   AS source",
                "CAST(nm.id AS CHAR)        AS origin_id",
                "nm.title                   AS title",
                "nm.message                 AS message",
                "nm.priority                AS priority",
                "nm.popup                   AS popup",
                "NULL                       AS data_title",
                "NULL                       AS data_message",
                "NULL                       AS data_link",
                "NULL                       AS ntype",
                "COALESCE(nmu.read, 0)      AS is_read",
                "nmu.read_at                AS read_at",
                "nm.created_at              AS created_at",
            ]));

        // ===== بخش ۲: اعلان‌های استاندارد لاراول (notifications) =====
        $system = DB::table('notifications as n')
            ->where('n.notifiable_type', $user->getMorphClass()) // morph map را خودش هندل می‌کند
            ->where('n.notifiable_id', $user->id)
            ->selectRaw(implode(', ', [
                "CONCAT('system_', n.id)                                        AS uid",
                "'system'                                                        AS source",
                "n.id                                                            AS origin_id",
                "NULL                                                            AS title",
                "NULL                                                            AS message",
                "NULL                                                            AS priority",
                "0                                                               AS popup",
                "JSON_UNQUOTE(JSON_EXTRACT(n.data, '$.title'))                   AS data_title",
                "JSON_UNQUOTE(JSON_EXTRACT(n.data, '$.message'))                 AS data_message",
                "JSON_UNQUOTE(JSON_EXTRACT(n.data, '$.link'))                    AS data_link",
                "n.type                                                          AS ntype",
                "CASE WHEN n.read_at IS NULL THEN 0 ELSE 1 END                   AS is_read",
                "n.read_at                                                       AS read_at",
                "n.created_at                                                     AS created_at",
            ]));

        return $manage->unionAll($system);
    }

    // ---------- یکسان‌سازی خروجی ----------
    private function mapRow($r): array
    {
        $isSystem = $r->source === 'system';
        $meta = $isSystem ? $this->systemMeta($r->ntype) : $this->priorityMeta($r->priority);

        return [
            'uid'      => $r->uid,
            'source'   => $r->source,
            'title'    => $r->title ?: ($r->data_title ?: ($meta['title'] ?? 'اعلان جدید')),
            'message'  => $r->message ?: ($r->data_message ?: ''),
            'link'     => $r->data_link ?: null,
            'icon'     => $meta['icon'],
            'c1'       => $meta['c1'],
            'c2'       => $meta['c2'],
            'priority' => $r->priority,
            'popup'    => (bool) $r->popup,
            'is_read'  => (bool) $r->is_read,
            'ago'      => jdate($r->created_at)->ago(),
            'date'     => jdate($r->created_at)->format('Y/m/d H:i'),
        ];
    }

    private function priorityMeta(?string $p): array
    {
        return match ($p) {
            'high'   => ['icon' => 'mdi-alert-octagram', 'c1' => '#FB7185', 'c2' => '#E11D48', 'title' => 'اعلان فوری'],
            'medium' => ['icon' => 'mdi-alert',          'c1' => '#FBBF24', 'c2' => '#D97706', 'title' => 'اعلان'],
            default  => ['icon' => 'mdi-information',    'c1' => '#34D399', 'c2' => '#059669', 'title' => 'اعلان'],
        };
    }

    private function systemMeta(?string $type): array
    {
        return match ($type) {
            'SendMessage' => ['icon' => 'mdi-email-outline',   'c1' => '#60A5FA', 'c2' => '#2563EB', 'title' => 'پیام جدید'],
            default       => ['icon' => 'mdi-bell-outline',    'c1' => '#818CF8', 'c2' => '#4F46E5', 'title' => 'اعلان سیستمی'],
        };
        // انواع دیگر را همین‌جا اضافه کنید
    }

    // ---------- خواندن تکی ----------
    public function markRead(User $user, string $uid): void
    {
        [$source, $id] = explode('_', $uid, 2);

        if ($source === 'manage') {
            // updateOrCreate → برای اعلان‌های گروهی که ردیف ندارند، ردیفِ «خوانده‌شده» می‌سازد
            NotificationManageUser::updateOrCreate(
                ['notification_manage_id' => $id, 'user_id' => $user->id],
                ['seller_id' => null, 'read' => true, 'read_at' => now()]
            );
        } else {
            DB::table('notifications')
                ->where('id', $id)
                ->where('notifiable_type', $user->getMorphClass())
                ->where('notifiable_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
    }

    // ---------- خواندن همه ----------
    public function markAllRead(User $user): int
    {
        // ۱) اعلان‌های لاراول
        $count = DB::table('notifications')
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // ۲) برای اعلان‌های گروهیِ بدون ردیف، ردیفِ خوانده‌شده بساز
        $broadcastIds = NotificationManage::whereIn('private', ['all', 'user'])
            ->where('allUsers', 1)->pluck('id');

        $existing = NotificationManageUser::where('user_id', $user->id)
            ->whereNull('seller_id')
            ->whereIn('notification_manage_id', $broadcastIds)
            ->pluck('notification_manage_id');

        $missing = $broadcastIds->diff($existing);
        if ($missing->isNotEmpty()) {
            $now = now();
            NotificationManageUser::insert($missing->map(fn ($nid) => [
                'notification_manage_id' => $nid,
                'user_id'                => $user->id,
                'seller_id'              => null,
                'read'                   => 1,
                'read_at'                => $now,
                'created_at'             => $now,
                'updated_at'             => $now,
            ])->all());
            $count += $missing->count();
        }

        // ۳) ردیف‌های موجودِ نخوانده
        $count += NotificationManageUser::where('user_id', $user->id)
            ->whereNull('seller_id')->where('read', 0)
            ->update(['read' => 1, 'read_at' => now()]);

        return $count;
    }
}
