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
                field: 'productid',
                sortable: false,
                width: 50,
                title: 'ID',
                template: function (row) {
                    return row.id;
                }
            },
            {
                field: 'image',
                title: 'تصویر شاخص',
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
                field: 'title',
                title: 'عنوان محصول',
                width: 200,
                template: function (row) {
                    return '<span class="float-left">'+row.title+'</span> <a title="کپی کردن" href="' +
                        row.links.copy +
                        '" target="_blank"><i class="feather icon-copy"></i></a>\
                    <a title="مشاهده" href="' +
                        row.links.front +
                        '" target="_blank"><i class="feather icon-external-link"></i></a>';
                }
            },
            {
                field: 'created_at',
                sortable: 'desc',
                title: 'تاریخ ایجاد',
                width: 103,
                template: function (row) {
                    if(row.seller_id){
                        return '<span class="ltr">' + row.created_at + '</span><span class="add-product-seller">ایجاد شده توسط فروشنده</span>';
                    }else{
                        return '<span class="ltr">' + row.created_at + '</span>';
                    }

                }
            },
            {
                field: 'addableToCart',
                title: 'تعداد تنوع',
                textAlign: 'center',
                width: 100,
                // callback function support for column rendering
                template: function (row) {
                    return `<div class="text text-pill">${row.variant_count}</div>`;
                }
            },
            {
                field: 'published',
                title: 'وضعیت انتشار',
                textAlign: 'center',
                width: 100,
                // callback function support for column rendering
                template: function (row) {
                    if (row.published) {
                        var publishedClass = 'badge-success';
                        var publishedText = 'منتشر شده';
                    } else {
                        var publishedClass = 'badge-danger';
                        var publishedText = 'پیش نویس';
                    }
                    if (row.status=="Accept") {
                        var statusClass = 'badge-success';
                        var statusText = 'تایید شده';
                    } else if (row.status=="Waiting"){
                        var statusClass = 'badge-warning';
                        var statusText = 'در انتضار تایید';
                    } else if (row.status=="Reject"){
                        var statusClass = 'badge-danger';
                        var statusText = 'تایید نشده';
                    }
                    return (
                        '<div class="badge ' + publishedClass +' ">' + publishedText +'</div>'+
                        '<div class="badge ' + statusClass +'">' + statusText +'</div>'
                    );
                }
            },
            {
                field: 'actions',
                title: 'عملیات',
                textAlign: 'center',
                sortable: false,
                width: 150,
                overflow: 'visible',
                autoHide: false,
                template: function (row) {
                    return (
                        `<div class="dropdown dropdown-action">
                             <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu`+row.id+`" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                 <i class="fa-solid fa-ellipsis-vertical"></i>
                             </button>
                             <div class="dropdown-menu" aria-labelledby="dropdownMenu`+row.id+`">
                             <a class="dropdown-item" target='_blank' href="`+row.links.front +`"><i class="fa-regular fa-eye mr-1"></i>نمایش</a>
                             <div class="dropdown-divider"></div>

                             <a class="dropdown-item" href="`+row.links.edit +`"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                             <div class="dropdown-divider"></div>

                             <a class="dropdown-item" href="`+ row.links.copy +`"><i class="fa-solid fa-copy mr-1"></i>کپی کردن</a>
                             <div class="dropdown-divider"></div>
                             <button class="dropdown-item btn-delete" data-toggle="modal" data-target="#delete-modal" data-action="`+row.links.destroy +`"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>

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

$('#product-delete-form').on('submit', function (e) {
    e.preventDefault();

    $('#delete-modal').modal('hide');

    var formData = new FormData(this);

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function (data) {
            showCustomToast('محصول با موفقیت حذف شد.','success')
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
            showCustomToast('محصولات انتخاب شده با موفقیت حذف شدند.','success')
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
