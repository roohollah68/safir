<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title')</title>

    {{--    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>--}}
    <script src="/js/jquery-3.5.1.min.js"></script>
    {{--    <script src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>--}}
    <script src="/js/jquery.dataTables.min.js"></script>

    {{--    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>--}}
    <script src="/js/jquery-ui.js"></script>
    {{--    <script src="//cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>--}}
    <script src="/js/numeral.min.js"></script>

    {{--    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>--}}
    <script src="/js/popper.min.js"></script>

    {{--    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>--}}
    {{--    <script src="/bootstrap-5.1.3-dist/js/bootstrap.min.js"></script>--}}
    <script src="/bootstrap-5.1.3-dist/js/bootstrap.bundle.min.js"></script>


    {{--    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">--}}
    <link rel="stylesheet" href="/css/jquery-ui.css">

    {{--    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">--}}
    {{--    <link rel="stylesheet" href="/css/bootstrap.min.css">--}}
    {{--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.rtl.min.css" integrity="sha384-gXt9imSW0VcJVHezoNQsP+TNrjYXoGcrqBZJpry9zJt8PCQjobwmhMGaDHTASo9N" crossorigin="anonymous">--}}
    <link rel="stylesheet" href="/bootstrap-5.1.3-dist/css/bootstrap.rtl.min.css">

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
    function num(x) {
        return numeral(x).format(0, 0);
    }

    $(function () {

        $(".price-input").on("keyup", function (event) {
            // When user select text in the document, also abort.
            var selection = window.getSelection().toString();
            if (selection !== '') {
                return;
            }
            // When the arrow keys are pressed, abort.
            if ($.inArray(event.keyCode, [38, 40, 37, 39]) !== -1) {
                return;
            }
            var $this = $(this);
            // Get the value.
            var input = $this.val();
            input = input.replace(/[\D\s\._\-]+/g, "");
            input = input ? parseInt(input, 10) : 0;
            $this.val(function () {
                return (input === 0) ? "0" : input.toLocaleString("en-US");
            });
        });
        $(".price-input").each(function (event) {
            // When user select text in the document, also abort.
            var selection = window.getSelection().toString();
            if (selection !== '') {
                return;
            }
            // When the arrow keys are pressed, abort.
            if ($.inArray(event.keyCode, [38, 40, 37, 39]) !== -1) {
                return;
            }
            var $this = $(this);
            // Get the value.
            var input = $this.val();
            input = input.replace(/[\D\s\._\-]+/g, "");
            input = input ? parseInt(input, 10) : 0;
            $this.val(function () {
                return (input === 0) ? "0" : input.toLocaleString("en-US");
            });
        });


    })

    function FarsiDate(uDate) {
        return new Intl.DateTimeFormat('fa-IR', {
            dateStyle: "short",
            timeStyle: "medium",
        }).format(uDate);
    }
</script>

</body>
</html>
