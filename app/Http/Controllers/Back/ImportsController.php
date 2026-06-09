<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Imports\PostsImport;
use App\Imports\ProductsImport;
use App\Imports\UsersImport;
use App\Models\Post;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:imports');
    }
    public function postsExcelImport()
    {
        return view('back.imports.postsExcelImport');
    }
    public function postsExcelImport_Store(Request $request)
    {

        $this->authorize('imports.posts');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
            'filters.title' => 'required',
        ],[
            'filters.title.required'=>'فیلد عنوان الزامی است',
        ]);

        Excel::import(new PostsImport($request), $request->file('file')->store('tmp'));

        return response([
            'error'=>session('ImportError'),
            'success'=>session('ImportSuccess'),
        ]);
    }


    public function productsExcelImport()
    {
        return view('back.imports.productsExcelImport');
    }
    public function productsExcelImport_Store(Request $request)
    {
        $this->authorize('imports.products');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
            "filters.title"    => "required",
            "filters.price"    => "required",
            "filters.stock"    => "required",
        ],[
            'filters.title.required'=>'فیلد عنوان الزامی است',
            'filters.price.required'=>'فیلد قیمت الزامی است',
            'filters.stock.required'=>'فیلد موجودی انبار الزامی است',
        ]);

        Excel::import(new ProductsImport($request), $request->file('file')->store('tmp'));

        return response([
            'error'=>session('ImportError'),
            'success'=>session('ImportSuccess'),
        ]);
    }


    public function usersExcelImport()
    {
        return view('back.imports.usersExcelImport');
    }
    public function usersExcelImport_Store(Request $request)
    {

        $this->authorize('imports.users');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
            'filters.email' => 'required_without_all:filters.mobile',
            'filters.mobile' => 'required_without_all:filters.email',
            "filters.password"  => "required",
        ],[
            'filters.email.required_without_all'=>'یکی از فیلد های ایمیل یا موبایل الزامی است',
            'filters.mobile.required_without_all'=>'یکی از فیلد های ایمیل یا موبایل الزامی است',
            'filters.password.required'=>'فیلد رمز ورود الزامی است',
        ]);

        Excel::import(new UsersImport($request), $request->file('file')->store('tmp'));

        return response([
            'error'=>session('ImportError'),
            'success'=>session('ImportSuccess'),
        ]);
    }
}
