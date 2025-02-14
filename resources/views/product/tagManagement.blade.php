@extends('layout.main')

@section('title')
    ثبت شناسه کالا ها
@endsection

@section('content')


    @foreach(config('goodCat') as $category => $desc)
        <label for="{{$category}}">{{$desc}}</label>
        <input type="checkbox" id="{{$category}}" class="checkboxradio" checked onclick="$('.{{$category}}').toggle(this.checked)">
    @endforeach
    <label for="zero-tag">محصولات با شناسه صفر</label>
    <input type="checkbox" id="zero-tag" class="checkboxradio" checked onclick="$('.zero-tag').toggle(this.checked)">
    <a class="btn btn-danger m-1" href="{{route('productList')}}">بازگشت</a>


    <br>
    <div>
        <table class="table table-striped" id="table">
            <thead>
            <tr>
                <th>شماره</th>
                <th>نام کالا</th>
                <th>شناسه کالا</th>
                <th>ارزش افزوده</th>
                <th>اینتا کد</th>
                <th>ذخیره</th>
            </tr>
            </thead>
            <tbody>
            @foreach($goods as $id=>$good)
                <form>
                <tr class="{{$good->category}} {{$good->tag === 0 ? 'zero-tag':''}}" id="good-{{$id}}">
                    <td>{{$id}}</td>
                    <td>{{$good->name}}</td>
                    <td>
                        <input type="text" name="tag" value="{{$good->tag}}" maxlength="13" style="width: 120px"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" pattern="^[0-9]*$"></td>
                    <td><input type="checkbox" name="vat" @checked($good->vat)></td>
                    <td><select name="isic" >
                            <option value="">لطفا انتخاب کنید</option>
                            <option value="1020250" @selected(old('isic')?:$good->isic==1020250)>قهوه، کاکائو، پودر و خمیر حاصل از آنها</option>
                            <option value="1010020" @selected(old('isic')?:$good->isic==1010020)>انواع چای(خشک کردن، سورت و بسته بندی)</option>
                            <option value="1010030" @selected(old('isic')?:$good->isic==1010030)>انواع گیاهان طبی و دارویی</option>
                        </select></td>
                    <td><input type="submit" value="ذخیره" onclick="save({{$id}})"></td>
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

        $(function () {
            dataTable = $('#table').DataTable({
                // pageLength: 100,
                paging: false,
                destroy: true,
                language:language,
            });

            $('form').submit(e=>{
                e.preventDefault();
            })
            $(".checkboxradio").checkboxradio();
        })



        function save(id){
            $.post('/good/tag/'+id , {
                _token: token,
                tag : $('#good-'+id + ' input[name=tag]').val() || null,
                vat : +$('#good-'+id + ' input[name=vat]').is(':checked'),
                isic : +$('#good-'+id + ' select[name=isic]').val() || null,
            }).done(res =>{
                $.notify('ذخیره شد','success');
            });
        }

    </script>
@endsection
