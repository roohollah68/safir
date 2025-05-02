@extends('layout.main')

@section('title')
    لیست پروژه‌ها
@endsection

@section('content')
<div class="container">
    <a class="btn btn-outline-success mb-5" href="{{ route('project.add.form') }}">
        <span class="fa fa-plus"></span>
        افزودن پروژه
    </a>
    <div id="location-tabs" dir="rtl">
        <ul class="nav nav-tabs">
            <li><a href="#tab-general">کلی</a></li>
            @foreach (config('withdrawalLocation') as $key => $value)
                @if ($key != 0)
                    <li><a href="#tab-{{ $key }}">{{ $value }}</a></li>
                @endif
            @endforeach
        </ul>
        <div id="tab-general">
            <div class="row">
                @foreach ($projects->where('location', 'general') as $project)
                <div class="col-12 mb-4">
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
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @foreach (config('withdrawalLocation') as $key => $value)
            @if ($key != 0)
                <div id="tab-{{ $key }}">
                    <div class="row">
                        @foreach ($projects->where('location', $key) as $project)
                        <div class="col-12 mb-4">
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
                                            {{ config('withdrawalLocation')[$project->location] }}
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
        });
    </script>
@endsection
