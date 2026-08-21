'use strict';
// Class definition

var datatable;

var order_datatable = (function () {
    // Private functions

    var server_data;

    var options = {
        // datasource definition
        data: {
            type: 'remote',
            source: {
                read: {
                    url: $('#orders_datatable').data('action'),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        )
                    },
                    map: function (raw) {
                        // sample data mapping
                        var dataSet = raw;
                        server_data = raw;

                        if (typeof raw.data !== 'undefined') {
                            dataSet = raw.data;
                        }

                        return dataSet;
                    },
                    params: {
                        query: $('#filter-orders-form').serializeJSON()
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
                field: 'ordering',
                sortable: false,
                title: 'ردیف',
                width: 27,
                template: function (row, i) {
                    let number =
                        parseInt(
                            server_data.meta.perpage *
                                (server_data.meta.current_page - 1)
                        ) +
                        parseInt(i) +
                        1;
                    return number;
                }
            },
            {
                field: 'actions',
                title: 'مرسوله ها',
                sortable: false,
                overflow: 'visible',
                width: 270,
                class: 'orders-badge p-0 width-fit',
                autoHide: false,
                template: function (row) {
                    // بررسی وجود مرسوله‌ها
                    if (!row.items || row.items.length === 0) {
                        return '<span class="text-muted">بدون مرسوله</span>';
                    }

                    // ساخت لیست مرسوله‌ها
                    var itemsHtml = '<ul class="mb-0">';

                    $.each(row.items, function(index, item) {
                        itemsHtml += `
                <li class="d-inline-flex align-items-center justify-content-between w-100 border-bottom">
                    <div class="d-flex flex-column justify-content-center">
                        <div class="lts-05">
                            <span class="fs-9 text-gray">شناسه مرسوله: </span>
                            <span class="fw-bold text-dark fs-8">${item.id || '---'}</span>
                        </div>
                        <div class="lts-05">
                            <span class="fs-9 text-gray">ارسال توسط: </span>
                            ${item.seller_name ?
                            `<a class="link fw-bold lts-05 fs-8" href="${item.seller_link || '#'}">${item.seller_name}</a>` :
                            `<span class="text-dark fw-bold fs-8">${item.sellerName || "فروشگاه اصلی"}</span>`
                        }
                        </div>
                    </div>
                    <div class="d-flex flex-column justify-content-center ms-4">
                        <div class="status lts-05">
                            <span class="badge ${getStatusBadge(item.status).class}">${getStatusBadge(item.status).title}</span>
                        </div>
                        <a href="${item.link}" class="btn btn-outline-light pt-1 pb-1 lts-05" type="button" onclick="viewShipment(${item.id})">
                            <span>بررسی</span>
                        </a>
                    </div>
                </li>
            `;
                    });

                    itemsHtml += '</ul>';
                    return itemsHtml;
                }
            },
            {
                field: 'name',
                width: 60,
                class: 'text-center',
                title: 'مشتری'
            },
            {
                field: 'order_id',
                width: 74,
                title: 'شماره سفارش'
            },
            {
                field: 'created_at',
                sortable: 'desc',
                title: 'تاریخ ثبت',
                width: 100,
                class: 'width-fit',
                textAlign: 'center',
                template: function (row) {
                    return '<span class="ltr">' + row.created_at + '</span>';
                }
            },
            {
                field: 'price',
                width: 110,
                class: 'width-fit',
                textAlign: 'center',
                title: 'مبلغ پرداخت',
                template: function (row) {
                    if (row.is_installment) {
                        // نمایش پیش‌پرداخت + badge اقساطی
                        var downPayment = row.installment_down_payment
                            ? Number(row.installment_down_payment).toLocaleString('fa-IR')
                            : '۰';
                        return `
                <div class="d-flex flex-column align-items-center gap-1">
                    <span class="fw-bold text-warning">${downPayment} ت</span>
                    <span class="badge badge-info" style="font-size:10px;">
                        💰 اقساطی
                    </span>
                    <small class="text-muted" style="font-size:10px;">
                        پیش‌پرداخت
                    </small>
                </div>
            `;
                    }
                    // ======== ۲. سفارش اعتباری ========
                    if (row.is_credit) {
                        var cashPaid = row.credit_cash_paid
                            ? Number(row.credit_cash_paid).toLocaleString('fa-IR')
                            : '۰';
                        var progress = row.credit_progress || 0;
                        var remaining = row.credit_remaining
                            ? Number(row.credit_remaining).toLocaleString('fa-IR')
                            : '۰';
                        var firstPaid = row.credit_first_installment_paid;

                        var statusBadge = '';
                        if (row.credit_status === 'active') {
                            statusBadge = '<span class="badge badge-primary" style="font-size:10px;">فعال</span>';
                        } else if (row.credit_status === 'completed') {
                            statusBadge = '<span class="badge badge-success" style="font-size:10px;">تکمیل</span>';
                        } else if (row.credit_status === 'refunded') {
                            statusBadge = '<span class="badge badge-warning" style="font-size:10px;">مرجوع</span>';
                        } else if (row.credit_status === 'cancelled') {
                            statusBadge = '<span class="badge badge-secondary" style="font-size:10px;">لغو</span>';
                        } else {
                            statusBadge = '<span class="badge badge-secondary" style="font-size:10px;">' + (row.credit_status || '—') + '</span>';
                        }

                        var firstInstallmentIcon = firstPaid
                            ? '<i class="fas fa-check-circle text-success" style="font-size:10px;" title="قسط اول پرداخت شده"></i>'
                            : '<i class="fas fa-clock text-warning" style="font-size:10px;" title="قسط اول پرداخت نشده"></i>';

                        return `
                <div class="d-flex flex-column align-items-center gap-1">
                    <span class="fw-bold text-primary">${cashPaid} ت</span>
                    <span class="badge badge-primary" style="font-size:10px;">
                        💳 اعتباری
                    </span>
                    <small class="text-muted" style="font-size:10px;">
                        نقدی + قسط اول
                    </small>
                    <div class="d-flex align-items-center gap-1" style="font-size:10px;">
                        ${firstInstallmentIcon}
                        <span class="text-muted">پیشرفت: ${progress}٪</span>
                    </div>
                    <div class="progress" style="width:60px;height:4px;">
                        <div class="progress-bar bg-primary" style="width:${progress}%;"></div>
                    </div>
                    ${statusBadge}
                </div>
            `;
                    }

                    return row.price;
                }
            },
            {
                field: 'status',
                title: 'وضعیت',
                textAlign: 'center',
                width: 110,
                // callback function support for column rendering
                template: function (row) {
                    var status = {
                        canceled: {
                            title: 'لغو شده',
                            class: ' badge-danger'
                        },
                        cancelled: {
                            title: 'لغو شده',
                            class: ' badge-danger'
                        },
                        unpaid: {
                            title: 'پرداخت نشده',
                            class: ' badge-danger'
                        },
                        paid: {
                            title: 'پرداخت شده',
                            class: ' badge-success'
                        }
                    };

                    var html = '<div class="badge ' + status[row.status].class + '">' + status[row.status].title + '</div>';

                    // اگه اقساطی باشه، badge اضافه کن
                    if (row.is_installment) {
                        var installmentBadges = {
                            'pending_down_payment': { title: 'در انتظار پیش‌پرداخت', class: 'badge-warning' },
                            'active':               { title: 'اقساطی فعال',          class: 'badge-info' },
                            'completed':            { title: 'اقساط تکمیل',          class: 'badge-success' },
                            'defaulted':            { title: 'اقساط معوق',           class: 'badge-danger' },
                            'cancelled':            { title: 'اقساط لغو',            class: 'badge-secondary' },
                        };
                        var badge = installmentBadges[row.installment_status] || { title: 'اقساطی', class: 'badge-info' };
                        html += '<div class="badge ' + badge.class + ' mt-1" style="font-size:10px;">💰 ' + badge.title + '</div>';
                    }

                    return html;
                }
            },


        ]
    };

    var initDatatable = function () {
        // enable extension
        options.extensions = {
            // boolean or object (extension options)
            checkbox: true
        };

        datatable = $('#orders_datatable').KTDatatable(options);

        $('#filter-orders-form .datatable-filter').on('change', function () {
            formDataToUrl('filter-orders-form');
            datatable.setDataSourceQuery(
                $('#filter-orders-form').serializeJSON()
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
    order_datatable.init();
});
// توابع کمکی خارج از template
function getStatusBadge(status) {
    var classes = {
        'w-pending': {
            title: 'در انتظار بررسی',
            class: 'badge-secondary',
            icon: 'clock',
            gradient: 'linear-gradient(135deg, #868e96 0%, #adb5bd 100%)'
        },
        'pending': {
            title: 'در حال بررسی',
            class: 'badge-info',
            icon: 'search',
            gradient: 'linear-gradient(135deg, #17a2b8 0%, #4fc3f7 100%)'
        },
        'processing': {
            title: 'در حال پردازش',
            class: 'badge-primary',
            icon: 'settings',
            gradient: 'linear-gradient(135deg, #4e73df 0%, #224abe 100%)'
        },
        'waiting': {
            title: 'منتظر ارسال',
            class: 'badge-warning',
            icon: 'package',
            gradient: 'linear-gradient(135deg, #f39c12 0%, #e67e22 100%)'
        },
        'sent': {
            title: 'ارسال شد',
            class: 'badge-success',
            icon: 'truck',
            gradient: 'linear-gradient(135deg, #28a745 0%, #20c997 100%)'
        },
        'post-sent': {
            title: 'تحویل به پست',
            class: 'badge-success',
            icon: 'mail',
            gradient: 'linear-gradient(135deg, #20c997 0%, #17a2b8 100%)'
        },
        'delivered': {
            title: 'تحویل داده شد',
            class: 'badge-success',
            icon: 'check-circle',
            gradient: 'linear-gradient(135deg, #198754 0%, #0f5132 100%)'
        },
        'canceled': {
            title: 'لغو شد',
            class: 'badge-danger',
            icon: 'x-circle',
            gradient: 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)'
        }
    };
    return classes[status] || 'bg-secondary';
}


$('#order-multiple-delete-form').on('submit', function (e) {
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
            showCustomToast('سفارشات انتخاب شده با موفقیت حذف شدند','success');
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

$('#multiple-shipping-status-change').on('submit', function (e) {
    e.preventDefault();

    $('#shiping-status-change').modal('hide');

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
            showCustomToast('سفارشات انتخاب شده با موفقیت تغیر وضعیت  شدند','success');
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

$('#print-all-btn').on('click', function (e) {
    let ids = datatable.checkbox().getSelectedId();
    let url = $(this).data('action') + '?';

    ids.forEach(function (id) {
        url += 'ids[]=' + id + '&'
    });

    window.open(url);
});

$('#print-all-shipping').on('click', function (e) {
    let ids = datatable.checkbox().getSelectedId();
    let url = $(this).data('action') + '?';

    ids.forEach(function (id) {
        url += 'ids[]=' + id + '&';
    });

    window.open(url);
});

$('#print-all-factor-btn').on('click', function (e){
   let ids = datatable.checkbox().getSelectedId();
   let url = $(this).data('action') + '?';

   ids.forEach(function (id) {
      url += 'ids[]=' + id + '&'
   });

   window.open(url);
});

$('#orders-export-form').on('submit', function (e) {
    e.preventDefault();

    let formData = datatable.getDataSourceParam();
    let queryString = $.param(formData);

    let formData2 = new FormData(this);
    let queryString2 = new URLSearchParams(formData2).toString();

    let url = `${$(this).attr('action')}?${queryString}&${queryString2}`;

    window.open(url);
});

$('#shiping-status-change select[name=status]').on('change', function (e){
   if (this.value=='canceled'){
       $('#shiping-status-change #shiping-status-canceled').removeClass('d-none')
   }else{
       $('#shiping-status-change #shiping-status-canceled').addClass('d-none')
   }
});
