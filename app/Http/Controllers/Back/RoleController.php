<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Role::class, 'role');
    }

    public function index()
    {
        $roles = Role::latest()->paginate(20);

        return view('back.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::whereNull('permission_id')->where('active', true)->orderBy('ordering')->get();

        return view('back.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'permissions'   => 'array',
            'permissions.*' => [
                Rule::exists('permissions', 'id')->where(function ($query) {
                    $query->where('active', true);
                }),
            ],
            'title'        => 'required|unique:roles,title'
        ]);

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';

        $role = Role::create([
            'title'       => $request->title,
            'description' => $request->description
        ]);

        $role->permissions()->attach($request->permissions);

        // دریافت نام دسترسی‌های انتخاب شده
        $permissionNames = Permission::whereIn('id', $request->permissions ?? [])->pluck('title')->toArray();

        // ساخت properties
        $properties = [
            'action' => 'create_role',
            'role_title' => $request->title,
            'attributes' => [
                'عنوان مقام' => $request->title,
                'توضیحات' => $request->description ?? 'ندارد',
                'دسترسی‌ها' => !empty($permissionNames) ? implode('، ', $permissionNames) : 'هیچ',
                'تعداد دسترسی‌ها' => count($permissionNames)
            ],
            'ip' => request()->ip()
        ];

        activity()
            ->performedOn($role)
            ->causedBy(auth('adminPanel')->user())
            ->event('created')
            ->withProperties($properties)
            ->log("مدیر {$adminName} مقام جدید «{$request->title}» را ایجاد کرد");

        session()->put('toast-success', 'مقام با موفقیت ایجاد شد.');
        return response('success');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::whereNull('permission_id')->where('active', true)->orderBy('ordering')->get();

        return view('back.roles.edit', compact('permissions', 'role'));
    }

    public function update(Role $role, Request $request)
    {
        $request->validate([
            'permissions'   => 'array',
            'permissions.*' => [
                Rule::exists('permissions', 'id')->where(function ($query) {
                    $query->where('active', true);
                }),
            ],
            'title'        => [
                'required',
                Rule::unique('roles', 'title')->ignore($role->id),
            ],
        ]);

        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';

        // ذخیره مقادیر قدیمی
        $oldTitle = $role->title;
        $oldDescription = $role->description;
        $oldPermissions = $role->permissions->pluck('id')->toArray();
        $oldPermissionNames = $role->permissions->pluck('title')->toArray();

        $role->update([
            'title'       => $request->title,
            'description' => $request->description
        ]);

        $role->permissions()->sync($request->permissions);

        // دریافت نام دسترسی‌های جدید
        $newPermissionNames = Permission::whereIn('id', $request->permissions ?? [])->pluck('title')->toArray();

        // پیدا کردن دسترسی‌های اضافه شده و حذف شده
        $addedPermissions = array_diff($request->permissions ?? [], $oldPermissions);
        $removedPermissions = array_diff($oldPermissions, $request->permissions ?? []);

        $addedNames = Permission::whereIn('id', $addedPermissions)->pluck('title')->toArray();
        $removedNames = Permission::whereIn('id', $removedPermissions)->pluck('title')->toArray();

        // ساخت old و attributes برای تغییرات
        $oldData = [];
        $newData = [];

        if ($oldTitle != $request->title) {
            $oldData['عنوان مقام'] = $oldTitle;
            $newData['عنوان مقام'] = $request->title;
        }

        if ($oldDescription != $request->description) {
            $oldData['توضیحات'] = $oldDescription ?: 'ندارد';
            $newData['توضیحات'] = $request->description ?: 'ندارد';
        }

        foreach ($removedNames as $name) {
            $oldData["دسترسی - {$name}"] = 'فعال';
            $newData["دسترسی - {$name}"] = 'غیرفعال';
        }

        foreach ($addedNames as $name) {
            $oldData["دسترسی - {$name}"] = 'غیرفعال';
            $newData["دسترسی - {$name}"] = 'فعال';
        }

        $properties = [
            'action' => 'update_role',
            'role_id' => $role->id,
            'old_title' => $oldTitle,
            'new_title' => $request->title,
            'ip' => request()->ip()
        ];

        if (!empty($oldData)) {
            $properties['old'] = $oldData;
            $properties['attributes'] = $newData;
        }

        activity()
            ->performedOn($role)
            ->causedBy(auth('adminPanel')->user())
            ->event('updated')
            ->withProperties($properties)
            ->log("مدیر {$adminName} مقام «{$oldTitle}» را ویرایش کرد");

        session()->put('toast-success', 'مقام با موفقیت ویرایش شد.');
        return response('success');
    }

    public function destroy(Role $role)
    {
        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';
        $roleTitle = $role->title;
        $permissionNames = $role->permissions->pluck('title')->toArray();

        $role->permissions()->detach();
        $role->delete();

        $properties = [
            'action' => 'delete_role',
            'role_title' => $roleTitle,
            'attributes' => [
                'عنوان مقام' => $roleTitle,
                'دسترسی‌های مرتبط' => !empty($permissionNames) ? implode('، ', $permissionNames) : 'هیچ',
                'تعداد دسترسی‌ها' => count($permissionNames)
            ],
            'ip' => request()->ip()
        ];

        activity()
            ->causedBy(auth('adminPanel')->user())
            ->event('deleted')
            ->withProperties($properties)
            ->log("مدیر {$adminName} مقام «{$roleTitle}» را حذف کرد");

        return response('success');
    }
}
