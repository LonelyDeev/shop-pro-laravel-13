'use strict';
// Class definition

var datatable;

function order_products() {


    $('#orders_products').show();

    var Url= $('#orders_products').data('action');
    //var routeParameter = $('#orders_products').data('parameter');
    var params = getQueryParams();

    $.ajax({
        url: Url,
        type: 'GET',
        data: params,
        success: function (data) {
            $('#orders_products #productItems').empty();
            $('#orders_products #productItems').append(data)
        },
        beforeSend: function (xhr) {
            block('#orders_products');
        },
        complete: function () {
            unblock('#orders_products');
        }
    });
}

$('#filter-orders-form select').on('change', function() {
    formDataToUrl('filter-orders-form');
    $('.users-list-filter form').serializeJSON()

    order_products();
  });

$('#filter-orders-form .datatable-filter').on('change', function () {
    formDataToUrl('filter-orders-form');
    $('.users-list-filter form').serializeJSON()

    order_products();
});

jQuery(document).ready(function() {
  order_products();
});

$('.users-list-filter .persian-date-picker').on('change', function () {
    formDataToUrl('filter-orders-form');

        $('.users-list-filter form').serializeJSON()

    order_products();
});

$('#shiping-status-change select[name=status]').on('change', function(e) {
  if (this.value == 'canceled') {
    $('#shiping-status-change #shiping-status-canceled').removeClass('d-none');
  } else {
    $('#shiping-status-change #shiping-status-canceled').addClass('d-none');
  }
});
function getQueryParams() {
    var params = {};
    var queryString = window.location.search.substring(1);
    var pairs = queryString.split('&');

    for (var i = 0; i < pairs.length; i++) {
        var pair = pairs[i].split('=');
        var key = decodeURIComponent(pair[0]);
        var value = decodeURIComponent(pair[1] || '');
        params[key] = value;
    }

    return params;
}
