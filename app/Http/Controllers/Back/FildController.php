<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Fild;
use Illuminate\Http\Request;

class FildController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:filds');
    }

    public function index()
    {
        $items = Fild::orderBy('created_at', 'desc')->paginate(15);
        return view('back.filds.index', compact('items'));
    }

    public function create(Request $request)
    {
        $this->authorize('filds.create');
        return view('back.filds.create');
    }

    public function store(Request $request)
    {
        $this->authorize('filds.create');
        $this->validate($request, [
            'title'          => 'required|max:255',
            'belongs_to'     => 'required|in:users,products,posts',
            'type'        => 'required|in:input,textarea,number,email,colorPicker,checkbox,select',
        ]);

        $published = false;
        if ($request->has('published')) {
            $published = true;
        }
        $required = false;
        if ($request->has('required')) {
            $required = true;
        }
        $user_show = false;
        if ($request->has('user_show')) {
            $user_show = true;
        }

        $item = new Fild();
        $item->title = $request->title;
        $item->belongs_to = $request->belongs_to;
        $item->type = $request->type;
        $item->value = $request->value;

        $item->select_options = $request->select_options;
        $item->published = $published;
        $item->required = $required;
        $item->user_show = $user_show;
        $item->save();


        session()->put('toast-success', 'فیلد با موفقیت ایجاد شد.');
        return response("success");
    }

    public function edit(Request $request,Fild $fild)
    {
        $this->authorize('filds.update');
        return view('back.filds.edit',compact('fild'));
    }

    public function update(Request $request,Fild $fild)
    {
        $this->authorize('filds.update');
        $this->validate($request, [
            'title'          => 'required|max:255',
            'belongs_to'     => 'required|in:users,products,posts',
            'type'        => 'required|in:input,textarea,number,email,colorPicker,checkbox,select',
        ]);

        $published = false;
        if ($request->has('published')) {
            $published = true;
        }
        $required = false;
        if ($request->has('required')) {
            $required = true;
        }
        $user_show = false;
        if ($request->has('user_show')) {
            $user_show = true;
        }

        $fild->title = $request->title;
        $fild->belongs_to = $request->belongs_to;
        $fild->type = $request->type;
        $fild->value = $request->value;

        $fild->select_options = $request->select_options;

        $fild->published = $published;
        $fild->required = $required;
        $fild->user_show = $user_show;
        $fild->save();


        session()->put('toast-success', 'فیلد با موفقیت ویرایش شد.');
        return response("success");
    }

    public function multipleDestroy(Request $request)
    {
        $this->authorize('filds.delete');

        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:filds,id',
        ]);

        foreach ($request->ids as $id) {
            $product = Fild::find($id);
            $this->destroy($product);
        }

        return response('success');
    }

    public function destroy(Fild $fild)
    {
        $this->authorize('filds.delete');
        $fild->delete();
    }
}
