<?php

namespace App\Http\Controllers\Back;

use App\Models\Banner;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Banner::class, 'banner');
    }

    public function index()
    {
        $banners = Banner::detectLang()->orderBy('ordering')->get();

        return view('back.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('back.banners.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'image' => 'required|max:2048',
            'group' => 'required',
            'title' => 'nullable',
            'description' => 'nullable',
        ]);

        Banner::create([
            'page' => $request->page,
            'link' => $request->link,
            'group' => $request->group,
            'place' => $request->place,
            'published' => $request->published ? true : false,
            'image' => $request->image,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        session()->put('toast-success','بنر با موفقیت ایجاد شد.');
        return response("success");
    }

    public function edit(Banner $banner)
    {
        return view('back.banners.edit', compact('banner'));
    }

    public function update(Banner $banner, Request $request)
    {
        $this->validate($request, [
            'image' => 'required|max:2048',
            'group' => 'required'
        ]);

        $banner->update([
            'page' => $request->page,
            'link' => $request->link,
            'group' => $request->group,
            'place' => $request->place,
            'published' => $request->published ? true : false,
            'title' => $request->title,
            'image' => $request->image,
            'description' => $request->description,
        ]);


        session()->put('toast-success','بنر با موفقیت ویرایش شد.');
        return response("success");
    }

    public function destroy(Banner $banner)
    {
       /* if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }*/

        $banner->delete();

        return response('success');
    }

    public function sort(Request $request)
    {
        $this->authorize('banners.update');

        $this->validate($request, [
            'banners' => 'required|array'
        ]);

        $i = 1;

        foreach ($request->banners as $banner) {
            Banner::findOrFail($banner)->update([
                'ordering' => $i++,
            ]);
        };

        return response('success');
    }
}
