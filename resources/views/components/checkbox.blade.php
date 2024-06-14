@props(['id'=>'', 'checked'=>false])


<label for="{{$id}}">{{$slot}}</label>
<input {{$attributes}} type="checkbox" id="{{$id}}" class="checkboxradio" onclick="{{$id}} = this.checked;prepare_data()"
@checked($checked)>
