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

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<div role="tabpanel" class="tab-pane fade" id="studentExam">
    @php
    $exam_count = count($exam_terms);
    @endphp

    @if ($exam_count > 1) <div class="no-search no-paginate no-table-info mb-2">
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
        @if ($result)
        @php
        $records = $result->records;
        $student = $result->student;
        $exam = $result->exam_type;
        $record_count = count($result->records);
        $params = ['id'=> $student_detail->id, 'exam_id'=>$exam->id];
        @endphp
        <div class=@if($key !=0) mt-40 @endif>
            <div class="col-lg-12">
                <div class="d-flex align-items-center mb-0">
                    <div class="main-title">
                        <h3 class="mb-0">{{ @$exam->title }}</h3>
                    </div>
                    <button onclick="showModal(this)" data-path="{{ route('result.preview', $params) }}" class="btn btn-link btn-sm open-result-modal">
                        @lang('result::student.preview')
                    </button>

                </div>
            </div>

            <div class="text-right mb-2">
                <button onclick="showModal(this)" data-path="{{ route('result.rating', $params) }}" class="primary-btn btn-sm tr-bg text-uppercase bord-rad open-result-modal">
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
                            <button onclick="showModal(this)" data-path="{{ route('result.remark', $params) }}" class="btn btn-link btn-sm open-result-modal">
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

<div class="modal fade admin-query" id="resultModal" tabindex="-1" role="dialog" aria-labelledby="resultModalLabel" aria-hidden="true">>
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('result::student.add_remark')</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div id="modalBody" class="modal-body"></div>
            <div class="modal-footer w-100">
                <div class="d-flex justify-content-between align-items-center mb-0">
                    <div class="pdf-navigation">
                        <button onclick="prevPage(this)" type="button" id="prevPage" class="primary-btn fix-gr-bg">
                            <i class="ti-arrow-left"></i>
                        </button>
                        <button onclick="nextPage(this)" type="button" id="nextPage" class="primary-btn fix-gr-bg">
                            <i class="ti-arrow-right"></i>
                        </button>
                        <span id="pageInfo"></span>
                    </div>
                    <div>
                        <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>
                        <button type="button" form="publishForm" class="primary-btn fix-gr-bg">@lang('result::student.publish')</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showModal(button) {
        button.disabled = true;

        // Reference the modal elements
        var $modal = $("#resultModal"); // Reference the modal element
        var $title = $modal.find(".modal-title"); // Reference the modal title
        var $body = $modal.find(".modal-body"); // Reference the modal body
        var $footer = $modal.find(".modal-footer"); // Reference the modal footer

        $.ajax({
            type: "POST"
            , url: $(button).data("path")
            , data: {
                student: @json($student_info)
            }
            , success: function(result) {
                console.log(result);
                $title.text(result.title || "Modal Title");
                if (result.preview) {
                    $footer.prepend(result.content);
                    $footer.show()
                    $body.addClass('p-1');
                    $('#resultModal').on('shown.bs.modal', function() {
                        preview(result.pdfUrl)
                    });
                    $modal.modal("show");
                    button.disabled = false;
                    return
                }
                $body.html(result.content);
                $footer.hide()
                $body.removeClass('p-1');
                $modal.modal("show");
                button.disabled = false;
                $('#resultModal').off('shown.bs.modal');
            }
            , error: function(xhr, status, error) {
                console.error("Error:", error);
                button.disabled = false;
                alert("An error occurred. Please try again.");
            }
        , });
    }

</script>


<script>
    let pdfDocument = null;
    let currentPage = 1; // Start at the first page
    let totalPages = 0;

    // Load PDF.js and render the PDF when the modal is shown
    function preview(url) {
        const modalBody = document.getElementById('modalBody');
        modalBody.innerHTML = 'Loading PDF...';
        // Load PDF.js document
        const loadingTask = pdfjsLib.getDocument(url);
        loadingTask.promise.then(function(pdf) {
            pdfDocument = pdf; // Save the PDF document
            totalPages = pdf.numPages; // Get total number of pages
            renderPage(currentPage); // Render the first page
        }).catch(function(error) {
            console.error('Error loading PDF:', error);
            modalBody.innerHTML = 'Error loading PDF';

        });
    }

    function renderPage(pageNum) {
        if (!pdfDocument) return;

        pdfDocument.getPage(pageNum).then(function(page) {
            const viewport = page.getViewport({
                scale: 1
            });
            const modalDialogWidth = $('#resultModal .modal-body').outerWidth(true);
            const scale = (modalDialogWidth * 0.98) / viewport.width;
            const scaledViewport = page.getViewport({
                scale
            });

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.height = scaledViewport.height;
            canvas.width = scaledViewport.width;

            const renderContext = {
                canvasContext: context
                , viewport: scaledViewport
            };

            page.render(renderContext).promise.then(function() {
                document.getElementById('modalBody').innerHTML = '';
                document.getElementById('modalBody').appendChild(canvas);
                document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages}`;
            });
        });
    }

    function prevPage() {
        if (currentPage > 1) {
            currentPage--;
            renderPage(currentPage);
        }
    }

    function nextPage() {
        if (currentPage < totalPages) {
            currentPage++;
            renderPage(currentPage);
        }
    }

    $('#resultModal').on('hidden.bs.modal', function() {
        pdfDocument = null;
        currentPage = 1;
        document.getElementById('modalBody').innerHTML = '';
        document.getElementById('pageInfo').textContent = '';
    });

</script>