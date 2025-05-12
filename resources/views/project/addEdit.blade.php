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

            {{-- زیرپروژه‌ها --}}
            <div class="col-md-6 my-2">
                <h4 class="mb-3 fw-bold text-primary">زیرپروژه‌ها</h4>
                <div class="input-group mb-3">
                    <input type="text" 
                        id="subproject-input" 
                        class="form-control"
                        placeholder="عنوان زیرپروژه جدید"
                    >
                    <button type="button" 
                        class="btn btn-primary" 
                        id="add-subproject-btn"
                    >
                        <i class="fas fa-plus"></i> افزودن
                    </button>
                </div>
    
                {{-- Subprojects list --}}
                <div id="subprojects-list" class="list-group mb-1">
                    @if($edit)
                        @foreach($project->subProjects as $index => $sub)
                            <div class="list-group-item d-flex align-items-center">
                                <input type="hidden" 
                                    name="subprojects[{{ $index }}][id]" 
                                    value="{{ $sub->id }}">
                                <input type="hidden" 
                                    name="subprojects[{{ $index }}][title]" 
                                    value="{{ $sub->title }}">
                                <span class="me-auto">{{ $sub->title }}</span>
                                <button type="button" class="btn btn-danger btn-sm remove-subproject">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        @endforeach
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
    <style>
        #subprojects-list .list-group-item {
            padding: 0.75rem 1.25rem;
            margin-bottom: 0.5rem;
        }
        .remove-subproject {
            margin-left: 1rem;
        }

        .list-group-item {
            transition: all 0.3s ease;
        }
    </style>
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

        let subprojectCounter = {{ $edit ? count($project->subProjects) : 0 }};
        const subprojectList = document.getElementById('subprojects-list');

        subprojectList.addEventListener('click', (e) => {
            if (e.target.closest('.remove-subproject')) {
                const item = e.target.closest('.list-group-item');
                item.remove();

                Array.from(subprojectList.children).forEach((item, index) => {
                    item.querySelectorAll('input').forEach(input => {
                        const inputType = input.name.includes('[id]') ? 'id' : 'title';
                        input.name = `subprojects[${index}][${inputType}]`;
                    });
                });
                
                subprojectCounter = subprojectList.children.length;
            }
        });

        const addSubprojectBtn = document.getElementById('add-subproject-btn');
        const subprojectInput = document.getElementById('subproject-input');

        addSubprojectBtn.addEventListener('click', () => {
            const title = subprojectInput.value.trim();
            if (title === '') {
                alert('عنوان زیرپروژه نمی‌تواند خالی باشد.');
                return;
            }

            const subprojectItem = document.createElement('div');
            subprojectItem.className = 'list-group-item d-flex align-items-center';
            subprojectItem.innerHTML = `
                <input type="hidden" name="subprojects[${subprojectCounter}][title]" value="${title}">
                <span class="me-auto">${title}</span>
                <button type="button" class="btn btn-danger btn-sm remove-subproject">
                    <i class="fas fa-trash"></i>
                </button>
            `;

            subprojectList.appendChild(subprojectItem);
            subprojectInput.value = '';
            subprojectCounter++;
        });
    });
</script>
@endsection