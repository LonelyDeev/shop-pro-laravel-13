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
                <form id="filter-products-form" method="GET"
                      action="{{ $filter_action }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label> نام فروشگاه یا ID فروشنده</label>
                            <fieldset class="form-group">
                                <input class="form-control datatable-filter" name="name" value="{{ request('name') }}">
                            </fieldset>
                        </div>
                        <div class="col-md-3">
                            <label>کد ملی یا شماره موبایل</label>
                            <fieldset class="form-group">
                                <input type="number" class="form-control datatable-filter" name="national_number" value="{{ request('national_number') }}">
                            </fieldset>
                        </div>

                        <div class="col-md-3">
                            <label>وضعیت مدارک</label>
                            <fieldset class="form-group">
                                <select class="form-control datatable-filter" name="status_documents">
                                    <option value="all" {{ request('status_documents') == 'all' ? 'selected' : '' }}>
                                        همه
                                    </option>
                                    <option value="Accept" {{ request('status_documents') == 'Accept' ? 'selected' : '' }}>
                                        تایید شده
                                    </option>
                                    <option value="Waiting" {{ request('status_documents') == 'Waiting' ? 'selected' : '' }}>
                                        در انتظار تایید
                                    </option>
                                    <option value="Reject" {{ request('status_documents') == 'Reject' ? 'selected' : '' }}>
                                        تایید نشده
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
