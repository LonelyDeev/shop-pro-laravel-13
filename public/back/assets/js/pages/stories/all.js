$(document).ready(function() {

    // ========== عناصر DOM ==========
    const $title = $('#storyTitle');
    const $coverUpload = $('#coverUpload');
    const $contentImageUpload = $('#contentImageUpload');
    const $storyType = $('#storyType');
    const $videoUrlInput = $('#videoUrlInput');
    const $expiryDate = $('#expiryDate');
    const $widgetTitle = $('#widgetTitle');
    const $widgetLink = $('#widgetLink');
    const $productId = $('#productId');

    // عناصر پیش‌نمایش
    const $storyContentImage = $('#storyContentImage');  // تصویر محتوای استوری
    const $storyVideo = $('#storyVideo');                // ویدیوی استوری
    const $liveTitle = $('#liveTitle');
    const $liveWidget = $('#liveWidget');
    const $liveWidgetTitle = $('#liveWidgetTitle');
    const $liveWidgetLink = $('#liveWidgetLink');
    const $liveExpiry = $('#liveExpiry');

    // عناصر نمایش Thumbnail
    const $coverThumb = $('#coverThumb');
    const $contentImageThumb = $('#contentImageThumb');
    const $coverFileName = $('#coverFileName');
    const $contentFileName = $('#contentFileName');

    // کانتینرها
    const $imageContentContainer = $('#imageContentContainer');
    const $videoContentContainer = $('#videoContentContainer');

    // ObjectURLها
    let coverObjectUrl = null;
    let contentObjectUrl = null;

    // ========== مخفی کردن همه تصاویر در ابتدای بارگذاری ==========

    if (editStory==false){
        $coverThumb.hide();

    }
    if (existContentImage==false){
        $contentImageThumb.hide();
        $storyContentImage.hide();
    }
    if (existContentVideo==false){
        $storyVideo.hide();
    }



    // ========== آپلود تصویر کاور ==========
    $('#uploadBoxCover').on('click', function() {
        $coverUpload.click();
    });

    $coverUpload.on('change', function() {
        if (this.files && this.files.length > 0) {
            const file = this.files[0];
            if (file.type.startsWith('image/')) {
                if (coverObjectUrl) URL.revokeObjectURL(coverObjectUrl);
                coverObjectUrl = URL.createObjectURL(file);
                $coverThumb.attr('src', coverObjectUrl).show();  // نمایش بعد از آپلود
                $coverFileName.text(file.name);
            }
        }
    });

    // ========== آپلود تصویر محتوای استوری ==========
    $('#uploadBoxContent').on('click', function() {
        $contentImageUpload.click();
    });

    $contentImageUpload.on('change', function() {
        if (this.files && this.files.length > 0) {
            const file = this.files[0];
            if (file.type.startsWith('image/')) {
                if (contentObjectUrl) URL.revokeObjectURL(contentObjectUrl);
                contentObjectUrl = URL.createObjectURL(file);
                $contentImageThumb.attr('src', contentObjectUrl).show();  // نمایش بعد از آپلود
                $contentFileName.text(file.name);

                // اگر در حالت تصویری هستیم، بلافاصله پیش‌نمایش را به‌روز کن
                if ($storyType.val() === 'image') {
                    $storyContentImage.attr('src', contentObjectUrl).show();
                }
            }
        }
    });

    // ========== تغییر نوع استوری (تصویری / ویدیویی) ==========
    function toggleStoryType() {
        const type = $storyType.val();

        if (type === 'video') {
            // حالت ویدیو: مخفی کردن آپلود تصویر، نمایش فیلد ویدیو
            $imageContentContainer.hide();
            $videoContentContainer.show();
            $storyContentImage.hide();

            // آپدیت ویدیو
            const videoLink = $videoUrlInput.val().trim();
            if (videoLink && (videoLink.startsWith('http') || videoLink.startsWith('https'))) {
                $storyVideo.show();
                $storyVideo.attr('src', videoLink);
                $storyVideo[0].load();
                $storyVideo[0].play().catch(e => console.log('playback error', e));
            } else {
                $storyVideo.hide();
                $storyVideo.attr('src', '');
            }
        } else {
            // حالت تصویر: نمایش آپلود تصویر، مخفی کردن فیلد ویدیو
            $imageContentContainer.show();
            $videoContentContainer.hide();
            $storyContentImage.show();
            $storyVideo.hide();
            $storyVideo[0].pause();

            // آپدیت تصویر محتوا (فقط اگه تصویری وجود داره)
            if ($contentImageUpload[0].files && $contentImageUpload[0].files.length > 0 && contentObjectUrl) {
                $storyContentImage.attr('src', contentObjectUrl).show();
            } else {
                if (existContentImage==false) {
                    $storyContentImage.hide();  // هیچ تصویری نشون نده
                }
            }
        }
    }

    // ========== آپدیت ویدیو ==========
    $videoUrlInput.on('input', function() {
        if ($storyType.val() === 'video') {
            const vUrl = $(this).val().trim();
            if (vUrl && (vUrl.startsWith('http') || vUrl.startsWith('https'))) {
                $storyVideo.show();
                $storyVideo.attr('src', vUrl);
                $storyVideo[0].load();
                $storyVideo[0].play().catch(e => console.log('play error', e));
            } else {
                $storyVideo.hide();
                $storyVideo.attr('src', '');
            }
        }
    });

    // ========== آپدیت عنوان ==========
    function updateTitle() {
        let newTitle = $title.val().trim();
        if (newTitle === "") newTitle = "";
        $liveTitle.text(newTitle);
    }

    // ========== آپدیت ویجت ==========
    function updateWidget() {
        const wTitle = $widgetTitle.val().trim();
        const wLink = $widgetLink.val().trim();

        if (wTitle !== "" && wLink !== "") {
            $liveWidgetTitle.text(wTitle);
            let finalLink = wLink;
            if (!finalLink.startsWith('http')) {
                finalLink = 'https://' + finalLink;
            }
            $liveWidgetLink.attr('href', finalLink);
            $liveWidget.fadeIn(150);
        } else {
            $liveWidget.fadeOut(150);
        }
    }



    // ========== آپدیت همه چیز ==========
    function refreshPreview() {
        updateTitle();
        updateWidget();

        // اگر در حالت ویدیو هستیم، ویدیو را آپدیت کن
        if ($storyType.val() === 'video') {
            const vUrl = $videoUrlInput.val().trim();
            if (vUrl && $storyVideo.attr('src') !== vUrl) {
                $storyVideo.show();
                $storyVideo.attr('src', vUrl);
                $storyVideo[0].load();
                $storyVideo[0].play().catch(e => {});
            } else if (!vUrl) {
                $storyVideo.hide();
            }
        } else {
            // حالت تصویر: فقط اگه تصویر آپلود شده باشه نشون بده
            if ($contentImageUpload[0].files && $contentImageUpload[0].files.length > 0 && contentObjectUrl) {
                $storyContentImage.attr('src', contentObjectUrl).show();
            } else {
                if (existContentImage==false) {
                    $storyContentImage.hide();  // هیچ تصویر پیش‌فرضی نشون نده
                }
            }
        }
    }

    // ========== ریست فرم ==========
    $('#resetFormBtn').on('click', function() {
        // ریست عنوان
        $title.val('');

        // ریست کاور
        $coverUpload.val('');
        if (coverObjectUrl) {
            URL.revokeObjectURL(coverObjectUrl);
            coverObjectUrl = null;
        }
        $coverThumb.hide();  // مخفی کن
        $coverThumb.attr('src', '');
        $coverFileName.text('هیچ فایلی انتخاب نشده');

        // ریست محتوای تصویر
        $contentImageUpload.val('');
        if (contentObjectUrl) {
            URL.revokeObjectURL(contentObjectUrl);
            contentObjectUrl = null;
        }
        $contentImageThumb.hide();  // مخفی کن
        $contentImageThumb.attr('src', '');
        $contentFileName.text('هیچ فایلی انتخاب نشده');

        // مخفی کردن تصویر اصلی استوری
        $storyContentImage.hide();
        $storyContentImage.attr('src', '');

        // ریست نوع استوری
        $storyType.val('image');

        // ریست ویدیو
        $videoUrlInput.val('');
        $storyVideo.hide();
        $storyVideo.attr('src', '');

        // ریست تاریخ
        $expiryDate.val('');

        // ریست ویجت
        $widgetTitle.val('');
        $widgetLink.val('');

        // ریست محصول
        $productId.val('');

        $('.story-product').addClass('hidden')
        $('.story-product .discount-percent').addClass('hidden')
        $('.story-product .image img').attr('src','')

        // اعمال تغییرات در UI
        toggleStoryType();
        refreshPreview();
    });

    // ========== رویدادها ==========
    $title.on('input', refreshPreview);
    $storyType.on('change', function() {
        toggleStoryType();
        refreshPreview();
    });
    $expiryDate.on('input', refreshPreview);
    $widgetTitle.on('input', refreshPreview);
    $widgetLink.on('input', refreshPreview);

    // وقتی تصویر محتوا آپلود شد
    $contentImageUpload.on('change', function() {
        if ($storyType.val() === 'image') {
            if (contentObjectUrl) {
                $storyContentImage.attr('src', contentObjectUrl).show();
            }
        }
    });

    // پخش خودکار ویدیو
    setInterval(() => {
        if ($storyType.val() === 'video' && $storyVideo[0] && $storyVideo[0].paused && $storyVideo[0].src && $storyVideo[0].src !== '') {
            $storyVideo[0].play().catch(e => {});
        }
    }, 2000);

    // ========== راه‌اندازی اولیه ==========
    toggleStoryType();
    refreshPreview();

    $('#expiry_date_picker').pDatepicker({
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
        altField: '#expiryDate',
        altFormat: 'YYYY-MM-DD HH:mm:ss',

        onSelect: function (unixDate) {
            var date = $('#expiryDate').val();
            $('#expiryDate').val(date.toEnglishDigit());
        }
    });


    $('#searchProductId').click(function() {
        const productId=$('#productId').val();
        if (productId==""){
            toastr.warning('شناسه محصول را وارد کنید', 'پیغام', {
                positionClass: 'toast-bottom-left',
                containerId: 'toast-bottom-left'
            });
            return
        }else {
            $('.story-product').addClass('hidden')
            $('.story-product .discount-percent').addClass('hidden')
            $('.story-product .image img').attr('src','')
            block('.get-product')
            $.ajax({
                url: $('#searchProductId').data('action'),
                type: 'POST',
                data: {productId:productId},
                success: function (response) {
                    if (!response.success){
                        toastr.error(response.message, 'پیغام', {
                            positionClass: 'toast-bottom-left',
                            containerId: 'toast-bottom-left'
                        });
                    }else if (response.success){
                        const product=response.product;
                        $('.story-product .image img').attr('src',product.image)
                        $('.story-product .title').text(product.title)
                        $('.story-product .title').attr('title',product.title)
                        $('.story-product .product-price-now').text(product.price)
                        $('.story-product .discount-percent').text(product.discount)
                        $('.story-product .product-colors li').css('background-color',product.color.value)
                        $('.story-product .product-colors li').attr('title',product.color.name)
                        toastr.success(response.message, 'پیغام', {
                            positionClass: 'toast-bottom-left',
                            containerId: 'toast-bottom-left'
                        });
                        $('.story-product').removeClass('hidden')
                        if (product.discount!=""){
                            $('.story-product .discount-percent').removeClass('hidden')
                        }

                    }
                    unblock('.get-product');
                },
                beforeSend: function (xhr) {
                    xhr.setRequestHeader(
                        'X-CSRF-TOKEN',
                        $('meta[name="csrf-token"]').attr('content')
                    );
                }
            });
        }
    })



    function toggleStoryActiveLikes() {
        if($('input[name="active_likes"]').is(':checked')) {
            $('.story-likes').removeClass('hidden');
        } else {
            $('.story-likes').addClass('hidden');

        }
    }

    toggleStoryActiveLikes();

    $('input[name="active_likes"]').on('change', function() {
        toggleStoryActiveLikes();
    });

    function toggleStoryActiveComments() {
        if($('input[name="active_comments"]').is(':checked')) {
            $('.story-comments').removeClass('hidden');
        } else {
            $('.story-comments').addClass('hidden');
        }
    }

    toggleStoryActiveComments();

    $('input[name="active_comments"]').on('change', function() {
        toggleStoryActiveComments();
    });

});
