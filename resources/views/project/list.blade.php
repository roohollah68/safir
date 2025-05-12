@extends('layout.main')

@section('title')
    لیست پروژه‌ها
@endsection

@section('content')
<div class="container">
    <a class="btn btn-outline-success mb-3" href="{{ route('project.add.form') }}">
        <span class="fa fa-plus"></span>
        افزودن پروژه
    </a>
    <br>
    <div class="btn-group mb-4 d-block justify-content-between" aria-label="Project Filter">
        <button type="button" class="btn btn-outline-primary active me-1 rounded" data-filter="all">همه</button>
        <button type="button" class="btn btn-outline-primary me-1 rounded" data-filter="created">پروژه‌های شما</button>
        <button type="button" class="btn btn-outline-primary me-1 rounded" data-filter="assigned">پروژه‌های دیگران برای شما</button>
    </div>
    <div id="location-tabs" dir="rtl">
        <ul class="nav nav-tabs">
            <li><a href="#tab-general">کلی</a></li>
            @foreach (config('projectLocation') as $key => $value)
                @if ($key != 0)
                    <li><a href="#tab-{{ $key }}">{{ $value }}</a></li>
                @endif
            @endforeach
        </ul>
        <div id="tab-general">
            <div class="row">
                @foreach ($projects->where('location', 'general') as $project)
                <div class="col-12 mb-4 project-card {{ $project->user_id == auth()->id() ? 'created-by-me' : '' }}
                     {{ $project->task_owner_id == auth()->id() ? 'assigned-to-me' : '' }}">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <h2 class="card-title">{{ $project->title }}</h2>
                            <div class="d-flex align-items-center">
                                @if ($project->user_id === auth()->id())
                                    <a href="{{ route('project.edit', $project->id) }}" class="btn btn-outline-primary mx-1">
                                        <i class="fas fa-edit"></i> ویرایش
                                    </a>
                                @endif
                                @if ($project->image)
                                    <a class="btn btn-outline-secondary mx-1" href="/project/{{$project->image}}" target="_blank">مشاهده تصویر</a>
                                @endif
                                <a href="#" class="btn btn-info view-comments-btn mx-1" 
                                   data-project-id="{{ $project->id }}" title="مشاهده پیام‌ها">
                                    <i class="fas fa-comments"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="card-text">{{ $project->desc }}</p>
                            <div class="mt-3">
                                <p class="text-muted mb-1">
                                    <i class="fas fa-map-marker-alt"></i> کلی
                                </p>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-calendar-alt"></i> 
                                    {{ verta($project->created_at)->formatJalaliDate() }}
                                </p>
                                <p class="text-muted mb-1">
                                <i class="fas fa-user-plus"></i> ایجاد کننده: {{ $project->user->name }}
                                </p>
                                @if($project->task_owner_id)
                                <p class="text-muted mb-1">
                                    <i class="fas fa-user-check"></i> مسئول: {{ $project->taskOwner->name }}
                                </p>
                                @endif
                               @if($project->deadline)
                                <p class="text-muted mb-4">
                                    <i class="fas fa-clock"></i> 
                                    مهلت: {{ verta($project->deadline)->formatJalaliDate() }}
                                </p>
                                @endif
                                @if ($project->subProjects->count() > 0)
                                    <div class="mb-3 d-flex align-items-center">
                                        <h5 class="mb-1 me-2">زیر پروژه‌ها:</h5>
                                        <span class="badge bg-success">
                                            {{ $project->subProjects->count() }} / {{ $project->subProjects->where('completed', true)->count() }}
                                        </span>
                                    </div>
                                @endif

                                <div class="subprojects">
                                    @foreach ($project->subProjects as $subProject)
                                        <div class="subproject-item d-flex align-items-center" data-id="{{ $subProject->id }}">
                                            @if (auth()->id() == $project->user_id || auth()->id() == $project->task_owner_id)
                                                <input type="checkbox" {{ $subProject->completed ? 'checked' : '' }} 
                                                    data-id="{{ $subProject->id }}" class="complete-subproject me-2">

                                            @else
                                               <span class="me-2 fw-bold">--</span> 
                                            @endif

                                            <h5 class="mb-0">{{ $subProject->title }}</h5>
                                            @if ($subProject->completed)
                                                <i class="fas fa-check text-success ms-2"></i> 
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @foreach (config('projectLocation') as $key => $value)
            @if ($key != 0)
                <div id="tab-{{ $key }}">
                    <div class="row">
                        @foreach ($projects->where('location', $key) as $project)
                        <div class="col-12 mb-4 project-card {{ $project->user_id == auth()->id() ? 'created-by-me' : '' }} 
                             {{ $project->task_owner_id == auth()->id() ? 'assigned-to-me' : '' }}">
                            <div class="card">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <h2 class="card-title">{{ $project->title }}</h2>
                                    <div class="d-flex align-items-center">
                                        @if ($project->user_id === auth()->id())
                                            <a href="{{ route('project.edit', $project->id) }}" class="btn btn-outline-primary mx-1">
                                                <i class="fas fa-edit"></i> ویرایش
                                            </a>
                                        @endif
                                        @if ($project->image)
                                            <a class="btn btn-outline-secondary mx-1" href="/project/{{$project->image}}" target="_blank">مشاهده تصویر</a>
                                        @endif
                                        <a href="#" class="btn btn-info view-comments-btn mx-1" 
                                        data-project-id="{{ $project->id }}" title="مشاهده پیام‌ها">
                                            <i class="fas fa-comments"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">{{ $project->desc }}</p>
                                    <div class="mt-3">
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-map-marker-alt"></i>
                                            {{ config('projectLocation')[$project->location] }}
                                        </p>
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-calendar-alt"></i> 
                                            {{ verta($project->created_at)->formatJalaliDate() }}
                                        </p>
                                        <p class="text-muted mb-1">
                                        <i class="fas fa-user-plus"></i> ایجاد کننده: {{ $project->user->name }}
                                        </p>
                                        @if($project->task_owner_id)
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-user-check"></i> مسئول: {{ $project->taskOwner->name }}
                                        </p>
                                        @endif
                                        @if($project->deadline)
                                        <p class="text-muted mb-4">
                                            <i class="fas fa-clock"></i> 
                                            مهلت: {{ verta($project->deadline)->formatJalaliDate() }}
                                        </p>
                                        @endif
                                        @if ($project->subProjects->count() > 0)
                                            <div class="mb-3 d-flex align-items-center">
                                                <h5 class="mb-1 me-2">زیر پروژه‌ها:</h5>
                                                <span class="badge bg-success">
                                                    {{ $project->subProjects->count() }} / {{ $project->subProjects->where('completed', true)->count() }}
                                                </span>
                                            </div>
                                        @endif

                                        <div class="subprojects">
                                            @foreach ($project->subProjects as $subProject)
                                                <div class="subproject-item d-flex align-items-center" data-id="{{ $subProject->id }}">
                                                    @if (auth()->id() == $project->user_id || auth()->id() == $project->task_owner_id)
                                                        <input type="checkbox" {{ $subProject->completed ? 'checked' : '' }} 
                                                            data-id="{{ $subProject->id }}" class="complete-subproject me-2">

                                                    @else
                                                    <span class="me-2 fw-bold">--</span> 
                                                    @endif

                                                    <h5 class="mb-0">{{ $subProject->title }}</h5>
                                                    @if ($subProject->completed)
                                                        <i class="fas fa-check text-success ms-2"></i> 
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
<div id="comments-dialog" style="display: none;"></div>
<style>
    .hidden-by-filter {
        display: none;
    }
    .subproject-item { 
        display: flex; align-items: center; margin-bottom: 5px;
    }
    .subproject-item input[type="checkbox"] {
        margin-left: 10px;
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    .subproject-item input[type="checkbox"]:checked {
        accent-color: green;
    }
</style>
@endsection

@section('files')
    <script>
        $(function() {
            $("#location-tabs").tabs();

            const commentsDialog = $("#comments-dialog");
            commentsDialog.dialog({
                autoOpen: false,
                width: 600,
                title: 'پیام‌های پروژه',
                create: function() {
                    $(this).closest(".ui-dialog").find(".ui-dialog-titlebar-close").hide();
                }
            });

            $(document).on('click', '#closeDialogBtn', function() {
                commentsDialog.dialog('close');
            });
            
            $('.view-comments-btn').click(function(e) {
            e.preventDefault();
            const projectId = $(this).data('project-id');
            $.get(`/projects/${projectId}/comments`)
                .done(function(html) {
                commentsDialog.html(html).dialog('open');
                })
                .fail(function() {
                alert('خطا در دریافت پیام‌ها');
                });
            });

            $(document).on('submit', '#commentForm', function(e) {
                e.preventDefault();
                const form = $(this);
                const projectId = form.data('project-id');
                
                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                })
                .done(function(response) {
                    if (response.success) {
                        const commentHtml = `
                            <div class="card mb-3 comment-item">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6 class="fw-bold mb-0">${response.comment.user_name}</h6>
                                        <small class="text-muted">${response.comment.timestamp}</small>
                                    </div>
                                    <p class="mb-0">${response.comment.text}</p>
                                </div>
                            </div>
                        `;
                        $('.comments-list').append(commentHtml);
                        $('#commentForm textarea').val('');
                        const list = $('.comments-list');
                        list.scrollTop(list.prop("scrollHeight"));
                    }
                })
                .fail(function() {
                    alert('خطا در ثبت پیام');
                });
            });

            $('.btn-group button').click(function() {
                $('.btn-group button').removeClass('active');
                $(this).addClass('active');
                const filter = $(this).data('filter');
                if (filter === 'all') {
                    $('.project-card').removeClass('hidden-by-filter');
                } else if (filter === 'created') {
                    $('.project-card').addClass('hidden-by-filter');
                    $('.project-card.created-by-me').removeClass('hidden-by-filter');
                } else if (filter === 'assigned') {
                    $('.project-card').addClass('hidden-by-filter');
                    $('.project-card.assigned-to-me').removeClass('hidden-by-filter');
                }
            });

            $(document).on('change', '.complete-subproject', function() {
                var checkbox = $(this);
                var id = checkbox.data('id');
                var completed = checkbox.is(':checked') ? 1 : 0;

                $.ajax({
                    url: `{{ route('subproject.update', '') }}/${id}`,
                    method: 'POST',
                    data: { 
                        completed: completed, 
                        _token: '{{ csrf_token() }}' 
                    },
                    success: function(response) {
                        if (response.success) {
                            var card = checkbox.closest('.project-card');
                            var total = card.find('.subproject-item').length;
                            var completedCount = card.find('.complete-subproject:checked').length;
                            card.find('.badge.bg-success').text(`${total} / ${completedCount}`);
                        } else {
                            alert('خطا در به‌روزرسانی زیر پروژه');
                            checkbox.prop('checked', !completed);
                        }
                    },
                    error: function() {
                        alert('خطا در ارتباط با سرور');
                        checkbox.prop('checked', !completed);
                    }
                });
            });
        });
    </script>
@endsection
