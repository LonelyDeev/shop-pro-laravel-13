$(document).ready(function() {
    /*=========+===================
      Information Tab Js Codes
    ===============================*/

    $('#tags').tagsInput({
        defaultText: 'افزودن',
        width: '100%'
    });

    // validate form with jquery validation plugin
    jQuery('#information-form').validate({
        rules: {
            info_site_title: {
                required: true
            }
        },
        messages: {
            info_site_title: {
                required: 'لطفا عنوان وبسایت را وارد کنید'
            }
        }
    });

    $('#information-form').submit(function(e) {
        e.preventDefault();

        if ($(this).valid()) {
            var formData = new FormData(this);
            $('#information-form-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> در حال ذخیره...');
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function(data) {
                    Swal.fire({
                        type: 'success',
                        title: 'تغییرات با موفقیت ذخیره شد',
                        confirmButtonClass: 'btn btn-primary',
                        confirmButtonText: 'باشه',
                        buttonsStyling: false
                    });

                    if (data.admin_route_prefix_changed) {
                        if (data.admin_route_prefix) {
                            window.location.href =
                                FRONT_URL +
                                '/admin/' +
                                data.admin_route_prefix +
                                '/settings/information';
                        } else {
                            window.location.href =
                                FRONT_URL + '/admin/settings/information';
                        }
                    }
                },
                beforeSend: function(xhr) {
                    block('#main-card');
                    xhr.setRequestHeader(
                        'X-CSRF-TOKEN',
                        $('meta[name="csrf-token"]').attr('content')
                    );
                },
                complete: function() {
                    unblock('#main-card');
                },

                cache: false,
                contentType: false,
                processData: false
            });
        }
    });
});


$(document).ready(function () {

    // متغیرهای سراسری برای دسترسی به نقشه‌ها و مارکرها
    let googleMap = null;
    let googleMarkers = [];

    let mapIrInstance = null;
    let mapIrMarker = null;

    // ==========================================
    // 1. توابع مربوط به گوگل مپ
    // ==========================================
    function initGoogleMap() {
        if (typeof google === 'undefined') return;

        const myLatlng = new google.maps.LatLng(info_latitude, info_Longitude);
        const mapOptions = {
            zoom: 16,
            center: myLatlng,
            scrollwheel: true,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        googleMap = new google.maps.Map(document.getElementById('googleMap'), mapOptions);

        // رویداد کلیک روی نقشه گوگل
        googleMap.addListener('click', function (event) {
            syncMapsAndInputs(event.latLng.lat(), event.latLng.lng());
        });

        // افزودن مارکر اولیه
        updateGoogleMarker(info_latitude, info_Longitude);

        // حذف اسپینر
        $('.gs-map-container').prop('disabled', false);
        $('.gs-map-container .spinner-loader').remove();
    }

    function updateGoogleMarker(latitude, longitude) {
        if (!googleMap) return;

        // حذف مارکرهای قبلی
        for (let i = 0; i < googleMarkers.length; i++) {
            googleMarkers[i].setMap(null);
        }
        googleMarkers = [];

        // ایجاد مارکر جدید
        const newPosition = new google.maps.LatLng(latitude, longitude);
        const marker = new google.maps.Marker({
            position: newPosition,
            map: googleMap
        });
        googleMarkers.push(marker);

        // مرکز کردن نقشه روی مارکر جدید
        googleMap.setCenter(newPosition);
    }


    // ==========================================
    // 2. توابع مربوط به نقشه Map.ir
    // ==========================================
    function initMapIr() {
        console.log(Mapp);
        if (typeof Mapp === 'undefined') return;

        mapIrInstance = new Mapp({
            element: '#mapIr',
            presets: {
                latlng: {
                    lat: info_latitude,
                    lng: info_Longitude
                },
                zoom: 16
            },
            apiKey: mapIrApiKey
        });

        mapIrInstance.addLayers();

        // رویداد کلیک روی نقشه مپیر
        mapIrInstance.map.on('click', function (e) {
            syncMapsAndInputs(e.latlng.lat, e.latlng.lng);
        });

        // افزودن مارکر اولیه
        updateMapIrMarker(info_latitude, info_Longitude);

        // حذف اسپینر
        $('.gs-map-container').prop('disabled', false);
        $('.gs-map-container .spinner-loader').remove();
    }

    function updateMapIrMarker(latitude, longitude) {
        if (!mapIrInstance) return;

        // حذف مارکر قبلی
        if (mapIrMarker) {
            mapIrInstance.removeMarker('advanced-marker');
        }

        // ایجاد مارکر جدید
        mapIrMarker = mapIrInstance.addMarker({
            name: 'advanced-marker',
            latlng: {
                lat: latitude,
                lng: longitude
            },
            icon: mapIrInstance.icons.red,
            popup: false,
            pan: false,
            draggable: false,
            history: false
        });
    }


    // ==========================================
    // 3. تابع مرکزی برای همگام‌سازی نقشه‌ها و اینپوت‌ها
    // ==========================================
    function syncMapsAndInputs(latitude, longitude) {
        // بروزرسانی مارکر در هر دو نقشه به صورت همزمان
        updateGoogleMarker(latitude, longitude);
        updateMapIrMarker(latitude, longitude);

        // بروزرسانی فیلدهای ورودی
        $('#Longitude').val(longitude);
        $('#latitude').val(latitude);
    }


    // ==========================================
    // 4. اجرای اولیه و رویدادها
    // ==========================================
    var mapInitialized=false
    $('.gs-tab-btn').click(function() {
        if ($('.gs-tab-btn.active').data('tab') === "map" && !mapInitialized) {
            $('.gs-map-container').prop('disabled', true).html('<div class="spinner-loader"><div class="spinner-tag"><span class="spinner-border spinner-border-sm"></span> در حال بارگذاری مپ...</div></div>');

            setTimeout(function() {
                // راه‌اندازی اولیه نقشه‌ها
                initMapIr();
                initGoogleMap();
                mapInitialized=true;
            }, 1000);
        }
    });




    // رویداد تغییر مختصات به صورت دستی در اینپوت‌ها
    $('#Longitude, #latitude').on('change', function () {
        const lat = $('#latitude').val();
        const lng = $('#Longitude').val();

        if (!lat || !lng) return;
        syncMapsAndInputs(lat, lng);
    });

    // کدهای مربوط به مخفی کردن نقشه‌ها کاملاً حذف شدند تا هر دو همزمان نمایش داده شوند

});


document.addEventListener('DOMContentLoaded', function() {

    /* ── Image uploaders (file manager callback) ── */
    var uploaders = [
        {btn: 'btn_icon', input: 'img_icon'},
        {btn: 'btn_logo', input: 'img_logo'},
        {btn: 'btn_logo_seller', input: 'img_logo_seller'},
        {btn: 'btn_logo_panel', input: 'img_logo_panel'},
        {btn: 'btn_og', input: 'img_og'}
    ];

    uploaders.forEach(function(u) {
        var el = document.getElementById(u.btn);
        if (el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                inputId = u.input;
                inputIdBtn = u.btn;
                window.open('/file-manager/fm-button', 'fm', 'width=1400,height=800');
            });
        }
    });

});

