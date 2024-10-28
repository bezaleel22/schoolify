<form id="publishForm" action="{{ route('result.publish', $id) }}" method="POST">
    @csrf
    <input type="hidden" name="filepath" value="{{ $filepath }}">
    <input type="hidden" name="exam_id" value="{{ $exam_type->id }}">
    <input type="hidden" name="title" value="{{ $exam_type->title  }}">
</form>
