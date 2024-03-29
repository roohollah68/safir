<style>
    @font-face {
        font-family: BNazanin;
        src: url('/css/BNAZANB.TTF');
    }

    @font-face {
        font-family: IranSans;
        src: url('/css/IRANSans/ttf/IRANSansWeb_FaNum.ttf');
    }

    @font-face {
        font-family: IranSans;
        src: url('/css/IRANSans/ttf/IRANSansWeb_FaNum_Bold.ttf');
        font-weight: bold;
    }

    * {
        font-family: IranSans;
    }

    body {
        text-align: right;
        margin-bottom: 150px !important;
    }

    .ui-widget {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        font-size: 1em;
    }

    .form-group.required label:after {
        content: "*";
        color: red;
    }

    .printed, .printed span, .printed b {
        font-size: 80px;
        direction: rtl;
        font-family: BNazanin;
    }

    .printed span {
        font-size: 90px;
    }

    .long-text, .long-text span, .long-text b {
        font-size: 73px;
    }

    .short-text, .short-text span, .short-text b {
        font-size: 99px;
    }

    canvas {
        direction: rtl;
    }

    .dataTables_wrapper {
        overflow-x: scroll;
    }
</style>
