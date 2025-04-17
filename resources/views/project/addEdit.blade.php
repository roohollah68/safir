@extends('layout.main')

@section('title')
    @if (!$edit)
        افزودن پروژه
    @else
        ویرایش پروژه
    @endif
@endsection

@section('content')
    <form action="{{ $edit ? route('projects.edit', $project->id) : route('projects.add') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row my-4">
            {{-- عنوان پروژه --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="title" class="input-group-text w-100">عنوان:</label>
                    </div>
                    <input id="title" type="text" class="form-control" name="title"
                        value="{{ old('title') ?: ($project->title ?? '') }}" required>
                </div>
            </div>

            {{-- مکان --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="location" class="input-group-text w-100">مکان:</label>
                    </div>
                    <select id="location" class="form-control" name="location" required>
                        <option value="" disabled selected>انتخاب کنید</option>
                        <option value="option1" {{ (old('location') ?: $project->location) == 'option1' ? 'selected' : '' }}>کلی</option>
                        <option value="option2" {{ (old('location') ?: $project->location) == 'option2' ? 'selected' : '' }}>2</option>
                        <option value="option3" {{ (old('location') ?: $project->location) == 'option3' ? 'selected' : '' }}>3</option>
                        <option value="option4" {{ (old('location') ?: $project->location) == 'option4' ? 'selected' : '' }}>4</option>
                        <option value="option5" {{ (old('location') ?: $project->location) == 'option5' ? 'selected' : '' }}>5</option>
                    </select>
                </div>
            </div>

            {{-- توضیحات --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="desc" class="input-group-text w-100">توضیحات:</label>
                    </div>
                    <textarea id="desc" class="form-control" name="desc" rows="3" required>{{ old('desc') ?: $project->desc }}</textarea>
                </div>
            </div>

            {{-- تصویر پروژه --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group required">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="image" class="input-group-text w-100">تصویر پروژه:</label>
                    </div>
                    <input id="image" type="file" class="form-control-file ms-2" name="image" @if(!$edit) required @endif>
                    @if($edit && $project->image)
                        <div class="mt-2">
                            <img src="{{ asset('project/' . $project->image) }}" alt="تصویر پروژه" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- دکمه‌ها --}}
        <div class="row my-4">
            <div class="col-md-6">
                <input type="submit" class="btn btn-success" value="{{ $edit ? 'ویرایش' : 'افزودن' }}">
                &nbsp;
                <a href="{{ route('projects.list') }}" class="btn btn-danger">بازگشت</a>
            </div>
        </div>
    </form>
@endsection

@section('files')


@endsection