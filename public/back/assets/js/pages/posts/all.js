// generate slug

$('#generate-post-slug').click(function(e) {
    e.preventDefault();

    var title = $('input[name="meta_title"]').val();

    $.ajax({
        url: BASE_URL + '/post/slug',
        type: 'POST',
        data: {
            title: title
        },
        success: function(data) {
            $('#slug').val(data.slug);
        },
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            $('#slug-spinner').show();
        },
        complete: function() {
            $('#slug-spinner').hide();
        }
    });
});

//------------ publish time picker js codes

$('#publish_date_picker').on('keydown', function(e) {
    e.preventDefault();
    $(this).val('');
    $('#publish_date').val('');
});

$('.product-categories').select2ToTree({
    rtl: true,
    width: '100%'
});

// مدیریت تغییر نوع مقاله
$('#post_type').on('change', function() {
    var selectedValue = $(this).val();

    // مخفی کردن همه فیلدهای اضافی
    $('#video_url_field').slideUp(300);
    $('#podcast_url_field').slideUp(300);

    // نمایش فیلد مناسب بر اساس انتخاب
    if (selectedValue === 'video') {
        $('#video_url_field').slideDown(300);
    } else if (selectedValue === 'podcast') {
        $('#podcast_url_field').slideDown(300);
    }
});

// تریگر اولیه برای حالت پیش‌فرض
$('#post_type').trigger('change');
