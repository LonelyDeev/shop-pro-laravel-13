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

    public function index()
    {


        return view('back.notifications.index');
    }

    // ---------- لیست اعلان‌ها (AJAX) ----------
    public function list()
    {


        $usersTotal   = User::count();
        $sellersTotal = Seller::count();

        $items = NotificationManage::latest()
            ->withCount([
                'recipients as selected_users'   => fn ($q) => $q->whereNotNull('user_id'),
                'recipients as selected_sellers' => fn ($q) => $q->whereNotNull('seller_id'),
                'recipients as read_count'       => fn ($q) => $q->where('read', 1),
            ])
            ->get()
            ->map(function ($n) use ($usersTotal, $sellersTotal) {
                $targetUsers   = in_array($n->private, ['all', 'user']);
                $targetSellers = in_array($n->private, ['all', 'seller']);

                return [
                    'id'               => $n->id,
                    'title'            => $n->title ?: 'بدون عنوان',
                    'message'          => $n->message,
                    'priority'         => $n->priority,
                    'popup'            => (bool) $n->popup,
                    'targets'          => [
                        'users'   => $targetUsers
                            ? ['mode' => $n->allUsers ? 'all' : 'selected', 'count' => $n->allUsers ? $usersTotal : (int) $n->selected_users]
                            : null,
                        'sellers' => $targetSellers
                            ? ['mode' => $n->allSellers ? 'all' : 'selected', 'count' => $n->allSellers ? $sellersTotal : (int) $n->selected_sellers]
                            : null,
                    ],
                    'read_count'       => (int) $n->read_count,
                    'date'             => jdate($n->created_at)->format('Y/m/d H:i'),
                    'ago'              => jdate($n->created_at)->ago(),
                ];
            });

        return response()->json(['data' => $items]);
    }

    // ---------- جستجوی گیرندگان (AJAX) ----------
    public function searchRecipients(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $group = $request->query('group', 'users');

        if ($group === 'sellers') {
            // جستجو در sellers با اطلاعات sellers_info
            $query = Seller::query()
                ->join('sellers_info', 'sellers.id', '=', 'sellers_info.seller_id')
                ->select('sellers.id', 'sellers_info.first_name', 'sellers.mobile');

            $cols = ['id' => 'sellers.id', 'name' => 'sellers_info.full_name', 'mobile' => 'sellers.mobile'];

            if ($q !== '') {
                $query->where(function ($qq) use ($q) {
                    $qq->where('sellers_info.first_name', 'like', "%{$q}%")
                        ->orWhere('sellers.mobile', 'like', "%{$q}%");
                });
            }

            $rows = $query->orderBy('sellers.id', 'desc')->limit(15)->get();

            return response()->json([
                'data' => $rows->map(fn ($r) => [
                    'id'     => $r->id,
                    'name'   => $r->fullname,
                    'mobile' => $r->mobile,
                ]),
            ]);
        } else {
            // جستجو در users
            $query = User::query();
            $cols = ['id', 'first_name', 'last_name', 'mobile'];

            if ($q !== '') {
                $query->where(function ($qq) use ($q) {
                    $qq->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('mobile', 'like', "%{$q}%");
                });
            }

            $rows = $query->orderBy('id', 'desc')->limit(15)->get($cols);

            return response()->json([
                'data' => $rows->map(fn ($r) => [
                    'id'     => $r->id,
                    'name'   => trim($r->first_name . ' ' . $r->last_name),
                    'mobile' => $r->mobile,
                ]),
            ]);
        }
    }

    // ---------- ایجاد (AJAX) ----------
    public function store(Request $request)
    {


        $data = $request->validate([
            'title'       => ['required', 'string', 'max:190'],
            'message'     => ['required', 'string', 'max:2000'],
            'priority'    => ['required', 'in:high,medium,low'],
            'popup'       => ['sometimes', 'boolean'],
            'send_users'   => ['sometimes', 'boolean'],
            'send_sellers' => ['sometimes', 'boolean'],
            'user_ids'    => ['nullable', 'array'],
            'user_ids.*'  => ['integer', 'exists:users,id'],
            'seller_ids'  => ['nullable', 'array'],
            'seller_ids.*' => ['integer', 'exists:sellers,id'],
        ]);

        $sendUsers   = $request->boolean('send_users');
        $sendSellers = $request->boolean('send_sellers');

        if (! $sendUsers && ! $sendSellers) {
            return response()->json([
                'errors' => ['send_users' => ['حداقل یکی از گروه‌های گیرنده را فعال کنید.']],
            ], 422);
        }

        $userIds   = array_values(array_unique(array_filter((array) $request->input('user_ids', []))));
        if ($sendUsers and !count($userIds)){
            $userIds=User::all()->pluck('id');
        }

        $sellerIds = array_values(array_unique(array_filter((array) $request->input('seller_ids', []))));
        if ($sendSellers and !count($sellerIds)){
            $sellerIds=Seller::all()->pluck('id');
        }

        $notification = NotificationManage::create([
            'admin_id'   => auth('adminPanel')->id(),
            'title'      => $data['title'],
            'message'    => $data['message'],
            'priority'   => $data['priority'],
            'popup'      => $request->boolean('popup'),
            'private'    => $sendUsers && $sendSellers ? 'all' : ($sendSellers ? 'seller' : 'user'),
            'allUsers'   => $sendUsers   ? (count($userIds)   ? 0 : 1) : 0,
            'allSellers' => $sendSellers ? (count($sellerIds) ? 0 : 1) : 0,
        ]);

        // ردیف‌های گیرندگان انتخابی
        $rows = [];
        $now  = now();
        foreach ($userIds as $id) {
            $rows[] = ['notification_manage_id' => $notification->id, 'user_id' => $id, 'seller_id' => null, 'read' => 0, 'created_at' => $now, 'updated_at' => $now];
        }
        foreach ($sellerIds as $id) {
            $rows[] = ['notification_manage_id' => $notification->id, 'user_id' => null, 'seller_id' => $id, 'read' => 0, 'created_at' => $now, 'updated_at' => $now];
        }
        if ($rows) {
            DB::table('notification_manage_users')->insert($rows);
        }

        $this->logActivity('ایجاد', $data['title'], count($rows));

        return response()->json([
            'message' => count($rows)
                ? "اعلان برای " . count($rows) . " گیرنده انتخابی ذخیره شد."
                : "اعلان به صورت گروهی (همه گیرندگان گروه انتخاب‌شده) ذخیره شد.",
        ], 201);
    }

    // ---------- ویرایش (فقط محتوا؛ گیرندگان ثابت می‌مانند) ----------
    public function update(Request $request, NotificationManage $notificationManage)
    {


        $data = $request->validate([
            'title'    => ['required', 'string', 'max:190'],
            'message'  => ['required', 'string', 'max:2000'],
            'priority' => ['required', 'in:high,medium,low'],
            'popup'    => ['sometimes', 'boolean'],
        ]);

        $notificationManage->update([
            'title'    => $data['title'],
            'message'  => $data['message'],
            'priority' => $data['priority'],
            'popup'    => $request->boolean('popup'),
        ]);

        $this->logActivity('ویرایش', $data['title'], 0);

        return response()->json(['message' => 'اعلان با موفقیت ویرایش شد.']);
    }

    // ---------- سوییچ پاپ‌آپ (AJAX) ----------
    public function togglePopup(NotificationManage $notificationManage)
    {


        $notificationManage->update(['popup' => ! $notificationManage->popup]);

        return response()->json([
            'message' => $notificationManage->popup ? 'نمایش پاپ‌آپ فعال شد.' : 'نمایش پاپ‌آپ غیرفعال شد.',
            'popup'   => (bool) $notificationManage->popup,
        ]);
    }

    // ---------- حذف (cascade گیرندگان) ----------
    public function destroy(NotificationManage $notificationManage)
    {


        $title = $notificationManage->title ?: 'بدون عنوان';
        $count = $notificationManage->recipients()->count();
        $notificationManage->delete(); // ردیف‌های pivot با cascade حذف می‌شوند

        $this->logActivity('حذف', $title, $count);

        return response()->json(['message' => "اعلان و {$count} ردیف گیرنده آن حذف شد."]);
    }

    private function logActivity(string $action, string $title, int $recipients): void
    {
        $adminName = auth('adminPanel')->user()->full_name
            ?? auth('adminPanel')->user()->name ?? 'مدیر';

        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('notification')
            ->withProperties([
                'action'     => 'notification_' . strtolower($action),
                'title'      => $title,
                'recipients' => $recipients,
                'ip'         => request()->ip(),
            ])
            ->log("مدیر {$adminName} اعلان «{$title}» را {$action} کرد.");
    }
}
