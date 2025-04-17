@extends('layout.main')

@section('title')
    لیست پروژه‌ها
@endsection

@section('content')
<div class="container">
    <a class="btn btn-outline-success mb-5" href="{{ route('projects.add.form') }}">
        <span class="fa fa-plus"></span>
        افزودن پروژه
    </a>
    <div class="row">
        @foreach ($projects as $project)
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <h2 class="card-title">{{ $project->title }}</h2>
                    <div>
                        <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit"></i> ویرایش
                        </a>
                        <a class="btn btn-outline-secondary" href="/project/{{$project->image}}" target="_blank">مشاهده تصویر</a>
                    </div>
                </div>
            
        
            <div class="card-body">
                <p class="card-text">{{ $project->desc }}</p>
                <div class="mt-3">
                <p class="text-muted mb-1">
                    <i class="fas fa-map-marker-alt"></i> {{ $project->location }}
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
@endsection