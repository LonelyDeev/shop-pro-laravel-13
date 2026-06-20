$('.dd').nestable({
    maxDepth: (typeof maxDepth !== 'undefined') ? maxDepth : 10,
    callback: function() {
        if (JSON.stringify($('.dd').nestable('serialize')) != JSON.stringify(categories)) {
            $('#save-changes').prop('disabled', false);
            saveChanges();
        }
    }
});

var categories = $('.dd').nestable('serialize');

$('#create-category').submit(function(e) {
    e.preventDefault();

    var form = $(this);
    var formData = new FormData(this);

    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: formData,
        success: function(data) {
            $('.dd-empty').remove();
            $('.dd').nestable('add', { "id": data.id, "content": '<span class="category-title">' + data.title + '</span><a data-category="' + data.slug + '" class="float-right delete-category dd-nodrag" href="javascript:void(0)"><i class="fa fa-trash text-danger px-1"></i>حذف</a><a data-category="' + data.slug + '"  class="float-right edit-category dd-nodrag" href="javascript:void(0)"><i class="fa fa-pencil text-info px-1"></i>ویرایش</a>' });
            $('#create-category').trigger('reset');

            categories = $('.dd').nestable('serialize');
        },
        beforeSend: function(xhr) {
            block('#main-block');
            xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
        },
        complete: function() {
            unblock('#main-block');
        },
        cache: false,
        contentType: false,
        processData: false
    });
});

$(document).on('click', '.delete-category', function() {
    $('#confirm-delete').data('category', $(this).data('category'));
    jQuery('#modal-delete').modal('show');
});

$(document).on('click', '.edit-category', function() {
    var category = $(this).data('category');

    $.ajax({
        url: BASE_URL + '/categories/' + category + '/edit',
        type: 'get',
        data: {},
        success: function(data) {
            $('#edit-form').attr('action', BASE_URL + '/categories/' + category);
            $('#edit-form').data('category', category);

            $('#modal-edit .modal-body').html(data);

            jQuery('#modal-edit').modal('show');

            $('.tags').tagsInput({
                'defaultText': 'افزودن',
                'width': '100%',
                'autocomplete_url': BASE_URL + '/get-tags',
            });

            if (typeof CKEDITOR !== 'undefined') {
                CKEDITOR.replace('category-description');
            }

            $('#filter_type').trigger('change');
        },
        beforeSend: function(xhr) {
            block('#main-block');
        },
        complete: function() {
            unblock('#main-block');
        },
        cache: false,
        contentType: false,
        processData: false
    });
});


$('#modal-edit').on('shown.bs.modal', function() {
    $('#edit-title').focus();
});


$('#edit-form').submit(function(e) {
    e.preventDefault();

    var formData = new FormData(this);
    var form = $(this);
    var category = form.data('category');

    if (typeof CKEDITOR !== 'undefined') {
        formData.append('description', CKEDITOR.instances['category-description'].getData())
    }

    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: formData,
        success: function(data) {
            $('a[data-category=' + category + ']').closest('.dd-handle').find('.category-title').text(data.title);
            $('[data-category=' + category + ']').data('category', data.slug);
            $('[data-category=' + category + ']').attr('data-category', data.slug);
            jQuery('#modal-edit').modal('hide');

        },
        beforeSend: function(xhr) {
            block('#modal-edit .modal-content');
            xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
        },
        complete: function() {
            unblock('#modal-edit .modal-content');
        },
        cache: false,
        contentType: false,
        processData: false
    });

});

$('#confirm-delete').click(function() {

    jQuery('#modal-delete').modal('hide');

    var category = $(this).data('category');

    $.ajax({
        url: BASE_URL + '/categories/' + category,
        type: 'post',
        data: {
            _method: 'DELETE',
        },
        success: function(data) {
            $('.dd').nestable('remove', data.id);

            Swal.fire({
                text: "دسته بندی با موفقیت حذف شد",
                type: 'success',
                confirmButtonText: 'باشه',
            });

            categories = $('.dd').nestable('serialize');
        },
        beforeSend: function(xhr) {
            block('#main-block');
            xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
        },
        complete: function() {
            unblock('#main-block');
        },
    });

});

function saveChanges() {

    if (!categories.length) {
        return;
    }

    $.ajax({
        url: BASE_URL + '/categories/sort',
        type: 'post',
        data: {
            categories: $('.dd').nestable('serialize'),
            type: $('input[name="type"]').first().val(),
        },
        success: function() {
            categories = $('.dd').nestable('serialize');
        },
        beforeSend: function(xhr) {
            xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
            $('#save-changes').show();
        },
        complete: function() {
            $('#save-changes').hide();
        },

    });
}

window.onbeforeunload = function() {
    if (!$('#save-changes').is(":hidden")) {
        return "Are you sure?";
    }
};

// generate slug

$(document).on('click', '#generate-category-slug', function(e) {
    e.preventDefault();

    var title = $('input[name="meta_title"]').val();

    $.ajax({
        url: BASE_URL + '/category/slug',
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

$(document).on('change', '#filter_type', function() {
    var filterType = $(this).val();

    if (filterType == 'filterId') {
        $('#filter_id').prop('disabled', false);
    } else {
        $('#filter_id').prop('disabled', true);
    }
});