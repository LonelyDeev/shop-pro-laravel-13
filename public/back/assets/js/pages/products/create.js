Dropzone.autoDiscover = false;

/* config dropzone uploader for uploading images */
var physicalDropzone = new Dropzone('div#product-images', {
    url: BASE_URL + '/products/image-store',
    addRemoveLinks: true,
    acceptedFiles: 'image/*',

    dictInvalidFileType: 'آپلود فایل با این فرمت ممکن نیست',
    dictRemoveFile: 'حذف',
    dictCancelUpload: 'لغو آپلود',
    dictResponseError: 'خطایی در بارگذاری فایل رخ داده است',

    init: function () {
        this.on('success', function (file, response) {
            file.upload.filename = response.imagename;

            $(file.previewElement).data('name', response.imagename);
            $(file.previewElement).attr('id', response.imagename);
        });
    },

    removedfile: function (file) {
        var name = file.upload.filename;

        if (file.accepted) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: BASE_URL + '/products/image-delete',
                data: {filename: name},
                success: function (data) {
                    // console.log("File has been successfully removed!!");
                },
                error: function (e) {
                    // console.log(e);
                }
            });
        }

        var fileRef;
        return (fileRef = file.previewElement) != null
            ? fileRef.parentNode.removeChild(file.previewElement)
            : void 0;
    },

    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$('#product-create-form').submit(function (e) {
    e.preventDefault();

    if ($(this).valid() && !$(this).data('disabled')) {
        if (physicalDropzone.getUploadingFiles().length) {
            toastr.error('لطفا تا اتمام آپلود تصاویر منتظر بمانید', 'خطا', {
                positionClass: 'toast-bottom-left',
                containerId: 'toast-bottom-left'
            });
            return;
        }

        var date = $('#publish_date').val();
        $('#publish_date').val(date.toEnglishDigit());


        var images = $('.dropzone-area').sortable('toArray');

        var formData = new FormData(this);
        formData.append(
            'description',
            CKEDITOR.instances['description'].getData()
        );
        formData.append('images', images);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function (data) {
                $('#product-create-form').data('disabled', true);
                window.location.href = BASE_URL + '/products';
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
    }
});

$('#publish_date_picker').pDatepicker({
    timePicker: {
        enabled: true,
        meridian: {
            enabled: false
        },
        second: {
            enabled: false
        }
    },
    toolbox: {
        // enabled: true,
        calendarSwitch: {
            enabled: false
        }
    },
    initialValue: false,
    altField: '#publish_date',
    altFormat: 'YYYY-MM-DD HH:mm:ss',

    onSelect: function (unixDate) {
        var date = $('#publish_date').val();
        $('#publish_date').val(date.toEnglishDigit());
    }
});

$('#special_date_picker').pDatepicker({
    timePicker: {
        enabled: true,
        meridian: {
            enabled: false
        },
        second: {
            enabled: false
        }
    },
    toolbox: {
        // enabled: true,
        calendarSwitch: {
            enabled: false
        }
    },
    initialValue: false,
    altField: '#special_end_date',
    altFormat: 'YYYY-MM-DD HH:mm:ss',

    onSelect: function (unixDate) {
        var date = $('#special_end_date').val();
        $('#special_end_date').val(date.toEnglishDigit());
    }
});

$('#get-product-from-site select').change(function() {
    var item=$(this);
    if ($(item).val()=="digikala"){
        $('#get-product-from-site label').text('کد محصول:');
        $('#get-product-from-site input').attr('placeholder','مثال : 272465');
    }else {
        $('#get-product-from-site label').text('لینک محصول:');
        $('#get-product-from-site input').attr('placeholder','مثال: ...//https');
    }

})

jQuery('#get-product-from-site').validate({
    rules: {
        'siteCode': {
            required: true,
        }
    },
});
$("#get-product-from-site").submit(function(e){
    e.preventDefault();
    $("#get-product-from-site button").text('منتظر بمانید');
    block('#get-product-from-site');
    block('#main-card');
    var url = $("#get-product-from-site").data('action');
    var siteCode = $("#get-product-from-site input[name='siteCode']").val();
    var typeG = $("#get-product-from-site select[name='siteName']").val();
    var formData = {
        siteCode:siteCode,
        type:typeG,
    };

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        success: function (data) {
            data=data[0];
            if(typeG == "digikala"){
                $("#base-image input[name='base_image_fromSite']").remove();

                $("#product-create-form input[name='title']").val(data['product'].title_fa);
                $("#product-create-form input[name='title_en']").val(data['product'].title_en);
                $("#product-create-form input[name='brand']").val(data['brands'].name);
                $("#product-create-form input[name='weight']").val(data['weight']);
                $("#product-create-form textarea[name='short_description']").val(data['product'].expert_reviews.description);
                CKEDITOR.instances['description'].insertHtml(data['description'].toString());
                $("#product-create-form input[name='meta_title']").val(data['seo'].meta_title);
                $("#product-create-form input[name='image_alt']").val(data['seo'].meta_title);
                $("#product-create-form textarea[name='meta_description']").val(data['seo'].meta_description);
                $("#product-create-form input[name='FromSite']").val('yes');
                $("#base-image label").text(data['image']);
                $("#base-image").append('<input name="base_image_fromSite" type="hidden" value="'+data['image']+'">');

               $('#product-images').empty();
               $('#images-FromSite').empty();
                $.each(data['images'],function(index, val){
                    $('#images-FromSite').append('<input name="image_fromSite[]" type="hidden" value="'+val.imageName+'">');
                    var mockFile = { name: val.imageName};
                    const imgUrl='/'+val.path;
                    physicalDropzone.emit("addedfile", mockFile);
                    physicalDropzone.emit("thumbnail", mockFile, imgUrl);
                    physicalDropzone.emit("complete", mockFile);
                });

                $('.dz-remove').click(function() {
                    $(this).parent('.dz-preview.dz-image-preview').remove();
                })

                if (data['colors'].length){
                    $('#product-prices-div').empty();
                    $.each(data['colors'],function(index, val){
                        $('#product-prices-div').append(val['original'][0])
                    });
                    priceCount=data['colors'].length;
                }

                if (data['category'].length){
                    var categoryId = data['category'].id;

                        $('#product-create-form select[name="category_id"]').val(categoryId).trigger('change');

                    $('#product-create-form select[name="category_id"]').val(categoryId).trigger('change.select2');


                }

                if (data['specifications'].length){
                    $('#specifications-area').empty();
                    $.each(data['specifications'],function(index, val){
                        $('#specifications-area').append(val['original'][0]);
                    });

                }

            }

            toastr.success('محصول دریافت شد', 'پیغام', {
                positionClass: 'toast-bottom-left',
                containerId: 'toast-bottom-left'
            });
            $("#get-product-from-site button").text('دریافت محصول');
            unblock('#get-product-from-site');
            unblock('#main-card');
        },
        beforeSend: function (xhr) {
            xhr.setRequestHeader(
                'X-CSRF-TOKEN',
                $('meta[name="csrf-token"]').attr('content')
            );
        }, error: function (xhr) {
            toastr.error('محصول یافت نشد', 'خطا', {
                positionClass: 'toast-bottom-left',
                containerId: 'toast-bottom-left'
            });
            $("#get-product-from-site button").text('دریافت محصول');
            unblock('#get-product-from-site');
            unblock('#main-card');
        }
    });
})

