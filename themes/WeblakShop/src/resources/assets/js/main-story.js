// theme_assets/js/main-story.js

$(document).ready(function() {
    // ===== تنظیمات اصلی =====
    const $storyModal = $('#story-modal');
    const $storyContainer = $('#story-container');

    let storyProgressInterval = null;
    let storyIsPaused = false;
    let storyCurrentElapsed = 0;
    let storyCurrentDuration = 10000;
    let isVideoMode = false;
    let currentVideoElement = null;
    let videoSyncHandler = null;
    let isContentLoaded = false;

    let currentStoriesList = [];
    let currentStoryIndex = 0;
    let viewedStories = {};
    let resumeTimeout = null;

    // ===== توابع اصلی =====

    // دریافت زمان استوری
    function getStoryDurationFromDB($storyElement) {
        let duration = $storyElement.data('duration');
        if (duration && !isNaN(parseInt(duration)) && parseInt(duration) > 0) {
            return parseInt(duration);
        }

        const storyId = $storyElement.data('story-id');
        if (storyId && window.storyDurations && window.storyDurations[storyId]) {
            return window.storyDurations[storyId];
        }

        return 10000;
    }

    // به‌روزرسانی شمارنده
    function updateStoryCounter() {
        const currentNumber = currentStoryIndex + 1;
        const totalNumber = currentStoriesList.length;

        $storyContainer.find('.story-current-index').text(currentNumber);
        $storyContainer.find('.story-total-count').text(totalNumber);
    }

    // ساخت نوار پیشرفت
    function buildProgressBar() {
        const $progressContainer = $storyContainer.find('.story-progress-bars');
        if ($progressContainer.length) {
            $progressContainer.empty();
            $progressContainer.append(`
                <div class="progress-bar-item">
                    <div class="progress-bar-fill" style="width: 0%; transition: width 0.1s linear;"></div>
                </div>
            `);
        }
    }

    // به‌روزرسانی نوار پیشرفت
    function updateProgressBar(percentage) {
        const $fill = $storyContainer.find('.progress-bar-fill');
        $fill.css('width', `${Math.min(percentage, 100)}%`);
    }

    // توقف تایمر ویدیو
    function stopVideoTimer(videoElement) {
        if (videoElement) {
            if (videoSyncHandler) {
                videoElement.removeEventListener('timeupdate', videoSyncHandler);
                videoSyncHandler = null;
            }
            videoElement.removeEventListener('ended', goToNextStory);
        }
    }

    // شروع تایمر برای تصویر
    function startImageTimer() {
        if (storyProgressInterval) {
            clearInterval(storyProgressInterval);
            storyProgressInterval = null;
        }

        if (storyIsPaused) return;

        if (!isContentLoaded) {
            setTimeout(() => {
                if (isContentLoaded) {
                    startImageTimer();
                }
            }, 100);
            return;
        }

        if (storyCurrentDuration <= 0) {
            storyCurrentDuration = 10000;
        }

        storyProgressInterval = setInterval(() => {
            if (!storyIsPaused && isContentLoaded) {
                storyCurrentElapsed += 100;
                const percentage = (storyCurrentElapsed / storyCurrentDuration) * 100;
                updateProgressBar(percentage);

                if (storyCurrentElapsed >= storyCurrentDuration) {
                    clearInterval(storyProgressInterval);
                    storyProgressInterval = null;
                    isContentLoaded = false;
                    goToNextStory();
                }
            }
        }, 100);
    }

    // شروع تایمر برای ویدیو
    function startVideoTimer(videoElement) {
        if (storyProgressInterval) {
            clearInterval(storyProgressInterval);
            storyProgressInterval = null;
        }

        if (!videoElement) return;

        if (!isContentLoaded) return;

        // همیشه از اول ویدیو شروع کن
        videoElement.currentTime = 0;
        storyCurrentElapsed = 0;
        updateProgressBar(0);

        // پخش خودکار ویدیو
        const playPromise = videoElement.play();
        if (playPromise !== undefined) {
            playPromise.catch(error => {
                videoElement.muted = true;
                videoElement.play().catch(e => console.log('Video muted autoplay error:', e));
            });
        }

        const updateVideoProgress = () => {
            if (!storyIsPaused && videoElement.duration && isFinite(videoElement.duration) && isContentLoaded) {
                storyCurrentElapsed = videoElement.currentTime * 1000;
                const percentage = (storyCurrentElapsed / storyCurrentDuration) * 100;
                updateProgressBar(percentage);
            }
        };

        videoElement.addEventListener('timeupdate', updateVideoProgress);
        videoElement.addEventListener('ended', () => {
            isContentLoaded = false;
            goToNextStory();
        });

        videoSyncHandler = updateVideoProgress;

        videoElement.addEventListener('pause', () => {
            if (videoElement.paused && !storyIsPaused) {
                storyIsPaused = true;
            }
        });

        videoElement.addEventListener('play', () => {
            if (storyIsPaused) {
                storyIsPaused = false;
            }
        });
    }

    // شروع تایمر بر اساس نوع
    function startProgressTimer() {
        if (storyProgressInterval) {
            clearInterval(storyProgressInterval);
            storyProgressInterval = null;
        }

        if (!isContentLoaded) return;

        if (isVideoMode && currentVideoElement) {
            startVideoTimer(currentVideoElement);
        } else {
            startImageTimer();
        }
    }

    // رفتن به استوری بعدی
    function goToNextStory() {
        if (currentStoryIndex + 1 < currentStoriesList.length) {
            currentStoryIndex++;
            updateStoryCounter();
            loadStory(currentStoriesList[currentStoryIndex]);
        } else {
            $storyModal.modal('hide');
            resetAllStories();
        }
    }

    // رفتن به استوری قبلی
    function goToPrevStory() {
        if (currentStoryIndex > 0) {
            currentStoryIndex--;
            updateStoryCounter();
            loadStory(currentStoriesList[currentStoryIndex]);
        }
    }

    // ریست همه چیز
    function resetAllStories() {
        if (storyProgressInterval) {
            clearInterval(storyProgressInterval);
            storyProgressInterval = null;
        }

        if (currentVideoElement) {
            stopVideoTimer(currentVideoElement);
            currentVideoElement.pause();
            currentVideoElement = null;
        }

        storyCurrentElapsed = 0;
        storyIsPaused = false;
        isVideoMode = false;
        isContentLoaded = false;

        if (resumeTimeout) {
            clearTimeout(resumeTimeout);
            resumeTimeout = null;
        }
    }

    // ثبت بازدید
    /*function seenStory(storyId) {
        if (viewedStories[storyId]) return;
        viewedStories[storyId] = true;

        $.post($('.allStoryIndex').data('action'), {
            story_id: storyId,
            _token: $('meta[name="csrf-token"]').attr('content')
        }).fail(function(xhr) {
            console.error('Error recording view:', xhr);
        });
    }*/

    // بارگذاری استوری
    function loadStory(storyId) {
        if (storyProgressInterval) {
            clearInterval(storyProgressInterval);
            storyProgressInterval = null;
        }

        if (currentVideoElement) {
            stopVideoTimer(currentVideoElement);
            currentVideoElement.pause();
            currentVideoElement = null;
        }

        isVideoMode = false;
        isContentLoaded = false;
        storyCurrentElapsed = 0;
        storyCurrentDuration = 10000;
        storyIsPaused = false;

        updateProgressBar(0);

        $.ajax({
            url: '/story/' + storyId,
            type: 'GET',
            beforeSend: function() {
                $storyContainer.html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">در حال بارگذاری...</p></div>');
            },
            success: function(html) {
                renderStoryInModal(html, storyId);
            },
            error: function() {
                $storyContainer.html('<div class="text-center p-5 text-danger"><i class="fa fa-exclamation-triangle fa-2x"></i><p class="mt-2">خطا در بارگذاری استوری</p></div>');
            }
        });
    }

    // رندر استوری در مودال
    function renderStoryInModal(html, storyId) {
        $storyContainer.html(html);

        const $storyElement = $storyContainer.find('.story-item');
        $storyElement.data('story-id', storyId);
        $storyElement.attr('id', `story-${storyId}`);

        buildProgressBar();
        updateProgressBar(0);

        if (storyProgressInterval) {
            clearInterval(storyProgressInterval);
            storyProgressInterval = null;
        }

        const $video = $storyContainer.find('video');
        isVideoMode = $video.length > 0;
        isContentLoaded = false;
        storyCurrentElapsed = 0;
        storyIsPaused = false;

        updateStoryCounter();

        if (isVideoMode) {
            currentVideoElement = $video[0];

            const startVideo = () => {
                isContentLoaded = true;
                storyCurrentDuration = currentVideoElement.duration * 1000;
                storyCurrentElapsed = 0;
                if (currentVideoElement) {
                    currentVideoElement.currentTime = 0;
                }
                updateProgressBar(0);
                startProgressTimer();
            };

            if (currentVideoElement.readyState >= 1) {
                startVideo();
            } else {
                currentVideoElement.addEventListener('loadedmetadata', startVideo, { once: true });
            }

            currentVideoElement.addEventListener('error', function() {
                isContentLoaded = true;
                storyCurrentDuration = 10000;
                startProgressTimer();
            });

        } else {
            currentVideoElement = null;
            const $image = $storyContainer.find('.story-content img');

            const startImage = () => {
                isContentLoaded = true;
                storyCurrentDuration = getStoryDurationFromDB($storyElement);
                storyCurrentElapsed = 0;
                updateProgressBar(0);
                startProgressTimer();
            };

            if ($image.length) {
                if ($image[0].complete) {
                    startImage();
                } else {
                    $image.off('load').on('load', startImage);
                    $image.off('error').on('error', startImage);
                }
            } else {
                startImage();
            }
        }

        seenStory(storyId);
    }

    // ===== رویدادها =====

    // کلیک روی استوری در لیست اصلی
    $('.allStoryIndex .storyItem').on('click', function() {
        const $this = $(this);
        const storyId = $this.attr('id');

        currentStoriesList = $('.allStoryIndex .storyItem').map(function() {
            return $(this).attr('id');
        }).get();

        currentStoryIndex = currentStoriesList.indexOf(storyId);
        updateStoryCounter();
        $this.addClass('unActive');

        $storyModal.modal('show');
        loadStory(storyId);
    });

    // دکمه‌های قبلی و بعدی
    $storyContainer.on('click', '.story-nav-prev', function(e) {
        e.stopPropagation();
        goToPrevStory();
    });

    $storyContainer.on('click', '.story-nav-next', function(e) {
        e.stopPropagation();
        goToNextStory();
    });

    // کلیک روی نیمه راست/چپ
    $storyContainer.on('click', '.story-item', function(e) {
        const $target = $(e.target);

        if ($target.closest('.story-nav, .story-widget-area, .story-product, .close-story, .story-likes-comments, .story-video-sound').length) {
            return;
        }

        const windowWidth = $(window).width();
        const clickX = e.clientX;

        if (clickX > windowWidth / 2) {
            goToNextStory();
        } else if (clickX < windowWidth / 2) {
            goToPrevStory();
        }
    });

    // دکمه بستن
    $storyContainer.on('click', '.close-story', function() {
        $storyModal.modal('hide');
    });

    // کنترل صدا
    $storyContainer.on('click', '.story-video-sound', function() {
        const storyId = currentStoriesList[currentStoryIndex];
        const video = document.getElementById('video-' + storyId);

        if (!video) return;

        const $icon = $(this).find('i');
        video.muted = !video.muted;

        if (video.muted) {
            $icon.removeClass('fa-volume-up').addClass('fa-volume-xmark');
        } else {
            $icon.removeClass('fa-volume-xmark').addClass('fa-volume-up');
        }
    });

    // لایک استوری
    $storyContainer.on('click', '.story-likes', function(e) {
        e.stopPropagation();

        const $this = $(this);
        const storyId = currentStoriesList[currentStoryIndex];
        const $likeIcon = $this.find('i');
        const $likeCount = $this.find('span');
        const isCurrentlyLiked = $this.hasClass('liked');

        if ($this.hasClass('loading')) return false;
        $this.addClass('loading');

        if (!isCurrentlyLiked) {
            $likeIcon.removeClass('fa-heart-o').addClass('fa-heart');
            $likeCount.text(parseInt($likeCount.text()) + 1);
            $this.addClass('liked');
        } else {
            $likeIcon.removeClass('fa-heart').addClass('fa-heart-o');
            $likeCount.text(parseInt($likeCount.text()) - 1);
            $this.removeClass('liked');
        }

        $.ajax({
            url: $this.data('action'),
            type: 'POST',
            data: {
                story_id: storyId,
                is_liked: !isCurrentlyLiked ? 1 : 0
            },
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
            },
            success: function(response) {
                if (response.success) {
                    $likeCount.text(response.likes_count);
                    if (response.is_liked) {
                        $likeIcon.removeClass('fa-heart-o').addClass('fa-heart');
                        $this.addClass('liked');
                    } else {
                        $likeIcon.removeClass('fa-heart').addClass('fa-heart-o');
                        $this.removeClass('liked');
                    }
                } else {
                    revertLike();
                }
            },
            error: function() {
                revertLike();
                toastr.error('مشکلی در ارتباط با سرور وجود دارد');
            },
            complete: function() {
                $this.removeClass('loading');
            }
        });

        function revertLike() {
            if (!isCurrentlyLiked) {
                $likeIcon.removeClass('fa-heart').addClass('fa-heart-o');
                $likeCount.text(parseInt($likeCount.text()) - 1);
                $this.removeClass('liked');
            } else {
                $likeIcon.removeClass('fa-heart-o').addClass('fa-heart');
                $likeCount.text(parseInt($likeCount.text()) + 1);
                $this.addClass('liked');
            }
        }
    });

    // ثبت تعامل کلیک روی محصول/ویجت
    $storyContainer.on('click', '[data-interaction-type]', function() {
        const $element = $(this);
        const storyId = currentStoriesList[currentStoryIndex];
        const interactionType = $element.data('interaction-type');
        const targetUrl = $element.attr('href');

        $.ajax({
            url: $('.allStoryIndex').data('action-interaction'),
            type: 'POST',
            data: {
                story_id: storyId,
                type: interactionType,
                target_url: targetUrl,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            error: function(xhr) {
                console.error('Error recording interaction:', xhr);
            }
        });
    });

    // ===== مدیریت دیدگاه های استوری =====

// باز کردن مودال کامنت
    $storyContainer.on('click', '.story-comments', function(e) {
        e.stopPropagation();

        const storyId = $(this).data('story-id');
        const $modal = $('#storyCommentsModal');
        const $commentsList = $('#storyCommentsList');
        const $totalSpan = $modal.find('.comments-total-count');
        const actionUrl = $(this).data('action');
        $('.story-comments-modal .modal-content').addClass('story-modal-from-'+storyId);

        // ذخیره story_id و action در فرم
        $('#comment_story_id').val(storyId);

        // توقف تایمر و ویدیو
        if (storyProgressInterval) {
            clearInterval(storyProgressInterval);
            storyProgressInterval = null;
        }
        storyIsPaused = true;

        if (currentVideoElement) {
            currentVideoElement.pause();
        }

        // بارگذاری دیدگاه ها
        $commentsList.html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">در حال بارگذاری دیدگاه ها...</p></div>');

        $.ajax({
            url: actionUrl,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    $commentsList.html(response.html);
                    $totalSpan.text(response.total);
                    $modal.find('.modal-title').html(`<i class="fa fa-comment"></i> دیدگاه ها (${response.total})`);
                }

                // حذف رویداد قبلی قبل از اتصال مجدد
                $('#storyCommentForm').off('submit');

                // ارسال کامنت جدید
                $('#storyCommentForm').on('submit', function(e) {
                    e.preventDefault();
                    storyCommentFormSubmit();
                });

                // بارگذاری بیشتر
                $('.load-more-comments').off('click').on('click', function() {
                    const btn = $(this);
                    loadMoreComments(btn);
                });
            },
            error: function() {
                $commentsList.html('<div class="text-center p-4 text-danger"><i class="fa fa-exclamation-triangle"></i><p>خطا در بارگذاری دیدگاه ها</p></div>');
            }
        });

        $modal.modal('show');
    });
