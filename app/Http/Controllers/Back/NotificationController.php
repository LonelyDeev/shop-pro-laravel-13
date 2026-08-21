<?php

namespace App\Http\Controllers\Back;

use App\Models\NotificationManage;
use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:notifications');
    }

    private function sellersQuery()
    {
        return User::where('level', 'seller');
        // یا اگر با رابطه است: return User::whereHas('store');
    }

    // کاربران عادی = همه به‌جز فروشندگان
    private function usersQuery()
    {
        return User::where(fn ($q) => $q->whereNull('level')->orWhere('level', '!=', 'seller'));
        // یا: return User::whereDoesntHave('store');
    }

    public function index()
    {


        return view('back.notifications.index');
    }

    // ---------- لیست اعلان‌های ارسال‌شده (گروه‌بندی بر اساس batch) ----------
    public function list()
    {


        $type = NotificationManage::class;

        $batches = DB::table('notifications')
            ->where('type', $type)
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.batch_id')) as batch_id")
            ->selectRaw('COUNT(*) as recipients')
            ->selectRaw('SUM(CASE WHEN read_at IS NOT NULL THEN 1 ELSE 0 END) as read_count')
            ->selectRaw('MAX(created_at) as created_at')
            ->groupBy('batch_id')
            ->get()
            ->sortByDesc('created_at')
            ->values();

        // یک نمونه از دیتا برای هر بسته (عنوان/پیام/نوع/گروه هدف)
        $firstIds = DB::table('notifications')
            ->where('type', $type)
            ->selectRaw('MIN(id) as id')
            ->groupBy('batch_id')
            ->pluck('id');

        $samples = NotificationManage::whereIn('id', $firstIds)->get()
            ->mapWithKeys(fn ($n) => [$n->data['batch_id'] ?? '' => $n->data]);

        $data = $batches->map(function ($b) use ($samples) {
            $s = $samples[$b->batch_id] ?? [];

            return [
                'batch_id'   => $b->batch_id,
                'title'      => $s['title'] ?? '—',
                'message'    => $s['message'] ?? '',
                'type'       => $s['type'] ?? 'info',
                'link'       => $s['link'] ?? null,
                'targets'    => $s['targets'] ?? [],
                'recipients' => (int) $b->recipients,
                'read_count' => (int) $b->read_count,
                'date'       => jdate($b->created_at)->format('Y/m/d H:i'),
                'ago'        => jdate($b->created_at)->ago(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    // ---------- جستجوی گیرندگان برای پیکر ----------
    public function searchRecipients(Request $request)
    {


        $group = $request->query('group') === 'sellers' ? 'sellers' : 'users';
        $q     = trim((string) $request->query('q', ''));

        $query = $group === 'sellers' ? $this->sellersQuery() : $this->usersQuery();

        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('fullname', 'like', "%{$q}%")
                    ->orWhere('mobile', 'like', "%{$q}%");
            });
        }

        return response()->json([
            'data' => $query->latest('id')->limit(15)
                ->get(['id', 'fullname', 'mobile'])
                ->map(fn ($u) => ['id' => $u->id, 'name' => $u->fullname, 'mobile' => $u->mobile]),
        ]);
    }

    // ---------- ارسال ----------
    public function store(Request $request)
    {


        $data = $request->validate([
            'title'        => ['required', 'string', 'max:190'],
            'message'      => ['required', 'string', 'max:1000'],
            'type'         => ['required', 'in:info,success,warning,danger'],
            'link'         => ['nullable', 'string', 'max:255'],
            'send_users'   => ['sometimes', 'boolean'],
            'send_sellers' => ['sometimes', 'boolean'],
            'user_ids'     => ['nullable', 'array'],
            'user_ids.*'   => ['integer', 'exists:users,id'],
            'seller_ids'   => ['nullable', 'array'],
            'seller_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $sendUsers   = $request->boolean('send_users');
        $sendSellers = $request->boolean('send_sellers');

        if (! $sendUsers && ! $sendSellers) {
            return response()->json([
                'errors' => ['send_users' => ['حداقل یکی از گروه‌های گیرنده را فعال کنید.']],
            ], 422);
        }

        $userIds   = array_filter((array) $request->input('user_ids', []));
        $sellerIds = array_filter((array) $request->input('seller_ids', []));

        // ---- جمع‌آوری گیرندگان: انتخاب خاص، وگرنه کل گروه ----
        $ids = collect();

        if ($sendUsers) {
            $ids = $ids->merge(
                $userIds ?: $this->usersQuery()->pluck('id')
            );
        }
        if ($sendSellers) {
            $ids = $ids->merge(
                $sellerIds ?: $this->sellersQuery()->pluck('id')
            );
        }

        $ids = $ids->unique()->values();

        if ($ids->isEmpty()) {
            return response()->json(['message' => 'هیچ گیرنده‌ای یافت نشد!'], 422);
        }

        $batchId = (string) Str::uuid();
        $targets = ['users' => $sendUsers, 'sellers' => $sendSellers];
        $count   = 0;

        // ارسال تکه‌تکه برای جلوگیری از پرشدن حافظه
        User::whereIn('id', $ids)->chunkById(500, function ($users) use ($data, $batchId, $targets, &$count) {
            Notification::send($users, new SiteNotification(
                $data['title'],
                $data['message'],
                $data['type'],
                $data['link'] ?? null,
                $batchId,
                $targets
            ));
            $count += $users->count();
        });

        $this->logActivity('ارسال', $data['title'], $count, $targets);

        return response()->json([
            'message' => "اعلان با موفقیت برای {$count} نفر ارسال شد.",
            'count'   => $count,
        ], 201);
    }

    // ---------- ویرایش (فقط متن — گیرندگان ثابت می‌مانند) ----------
    public function update(Request $request, string $batchId)
    {


        $data = $request->validate([
            'title'   => ['required', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:1000'],
            'type'    => ['required', 'in:info,success,warning,danger'],
            'link'    => ['nullable', 'string', 'max:255'],
        ]);

        $existing = NotificationManage::where('type', SiteNotification::class)
            ->where('data->batch_id', $batchId)
            ->first();

        if (! $existing) {
            return response()->json(['message' => 'اعلان یافت نشد!'], 404);
        }

        $payload = array_merge($existing->data, [
            'title'   => $data['title'],
            'message' => $data['message'],
            'type'    => $data['type'],
            'link'    => $data['link'] ?? null,
        ]);

        NotificationManage::where('type', SiteNotification::class)
            ->where('data->batch_id', $batchId)
            ->update(['data' => json_encode($payload, JSON_UNESCAPED_UNICODE)]);

        $this->logActivity('ویرایش', $data['title'], 0, $payload['targets'] ?? []);

        return response()->json(['message' => 'اعلان با موفقیت ویرایش شد (برای همه گیرندگان اعمال شد).']);
    }

    // ---------- حذف کل بسته ----------
    public function destroy(string $batchId)
    {


        $existing = NotificationManage::where('type', SiteNotification::class)
            ->where('data->batch_id', $batchId)
            ->first();

        if (! $existing) {
            return response()->json(['message' => 'اعلان یافت نشد!'], 404);
        }

        $count = NotificationManage::where('type', SiteNotification::class)
            ->where('data->batch_id', $batchId)
            ->delete();

        $this->logActivity('حذف', $existing->data['title'] ?? '—', $count, $existing->data['targets'] ?? []);

        return response()->json(['message' => "اعلان برای {$count} گیرنده حذف شد."]);
    }

    private function logActivity(string $action, string $title, int $count, array $targets): void
    {
        $adminName = auth('adminPanel')->user()->full_name
            ?? auth('adminPanel')->user()->name ?? 'مدیر';

        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('notification')
            ->withProperties([
                'action'     => 'notification_' . strtolower($action),
                'title'      => $title,
                'recipients' => $count,
                'targets'    => $targets,
                'ip'         => request()->ip(),
            ])
            ->log("مدیر {$adminName} اعلان «{$title}» را {$action} کرد ({$count} گیرنده).");
    }
}
