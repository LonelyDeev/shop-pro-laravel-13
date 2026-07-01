<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('CheckCreator');
    }

    public function index()
    {
        $permissions = Permission::whereNull('permission_id')->orderBy('ordering')->get();

        return view('back.permissions.index', compact('permissions'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'permission'   => 'required|array',
            'permission.*' => 'exists:permissions,id'
        ]);

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';

        // دریافت لیست دسترسی‌های قبلی فعال
        $oldActivePermissions = Permission::where('active', true)->get();
        $oldActiveIds = $oldActivePermissions->pluck('id')->toArray();

        // دریافت لیست دسترسی‌های جدید
        $newActiveIds = $request->permission;

        // پیدا کردن دسترسی‌های اضافه شده و حذف شده
        $addedPermissions = array_diff($newActiveIds, $oldActiveIds);
        $removedPermissions = array_diff($oldActiveIds, $newActiveIds);

        // دریافت عنوان فارسی دسترسی‌ها (از جدول permissions)
        $allPermissions = Permission::all()->keyBy('id');

        // ایجاد یک مدل Permission ساختگی برای performedOn
        $dummyPermission = new Permission();
        $dummyPermission->id = 0;
        $dummyPermission->name = 'permissions_update';

        // به‌روزرسانی دسترسی‌ها
        Permission::query()->update([
            'active' => false,
        ]);

        foreach ($request->permission as $permissionId) {
            Permission::find($permissionId)->update([
                'active' => true,
            ]);
        }

        // ساخت properties با ساختار old و attributes
        $oldData = [];
        $newData = [];

        foreach ($removedPermissions as $permissionId) {
            $title = $allPermissions[$permissionId]->title ?? $allPermissions[$permissionId]->name ?? "دسترسی #{$permissionId}";
            $oldData[$title] = 'فعال';
            $newData[$title] = 'غیرفعال';
        }

        foreach ($addedPermissions as $permissionId) {
            $title = $allPermissions[$permissionId]->title ?? $allPermissions[$permissionId]->name ?? "دسترسی #{$permissionId}";
            $oldData[$title] = 'غیرفعال';
            $newData[$title] = 'فعال';
        }

        // ثبت لاگ با performedOn برای تنظیم subject_type
        activity()
            ->performedOn($dummyPermission)  // اضافه شد - برای تنظیم subject_type
            ->causedBy(auth('adminPanel')->user())
            ->event('updated')
            ->withProperties([
                'action' => 'update_permissions',
                'old' => $oldData,
                'attributes' => $newData,
                'ip' => request()->ip()
            ])
            ->log("مدیر {$adminName} سطح دسترسی‌ها را تغییر داد");

        return response('success');
    }
}
