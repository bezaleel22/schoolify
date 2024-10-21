@push('css')
<style>
    .school-table-style tr th {
        min-width: 150px;
    }

    .student-exam-data-table tr td:first-child {
        padding-left: 20px !important;
    }

    textarea {
        overflow-x: hidden;
    }

</style>
@endpush

<div role="tabpanel" class="tab-pane fade" id="studentExam">
    @php
    $exam_count = count($exam_terms);
    @endphp

    @if ($exam_count < 0) <div class="no-search no-paginate no-table-info mb-2">
        <div class="table-responsive">
            <table class="table school-table-style shadow-none pb-0" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>@lang('result::student.subject')</th>
                        <th>@lang('result::student.mta1')</th>
                        <th>@lang('result::student.mta2')</th>
                        <th>@lang('result::student.oral')</th>
                        <th>@lang('result::student.exam')</th>
                        <th>@lang('result::student.score')</th>
                        <th>@lang('result::student.grade')</th>
                    </tr>
                </thead>
            </table>
        </div>
</div>
@endif
<div class="no-search no-paginate no-table-info mb-2">
    @foreach ($results as $key=>$result)
    @php
    $records = $result->records;
    $student = $result->student;
    $exam = $result->exam_type;
    $record_count = count($result->records);
    @endphp
    @if ($record_count > 1)
    <div class=@if($key !=0) mt-40 @endif>
        <div class="col-lg-12">
            <div class="d-flex align-items-center mb-0">
                <div class="main-title">
                    <h3 class="mb-0">{{ @$exam->title }}</h3>
                </div>
                <button type="button" data-pdf-url="http://localhost/public/uploads/student/timeline/stu-0ac59f3663dd7437b7f264b58784476e.pdf" data-exam-id="{{ $exam->id }}" data-toggle="modal" data-target="#pdfPreviewModal" class="btn btn-link btn-sm open-preview-modal">
                    @lang('result::student.preview')
                </button>

            </div>
        </div>

        <div class="text-right mb-2">
            <button type="button" data-opened="{{ $student->opened }}" data-absent="{{ $student->absent }}" data-present="{{ $student->present }}" data-exam-id=" {{ $exam->id }}" data-toggle="modal" data-target="#perf_madal" class="primary-btn btn-sm tr-bg text-uppercase bord-rad open-perf-modal">
                @lang('result::student.performance')
                <span class="pl ti-plus"></span>
            </button>
        </div>
        <div class="table-responsive pb-10">
            <table id="table_id" class="table student-exam-data-table mt-5" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>@lang('result::student.subject')</th>
                        <th>@lang('result::student.mta1')</th>
                        <th>@lang('result::student.mta2')</th>
                        <th>@lang('result::student.oral')</th>
                        <th>@lang('result::student.exam')</th>
                        <th>@lang('result::student.score')</th>
                        <th>@lang('result::student.grade')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records as $record)
                    <tr>
                        <td>{{ @$record->subject }}</td>
                        @foreach ($record->marks as $mark)
                        <td>{{ $mark }}</td>
                        @endforeach
                        <td>{{ @$record->total_score }}</td>
                        <td>{{ @$record->grade }}</td>
                    </tr>
                    @endforeach
                </tbody>
                @php
                $score = $result->score;
                $remark = $result->remark;
                $min_average = $score->min_average->value;
                $max_average = $score->max_average->value;
                @endphp
                <tfoot>
                    <tr>
                        <th>@lang('result::student.total_score'): {{ @$score->total }}</th>
                        <th>@lang('result::student.average'): {{ @$score->average }}</th>
                        <th></th>
                        <th colspan="2">@lang('result::student.max_average'): {{ $max_average }}</th>
                        <th colspan="2">@lang('result::student.min_average'): {{ $min_average }}</th>
                    </tr>
                </tfoot>
            </table>

            <div class="col-lg-12 mt-10">
                <div class="input-effect">
                    <div class="d-flex justify-content-between align-items-center mb-0">
                        <label class="mb-0">@lang('result::student.remark')<span></span></label>
                        <button type="button" data-remark="{{ @$remark->comment }}" data-exam-id="{{ $exam->id }}" data-toggle="modal" data-target="#add_remark_modal" class="btn btn-link btn-sm open-remark-modal">
                            @lang('result::student.add_remark')
                        </button>
                    </div>
                    <textarea class="primary_input_field form-control" cols="0" rows="2" name="remark" id="Remark">{{ @$remark->comment }}</textarea>
                    <span class="focus-border textarea"></span>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endforeach
</div>
</div>


@include('result::include.performance_modal')
@include('result::include.remark_modal')
@include('result::include.preview_modal')

<script type="text/javascript">
    $(document).ready(function() {
        $('.open-perf-modal').on('click', function() {
            var examId = $(this).data('exam-id');
            $('#examTypeId').val(examId);
        });
    });

</script>

<script type="text/javascript">
    $(document).ready(function() {
        $('.open-preview-modal').on('click', function() {
            var examId = $(this).data('exam-id');
            $('#previewExamTypeId').val(examId);
        });
    });

</script>

<script type="text/javascript">
    $(document).ready(function() {
        $('.open-remark-modal').on('click', function() {
            var examId = $(this).data('exam-id');
            var remark = $(this).data('remark');
            $('#remarkExamTypeId').val(examId);
            $('#selectedRemark').val(remark);
        });
    });
</script>


{{-- <script type="text/javascript">
    $('#deleteModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');

        $('#userForm').attr("action", "{{ url('/companies') }}" + "/" + id);
});

</script> --}}