let inputId = '';
let inputIdBtn = '';

// set file link
function fmSetLink($url) {
    document.getElementById(inputId).value = $url;
    $('#' + inputIdBtn + ' .img-uploader').removeClass('display-hidden');

    $('#' + inputIdBtn).parent().find('.remove-img-uploader').removeClass('display-hidden');
    $('#' + inputIdBtn + ' .dz-message').addClass('display-hidden');
    $('#' + inputIdBtn + ' img').attr('src', $url);
}

$('.remove-img-uploader').click(function() {
    var item = this;
    $(item).parent().find('.img-uploader').addClass('display-hidden');
    $(item).parent().find('.dz-message').removeClass('display-hidden');
    $(item).parent().find('input').val('');
    $(item).addClass('display-hidden');
});

/* ── Tab switching ── */
const tabBtns = document.querySelectorAll('.gs-tab-btn');
const tabPanes = document.querySelectorAll('.gs-tab-pane');

tabBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
        const target = this.dataset.tab;

        tabBtns.forEach(b => b.classList.remove('active'));
        tabPanes.forEach(p => p.classList.remove('active'));

        this.classList.add('active');
        const pane = document.getElementById('tab-' + target);
        if (pane) pane.classList.add('active');
    });
});

/* ── Multi-language toggle ── */
document.querySelectorAll('#multiLangToggle .gs-toggle-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const val = this.dataset.value;
        document.getElementById('multiLangValue').value = val;

        document.querySelectorAll('#multiLangToggle .gs-toggle-btn').forEach(function(b) {
            b.classList.remove('gs-toggle-on', 'gs-toggle-off');
        });

        this.classList.add(val === '1' ? 'gs-toggle-on' : 'gs-toggle-off');
    });
});

/* ── Map type card highlight ── */
document.querySelectorAll('.gs-map-radio').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.gs-map-type-card').forEach(c => c.classList.remove('gs-map-selected'));
        this.closest('.gs-map-type-card').classList.add('gs-map-selected');
    });
    if (radio.checked) {
        radio.closest('.gs-map-type-card').classList.add('gs-map-selected');
    }
});

/* ── Character counter ── */
document.querySelectorAll('[data-max]').forEach(function(counter) {
    const targetName = counter.dataset.target;
    const max = parseInt(counter.dataset.max, 10);
    const textarea = document.querySelector('[name="' + targetName + '"]');

    if (!textarea) return;

    function update() {
        counter.textContent = textarea.value.length + ' / ' + max + ' کاراکتر';
    }

    textarea.addEventListener('input', update);
    update();
});

/* ── Color sync ── */
const colorPicker = document.querySelector('[name="info_primary_color"]');
const colorText = document.querySelector('[name="info_primary_color_text"]');

if (colorPicker && colorText) {
    colorPicker.addEventListener('input', () => colorText.value = colorPicker.value);
    colorText.addEventListener('input', () => {
        if (/^#[0-9a-fA-F]{6}$/.test(colorText.value)) {
            colorPicker.value = colorText.value;
        }
    });
}



function copyText(element) {
    // دریافت متن داخل عنصر
    const textToCopy = element.innerText.trim();

    // استفاده از Clipboard API
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(textToCopy)
            .then(() => {
                // نمایش پیام موفقیت (اختیاری)
                toastr.success('متن کپی شد', null,{ positionClass: 'toast-bottom-left', containerId: 'toast-bottom-left' });
            })
            .catch(err => {
                console.error('خطا در کپی:', err);
                fallbackCopy(textToCopy);
            });
    }
}
