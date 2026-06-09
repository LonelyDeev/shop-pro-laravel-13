<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Datatable\Admin\AdminCollection;
use App\Models\Role;
use App\Rules\NotSpecialChar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Admin::class, 'admin');
    }

    public function index()
    {
        return view('back.admins.index');
    }

    public function apiIndex(Request $request)
    {
        $this->authorize('admins.index');

        $users = Admin::filter($request);
        $users = datatable($request, $users);

        return new AdminCollection($users);
    }

    public function create()
    {
        $roles = Role::latest()->get();

        return view('back.admins.create', compact('roles'));
    }

    public function edit(Admin $admin)
    {
        $roles = Role::latest()->get();

        return view('back.admins.edit', compact('admin', 'roles'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => ['required', 'string', 'max:255', new NotSpecialChar()],
            'last_name'  => ['required', 'string', 'max:255', new NotSpecialChar()],
            'username'   => ['required', 'string', 'unique:admins'],
            'email'      => ['string', 'email', 'max:255', 'unique:admins', 'nullable'],
            'password'   => ['required', 'string', 'confirmed'],
            'roles'      => 'nullable|array',
            'roles.*'    => 'exists:roles,id'
        ]);

        $admin = Admin::create([
            'first_name'  => $request->first_name,
            'last_name'   => $request->last_name,
            'username'    => $request->username,
            'email'       => $request->email,
            'level'       => "admin",
            'password'    => Hash::make($request->password),
            'verified_at' => $request->verified_at ? Carbon::now() : null,
        ]);

        if ($request->hasFile('image')) {
            $admin->image = uploadOptimizedImage($request->image, 'admins');
            $admin->save();
        }

        $admin->roles()->attach($request->roles);

        session()->put('toast-success','مدیر با موفقیت ایجاد شد.');
        return response('success');
    }

    public function update(Admin $admin, Request $request)
    {
        $this->validate($request, [
            'first_name' => ['required', 'string', 'max:255', new NotSpecialChar()],
            'last_name'  => ['required', 'string', 'max:255', new NotSpecialChar()],
            'username'   => ['required', 'string', "unique:admins,username,$admin->id"],
            'email'      => ['string', 'email', 'max:255', "unique:admins,email,$admin->id", 'nullable'],
            'password'   => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles'      => 'nullable|array',
            'roles.*'    => 'exists:roles,id'
        ]);

        $verified_at = $admin->verified_at ?: Carbon::now();

        $admin->update([
            'first_name'  => $request->first_name,
            'last_name'   => $request->last_name,
            'username'    => $request->username,
            'email'       => $request->email,
            'verified_at' => $request->verified_at ? $verified_at : null,
        ]);

        if ($request->password) {
            $password = Hash::make($request->password);

            $admin->update([
                'password' => $password
            ]);

            DB::table('sessions')->where('user_id', $admin->id)->delete();
        }

        if ($request->hasFile('image')) {

            $admin->image =  uploadOptimizedImage($request->image, 'admins',$admin->id);
            $admin->save();
        }

        $admin->roles()->sync($request->roles);

        session()->put('toast-success','مدیر با موفقیت ویرایش شد.');
        return response('success');
    }

    public function show(Admin $admin)
    {
        return view('back.admins.show', compact('admin'));
    }

    public function destroy(Admin $admin, $multiple = false)
    {
        if ($admin->image) {
            Storage::disk('public')->delete($admin->image);
        }

        $admin->delete();

        if (!$multiple) {
            session()->put('toast-success','کاربر با موفقیت حذف شد.');
        }

        return response('success');
    }

    public function multipleDestroy(Request $request)
    {
        $this->authorize('admins.delete');

        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => [
                Rule::exists('admins', 'id')->where(function ($query) {
                    $query->where('id', '!=', auth('adminPanel')->user()->id)->where('level', '!=', 'creator');
                })
            ]
        ]);
        foreach ($request->ids as $id) {
            $admin = Admin::find($id);
            $this->destroy($admin, true);
        }

        return response('success');
    }


    public function views(Admin $admin)
    {
        $views = $admin->views()->latest()->paginate(20);

        return view('back.admins.views', compact('views', 'admin'));
    }

    public function showProfile()
    {
        return view('back.admins.profile');
    }

    public function updateProfile(Request $request)
    {
        $admin = auth('adminPanel')->user();
        $this->validate($request, [
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'username' => 'required|string|max:191',

            'instagram' => 'nullable|url|regex:/^(https?:\/\/)?(www\.)?instagram\.com\/[A-Za-z0-9_.]+\/?$/|max:255',
            'twitter' => 'nullable|url|regex:/^(https?:\/\/)?(www\.)?twitter\.com\/[A-Za-z0-9_]+\/?$/|max:255',
            'facebook' => 'nullable|url|regex:/^(https?:\/\/)?(www\.)?facebook\.com\/[A-Za-z0-9.]+\/?$/|max:255',

            // شماره واتساپ (فرمت بین‌المللی)
            'whatsapp' => 'nullable|string|regex:/^[\+]?[0-9]{10,15}$/|max:20',

            // آیدی (بدون @)
            'telegram' => 'nullable|string|regex:/^[A-Za-z][A-Za-z0-9_]{4,31}$/|max:32',
            'eitaa' => 'nullable|string|regex:/^[A-Za-z0-9_]{3,30}$/|max:30',
            'rubika' => 'nullable|string|regex:/^[A-Za-z0-9_]{3,30}$/|max:30',
            'bale' => 'nullable|string|regex:/^[A-Za-z0-9_]{3,30}$/|max:30',
        ]);

        if ($request->password || $request->password_confirmation) {
            $this->validate($request, [
                'password' => 'required|min:6|confirmed',
                'password_confirmation' => 'required',
            ]);

            $admin->password = Hash::make($request->password);
        }

        if ($request->hasFile('image')) {
            $this->validate($request, [
                'image' => 'image|max:2048',
            ]);

            $imageName = time() . '_' . $admin->id . '.' . $request->image->getClientOriginalExtension();
            $request->image->move(public_path('uploads/admins/'), $imageName);

            if ($admin->image && file_exists(public_path($admin->image))) {
                unlink(public_path($admin->image));
            }

            $admin->image = '/uploads/admins/' . $imageName;
        }

        $admin->first_name = $request->first_name;
        $admin->last_name = $request->last_name;
        $admin->username = $request->username;
        $admin->bio = $request->bio;

        $admin->instagram = $request->instagram;
        $admin->whatsapp = $request->whatsapp;
        $admin->eitaa = $request->eitaa;
        $admin->telegram = $request->telegram;
        $admin->twitter = $request->twitter;
        $admin->facebook = $request->facebook;
        $admin->rubika = $request->rubika;
        $admin->bale = $request->bale;


        $admin->save();

        if ($request->password) {
            DB::table('sessions')->where('admin_id', auth('adminPanel')->user()->id)->delete();
        }

        $options = $request->only([
            'theme_color',
            'theme_font',
            'menu_type'
        ]);

        foreach ($options as $option => $value) {
            user_option_update($option, $value, auth('adminPanel')->user()->id);
        }

        return response()->json('success');
    }
}