// بستن مودال کامنت
    $('#storyCommentsModal .close-comments-modal, #storyCommentsModal .btn-close').on('click', function() {
        $('#storyCommentsModal').modal('hide');
    });

    $('#storyCommentsModal').on('hidden.bs.modal', function() {
        if (storyIsPaused) {
            storyIsPaused = false;
            const $fill = $storyContainer.find('.progress-bar-fill');
            const currentWidth = parseFloat($fill.css('width')) || 0;

            if (currentWidth < 98) {
                if (isVideoMode && currentVideoElement && currentVideoElement.ended === false) {
                    currentVideoElement.play().catch(e => console.log('Video play error:', e));
                } else if (!isVideoMode) {
                    startProgressTimer();
                }
            }
        }

        // پاک کردن فرم
        $('#comment_input').val('');
        // ریست وضعیت دکمه ارسال
        $('.story-comment-submit').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> ارسال');
        $('.story-comment-submit').removeData('submitting');
        // حذف رویداد submit برای جلوگیری از اتصال مجدد
        $('#storyCommentForm').off('submit');
    });
// ارسال کامنت جدید
    function storyCommentFormSubmit() {
        const $form = $('#storyCommentForm');
        const storyId = $form.find('#comment_story_id').val();
        const $input = $form.find('.story-comment-input');
        const comment = $input.val().trim();
        const $submitBtn = $form.find('.story-comment-submit');

        // جلوگیری از ارسال همزمان چندباره
        if ($submitBtn.data('submitting') === true) {
            return;
        }

        if (!comment) {
            toastr.error('لطفا متن نظر را وارد کنید');
            return;
        }
        console.log($form.attr('action'));
        $submitBtn.data('submitting', true);
        $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: {
                story_id: storyId,
                comment: comment,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $input.val('');  // پاک کردن input

                    // به‌روزرسانی تعداد کل
                /*    const $totalSpan = $('.story-comments-modal .story-modal-from-'+storyId).find('#storyCommentsModal .comments-total-count');
                    const currentTotal = parseInt($totalSpan.text());
                    console.log($totalSpan);
                    console.log(currentTotal);
                    $totalSpan.text(currentTotal + 1);
                    $('.story-comments-modal .story-modal-from-'+storyId).find('#storyCommentsModal .comments-total-count').html(currentTotal);
*/
                    // اضافه کردن کامنت جدید به لیست
                    const $commentsList = $('.story-comments-modal .story-modal-from-'+storyId).find('.story-comments-list')
                    const newCommentHtml = `
                    <div class="comment-item" id="story-comment-${response.comment.id}">
                        <div class="comment-avatar">
                            <img src="${response.comment.avatar}" alt="${response.comment.name}">
                        </div>
                        <div class="comment-content">
                            <div class="comment-header">
                                <span class="comment-user">${response.comment.name}</span>
                                <span class="comment-time">لحظاتی پیش</span>
                            </div>
                            <div class="comment-text">${response.comment.comment}</div>
                        </div>
                    </div>
                `;

                    if ($commentsList.find('.text-muted').length) {
                        $commentsList.html(newCommentHtml);
                    } else {
                        $commentsList.prepend(newCommentHtml);
                    }

                    // به‌روزرسانی تعداد کامنت در فوتر استوری
                    //const storyIdValue = $('#comment_story_id').val();
                    //$(`.story-comments[data-story-id="${storyIdValue}"] span`).text(currentTotal + 1);
                }
            },
            error: function(xhr) {
                let errorMsg = 'خطا در ارسال نظر';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                toastr.error(errorMsg);
            },
            complete: function() {
                $submitBtn.data('submitting', false);
                $submitBtn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> ارسال');
            }
        });
    }
    // بارگذاری بیشتر دیدگاه ها
    // بارگذاری بیشتر دیدگاه ها
    function loadMoreComments(btn) {
        const page = btn.data('page');
        const actionUrl = btn.data('action');

        btn.prop('disabled', true).text('در حال بارگذاری...');

        $.ajax({
            url: actionUrl + '?page=' + page,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    // حذف دکمه قبلی
                    btn.closest('.text-center').remove();
                    // اضافه کردن محتوای جدید
                    $('#storyCommentsList').append(response.html);

                    // اتصال رویداد به دکمه‌های جدید
                    $('.load-more-comments').off('click').on('click', function() {
                        const newBtn = $(this);
                        loadMoreComments(newBtn);
                    });
                }
            },
            error: function() {
                toastr.error('خطا در بارگذاری بیشتر');
                btn.prop('disabled', false).text('مشاهده بیشتر');
            }
        });
    }
