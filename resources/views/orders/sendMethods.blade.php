@props(['sendMethods'=>config('sendMethods')])
<div title="نحوه ارسال" class="dialogs">
    <form method="post" id="sendForm" action="" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-sm-6">
                <input type="radio" id="inPerson" value="{{$sendMethods[7]}}" name="sendMethod" class="checkboxradio">
                <label for='inPerson' class="btn btn-success my-1">{{$sendMethods[7]}}</label><br>

                <input type="radio" id="car" value="{{$sendMethods[1]}}" name="sendMethod" class="checkboxradio">
                <label for='car' class="btn btn-success my-1">{{$sendMethods[1]}}</label><br>

                <input type="radio" id="snap" value="{{$sendMethods[2]}}" name="sendMethod" class="checkboxradio">
                <label for='snap' class="btn btn-info my-1">{{$sendMethods[2]}}</label><br>

                <input type="radio" id="tipax" value="{{$sendMethods[4]}}" name="sendMethod" class="checkboxradio">
                <label for='tipax' class="btn btn-secondary my-1">{{$sendMethods[4]}}</label><br>

                <input type="radio" id="bar" value="{{$sendMethods[5]}}" name="sendMethod" class="checkboxradio">
                <label for='bar' class="btn btn-danger my-1">{{$sendMethods[5]}}</label><br>
            </div>
            <div class="col-sm-6">
                <input type="radio" id="peyk" value="{{$sendMethods[6]}}" name="sendMethod" class="checkboxradio">
                <label for='peyk' class="btn btn-primary my-1">{{$sendMethods[6]}}</label><br>

                <input type="radio" id="NAFIS" value="{{$sendMethods[8]}}" name="sendMethod" class="checkboxradio">
                <label for='NAFIS' class="btn btn-warning my-1">{{$sendMethods[8]}}</label><br>

                <input type="radio" id="BUS" value="{{$sendMethods[9]}}" name="sendMethod" class="checkboxradio">
                <label for='BUS' class="btn btn-info my-1">{{$sendMethods[9]}}</label><br>

                <input type="radio" id="post" value="{{$sendMethods[3]}}" name="sendMethod" class="checkboxradio">
                <label for='post' class="btn btn-danger my-1">{{$sendMethods[3]}}</label><br>

            </div>
        </div>
        <label for="postCode">کد مرسوله:</label>
        <input id="postCode" name="note" type="text" class="w-100"><br><br><br>

        <input type="submit" class="btn btn-success" value="ثبت">
    </form>
</div>
