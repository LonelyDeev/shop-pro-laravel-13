"use strict";
// Class definition

var datatable;

var transaction_datatable = function() {
    // Private functions

    var options = {
        // datasource definition
        data: {
            type: 'remote',
            source: {
                read: {
                    url: $('#transactions_datatable').data('action'),
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
                        query: $('#filter-transactions-form').serializeJSON()
                    }
                },
            },
            pageSize: 10,
            serverPaging: true,
            serverFiltering: true,
            serverSorting: true,
        },

        sortable: true,

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
                field: 'fullname',
                title: 'نام پرداخت کننده',
                template: function(row) {
                    return '<a class="text-dark" href="' + row.links.user + '" target="_blank">' + row.fullname + '</a>';
                }
            },
            {
                field: 'created_at',
                sortable: 'desc',
                title: 'تاریخ تراکنش',
                template: function(row) {
                    return '<span class="ltr">' + row.created_at + '</span>';
                }
            },
            {
                field: 'amount',
                title: 'مبلغ',
            },
            {
                field: 'status',
                title: 'وضعیت',
                textAlign: 'center',
                // callback function support for column rendering
                template: function(row) {

                    if (row.status == '1') {
                        var statusClass = 'badge-success';
                        var statusText = 'موفق';
                    } else {
                        var statusClass = 'badge-danger';
                        var statusText = 'ناموفق';
                    }
                    return '<div class="badge ' + statusClass + '">' + statusText + '</div>';
                },
            },
            {
                field: 'actions',
                title: 'عملیات',
                textAlign: 'center',
                sortable: false,
                width: 180,
                overflow: 'visible',
                autoHide: false,
                template: function(row) {

                    return `<div class="dropdown dropdown-action">
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu`+row.id+`" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenu`+row.id+`">
                                <button class="dropdown-item show-transaction" data-action="` + row.links.view + `" ><i class="fa-regular fa-eye mr-1"></i> مشاهده</button>
                                <div class="dropdown-divider"></div>

                                <button class="dropdown-item btn-delete" data-toggle="modal" data-target="#delete-modal" data-action="`+row.links.destroy +`"><i class="fa-solid fa-trash-can mr-1"></i> حذف</button>

                                </div>
                           </div>`
                },
            }
        ],
    };

    var initDatatable = function() {
        // enable extension
        options.extensions = {
            // boolean or object (extension options)
            checkbox: true,
        };

        datatable = $('#transactions_datatable').KTDatatable(options);

        $('#filter-transactions-form .datatable-filter').on('change', function() {
            formDataToUrl('filter-transactions-form');
            datatable.setDataSourceQuery($('#filter-transactions-form').serializeJSON());
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
    transaction_datatable.init();
});

$(document).on('click', '.btn-delete', function() {
    $('#transaction-delete-form').attr('action', $(this).data('action'));
});

$('#transaction-delete-form').on('submit', function(e) {
    e.preventDefault();

    $('#delete-modal').modal('hide');

    var formData = new FormData(this);

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function(data) {
            showCustomToast('تراکنش با موفقیت حذف شد.','success')
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

$('#transaction-multiple-delete-form').on('submit', function(e) {
    e.preventDefault();

    $('#multiple-delete-modal').modal('hide');

    var formData = new FormData(this);
    var ids = datatable.checkbox().getSelectedId();

    ids.forEach(function(id) {
        formData.append('ids[]', id);
    });

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function(data) {
            showCustomToast('تراکنش های انتخاب شده با موفقیت حذف شدند.','success')
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


$(document).on('click', '.show-transaction', function() {
    var btn = $(this);

    $.ajax({
        url: $(this).data('action'),
        type: 'GET',
        success: function(data) {
            $('#transaction-detail').empty();
            $('#transaction-detail').append(data);
            $('#show-modal').modal('show');
        },
        beforeSend: function(xhr) {
            block(btn);
        },
        complete: function() {
            unblock(btn);
        }
    });
});
