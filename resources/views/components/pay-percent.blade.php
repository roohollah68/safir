@props(['percent'=>'percent', 'total'=>'total'])

@if($total < 0)
    <i class="btn btn-info">بازگشت به انبار</i>
@elseif($percent == 0)
    <i class="btn btn-danger">0 %</i>
@elseif($percent == 100)
    <i class="btn btn-success">100 %</i>
@else
    <i class="btn btn-warning">{{$percent}} %</i>
@endif
