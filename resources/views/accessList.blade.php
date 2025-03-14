@extends('layout.main')

@section('title')
    لیست دسترسی‌های کاربران
@endsection

@section('content')
    <style>
        input:focus {
            outline: none;
            box-shadow: none;
        }
        .accordion {
            max-width: 900px;
            margin: auto;
        }
    </style>
    <div class="d-flex justify-content-center mb-4">
        <div class="input-group" style="max-width: 400px;">
            <span class="input-group-text bg-white border-end-0 rounded-start" id="search-addon">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" id="userSearch" class="form-control shadow-none border-start-0 focus-ring" placeholder="جستجوی کاربران..." aria-label="Search" aria-describedby="search-addon">
        </div>
    </div>

    <div class="accordion accordion-flush rounded-3" id="userAccessAccordion">
        @foreach($users as $user)
            <div class="accordion-item border rounded-3 mb-2">
                <h2 class="accordion-header" id="heading{{ $user->id }}">
                    <button class="accordion-button collapsed rounded-3" style="background-color:gainsboro" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $user->id }}" aria-expanded="false" aria-controls="collapse{{ $user->id }}">
                        {{ $user->name }}
                    </button>
                </h2>
                <div id="collapse{{ $user->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $user->id }}" data-bs-parent="#userAccessAccordion">
                    <div class="accordion-body">
                        <div class="row">
                            @foreach(config('userMeta.access') as $key => $value)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox" id="permission{{ $user->id }}{{ $key }}" data-user-id="{{ $user->id }}" data-permission="{{ $key }}" {{ $user->meta($key) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="permission{{ $user->id }}{{ $key }}">
                                            {{ $value }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@section('files')
    <script>
        $(document).ready(function () {
            $('#access-table').DataTable({
                pageLength: 25,
                order: [[0, "asc"]],
            });

            $('#userSearch').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('.accordion-item').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            $('.permission-checkbox').on('change', function() {
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
                    },
                    success: function(response) {
                        console.log(response.message);
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
@endsection
