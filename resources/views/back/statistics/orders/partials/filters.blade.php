<div class="card">
    <div class="card-header filter-card">
        <h4 class="card-title">فیلتر کردن</h4>
        <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
        <div class="heading-elements">
            <ul class="list-inline mb-0">
                <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="card-content collapse {{ request()->except('page') ? 'show' : '' }}">
        <div class="card-body">
            <div class="users-list-filter">
                <form id="filter-orders-form" method="GET">
                    <div class="row">

                        <div class="col-md-3">
                            <label>نام محصول</label>
                            <fieldset class="form-group">
                                <input class="form-control datatable-filter" name="product_name" value="{{ request('product_name') }}">
                            </fieldset>
                        </div>

                        <div class="col-md-2">
                            <label class="pre-space" for="from">از تاریخ : </label>
                            <div class="form-group">
                                <input class="form-control persian-date-picker" name="from_date" type="text">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="pre-space" for="from">تا تاریخ : </label>
                            <div class="form-group">
                                <input class="form-control persian-date-picker" name="to_date" type="text">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label>مرتب سازی</label>
                            <fieldset class="form-group">
                                <select class="form-control" name="ordering">
                                    <option value="newest" {{ request('ordering') == 'newest' ? 'selected' : '' }}>
                                        جدیدترین
                                    </option>
                                    <option value="oldest" {{ request('ordering') == 'oldest' ? 'selected' : '' }}>
                                        قدیمی ترین
                                    </option>

                                    <option value="most_sold" {{ request('ordering') == 'most_sold' ? 'selected' : '' }}>
                                        پرفروش ترین
                                    </option>

                                    <option value="least_sold" {{ request('ordering') == 'least_sold' ? 'selected' : '' }}>
                                        کم فروش ترین
                                    </option>
                                </select>
                            </fieldset>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
