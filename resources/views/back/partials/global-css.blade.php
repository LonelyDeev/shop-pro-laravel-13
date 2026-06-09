<!-- BEGIN: Custom CSS-->
<link rel="stylesheet" type="text/css" href="{{ asset('back/app-assets/css-rtl/custom-rtl.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('back/assets/css/style-rtl.css') }}?v=11">
<!-- END: Custom CSS-->

<!-- font css file -->
@if (user_option('theme_font', 'iranyekan') == 'iranyekan')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/fonts/iranyekan/style.css') }}">
@elseif (user_option('theme_font', 'iranyekan') == 'Yekan')
     <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/fonts/yekan/style.css') }}">
@elseif (user_option('theme_font', 'iranyekan') == 'Vazir')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/fonts/vazir/style.css') }}">
@else
    <link rel="stylesheet" type="text/css" href="{{ asset('back/assets/fonts/iransansdn/style.css') }}">
@endif
