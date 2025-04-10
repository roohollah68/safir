@props(['id'=>'', 'checked'=>false, 'class' => ''])


<label for="{{$id}}" class="{{$class}}">{{$slot}}</label>
<input {{$attributes}} type="checkbox" id="{{$id}}" class="checkboxradio" onclick="{{$id . '= this.checked'}};prepare_data()"
@checked($checked)>
