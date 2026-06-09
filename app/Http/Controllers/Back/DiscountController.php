<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Http\Requests\Back\Discount\StoreDiscountRequest;
use App\Http\Requests\Back\Discount\UpdateDiscountRequest;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;

class DiscountController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Discount::class, 'discount');
    }

    public function index()
    {
        $discounts = Discount::detectLang()->latest()->paginate(20);

        return view('back.discounts.index', compact('discounts'));
    }

    public function create()
    {
        $users      = User::latest()->get();
        $products   = Product::latest()->get();
        $categories = Category::where('type', 'productcat')->orderBy('ordering')->get();

        return view('back.discounts.create', compact(
            'users',
            'categories',
            'products'
        ));
    }

    public function store(StoreDiscountRequest $request)
    {
        $data = $request->validated();

        $data['start_date']=convertPersianToEnglish($data['start_date']);
        $data['end_date']=convertPersianToEnglish($data['end_date']);
        $data['amount']        = $data['type'] == 'amount' ? $data['price'] : $data['percent'];
        $data['start_date']    = Jalalian::fromFormat('Y-m-d H:i:s', $data['start_date'])->toCarbon();
        $data['end_date']      = Jalalian::fromFormat('Y-m-d H:i:s', $data['end_date'])->toCarbon();
        $data['lang']          = app()->getLocale();



        $discount = Discount::create($data);

        $this->updateRelations($discount, $request);

        session()->put('toast-success','تخفیف با موفقیت ایجاد شد.');
        return response('success');
    }

    public function edit(Discount $discount)
    {
        $users      = User::latest()->get();
        $products   = Product::latest()->get();
        $categories = Category::where('type', 'productcat')->orderBy('ordering')->get();

        return view('back.discounts.edit', compact('users', 'categories', 'products', 'discount'));
    }

    public function update(Discount $discount, UpdateDiscountRequest $request)
    {
        $data = $request->validated();

        $data['start_date']=convertPersianToEnglish($data['start_date']);
        $data['end_date']=convertPersianToEnglish($data['end_date']);

        $data['amount']         = $data['type'] == 'amount' ? $data['price'] : $data['percent'];
        $data['start_date']     = Jalalian::fromFormat('Y-m-d H:i:s', $data['start_date'])->toCarbon();
        $data['end_date']       = Jalalian::fromFormat('Y-m-d H:i:s', $data['end_date'])->toCarbon();

        $discount->update($data);

        $this->updateRelations($discount, $request);

        session()->put('toast-success','تخفیف با موفقیت ویرایش شد.');
        return response('success');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();

        return response('success');
    }

    private function updateRelations(Discount $discount, $request)
    {
        $discount->includeCategories()->detach();
        $discount->includeProducts()->detach();

        if ($request->include_type == 'category') {
            $discount->includeCategories()->attach($request->include_categories, ['type' => 'include']);
        }

        if ($request->exclude_type == 'category') {
            $discount->excludeCategories()->attach($request->exclude_categories, ['type' => 'exclude']);
        }

        if ($request->include_type == 'product') {
            $discount->includeProducts()->attach($request->include_products, ['type' => 'include']);
        }

        if ($request->exclude_type == 'product') {
            $discount->excludeProducts()->attach($request->exclude_products, ['type' => 'exclude']);
        }

        $discount->users()->sync($request->users);
    }
}
