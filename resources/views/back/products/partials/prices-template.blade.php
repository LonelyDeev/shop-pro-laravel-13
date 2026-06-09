<script id="prices-template" type="text/x-custom-template">
    <div class="row animated fadeIn single-price">
        <div class="col-12">
            <div class="row">
                {{-- انتخاب انبار --}}
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>انبار <span class="text-danger">*</span></label>
                        <select class="form-control warehouses-attribute-select" name="warehouse" required>
                            <option value="">انتخاب انبار</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}"
                                        data-code="{{ $warehouse->code }}"
                                        data-type="{{ $warehouse->type }}">
                                    {{ $warehouse->name }} ({{ $warehouse->code }})
                                    @if($warehouse->type == 'main')
                                        - انبار اصلی
                                    @elseif($warehouse->type == 'seller')
                                        - انبار فروشنده
                                    @else
                                        - انبار موقت
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">انبار مورد نظر برای نگهداری این محصول را انتخاب کنید</small>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-12">
            <div class="row">
                @foreach ($attributeGroups as $attributeGroup)
                    <div class="col-md-3 col-12">
                        <div class="form-group">
                            <label>{{ $attributeGroup->name }}</label>
                            <select class="form-control price-attribute-select" name="attribute"
                                    data-group-type="{{ $attributeGroup->type }}"
                                    data-group-id="{{ $attributeGroup->id }}">
                                <option value="">انتخاب کنید</option>
                                @foreach ($attributeGroup->get_attributes as $attribute)
                                    <option value="{{ $attribute->id }}"
                                            data-color="{{ $attribute->value ?? '#000000' }}"
                                            data-is-color="{{ $attributeGroup->type == 'color' ? 'true' : 'false' }}"
                                    >{{ $attribute->name }}</option>
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
                <input type="number" data-unit="تومان" class="form-control amount-input price" name="price" required>
            </div>
        </div>

        <div class="col-md-3 col-12">
            <div class="form-group">
                <label>تخفیف(درصد)</label>
                <input type="number" class="form-control discount" name="discount" min="0" max="100" placeholder="%">
            </div>
        </div>
        <div class="col-md-3 col-12">
            <div class="form-group">
                <label>زمان انقضای تخفیف</label>
                <input type="text" class="form-control discount_expire_at persian-date-picker" data-timestamps="true" name="discount_expire_at">
            </div>
        </div>
        <div class="col-md-3 col-12">
            <div class="form-group">
                <label>بیشترین تعداد مجاز در هر سفارش</label>
                <input type="number" class="form-control" name="cart_max" min="1">
            </div>
        </div>
        <div class="col-md-3 col-12">
            <div class="form-group">
                <label>کمترین تعداد مجاز در هر سفارش</label>
                <input type="number" class="form-control" name="cart_min" min="1">
            </div>
        </div>
        <div class="col-md-3 col-12">
            <div class="form-group">
                <label>موجودی انبار
                    <span class="required-star" style="color:red;">*</span>
                </label>
                <input type="number" class="form-control" name="stock" min="0" required>
            </div>
        </div>

        <div class="col-md-3 col-12">
            <div class="form-group">
                <label>وضعیت</label>
                <select class="form-control" name="published">
                    <option value="1">نمایش</option>
                    <option value="0">عدم نمایش</option>

                </select>
            </div>
        </div>
        <div class="col-md-3 col-12">
            <div class="form-group">
                <label>قیمت نهایی</label>
                <input type="text" class="form-control final-price" disabled>
            </div>
        </div>

        @if(option('multi_vendor_system_status','false')=="true")
        <div class="col-md-6">
            <div class="form-group">
                <label>فروشنده</label>
                <input type="hidden" class="form-control seller-input" name="seller_id" value="" >
                <select class="form-control sellers" name="attribute">
                    <option value="">انتخاب کنید</option>
                    @foreach ($sellers as $seller)
                        <option value="{{ $seller->id }}"> {{$seller->id.'<=id' }} {{ $seller->seller_info->business_name ?: $seller->seller_info->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-flat-danger waves-effect waves-light remove-product-price custom-padding">حذف<i class="fa fa-trash text-danger"></i></button>
        </div>

        <div class="col-md-12"><hr></div>
    </div>
</script>
