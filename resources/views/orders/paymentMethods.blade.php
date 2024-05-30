<div title="نحوه پرداخت" class="dialogs">
<p class="btn btn-success" onclick="sendConfirm(${id},1)">${payMethods[1]}</p><br>
<p class="btn btn-info" onclick="sendConfirm(${id},2)">${payMethods[2]}</p><br>
<p class="btn btn-primary" onclick="sendConfirm(${id},3)">${payMethods[3]}</p><br>
<p class="btn btn-secondary" onclick="sendConfirm(${id},6)">${payMethods[6]}</p><br>
<p class="btn btn-warning" onclick="sendConfirm(${id},4)">${payMethods[4]}</p><br>
<p class="btn btn-danger" id="dtp1">${payMethods[5]}</p>
<input type="text" id="dateOfPayment" class="form-control d-none" placeholder="تاریخ پرداخت" data-name="dtp1-text">
<br><label for="send-note">یادداشت:</label>
<input id="send-note" name="note" type="text" class="w-100">
</div>
