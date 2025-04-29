<div class="comments-dialog-overlay">
    <div class="comments-dialog-content" dir="rtl">    
        <form id="commentForm" action="{{ route('comments.store', $project->id) }}" method="POST" data-project-id="{{ $project->id }}">
            @csrf
            <div class="form-group mb-3">
                <textarea name="comment" class="form-control" rows="3" style="font-family: inherit;"
                          placeholder="پیام خود را وارد کنید..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="font-family: inherit;">ثبت پیام</button>
            <button type="button" class="btn btn-danger" id="closeDialogBtn" style="font-family: inherit;">بستن</button>
        </form>
        @if($project->comments->isNotEmpty())
        <div class="comments-list mt-4">
            @foreach($project->comments as $comment)
            <div class="card mb-3 comment-item">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="card-title mb-0 fw-bold">{{ $comment->user->name }}</h6>
                        <small class="text-muted">
                            {{ verta($comment->created_at)->formatJalaliDatetime() }}
                        </small>
                    </div>
                    <p class="card-text mb-0">{{ $comment->comment }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<style>
.comments-list {
    overflow-y: scroll;
    height: 190px;
    scroll-behavior: smooth;
}
</style>