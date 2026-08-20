<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Slider::class, 'slider');
    }

    public function index()
    {
        $sliders = Slider::detectLang()->orderBy('ordering')->get();

        return view('back.sliders.index', compact('sliders'));
    }

    public function create()
    {
        $groups = Slider::availableGroups();

        return view('back.sliders.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $this->validateSlider($request);

        Slider::create([
            'title'       => $request->title,
            'pages'       => $request->pages,
            'groups'      => $request->groups,
            'link'        => $request->link,
            'motionTitle' => $request->motionTitle,
            'description' => $request->description,
            'published'   => $request->boolean('published'),
            'image'       => $request->image,
            'lang'       => app()->getLocale(),
        ]);

        session()->put('toast-success', 'اسلایدر با موفقیت ایجاد شد.');

        return response('success');
    }

    public function edit(Slider $slider)
    {
        $groups = Slider::availableGroups();

        return view('back.sliders.edit', compact('slider', 'groups'));
    }

    public function update(Slider $slider, Request $request)
    {
        $this->validateSlider($request);

        $slider->update([
            'title'       => $request->title,
            'pages'       => $request->pages,
            'groups'      => $request->groups,
            'link'        => $request->link,
            'motionTitle' => $request->motionTitle,
            'description' => $request->description,
            'published'   => $request->boolean('published'),
            'image'       => $request->image,
            'lang'       => app()->getLocale(),
        ]);

        session()->put('toast-success', 'اسلایدر با موفقیت ویرایش شد.');

        return response('success');
    }

    public function destroy(Slider $slider)
    {
        $slider->delete();

        return response('success');
    }

    public function sort(Request $request)
    {
        $this->authorize('sliders.update');

        $this->validate($request, [
            'sliders' => 'required|array',
        ]);

        $i = 1;

        foreach ($request->sliders as $slider) {
            Slider::findOrFail($slider)->update([
                'ordering' => $i++,
            ]);
        }

        return response('success');
    }

    private function validateSlider(Request $request): array
    {
        return $request->validate([
            'image'       => 'required|max:2048',
            'pages'       => 'required|array|min:1',
            'pages.*'     => 'required|string|in:' . implode(',', array_keys(Slider::availablePages())),
            'groups'      => 'required|array|min:1',
            'groups.*'    => 'required|string|in:' . implode(',', array_keys(Slider::availableGroups())),
            'title'       => 'nullable|string|max:255',
            'motionTitle' => 'nullable|string|max:255',
            'link'        => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'published'   => 'nullable|boolean',
        ], [
            'image.required'  => 'انتخاب تصویر اسلایدر الزامی است.',
            'image.max'       => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.',
            'pages.required'  => 'انتخاب حداقل یک صفحه الزامی است.',
            'pages.min'       => 'باید حداقل یک صفحه را انتخاب کنید.',
            'pages.*.in'      => 'صفحه‌ی انتخاب شده معتبر نیست.',
            'groups.required' => 'انتخاب حداقل یک گروه الزامی است.',
            'groups.min'      => 'باید حداقل یک گروه را انتخاب کنید.',
            'groups.*.in'     => 'گروه انتخاب شده معتبر نیست.',
        ]);
    }
}
