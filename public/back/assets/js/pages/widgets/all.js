// Drives the modal-based type picker and keeps the original <select id="widget-key">
// in sync, so the existing create.js logic (fetching template fields, etc.) keeps working untouched.
(function () {
    var $select      = $('#widget-key');
    var $modal       = $('#widget-type-modal');
    var $cards       = $('#template-gallery .template-card');
    var $search      = $('#type-picker-search');
    var $noResults   = $('#type-picker-no-results');
    var $trigger     = $('#type-picker-trigger');
    var $triggerThumb= $('#type-picker-thumb');
    var $triggerTitle= $('#type-picker-title');
    var $triggerSub  = $('#type-picker-subtitle');
    var $image       = $('#widget-image');
    var $placeholder = $('#preview-placeholder');

    // guards against selectKey() -> select.trigger('change') -> our own change
    // handler calling selectKey() again -> infinite loop (stack overflow).
    var isSyncingFromPicker = false;

    function updatePreview(key) {
        var $option = $select.find('option[value="' + key + '"]');
        var image   = $option.data('image');
        var title   = $option.data('title') || $option.text().trim();

        $cards.removeClass('is-selected').filter('[data-key="' + key + '"]').addClass('is-selected');

        $trigger.addClass('has-value');
        $triggerTitle.text(title);
        $triggerSub.text('برای تغییر، دوباره کلیک کنید');

        if (image) {
            $triggerThumb.css('background-image', 'url(' + image + ')').html('');
            $image.attr('src', image).show();
            $placeholder.hide();
        } else {
            $triggerThumb.css('background-image', 'none').html('<i class="feather icon-layout"></i>');
            $image.hide();
            $placeholder.show();
        }
    }

    // called when the user picks a card in the modal
    function selectKey(key) {
        updatePreview(key);

        isSyncingFromPicker = true;
        $select.val(key).trigger('change');
        isSyncingFromPicker = false;
    }

    $cards.on('click', function () {
        selectKey($(this).data('key'));
        $modal.modal('hide');
    });

    $search.on('keyup', function () {
        var term = $(this).val().trim().toLowerCase();
        var visible = 0;

        $cards.each(function () {
            var match = $(this).data('title').toString().toLowerCase().indexOf(term) !== -1;
            $(this).toggle(match);
            if (match) visible++;
        });

        $noResults.toggle(visible === 0);
    });

    $modal.on('hidden.bs.modal', function () {
        $search.val('').trigger('keyup');
    });

    // if the select gets changed by something other than the picker itself
    // (e.g. programmatically elsewhere), just refresh the UI — don't re-trigger change.
    $select.on('change', function () {
        if (isSyncingFromPicker) return;
        var key = $(this).val();
        if (key) updatePreview(key);
    });

    var initialKey = $select.val();
    if (initialKey) {
        updatePreview(initialKey);
    }
})();
