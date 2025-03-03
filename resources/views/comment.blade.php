<div title="مشاهده تاریخچه" class="dialogs">
    @foreach($comments as $comment)
        {{$comment->user->name}}:
        <span class="btn m-1
            @if($comment->user->id == auth()->user()->id) btn-info @else btn-primary @endif "
             title="{{verta($comment->created_at)->timezone('Asia/tehran')->formatJalaliDatetime()}}"> {{$comment->text}}
            @if($comment->photo)<a class="fa fa-image" href="/comment/{{$comment->photo}}" target="_blank"></a>@endif</span>
        <i dir="ltr">{{verta($comment->created_at)->timezone('Asia/tehran')->format('m/d h:i')}}</i>
        <br>
    @endforeach
    <br><form action="" method="post" id="commentForm">
        @csrf
            @if($order->state == 10)
                <input type="checkbox" class="checkboxradio" name="delivered" id="delivered">
                <label for="delivered">اطلاعات ارسال</label>
            @endif
        <div class="m-4">
            <div class="form-group input-group">
                <span class="btn btn-outline-success fa fa-paper-plane" onclick="addComment({{$order->id}})"></span>
                <input value="" type="text" id="text" class="form-control" name="text">
                <input type="file" name="photo" id="photo" class="compress-image" style="display: none;">
                <label for="photo" class="btn btn-info fa fa-paperclip"></label>
            </div>
        </div>
    </form>
</div>
