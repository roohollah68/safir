<form id="report-form">
    @csrf
    <input type="hidden" name="project_id" id="report-project-id" value="{{ $project->id }}">
    <div class="mb-3">
        <textarea class="form-control" name="report" id="report-text" rows="5">{{ $project->report ?? '' }}</textarea>
    </div>
    <button type="submit" class="btn btn-success" style="font-family: inherit">ثبت گزارش</button>
    <button type="button" class="btn btn-danger" id="closeDialogBtn" style="font-family: inherit;">بستن</button>
</form>
