<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title')</title>

{{--    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>--}}
    <script src="/js/jquery-3.5.1.min.js"></script>
{{--    <script src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>--}}
    <script src="/js/jquery.dataTables.min.js"></script>

{{--    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>--}}
    <script src="/js/jquery-ui.js"></script>
{{--    <script src="//cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>--}}
    <script src="/js/numeral.min.js"></script>

{{--    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">--}}
    <link rel="stylesheet" href="/css/jquery-ui.css">

{{--    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">--}}
    <link rel="stylesheet" href="/css/bootstrap.min.css">

{{--    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css">--}}
    <link rel="stylesheet" href="/css/jquery.dataTables.min.css">

{{--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css"--}}
{{--          integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA=="--}}
{{--          crossorigin="anonymous"/>--}}

    <link rel="stylesheet" href="/fontAwesome/css/all.min.css"/>

{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js"></script>--}}
    <script src="/js/notify.min.js"></script>

    @include('layout.style_css')
    @yield('files')
</head>

<body>

@include('layout.nav')

<div class="container mt-5">
    @yield('content')
</div>

<script>
    function num(x){
        return numeral(x).format(0,0);
    }
</script>

</body>
</html>
