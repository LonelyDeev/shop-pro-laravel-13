$('.users').select2ToTree({
    rtl: true,
    width: '100%'
});
$('.sellers').select2ToTree({
    rtl: true,
    width: '100%'
});

$('input[name=users]').click(function () {
    if ($('input[name=users]').is(':checked')) {
        $('#users-div').removeClass('d-none')
    }else {
        $('#users-div').addClass('d-none')
    }
});

$('input[name=sellers]').click(function () {
    if ($('input[name=sellers]').is(':checked')) {
        $('#sellers-div').removeClass('d-none')
    }else {
        $('#sellers-div').addClass('d-none')
    }
});

