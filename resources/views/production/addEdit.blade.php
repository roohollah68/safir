@extends('layout.main')

@section('title')
    @if (!$edit)
        ثبت درخواست تولید
    @else
        ویرایش درخواست تولید
    @endif
@endsection

@section('content')
<div class="container mt-5">
            <form action="{{ $edit ? route('production.update', $production->id) : route('production.store') }}" method="POST">
                @csrf
                @if($edit)
                    @method('POST')
                @endif
                <div class="row my-4">
                    {{-- محصول --}}
                    <div class="col-md-6 my-2">
                        <div class="form-group input-group required">
                            <div class="input-group-append" style="min-width: 160px">
                                <label for="good_id" class="input-group-text w-100">محصول:</label>
                            </div>
                            <select id="good_id" name="good_id" class="form-select" required>
                                @foreach ($goods as $good)
                                    <option value="{{ $good->id }}" @selected($good->id == old('good_id', $production->good_id ?? null))>
                                        {{ $good->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- تعداد --}}
                    <div class="col-md-6 my-2">
                        <div class="form-group input-group required">
                            <div class="input-group-append" style="min-width: 160px">
                                <label for="requested_quantity" class="input-group-text w-100">تعداد:</label>
                            </div>
                            <input type="number" id="requested_quantity" name="requested_quantity" 
                                   class="form-control" value="{{ old('requested_quantity', $production->requested_quantity ?? null) }}" required>
                        </div>
                    </div>
                </div>

                <div class="row my-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success">{{ $edit ? 'ویرایش' : 'ثبت' }}</button>
                        <a href="{{ route('productionList') }}" class="btn btn-danger">بازگشت</a>
                    </div>
                </div>
            </form>
</div>
@endsection