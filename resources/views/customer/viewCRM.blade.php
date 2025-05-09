<div title="سابقه ارتباط با {{$customer->name}}" class="dialogs">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>تاریخ</th>
            <th>توضیحات</th>
        </tr>
        </thead>
    @foreach($customer->CRMs as $CRM)
        <tr>
            <td>{{verta($CRM->created_at)->formatJalaliDate()}}</td>
            <td>{{$CRM->description}}</td>
        </tr>
    @endforeach
    </table>
    <br>
    <form method="post" action="/customer/CRM">
        @csrf
        <input type="hidden" name="customer_id" value="{{$customer->id}}">
        <label for="description">افزودن توضیحات</label>
        <textarea id="description" name="description" class="form-control"></textarea>
        <br>
        <input class="btn btn-success" type="submit" value="ثبت">
    </form>
</div>
