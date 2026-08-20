<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Banner::class, 'banner');
    }

    public function index()
    {
        $banners = Banner::orderBy('ordering')->get();

        return view('back.banners.index', compact('banners'));
    }

    public function create()
    {
        $groups = Banner::availableGroups();
        $places = Banner::availablePlaces();

        return view('back.banners.create', compact('groups', 'places'));
    }

    public function store(Request $request)
    {
        $this->validateBanner($request);

        Banner::create([
            'title'       => $request->title,
            'pages'       => $request->pages,
            'groups'      => $request->groups,
            'places'      => $request->places,
            'link'        => $request->link,
            'description' => $request->description,
            'published'   => $request->boolean('published'),
            'image'       => $request->image,
        ]);

        session()->put('toast-success', 'بنر با موفقیت ایجاد شد.');

        return response('success');
    }

    public function edit(Banner $banner)
    {
        $groups = Banner::availableGroups();
        $places = Banner::availablePlaces();

        return view('back.banners.edit', compact('banner', 'groups', 'places'));
    }

    public function update(Banner $banner, Request $request)
    {
        $this->validateBanner($request);

        $banner->update([
            'title'       => $request->title,
            'pages'       => $request->pages,
            'groups'      => $request->groups,
            'places'      => $request->places,
            'link'        => $request->link,
            'description' => $request->description,
            'published'   => $request->boolean('published'),
            'image'       => $request->image,
        ]);

        session()->put('toast-success', 'بنر با موفقیت ویرایش شد.');

        return response('success');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();

        return response('success');
    }

    public function sort(Request $request)
    {
        $this->authorize('banners.update');

        $this->validate($request, [
            'banners' => 'required|array',
        ]);

        $i = 1;

        foreach ($request->banners as $banner) {
            Banner::findOrFail($banner)->update([
                'ordering' => $i++,
            ]);
        }

        return response('success');
    }

    private function validateBanner(Request $request): array
    {
        return $request->validate([
            'image'       => 'required|max:2048',
            'pages'       => 'required|array|min:1',
            'pages.*'     => 'required|string|in:' . implode(',', array_keys(Banner::availablePages())),
            'groups'      => 'required|array|min:1',
            'groups.*'    => 'required|string|in:' . implode(',', array_keys(Banner::availableGroups())),
            'places'      => 'required|array|min:1',
            'places.*'    => 'required|string|in:' . implode(',', array_keys(Banner::availablePlaces())),
            'title'       => 'nullable|string|max:255',
            'link'        => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'published'   => 'nullable|boolean',
        ], [
            'image.required'  => 'انتخاب تصویر بنر الزامی است.',
            'image.max'       => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.',
            'pages.required'  => 'انتخاب حداقل یک صفحه الزامی است.',
            'pages.min'       => 'باید حداقل یک صفحه را انتخاب کنید.',
            'pages.*.in'      => 'صفحه‌ی انتخاب شده معتبر نیست.',
            'groups.required' => 'انتخاب حداقل یک گروه الزامی است.',
            'groups.min'      => 'باید حداقل یک گروه را انتخاب کنید.',
            'groups.*.in'     => 'گروه انتخاب شده معتبر نیست.',
            'places.required' => 'انتخاب حداقل یک موقعیت الزامی است.',
            'places.min'      => 'باید حداقل یک موقعیت را انتخاب کنید.',
            'places.*.in'     => 'موقعیت انتخاب شده معتبر نیست.',
        ]);
    }
}
