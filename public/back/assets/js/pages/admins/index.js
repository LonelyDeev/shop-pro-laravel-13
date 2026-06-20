"use strict";
// Class definition

var datatable;

var user_datatable = function() {
    // Private functions

    var options = {
        // datasource definition
        data: {
            type: 'remote',
            source: {
                read: {
                    url: $('#users_datatable').data('action'),
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    map: function(raw) {
                        // sample data mapping
                        var dataSet = raw;
                        if (typeof raw.data !== 'undefined') {
                            dataSet = raw.data;
                        }
                        return dataSet;
                    },
                    params: {
                        query: $('#filter-users-form').serializeJSON()
                    }
                },
            },
            pageSize: 10,
            serverPaging: true,
            serverFiltering: true,
            serverSorting: true,
        },

        layout: {
            scroll: true
        },

        rows: {
            autoHide: false,
        },

        // columns definition
        columns: [{
                field: 'id',
                title: '#',
                sortable: false,
                width: 32,
                selector: {
                    class: ''
                },
                textAlign: 'center',
            },
            {
                field: 'image',
                title: 'تصویر',
                sortable: false,
                width: 80,
                template: function(row) {
                    return '<img class="post-thumb" src="' + row.image + '" alt="' + row.title + '">';
                }
            },
            {
                field: 'fullname',
                title: 'نام',
                width: 200,
                template: function(row) {
                    return row.fullname;
                }
            },
            {
                field: 'mobile',
                title: 'شماره موبایل',
                width: 200,
                template: function(row) {
                    return row.mobile;
                }
            },
            {
                field: 'created_at',
                sortable: 'desc',
                title: 'تاریخ عضویت',
                template: function(row) {
                    return '<span class="ltr">' + row.created_at + '</span>';
                }
            },
            {
                field: 'actions',
                title: 'عملیات',
                textAlign: 'center',
                sortable: false,
                width: 200,
                overflow: 'visible',
                autoHide: false,
                template: function(row) {
                    return `<div class="dropdown dropdown-action">
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu`+row.id+`" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu" aria-labelledby="dropdownMenu`+row.id+`">
                                                                <a class="dropdown-item" href="`+row.links.show +`"><i class="fa-regular fa-eye mr-1"></i>نمایش</a>
                                                                <div class="dropdown-divider"></div>

                                                                    <a class="dropdown-item" href="`+row.links.edit +`"><i class="fa-solid fa-pencil mr-1"></i>ویرایش</a>
                                                                    <div class="dropdown-divider"></div>


                                                                    <button class="dropdown-item btn-delete" data-toggle="modal" data-target="#delete-modal" data-action="`+row.links.destroy +
                        `"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>

                                                              </div>
                                                        </div>`;

                },
            },
        ],
    };

    var initDatatable = function() {
        // enable extension
        options.extensions = {
            // boolean or object (extension options)
            checkbox: true,
        };

        datatable = $('#users_datatable').KTDatatable(options);

        $('#filter-users-form .datatable-filter').on('change', function() {
            formDataToUrl('filter-users-form');
            datatable.setDataSourceQuery($('#filter-users-form').serializeJSON());
            datatable.reload();
        });

        datatable.on('datatable-on-click-checkbox',
            function(e) {
                var ids = datatable.checkbox().getSelectedId();
                var count = ids.length;

                $('#datatable-selected-rows').html(count);

                if (count > 0) {
                    $('.datatable-actions').collapse('show');
                } else {
                    $('.datatable-actions').collapse('hide');
                }
            }
        );

        datatable.on('datatable-on-reloaded',
            function(e) {
                $('.datatable-actions').collapse('hide');
            }
        );
    };

    return {
        // public functions
        init: function() {
            initDatatable();
        },
    };
}();

jQuery(document).ready(function() {
    user_datatable.init();
});

$('#user-multiple-delete-form').on('submit', function(e) {
    e.preventDefault();

    $('#multiple-delete-modal').modal('hide');

    var formData = new FormData(this);
    var ids = datatable.checkbox().getSelectedId();
        console.log(ids)
    ids.forEach(function(id) {
        formData.append('ids[]', id);
    });

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function(data) {
            toastr.success('کاربران انتخاب شده با موفقیت حذف شدند.', null,{ positionClass: 'toast-bottom-left', containerId: 'toast-bottom-left' });
            datatable.reload();
        },
        beforeSend: function(xhr) {
            block('#main-card');
            xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
        },
        complete: function() {
            unblock('#main-card');
        },
        cache: false,
        contentType: false,
        processData: false
    });
});
$(document).on('click', '.btn-delete', function () {
    $('#user-delete-form').attr('action', $(this).data('action'));
});

$('#user-delete-form').on('submit', function (e) {
    e.preventDefault();

    $('#delete-modal').modal('hide');

    var formData = new FormData(this);

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function (data) {
            toastr.success('کاربر با موفقیت حذف شد.', null,{ positionClass: 'toast-bottom-left', containerId: 'toast-bottom-left' });
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
