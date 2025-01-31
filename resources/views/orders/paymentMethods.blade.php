<div title="نحوه پرداخت" class="dialogs">
    <form method="post" id="paymentForm" action="" enctype="multipart/form-data">
        @csrf
        <a class="btn btn-info" onclick="dialog.remove();" href="/customerDeposit/add/${order.customer_id}/${id}">پرداخت نقدی یا چکی</a>
        <br>
        <br>
        <input type="radio" id="cod" value="cod" name="paymentMethod" class="checkboxradio" >
        <label for='cod' class="btn btn-primary my-1">پرداخت در محل</label>
        <br>
        <input type="radio" id="payInDate" value="payInDate" name="paymentMethod" class="checkboxradio" >
        <label for="payInDate" class="btn btn-danger my-1">پرداخت در تاریخ</label>
        <input type="text" name="payInDatePersian" id="dateOfPayment" class="form-control" placeholder="تاریخ پرداخت" >
        <input type="hidden" name="payInDate">

        <br><label for="send-note">یادداشت:</label>
        <input id="send-note" name="note" type="text" class="w-100">

        <input type="submit" class="btn btn-success" value="ارسال">
        </form>
</div>
