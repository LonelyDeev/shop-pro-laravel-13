<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class RedirectInternalController extends Controller
{
    public function index()
    {
        $this->authorize('redirects.index');
        $items = Redirect::orderBy('created_at', 'desc')->paginate(15);
        return view('back.redirects.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('redirects.create');
        return view('back.redirects.create');
    }

    public function store(Request $request)
    {
        $this->authorize('redirects.create');
        $validatedData = $request->validate([
            'from' => 'required|string|max:512|unique:redirects,from',
            'to'   => 'required|string|max:2048',
            'type' => 'required|integer|in:301,302,303,403,410,503',
        ], [
            'from.required' => 'آدرس مبدأ الزامی است.',
            'from.unique'   => 'این آدرس مبدأ قبلاً ثبت شده است.',
            'to.required'   => 'آدرس مقصد الزامی است.',
            'type.in'       => 'نوع ریدایرکت باید یکی از مقادیر 301, 302, 303, 403, 410, 503 باشد.',
        ]);

        Redirect::create($validatedData);
        session()->put('toast-success', ' با موفقیت اضافه شد.');
        return response()->json([
            'status'  => 'success',
            'message' => 'با موفقیت اضافه شد.',
        ]);
    }

    public function edit(Redirect $redirect)
    {
        $this->authorize('redirects.update');
        return view('back.redirects.edit', compact('redirect'));
    }

    public function update(Request $request, Redirect $redirect)
    {
        $this->authorize('redirects.update');
        $validatedData = $request->validate([
            'from' => 'required|string|max:512|unique:redirects,from,' . $redirect->id,
            'to'   => 'required|string|max:2048',
            'type' => 'required|integer|in:301,302,303,403,410,503',
        ], [
            'from.required' => 'آدرس مبدأ الزامی است.',
            'from.unique'   => 'این آدرس مبدأ قبلاً ثبت شده است.',
            'to.required'   => 'آدرس مقصد الزامی است.',
            'type.in'       => 'نوع ریدایرکت باید یکی از مقادیر 301, 302, 303, 403, 410, 503 باشد.',
        ]);

        $redirect->update($validatedData);

        session()->put('toast-success', 'با موفقیت ویرایش شد.');

        return response()->json([
            'status'  => 'success',
            'redirect'  => Route('admin.redirects.index'),
            'message' => 'با موفقیت ویرایش شد.',
        ]);
    }

    public function multipleDestroy(Request $request)
    {
        $this->authorize('redirects.delete');

        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:redirects,id',
        ]);

        foreach ($request->ids as $id) {
            $product = Redirect::find($id);
            $this->destroy($product);
        }

        return response('success');
    }

    public function destroy(Redirect $redirect)
    {
        $this->authorize('redirects.delete');
        $redirect->delete();
    }
}
