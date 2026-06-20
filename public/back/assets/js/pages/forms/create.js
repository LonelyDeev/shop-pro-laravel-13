$(document).ready(function() {

    $('#tags').tagsInput({
        defaultText: 'افزودن',
        width: '100%',
        autocomplete_url: BASE_URL + '/get-tags'
    });


});
