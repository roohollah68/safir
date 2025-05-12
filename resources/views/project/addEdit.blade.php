@extends('layout.main')

@section('title')
    @if (!$edit)
        افزودن پروژه
    @else
        ویرایش پروژه
    @endif
@endsection

@section('content')
    <form action="{{ $edit ? route('project.update', $project->id) : route('project.add') }}" method="post" enctype="multipart/form-data">
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
                        <option value="general" {{ (old('location') ?: $project->location) == 'general' ? 'selected' : '' }}>کلی</option>
                        @foreach (config('projectLocation') as $key => $value)
                            @if ($key != 0)
                                <option value="{{ $key }}" {{ (old('location') ?: $project->location) == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- مسئول --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label class="input-group-text w-100">واگذاری به:</label>
                    </div>
                    <input type="text" id="task-owner-search" class="form-control" 
                        placeholder="جستجوی کاربر..." autocomplete="off"
                        value="{{ $project->taskOwner->name ?? '' }}">
                    <input type="hidden" name="task_owner_id" id="task_owner_id" 
                        value="{{ old('task_owner_id') ?? $project->task_owner_id ?? '' }}">
                </div>
            </div>

            {{-- مهلت --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label class="input-group-text w-100">مهلت:</label>
                    </div>
                    <input type="text" id="deadline_date" class="form-control" autocomplete="off"
                        value="{{ old('deadline') ?: ($project->deadline ? verta($project->deadline)->formatJalaliDate() : '') }}">
                    <input type="hidden" name="deadline" id="deadline"
                        value="{{ old('deadline') ?: ($project->deadline ?? '') }}">
                </div>
            </div>

            {{-- توضیحات --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="desc" class="input-group-text w-100">توضیحات:</label>
                    </div>
                    <textarea id="desc" class="form-control" name="desc" rows="3">{{ old('desc') ?: $project->desc }}</textarea>
                </div>
            </div>

            {{-- تصویر پروژه --}}
            <div class="col-md-6 my-2">
                <div class="form-group input-group">
                    <div class="input-group-append" style="min-width: 160px">
                        <label for="image" class="input-group-text w-100">تصویر پروژه:</label>
                    </div>
                    <input id="image" type="file" class="form-control-file ms-2" name="image">
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
                <a href="{{ route('projectList') }}" class="btn btn-danger">بازگشت</a>
            </div>
        </div>
    </form>
@endsection

@section('files')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const users = @json(\App\Models\User::all()->map(fn($user) => [
            'id' => $user->id,
            'name' => $user->name,
        ]));

        const searchInput = document.getElementById('task-owner-search');
        const taskOwnerIdInput = document.getElementById('task_owner_id');
        const dropdown = document.createElement('ul');
        
        Object.assign(dropdown.style, {
            position: 'absolute', zIndex: '1000', display: 'none', width: "420px",
            marginTop: `${searchInput.offsetHeight}px`, left: `${searchInput.offsetLeft}px`,
        });

        const parentDiv = searchInput.parentNode;
        parentDiv.style.position = 'relative';
        parentDiv.appendChild(dropdown);

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();
            dropdown.innerHTML = '';

            if (query.length > 1) {
                const filteredUsers = users.filter(user => 
                    user.name.toLowerCase().includes(query)
                );

                filteredUsers.forEach(user => {
                    const item = document.createElement('li');
                    item.className = 'list-group-item list-group-item-action';
                    item.style.cursor = 'pointer';
                    item.style.padding = '8px';
                    item.innerHTML = `${user.name}`;
                    
                    item.addEventListener('click', () => {
                        searchInput.value = user.name;
                        taskOwnerIdInput.value = user.id;
                        dropdown.style.display = 'none';
                    });

                    dropdown.appendChild(item);
                });

                dropdown.style.display = filteredUsers.length ? 'block' : 'none';
            } else {
                dropdown.style.display = 'none';
            }
        });
        document.addEventListener('click', (e) => {
            if (!parentDiv.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        const deadline =
            @if ($project->deadline)
                new Date('{{ $project->deadline }}')
            @else
                null
            @endif;

        new mds.MdsPersianDateTimePicker($('#deadline_date')[0], {
            targetTextSelector: '#deadline_date',
            targetDateSelector: '#deadline',
            selectedDate: deadline,
            isGregorian: false,
        });
    });
</script>
@endsection