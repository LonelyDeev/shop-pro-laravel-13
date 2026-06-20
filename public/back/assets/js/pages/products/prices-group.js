

$('.product-category').select2ToTree({
    rtl: true,
    width: '100%'
});


$('.product-categories').select2ToTree({
    rtl: true,
    width: '100%'
});
$('.price-attribute-select').select2ToTree({
    rtl: true,
    width: '100%'
});
$('.warehouses-attribute-select').select2ToTree({
    rtl: true,
    width: '100%'
});

let productsCount = 0;

$('#add-product-to-order').autocomplete({
        delay: 1000,
        minLength: 3,
        source: function (term, response) {
            $.ajax({
                url: $('#add-product-to-order').data('action'),
                type: 'GET',
                data: term,
                success: function (data) {
                    response(data.data);
                    unblock('.add-product-to-order-loader');
                },
                beforeSend: function (xhr) {
                    block('.add-product-to-order-loader');
                },
                error: function (data) {
                    //
                }
            });
        },
        select: function (event, ui) {
            let template = ejs.render($('#product-template').html(), {
                product: ui.item
            });

            $('#order-products-list').append(template);
            productsCount++;
        }
    })
    .autocomplete('instance')._renderItem = function (ul, item) {
    return $('<li>')
        .attr('data-value', item.title)
        .append(
            `<li data-value="${item.title}" class="d-flex">
                <img src="${item.image}"
                    alt="${item.title}" style="width: 50px">
                <div class="ml-2">
                    ${item.title}
                    <small class="text-muted">
                        <p class="m-0">${number_format(item.price)} تومان</p>
                    </small>
                </div>
            </li>`
        )
        .appendTo(ul);
};

$(document).on(
    'click',
    '.order-single-product .delete-product-btn',
    function () {
        $(this).closest('.order-single-product').remove();
    }
);

$(document).ready(function () {
    // تابعی برای غیرفعال کردن فیلدهای تب‌های غیرفعال
    function disableInactiveTabs() {
        $('.tab-pane').not('.active').find('input, select, textarea').prop('disabled', true);
        $('.tab-pane.active').find('input, select, textarea').prop('disabled', false);
    }

    // زمانی که تب تغییر کرد، فیلدهای غیرفعال شوند
    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        disableInactiveTabs();
    });

    // در لحظه لود صفحه، تب‌های غیرفعال را disable کن
    disableInactiveTabs();

    // مدیریت ارسال فرم
    $(document).on('submit', '#product-prices-group-form', function (e) {
        e.preventDefault();

        if (!$(this).valid()) {  // بررسی ولیدیشن فرم قبل از ارسال
            return;
        }

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function (data) {
                location.reload();
            },
            beforeSend: function (xhr) {
                block('#main-card');
                xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            },
            complete: function () {
                unblock('#main-card');
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });

    // تنظیم ولیدیشن روی فقط فیلدهای تب فعال
    $.validator.addMethod("atLeastOne", function(value, element) {
        var percent = $('input[name="percent"]').val();
        var price = $('input[name="price"]').val();
        return percent.trim() !== '' || price.trim() !== ''; // بررسی اینکه حداقل یکی از فیلدها مقدار داشته باشد
    }, "حداقل یکی از فیلدهای درصد یا مبلغ ثابت باید مقدار داشته باشد.");

    $('#product-prices-group-form').validate({
        ignore: ":hidden, .tab-pane:not(.active) input, .tab-pane:not(.active) select, .tab-pane:not(.active) textarea",
        rules: {
            type: { required: true },
            percent: {
                number: true,
                min: 0,
                max: 100
            },
            price: {
                number: true
            }
        },
        messages: {
            percent: {
                number: "لطفاً مقدار عددی وارد کنید",
                min: "درصد نمی‌تواند کمتر از 0 باشد",
                max: "درصد نمی‌تواند بیشتر از 100 باشد"
            },
            price: {
                number: "لطفاً مقدار عددی وارد کنید"
            }
        }
    });


});
