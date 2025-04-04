@extends('layout.main')

@section('title')
    لیست دسترسی‌های کاربران
@endsection

@section('content')
    <label for="permission-all">همه</label>
    <input class="checkboxradio" type="checkbox" id="permission-all"
           onclick="$('.permisions').click()">
    <br>
    <br>
    @foreach(config('userMeta.access') as $key => $value)
        <span style="width: 300px; display: inline-block">
        <label class="form-check-label" for="permission{{ $key }}">{{$value}}</label>
        <input class="checkboxradio permisions" type="checkbox" id="permission{{ $key }}"
               onclick="$('.{{$key}}').toggle(this.checked)">
        </span>
    @endforeach

    <table class="table table-striped" id="user-table">
        <thead>
            <tr>
                <th>نام</th>
                @foreach(config('userMeta.access') as $key => $value)
                    <th class="hide {{$key}}">{{ $value }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->name }} ({{$user->safir()?'سفیر':'کاربر'}})</td>
                @foreach(config('userMeta.access') as $key => $value)
                    <td class="hide {{$key}}">
                        <span class="hide">{{$user->meta($key)?1:0}}</span>
                        <input class="form-check-input permission-checkbox" type="checkbox"
                               id="permission{{ $user->id }}{{ $key }}" data-user-id="{{ $user->id }}"
                               data-permission="{{ $key }}" @checked($user->meta($key))>
                    </td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection

@section('files')
    <script>
        $(document).ready(function () {
            $('#user-table').DataTable({
                pageLength: 100,
            });

            $('.checkboxradio').checkboxradio();

            $('.permission-checkbox').on('change', function () {
                var userId = $(this).data('user-id');
                var permission = $(this).data('permission');
                var isChecked = $(this).is(':checked');

                $.ajax({
                    url: '{{ route("updateUserPermission") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        user_id: userId,
                        permission: permission,
                        value: isChecked ? 1 : 0
                    }
                });
            });
        });
    </script>


@endsection
