'use strict';
// Class definition

var datatable;

var product_datatable = (function () {
    // Private functions

    var options = {
        // datasource definition
        data: {
            type: 'remote',
            source: {
                read: {
                    url: $('#products_datatable').data('action'),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        )
                    },
                    map: function (raw) {
                        // sample data mapping

                        var dataSet = raw;
                        if (typeof raw.data !== 'undefined') {
                            dataSet = raw.data;
                        }
                        return dataSet;


                    },
                    params: {
                        query: $('#filter-products-form').serializeJSON()
                    }
                }
            },
            pageSize: 10,
            serverPaging: true,
            serverFiltering: true,
            serverSorting: true
        },

        layout: {
            scroll: true
        },

        rows: {
            autoHide: false
        },

        // columns definition
        columns: [
            {
                field: 'id',
                title: '#',
                sortable: false,
                width: 32,
                selector: {
                    class: ''
                },
                textAlign: 'center'
            },
            {
                field: 'sellerid',
                sortable: false,
                width: 35,
                title: 'ID',
                template: function (row) {
                    return row.id;
                }
            },
            {
                field: 'image',
                title: 'تصویر فروشگاه',
                sortable: false,
                width: 60,
                template: function (row) {
                    return (
                        '<img class="post-thumb" src="' +
                        row.image +
                        '" alt="' +
                        row.title +
                        '">'
                    );
                }
            },
            {
                field: 'business_name',
                title: 'نام فروشگاه',
                width: 60,
                template: function (row) {
                    return '<span class="float-left">'+row.business_name+'</span> ';
                }
            },
            {
                field: 'title',
                title: 'نام و نام خانوادگی',
                width: 94,
                template: function (row) {
                    return '<span class="float-left">'+row.full_name+'</span> ';
                }
            },
            {
                field: 'mobile',
                title: 'شماره',
                width: 88,
                template: function (row) {
                    return '<span class="float-left">'+row.mobile+'</span> ';
                }
            },
            {
                field: 'created_at',
                sortable: 'desc',
                title: 'تاریخ ثبت',
                width: 103,
                template: function (row) {
                    if(row.seller_id){
                        return '<span class="ltr">' + row.created_at + '</span>';
                    }else{
                        return '<span class="ltr">' + row.created_at + '</span>';
                    }

                }
            },
            {
                field: 'addableToCart',
                title: 'تعداد تنوع',
                textAlign: 'center',
                width: 56,
                // callback function support for column rendering
                template: function (row) {

                    return '<div class="text text-pill">'+row.variant_count+'</div>';
                }
            },
            {
                field: 'published',
                title: 'وضعیت',
                textAlign: 'center',
                width: 108,
                // callback function support for column rendering
                template: function (row) {


                    if (row.status_register=="complete") {
                        if (row.status_documents=="Accept"){
                            if (row.status=="ACTIVE"){
                                if (row.status_work=="ACTIVE"){
                                    return '<div class="badge badge-success badge-status-register " >فعال<span class="question-icon" onmouseenter="status_register_show(this)" onmouseleave="status_register_show(this)"><i class="fa-solid fa-question"></i></span></div>' +
                                        '<div class="status-register-show-sellers">' +
                                        '<span>وضعیت : <b class="badge badge-success">فعال</b></span><br>' +
                                        '<span>وضعیت کار: <b class="badge badge-success">فعال</b></span><br>' +
                                        '<span>وضعیت مدارک: <b class="badge badge-success">تایید شده</b></span>' +
                                        '</div>';
                                }else {
                                    return '<div class="badge badge-danger badge-status-register">توقف کار</div>';
                                }

                            }else {
                                return '<div class="badge badge-danger badge-status-register">غیر فعال</div>';
                            }
                        }else if (row.status_documents=="Waiting"){
                            return  '<div class="badge badge-warning badge-status-register">در انتظار تایید مدارک</div>';
                        }else {
                            return  '<div class="badge badge-danger badge-status-register">رد مدارک</div>';
                        }

                    }else{
                        return  '<div class="badge badge-danger badge-status-register">در حال ثبت نام (بارگذاری مدارک)</div>';
                    }



                }
            },
            {
                field: 'actions',
                title: 'عملیات',
                textAlign: 'center',
                sortable: false,
                width: 90,
                overflow: 'visible',
                autoHide: false,
                template: function (row) {
                    return (
                        `<div class="dropdown dropdown-action">
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu`+row.id+`" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu" aria-labelledby="dropdownMenu`+row.id+`">
                                                                <a class="dropdown-item" href="`+row.links.show +`"><i class="fa-regular fa-eye mr-1"></i>نمایش</a>
                                                                <div class="dropdown-divider"></div>

                                                                    <button class="dropdown-item btn-delete" data-toggle="modal" data-target="#delete-modal" data-action="`+row.links.destroy +
                        `"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>

                                                              </div>
                                                        </div>`
                    );
                }
            }
        ]
    };

    var initDatatable = function () {
        // enable extension
        options.extensions = {
            // boolean or object (extension options)
            checkbox: true
        };

        datatable = $('#products_datatable').KTDatatable(options);

        $('#filter-products-form .datatable-filter').on('change', function () {
            formDataToUrl('filter-products-form');
            datatable.setDataSourceQuery(
                $('#filter-products-form').serializeJSON()
            );
            datatable.reload();
        });

        datatable.on('datatable-on-click-checkbox', function (e) {
            var ids = datatable.checkbox().getSelectedId();
            var count = ids.length;

            $('#datatable-selected-rows').html(count);

            if (count > 0) {
                $('.datatable-actions').collapse('show');
            } else {
                $('.datatable-actions').collapse('hide');
            }
        });

        datatable.on('datatable-on-reloaded', function (e) {
            $('.datatable-actions').collapse('hide');
        });
    };

    return {
        // public functions
        init: function () {
            initDatatable();
        }
    };



})();

