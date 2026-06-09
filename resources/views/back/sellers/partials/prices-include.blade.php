<div class="row single-price">

    @if($price->seller_id)
        <div class="abilityPostToolTip w-100 mb-2">
            <i>
                <svg class="icon">
                    <use xlink:href="#lamp">
                        <symbol id="lamp" enable-background="new 0 0 24 24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="m12 3.457c-.414 0-.75-.336-.75-.75v-1.957c0-.414.336-.75.75-.75s.75.336.75.75v1.957c0 .414-.336.75-.75.75z"></path>
                            <path
                                d="m18.571 6.179c-.192 0-.384-.073-.53-.22-.293-.293-.293-.768 0-1.061l1.384-1.384c.293-.293.768-.293 1.061 0s.293.768 0 1.061l-1.384 1.384c-.147.146-.339.22-.531.22z"></path>
                            <path
                                d="m23.25 12.75h-1.957c-.414 0-.75-.336-.75-.75s.336-.75.75-.75h1.957c.414 0 .75.336.75.75s-.336.75-.75.75z"></path>
                            <path
                                d="m19.955 20.705c-.192 0-.384-.073-.53-.22l-1.384-1.384c-.293-.293-.293-.768 0-1.061s.768-.293 1.061 0l1.384 1.384c.293.293.293.768 0 1.061-.147.147-.339.22-.531.22z"></path>
                            <path
                                d="m4.045 20.705c-.192 0-.384-.073-.53-.22-.293-.293-.293-.768 0-1.061l1.384-1.384c.293-.293.768-.293 1.061 0s.293.768 0 1.061l-1.384 1.384c-.147.147-.339.22-.531.22z"></path>
                            <path
                                d="m2.707 12.75h-1.957c-.414 0-.75-.336-.75-.75s.336-.75.75-.75h1.957c.414 0 .75.336.75.75s-.336.75-.75.75z"></path>
                            <path
                                d="m5.429 6.179c-.192 0-.384-.073-.53-.22l-1.384-1.384c-.293-.293-.293-.768 0-1.061s.768-.293 1.061 0l1.384 1.384c.293.293.293.768 0 1.061-.148.146-.339.22-.531.22z"></path>
                            <path d="m15 21v1.25c0 .96-.79 1.75-1.75 1.75h-2.5c-.84 0-1.75-.64-1.75-2.04v-.96z"></path>
                            <path
                                d="m16.41 6.56c-1.64-1.33-3.8-1.85-5.91-1.4-2.65.55-4.8 2.71-5.35 5.36-.56 2.72.46 5.42 2.64 7.07.59.44 1 1.12 1.14 1.91v.01c.02-.01.05-.01.07-.01h6c.02 0 .03 0 .05.01v-.01c.14-.76.59-1.46 1.28-2 1.69-1.34 2.67-3.34 2.67-5.5 0-2.12-.94-4.1-2.59-5.44zm-.66 5.94c-.41 0-.75-.34-.75-.75 0-1.52-1.23-2.75-2.75-2.75-.41 0-.75-.34-.75-.75s.34-.75.75-.75c2.34 0 4.25 1.91 4.25 4.25 0 .41-.34.75-.75.75z"></path>
                            <path d="m8.93 19.5h.07c-.02 0-.05 0-.07.01z"></path>
                            <path d="m15.05 19.5v.01c-.02-.01-.03-.01-.05-.01z"></path>
                        </symbol>
                    </use>
                </svg>
            </i>
            <p class="m-0">تنوع فروشنده می باشد.</p>
        </div>
    @endif

    <div class="col-12">
        <div class="row">

            @foreach ($attributeGroups as $attributeGroup)
                <div class="col-md-3 col-12">
                    <div class="form-group">
                        <label>{{ $attributeGroup->name }}</label>
                        <select class="form-control price-attribute-select" name="prices[{{ $loop->parent->iteration }}][attributes][]">
                            <option value="">انتخاب کنید</option>
                            @foreach ($attributeGroup->get_attributes as $attribute)
                                <option value="{{ $attribute->id }}" {{ $price->get_attributes()->find($attribute->id) ? 'selected' : '' }}>{{ $attribute->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-md-3 col-12">
        <div class="form-group">
            <label>قیمت
                <span class="required-star" style="color:red;">*</span>
            </label>
            <input type="number" data-unit="تومان" class="form-control amount-input price" name="prices[{{ $loop->iteration }}][price]" value="{{ $price->price() }}" required>
        </div>
    </div>

    <div class="col-md-3 col-12">
        <div class="form-group">
            <label>تخفیف</label>
            <input type="number" class="form-control discount" name="prices[{{ $loop->iteration }}][discount]" value="{{ $price->discount }}" min="0" max="100" placeholder="%">
        </div>
    </div>

    <div class="col-md-3 col-12">
        <div class="form-group">
            <label>بیشترین تعداد مجاز در هر سفارش</label>
            <input type="number" class="form-control" name="prices[{{ $loop->iteration }}][cart_max]" value="{{ $price->cart_max }}" min="1">
        </div>
    </div>
    <div class="col-md-3 col-12">
        <div class="form-group">
            <label>کمترین تعداد مجاز در هر سفارش</label>
            <input type="number" class="form-control" name="prices[{{ $loop->iteration }}][cart_min]" value="{{ $price->cart_min }}" min="1">
        </div>
    </div>
    <div class="col-md-3 col-12">
        <div class="form-group">
            <label>موجودی انبار
                <span class="required-star" style="color:red;">*</span>
            </label>
            <input type="number" class="form-control" name="prices[{{ $loop->iteration }}][stock]" value="{{ $price->stock }}" min="0" required>
        </div>
    </div>
    <div class="col-md-3 col-12">
        <div class="form-group">
            <label>قیمت نهایی</label>
            <input type="text" class="form-control final-price" disabled>
        </div>
    </div>
    <div class="col-md-3 col-12">
        <div class="form-group">
            <label>وضعیت</label>
            <select class="form-control" name="prices[{{ $loop->iteration }}][published]">
                <option @if($price->published==1) selected @endif value="1">نمایش</option>
                <option @if($price->published==0) selected @endif value="0">عدم نمایش</option>

            </select>
        </div>
    </div>


    <div class="col-md-6">
        <div class="form-group">
            <label>فروشنده</label>
            <input type="hidden" class="form-control seller-input" name="prices[{{ $loop->iteration }}][seller_id]" value="{{ $price->seller_id }}" >

            <select class="form-control sellers" data-loop="{{ $loop->iteration }}" name="prices[{{ $loop->iteration }}][attributes][]">
                <option value="">انتخاب کنید</option>
                @foreach ($sellers as $seller)
                    <option {{ ($seller->id == $price->seller_id) ? 'selected' : '' }} value="{{ $seller->id }}"> {{$seller->id.'<=id' }} {{ $seller->seller_info->full_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>




    <div class="col-md-12 text-right">
        <button type="button" class="btn btn-flat-danger waves-effect waves-light remove-product-price custom-padding">حذف<i class="fa fa-trash text-danger"></i></button>
    </div>

    <div class="col-md-12"><hr></div>
</div>
