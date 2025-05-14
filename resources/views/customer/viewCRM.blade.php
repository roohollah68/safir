<div title="سابقه ارتباط با {{$customer->name}}" class="dialogs">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>تاریخ</th>
            <th>توضیحات</th>
            <th>تاریخ پیگیری بعدی</th>
        </tr>
        </thead>
    @foreach($customer->CRMs as $CRM)
        <tr>
            <td>{{verta($CRM->created_at)->formatJalaliDate()}}</td>
            <td>{{$CRM->description}}</td>
            <td>{{ $CRM->next_date ? verta($CRM->next_date)->formatJalaliDate() : '-' }}</td>
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
        <label for="next_date_text" class="mb-1">تاریخ پیگیری بعدی</label>
        <div class="form-group input-group">
        <br>
        <input type="text" id="next_date_text" name="next_date_text" class="form-control rounded" style="cursor: pointer;"
        value="{{ old('next_date') ?: ($customer->next_date ? verta($customer->next_date)->formatJalaliDate() : '') }}">
        <input type="hidden" id="next_date" name="next_date" value="{{ old('next_date') ?: ($customer->next_date ?? '') }}">
        </div>
        <br>
        <input class="btn btn-success" type="submit" value="ثبت">
    </form>
    <script>
    
        $(function () {
            const next_date =
            @if ($customer->next_date)
                new Date('{{ $customer->next_date }}')
            @else
                null
            @endif;

            new mds.MdsPersianDateTimePicker(document.getElementById('next_date_text'), {
                targetTextSelector: '#next_date_text',
                targetDateSelector: '#next_date',
                selectedDate: next_date,
                isGregorian: false,
            });
        });

    </script>
</div>
