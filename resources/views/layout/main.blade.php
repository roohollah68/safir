<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title')</title>

    <script src="/js/jquery-3.5.1.min.js"></script>

    <script src="/js/jquery.dataTables.min.js"></script>

    <script src="/js/jquery-ui.js"></script>

    <script src="/js/numeral.min.js"></script>

    <script src="/js/popper.min.js"></script>

    <script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="/css/jquery-ui.css">

    <link rel="stylesheet" href="/bootstrap-5.1.3-dist/css/bootstrap.rtl.min.css">

    <link rel="stylesheet" href="/css/jquery.dataTables.min.css">

    <link rel="stylesheet" href="/fontAwesome/css/all.min.css"/>

    <script src="/js/notify.min.js"></script>

    <script src="/js/dom-to-image.min.js"></script>

    @include('layout.style_css')

    @yield('files')
</head>

<body>

@include('layout.nav')

<div class="container mt-5">
    @yield('content')
</div>


@include('layout.js')
</body>
</html>
