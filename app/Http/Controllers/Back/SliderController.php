<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        return view('back.sliders.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'image' => 'required|max:2048',
            'group' => 'required'
        ]);

        /* $file = $request->image;
         $name = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
         $request->image->storeAs('sliders', $name);*/

        Slider::create([
            'title'       => $request->title,
            'page'       => $request->page,
            'link'        => $request->link,
            'motionTitle'        => $request->motionTitle,
            'group'       => $request->group,
            'description' => $request->description,
            'published'   => $request->published ? true : false,
            'image'       => $request->image
        ]);

        session()->put('toast-success','اسلایدر با موفقیت ایجاد شد.');
        return response("success");
    }

    public function edit(Slider $slider)
    {
        return view('back.sliders.edit', compact('slider'));
    }

    public function update(Slider $slider, Request $request)
    {
        $this->validate($request, [
            'image' => 'required|max:2048',
            'group' => 'required'
        ]);

        $slider->update([
            'title'       => $request->title,
            'page'       => $request->page,
            'link'        => $request->link,
            'motionTitle'        => $request->motionTitle,
            'group'       => $request->group,
            'description' => $request->description,
            'published'   => $request->published ? true : false,
            'image'       => $request->image
        ]);

        session()->put('toast-success','اسلایدر با موفقیت ویرایش شد.');
        return response("success");
    }


    public function destroy(Slider $slider)
    {
      /*  if ($slider->image) {
            Storage::disk('public')->delete($slider->image);
        }*/

        $slider->delete();

        return response('success');
    }

    public function sort(Request $request)
    {
        $this->authorize('sliders.update');

        $this->validate($request, [
            'sliders' => 'required|array'
        ]);

        $i = 1;

        foreach ($request->sliders as $slider) {
            Slider::findOrFail($slider)->update([
                'ordering' => $i++,
            ]);
        };

        return response('success');
    }
}