jQuery(document).ready(function () {
    product_datatable.init();
});

$(document).on('click', '.btn-delete', function () {
    $('#product-delete-form').attr('action', $(this).data('action'));
});
function status_register_show (item){
    $(item).parents('span').find('.status-register-show-sellers').toggleClass('active');
    $('.datatable-body').toggleClass('ps');
    $('.datatable-body').toggleClass('over-unset');
    $('.datatable-table').toggleClass('over-unset');
}
$('#product-delete-form').on('submit', function (e) {
    e.preventDefault();

    $('#delete-modal').modal('hide');

    var formData = new FormData(this);

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function (data) {
            toastr.success('فروشنده با موفقیت حذف شد.', null,{ positionClass: 'toast-bottom-left', containerId: 'toast-bottom-left' });
            datatable.reload();
        },
        beforeSend: function (xhr) {
            block('#main-card');
            xhr.setRequestHeader(
                'X-CSRF-TOKEN',
                $('meta[name="csrf-token"]').attr('content')
            );
        },
        complete: function () {
            unblock('#main-card');
        },
        cache: false,
        contentType: false,
        processData: false
    });
});

$('#product-multiple-delete-form').on('submit', function (e) {
    e.preventDefault();

    $('#multiple-delete-modal').modal('hide');

    var formData = new FormData(this);
    var ids = datatable.checkbox().getSelectedId();

    ids.forEach(function (id) {
        formData.append('ids[]', id);
    });

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function (data) {
            toastr.success('فروشنده های انتخاب شده با موفقیت حذف شدند.', null,{ positionClass: 'toast-bottom-left', containerId: 'toast-bottom-left' });
            datatable.reload();
        },
        beforeSend: function (xhr) {
            block('#main-card');
            xhr.setRequestHeader(
                'X-CSRF-TOKEN',
                $('meta[name="csrf-token"]').attr('content')
            );
        },
        complete: function () {
            unblock('#main-card');
        },
        cache: false,
        contentType: false,
        processData: false
    });
});

$('#products-export-form').on('submit', function (e) {
    e.preventDefault();

    let formData = datatable.getDataSourceParam();
    let queryString = $.param(formData);

    let formData2 = new FormData(this);
    let queryString2 = new URLSearchParams(formData2).toString();

    let url = `${$(this).attr('action')}?${queryString}&${queryString2}`;

    window.open(url);
});

