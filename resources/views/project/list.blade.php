@extends('layout.main')

@section('title')
    لیست پروژه‌ها
@endsection

@section('content')
<div class="container">
    <a class="btn btn-outline-success mb-5" href="{{ route('add.form') }}">
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
                        <div class="card-body d-flex justify-content-between align-items-start">
                            <h2 class="card-title">{{ $project->title }}</h2>
                            <div>
                                <a href="{{ route('edit', $project->id) }}" class="btn btn-outline-primary">
                                <i class="fas fa-edit"></i> ویرایش
                                </a>
                                @if ($project->image)
                                    <a class="btn btn-outline-secondary" href="/project/{{$project->image}}" target="_blank">مشاهده تصویر</a>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="card-text">{{ $project->desc }}</p>
                            <div class="mt-3">
                                <p class="text-muted mb-1">
                                    <i class="fas fa-map-marker-alt"></i> کلی
                                </p>
                                <p class="text-muted">
                                    <i class="fas fa-calendar-alt"></i> 
                                    {{ verta($project->created_at)->formatJalaliDate() }}
                                </p>
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
                                <div class="card-body d-flex justify-content-between align-items-start">
                                    <h2 class="card-title">{{ $project->title }}</h2>
                                    <div>
                                        <a href="{{ route('edit', $project->id) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i> ویرایش
                                        </a>
                                        @if ($project->image)
                                            <a class="btn btn-outline-secondary" href="/project/{{$project->image}}" target="_blank">مشاهده تصویر</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">{{ $project->desc }}</p>
                                    <div class="mt-3">
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-map-marker-alt"></i>
                                            {{ config('withdrawalLocation')[$project->location] }}
                                        </p>
                                        <p class="text-muted">
                                            <i class="fas fa-calendar-alt"></i> 
                                            {{ verta($project->created_at)->formatJalaliDate() }}
                                        </p>
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
@endsection

@section('files')
    <script>
        $(function() {
            $("#location-tabs").tabs();
        });
    </script>
@endsection