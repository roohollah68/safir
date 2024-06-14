@props(['id'=>'', 'checked'=>false , 'name'=>''])


<label for="{{$id}}">{{$slot}}</label>
<input {{$attributes->class(["checkboxradio"])}} type="radio" name="{{$name}}" id="{{$id}}"
@checked($checked)>