/*
    // ===== هاور موس =====
    $storyContainer.on('mouseenter', '.story-item', function() {
        if (resumeTimeout) clearTimeout(resumeTimeout);

        if (storyProgressInterval) {
            clearInterval(storyProgressInterval);
            storyProgressInterval = null;
        }
        storyIsPaused = true;

        if (currentVideoElement && !currentVideoElement.paused) {
            currentVideoElement.pause();
        }
    });

    $storyContainer.on('mouseleave', '.story-item', function() {
        resumeTimeout = setTimeout(() => {
            if ($storyModal.hasClass('show') && storyIsPaused === true && isContentLoaded) {
                const $fill = $storyContainer.find('.progress-bar-fill');
                const currentWidth = parseFloat($fill.css('width')) || 0;

                if (currentWidth < 98) {
                    storyIsPaused = false;

                    if (isVideoMode && currentVideoElement && currentVideoElement.ended === false) {
                        currentVideoElement.play().catch(e => console.log('Video play error:', e));
                    } else if (!isVideoMode) {
                        startProgressTimer();
                    }
                }
            }
            resumeTimeout = null;
        }, 50);
    });
*/

    // باز و بسته شدن مودال اصلی
    $storyModal.on('shown.bs.modal', function() {
        resetAllStories();
    });

    $storyModal.on('hide.bs.modal', function() {
        resetAllStories();
    });

    // ===== تابع کمکی برای escape HTML =====
    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // ===== تنظیم دکمه‌های ناوبری =====
    const $prev = $('.carousel-control-prev');
    const $next = $('.carousel-control-next');

    $prev.insertAfter($next);
    $prev.removeClass('carousel-control-prev').addClass('carousel-control-next');
    $next.removeClass('carousel-control-next').addClass('carousel-control-prev');

    $prev.find('.fa-angle-left').removeClass('fa-angle-left').addClass('fa-angle-right');
    $next.find('.fa-angle-right').removeClass('fa-angle-right').addClass('fa-angle-left');


});
