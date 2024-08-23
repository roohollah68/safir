@props(['required'=>false,'name'=>'','type'=>'text','readonly'=>false , 'content'=>'' , 'tag'=>'input'])

<div class="col-md-6 mb-2">
    <div class="form-group input-group @required($required)">
        <div class="input-group-append" style="min-width: 160px">
            <label for="{{$name}}" class="input-group-text w-100">{{$slot}}</label>
        </div>
        <{{$tag}} {{$attributes}} type="{{$type}}" id="{{$name}}" class="form-control" name="{{$name}}"
        @required($required) @readonly($readonly)> {{$content}}</{{$tag}}>
    </div>
</div>
