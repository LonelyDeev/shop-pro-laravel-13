$(document).ready(function () {
    $("#redirect-form").validate({
        rules: {
            from: {
                required: true,
                maxlength: 512,
            },
            to: {
                maxlength: 2048,
            },
            type: {
                required: true,
                digits: true,
                range: [301, 503],
            },
        },
        messages: {
            from: {
                required: "آدرس مبدأ الزامی است.",
                url: "لطفاً یک URL معتبر وارد کنید.",
                maxlength: "آدرس مبدأ نباید بیشتر از 2048 کاراکتر باشد.",
            },
            to: {
                url: "لطفاً یک URL معتبر وارد کنید.",
                maxlength: "آدرس مقصد نباید بیشتر از 2048 کاراکتر باشد.",
            },
            type: {
                required: "لطفاً نوع ریدایرکت را انتخاب کنید.",
                digits: "نوع ریدایرکت باید یک عدد باشد.",
                range: "لطفاً مقدار صحیحی بین 301 تا 503 انتخاب کنید.",
            },
        },
        errorPlacement: function (error, element) {
            error.addClass("text-danger"); // اضافه کردن کلاس خطای قرمز
            error.insertAfter(element); // نمایش خطا بعد از فیلد
        },

    });
});

$('#redirect-form').submit(function(e) {
    e.preventDefault();

    if (!$(this).valid()) {  // بررسی ولیدیشن فرم قبل از ارسال
        return;
    }

    var formData = new FormData(this);

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function(data) {
            console.log(data)
            if(data.redirect){
                window.location.href = data.redirect;
            }else{
                   location.reload();
            }

        },
        beforeSend: function(xhr) {
            block('#redirect-form');
            xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
        },
        complete: function() {
            unblock('#redirect-form');
        },
        cache: false,
        contentType: false,
        processData: false
    });

});
