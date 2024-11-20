<form id="publishForm" action="{{ route('result.publish', $student->id) }}" method="POST">
    @csrf
    <input type="hidden" name="exam_id" value="{{ $student->exam_id }}">
    <input type="hidden" name="title" value="{{ $student->title  }}">
    <input type="hidden" name="term" value="{{ $student->term  }}">
    <input type="hidden" name="filepath" value="{{ $student->filepath }}">
    <input type="hidden" name="full_name" value="{{ $student->full_name }}">
    <input type="hidden" name="parent_email" value="{{ $student->parent_email }}">
    <input type="hidden" name="parent_name" value="{{ $student->parent_name }}">
    <input type="hidden" name="gender" value="{{ $student->gender }}">
    <input type="hidden" name="admin" value="{{ $student->admin }}">
    <input type="hidden" name="support" value="{{ $student->support }}">
</form>
