@extends('layout.main')

@section('title')
    ثبت شناسه کالا ها
@endsection

@section('content')

    <label for="zero-tag">محصولات با شناسه صفر</label>
    <input type="checkbox" id="zero-tag" class="checkboxradio" checked onclick="$('.zero-tag').toggle(this.checked)">

    <label for="inta-code">نمایش اینتا کد</label>
    <input type="checkbox" id="inta-code" class="checkboxradio" onclick="$('.intacode').toggle(this.checked);$('.replace').toggle(!this.checked);">

    <a class="btn btn-primary mb-1" href="/keysun/good">
        دریافت اکسل کیسان
    </a>
    <a class="btn btn-danger m-1" href="{{route('productList')}}">بازگشت</a>


    <br>
    <div>
        <table class="table table-striped" id="table">
            <thead>
            <tr>
                <th>شماره</th>
                <th style="width:300px;">نام کالا</th>
                <th>شناسه کالا</th>
                <th>ارزش افزوده</th>
                <th class="intacode hide">اینتا کد</th>
                <th class="replace">کالای جایگزین</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            @foreach($goods as $id=>$good)
                <form>
                    <tr class="{{$good->category}} {{$good->tag === 0 ? 'zero-tag':''}}" id="good-{{$id}}">
                        <td>{{$id}}</td>
                        <td>{{$good->name}}
                            @isset($keysungoods[$id])
                                <span class="fa fa-check btn btn-success"
                                      title="{{$keysungoods[$id]->tag}} - {{$keysungoods[$id]->name}}"></span>
                            @endisset
                        </td>
                        <td>
                            <input type="text" name="tag" value="{{$good->tag}}" maxlength="13" style="width: 120px"
                                   onkeypress="return event.charCode >= 48 && event.charCode <= 57" pattern="^[0-9]*$">
                        </td>
                        <td><input type="checkbox" name="vat" @checked($good->vat)></td>
                        <td class="intacode hide"><select name="isic">
                                <option value="">لطفا انتخاب کنید</option>
                                <option value="1020250" @selected(old('isic')?:$good->isic==1020250)>قهوه، کاکائو، پودر
                                    و خمیر حاصل از آنها
                                </option>
                                <option value="1010020" @selected(old('isic')?:$good->isic==1010020)>انواع چای(خشک کردن،
                                    سورت و بسته بندی)
                                </option>
                                <option value="1010030" @selected(old('isic')?:$good->isic==1010030)>انواع گیاهان طبی و
                                    دارویی
                                </option>
                            </select>
                        </td>
                        <td class="replace">
                            @if(!isset($keysungoods[$id]))
                                <input type="text" name="replace_name" class="replace_name" style="width: 250px"
                                       value="{{$good->replace_id?$good->replace()->name:''}}">
                                <input type="text" style="width: 60px" name="replace_id" class="replace_id" value="{{$good->replace_id}}" readonly>
                            @endif
                        </td>
                        <td>
                            <input type="submit" class="btn btn-success" value="ذخیره" onclick="save({{$id}})">
                            <span class="btn btn-danger" onclick="deleteGood({{$id}})">حذف</span>
                        </td>
                    </tr>
                </form>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('files')

    <script>
        let dataTable;
        let goods = {!! json_encode($goods) !!};
        let keysungoods = {!! json_encode($keysungoods->keyBy('name')) !!}

        $(function () {
            dataTable = $('#table').DataTable({
                // pageLength: 100,
                paging: false,
                destroy: true,
                language: language,
                columns: [null, null, null, null, null, null, {width: '15%'}]
            });

            $('form').submit(e => {
                e.preventDefault();
            })
            $(".checkboxradio").checkboxradio();

            $(".replace_name").autocomplete({
                source: Object.keys(keysungoods),
            }).change((e)=>{
                $(e.target).next().val(keysungoods[e.target.value].id ||  '');
            });
        })

        function deleteGood(id) {
            if (confirm(' آیا ' + goods[id].name + ' حذف شود؟ '))
                $.post('/good/delete/' + id, {
                    _token: token,
                }).done(res => {
                    $.notify('با موفقیت حذف شد.', 'success');
                    $('#good-' + id).remove();
                });
        }

        function save(id) {
            $.post('/good/tag/' + id, {
                _token: token,
                tag: $('#good-' + id + ' input[name=tag]').val() || null,
                vat: +$('#good-' + id + ' input[name=vat]').is(':checked'),
                isic: +$('#good-' + id + ' select[name=isic]').val() || null,
                replace_id: +$('#good-' + id + ' input[name=replace_id]').val() || null,
            }).done(res => {
                $.notify('ذخیره شد', 'success');
            });
        }

    </script>
@endsection
