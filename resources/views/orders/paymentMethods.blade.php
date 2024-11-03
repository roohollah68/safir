<div title="نحوه پرداخت" class="dialogs">
    <form method="post" id="paymentForm" action="" enctype="multipart/form-data">
        @csrf
        <input type="radio" id="cash" value="cash" name="paymentMethod" class="checkboxradio" onchange="$('.hide').hide();$('.cashPhoto').show()">
        <label for='cash' class="btn btn-success my-1">پرداخت نقدی</label>
        <label for='cashPhoto' class="btn btn-info m-2 hide cashPhoto" >بارگذاری رسید بانکی  <i class="fa fa-image"></i></label>
        <input type="file" class="hide" name="cashPhoto" id="cashPhoto">
        <br>
        <input type="radio" id="cheque" value="cheque" name="paymentMethod" class="checkboxradio" onchange="$('.hide').hide();$('.chequePhoto').show()">
        <label for='cheque' class="btn btn-info my-1">پرداخت چکی</label>
        <label for='chequePhoto' class="btn btn-info m-2 hide chequePhoto">بارگذاری تصویر چک  <i class="fa fa-image"></i></label>
        <input type="file" class="hide" name="chequePhoto" id="chequePhoto">
        <br>
        <input type="radio" id="cod" value="cod" name="paymentMethod" class="checkboxradio" onchange="$('.hide').hide();">
        <label for='cod' class="btn btn-primary my-1">پرداخت در محل</label>
{{--        <br>--}}
{{--        <input type="radio" id="factor" value="factorFactor" name="paymentMethod" class="checkboxradio" onchange="$('.hide').hide();">--}}
{{--        <label for='factor' class="btn btn-secondary my-1">فاکتور به فاکتور</label>--}}
{{--        <br>--}}
{{--        <input type="radio" id="barrow" value="barrow" name="paymentMethod" class="checkboxradio" onchange="$('.hide').hide();">--}}
{{--        <label for='barrow' class="btn btn-warning my-1">امانی</label>--}}
        <br>
        <input type="radio" id="payInDate" value="payInDate" name="paymentMethod" class="checkboxradio" onchange="$('.hide').hide();$('#dateOfPayment').show()">
        <label for="payInDate" class="btn btn-danger my-1">پرداخت در تاریخ</label>
        <input type="text" name="payInDatePersian" id="dateOfPayment" class="form-control hide" placeholder="تاریخ پرداخت" >
        <input type="hidden" name="payInDate">

        <br><label for="send-note">یادداشت:</label>
        <input id="send-note" name="note" type="text" class="w-100">

        <input type="submit" class="btn btn-success" value="ارسال">
        </form>
</div>
