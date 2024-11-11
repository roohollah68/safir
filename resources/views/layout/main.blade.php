<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title')</title>

    <script src="/js/jquery-3.7.1.js"></script>

    <script src="/js/jquery-ui.js"></script>

    <script src="/js/numeral.min.js"></script>

    <script src="/js/popper.min.js"></script>

    <script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="/css/jquery-ui.css">

    <link rel="stylesheet" href="/bootstrap-5.1.3-dist/css/bootstrap.rtl.min.css">

    <link rel="stylesheet" href="/css/jquery.dataTables.min.css">
{{--    <link rel="stylesheet" href="/css/fixedHeader.dataTables.min.css">--}}

    <link rel="stylesheet" href="/fontAwesome/css/all.min.css"/>

    <script src="/js/notify.min.js"></script>

    <script src="/js/dom-to-image.min.js"></script>

    <script src="/js/dataTables.js"></script>
    <script src="/js/dataTables.buttons.js"></script>
    <script src="/js/buttons.dataTables.js"></script>
    <script src="/js/jszip.min.js"></script>
    <script src="/js/pdfmake.min.js"></script>
    <script src="/js/vfs_fonts.js"></script>
    <script src="/js/buttons.html5.min.js"></script>
    <script src="/js/buttons.print.min.js"></script>
{{--    <script src="/js/dataTables.fixedHeader.min.js"></script>--}}

    @include('layout.style_css')
    @include('layout.js')
    @yield('files')
    <img src="/Peptina-Logo.webp" class="d-none">
</head>

<body>

@include('layout.nav')

<div class="container mt-5">
    @yield('content')
</div>

<div id="loadingDiv">
    <div class="loading">Loading&#8230;</div>
    <div class="content"><h3></h3></div>
</div>


</body>
</html>
