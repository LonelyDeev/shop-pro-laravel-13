<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Size;
use App\Models\SizeType;
use Illuminate\Http\Request;

class SizeTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:products.sizetypes');
    }
    public function index()
    {
        $sizetypes = SizeType::detectLang()->latest()->paginate(15);

        return view('back.sizetypes.index', compact('sizetypes'));
    }

    public function show(SizeType $sizetype)
    {
        return view('back.sizetypes.show', compact('sizetype'));
    }

    public function create()
    {
        return view('back.sizetypes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => ['required', 'unique:size_types,title'],
            'sizes'       => ['required'],
            'description' => 'nullable|string'
        ]);

        $sizetype = SizeType::create([
            'title'       => $request->title,
            'description' => $request->description
        ]);

        $ordering = 1;

        foreach ($request->sizes as $title) {
            $sizetype->sizes()->create([
                'title'    => $title,
                'ordering' => $ordering++
            ]);
        }

        session()->put('toast-success','راهنمای سایز با موفقیت ایجاد شد.');
        return response('success');
    }

    public function edit(SizeType $sizetype)
    {
        return view('back.sizetypes.edit', compact('sizetype'));
    }

    public function update(Request $request, SizeType $sizetype)
    {
        $request->validate([
            'title'       => ['required', 'unique:size_types,title,' . $sizetype->id],
            'sizes'       => ['required', 'array'],
            'description' => 'nullable|string'
        ]);

        $sizetype->update([
            'title'       => $request->title,
            'description' => $request->description,
        ]);

        $ordering = 1;

        $sizes = [];

        foreach ($request->sizes as $key => $title) {
            $id = $request->sizes_id[$key] ?? null;
            if ($id) {
                $size = $sizetype->sizes()->where('id', $id)->first();
                $size->update([
                    'title'    => $title,
                    'ordering' => $ordering++
                ]);
            } else {
                $size = $sizetype->sizes()->create([
                    'title'    => $title,
                    'ordering' => $ordering++
                ]);
            }

            $sizes[] = $size->id;
        }

        $sizetype->sizes()->whereNotIn('id', $sizes)->delete();

        session()->put('toast-success','راهنمای سایز با موفقیت ویرایش شد.');
        return response('success');
    }

    public function destroy(SizeType $sizetype)
    {
        $sizetype->delete();

        return response('success');
    }

    public function editValues(SizeType $sizetype)
    {
        return view('back.sizetypes.values', compact('sizetype'));
    }

    public function updateValues(Request $request, SizeType $sizetype)
    {
        $adminName = auth()->user()->full_name ?? auth()->user()->name ?? 'مدیر';
        $sizeTypeName = $sizetype->title ?? $sizetype->name ?? "#{$sizetype->id}";

        // ذخیره مقادیر قدیمی قبل از آپدیت
        $oldValues = [];
        $oldRelations = $sizetype->values()->get();

        foreach ($oldRelations as $relation) {
            //直接用 size_id بدون relationship
            $sizeId = $relation->size_id;
            $key = "گروه {$relation->pivot->group} - سایز {$sizeId}";
            $oldValues[$key] = $relation->pivot->value;
        }

        $sizetype->values()->detach();
        $ordering = 1;
        $groupordering = 1;

        $newValues = [];

        foreach ($request->values as $group => $values) {
            foreach ($values as $size_id => $value) {
                $sizetype->values()->attach(
                    [
                        $size_id => [
                            'group'    => $groupordering,
                            'value'    => $value,
                            'ordering' => $ordering++
                        ]
                    ]
                );

                // ذخیره مقادیر جدید برای لاگ
                $key = "گروه {$groupordering} - سایز {$size_id}";
                $newValues[$key] = $value;
            }
            $groupordering++;
        }

        // ساخت تغییرات با فرمت old و attributes
        $oldData = [];
        $newData = [];

        // بررسی تغییرات
        $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));

        foreach ($allKeys as $key) {
            $oldVal = $oldValues[$key] ?? 'ندارد';
            $newVal = $newValues[$key] ?? 'حذف شده';

            if ($oldVal != $newVal) {
                $oldData[$key] = $oldVal;
                $newData[$key] = $newVal;
            }
        }

        // ثبت لاگ
        if (!empty($oldData)) {
            activity()
                ->performedOn($sizetype)
                ->causedBy(auth()->user())
                ->event('updated')
                ->withProperties([
                    'action' => 'update_size_type_values',
                    'size_type_name' => $sizeTypeName,
                    'size_type_id' => $sizetype->id,
                    'old' => $oldData,
                    'attributes' => $newData,
                    'ip' => request()->ip()
                ])
                ->log("مدیر {$adminName} مقادیر راهنمای سایز «{$sizeTypeName}» را ویرایش کرد");
        } else {
            activity()
                ->performedOn($sizetype)
                ->causedBy(auth()->user())
                ->event('updated')
                ->withProperties([
                    'action' => 'update_size_type_values_no_change',
                    'size_type_name' => $sizeTypeName,
                    'size_type_id' => $sizetype->id,
                    'ip' => request()->ip()
                ])
                ->log("مدیر {$adminName} مقادیر راهنمای سایز «{$sizeTypeName}» را ویرایش کرد اما تغییری اعمال نشد");
        }

        session()->put('toast-success', 'مقادیر راهنمای سایز با موفقیت ویرایش شد.');
        return response('success');
    }
}
