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
    // Get the current term (last exam term) and check if it's null
    $current_term_id = $exam_terms->last()->id;
    $current_term_result = $results[$current_term_id] ?? null;
    @endphp


    <hr />
    @php
    $first_result_with_data = collect($results)->filter()->first();
    @endphp
    @if ($exam_count > 1 && $first_result_with_data) <div class="no-search no-paginate no-table-info mb-2">
        @php
        $records = $first_result_with_data->records;
        @endphp
        <div class="table-responsive">
            <table class="table school-table-style shadow-none pb-0" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>@lang('result::student.subject')</th>
                        @foreach ($records[0]->marks as $exam_title => $record)
                        <th>{{ $exam_title }}</th>
                        @endforeach
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
        <div class=@if($key !=1) mt-40 @endif>
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
                            @if($student->type == 'GRADERS')
                            <th>MTA</th>
                            <th>CA</th>
                            <th>REPORT</th>
                            <th>EXAM</th>
                            @else
                            @foreach ($records[0]->marks as $exam_title => $record)
                            <th>{{ $exam_title }}</th>
                            @endforeach
                            @endif
                            <th>@lang('result::student.score')</th>
                            <th>@lang('result::student.grade')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                        <tr>
                            <td>{{ @$record->subject }}</td>
                            @if($student->type == 'GRADERS')
                            <td>{{ $record->marks["MTA"] ?? 0 }}</td>
                            <td>{{ $record->marks["CA"] ?? 0 }}</td>
                            <td>{{ $record->marks["REPORT"]?? 0 }}</td>
                            <td>{{ $record->marks["EXAM"]?? 0 }}</td>
                            @else
                            @foreach ($record->marks as $mark)
                            <td>{{ $mark }}</td>
                            @endforeach
                            @endif
                            <td>{{ @$record->total_score }}</td>
                            <td>{{ @$record->grade }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    @php
                    $score = $result->score;
                    $teacher_remark = $result->remark;
                    $min_average = $score->min_average->value;
                    $max_average = $score->max_average->value;
                    @endphp
                    <tfoot>
                        <tr>
                            <th>
                                @if($exam->id == $current_term_id)
                                <div class="col-lg-12 mb-20">
                                    <button onclick="showModal(this)" data-path="{{ route('score.book.modal') }}" data-exam-id="{{ $exam->id }}" class="btn btn-primary btn-sm open-score-modal">
                                        Update Marks
                                    </button>
                                </div>
                                @endif
                            </th>
                            @if($student->type == 'GRADERS')
                            <th colspan="2"></th>
                            @endif

                            <th colspan="2" style="text-align: right;">
                                @lang('result::student.total_score'): {{ @$score->total }}
                                <br>
                                @lang('result::student.average'): {{ @$score->average }}
                            </th>
                            <th colspan="2" style="text-align: right;">
                                @lang('result::student.max_average'): {{ $max_average }}
                                <br>
                                @lang('result::student.min_average'): {{ $min_average }}
                            </th>
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
                        <textarea disabled class="primary_input_field form-control" cols="0" rows="2" name="remark" id="Remark">{{ @$teacher_remark->remark ?? '' }}</textarea>

                        <span class="focus-border textarea"></span>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
        @if(is_null($current_term_result))
        <div class="col-lg-12 mb-20">
            <hr />
            <div class="d-flex align-items-center justify-content-end p-2">
                <button onclick="showModal(this)" data-path="{{ route('score.book.modal') }}" data-exam-id="{{ $current_term_id }}" class="btn btn-primary btn-sm open-score-modal">
                    Add Marks Book
                </button>
            </div>
        </div>
        @endif

    </div>

    <div class="modal fade admin-query" id="resultModal" tabindex="-1" role="dialog" aria-labelledby="resultModalLabel" aria-hidden="true">>
        <form id="publishForm" class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document" action="" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    {{-- <h4 class="modal-title">@lang('result::student.add_remark')</h4> --}}
                    <div class="primary_input col-lg-6 col-md-8">
                        <label class="primary_input_label" for="parent_id">Parent</label>
                        <select class="primary_select form-control" name="parent_id" id="parent_id">
                            <option data-display="Select Parent" value="">@lang('student.select_parent')</option>
                            @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}" {{ $student_detail->parent_id == $parent->id ? 'selected' : '' }}>
                                {{ $parent->fathers_name ?? $parent->mothers_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="primary_input col-lg-6 col-md-8">
                        <label class="primary_input_label" for="parent_email">Parent Emails</label>
                        <select class="primary_select form-control" name="parent_email" id="parent_email">
                            <option data-display="Select Parent" value="">Select parent email</option>
                            <option value="{{ $student_info->parent_email ?? ""}}" selected>{{ $student_info->parent_email ?? ""}}</option>
                            @foreach ($emails as $email)
                            <option value="{{ $email }}">
                                {{ $email }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div id="modalBody" class="modal-body"></div>
                <div class="modal-footer w-100">
                    <div class="d-flex justify-content-between align-items-center mb-0 w-100">
                        <div class="pdf-navigation">
                            <button type="button" type="button" id="prevPage" class="primary-btn fix-gr-bg">
                                <i class="ti-arrow-left"></i>
                            </button>

                            <button type="button" type="button" id="nextPage" class="primary-btn fix-gr-bg">
                                <i class="ti-arrow-right"></i>
                            </button>
                            <span id="pageInfo"></span>
                        </div>
                        <div>
                            <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>
                            <button type="submit" class="primary-btn fix-gr-bg">@lang('result::student.publish')</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<template id="pdfLoader">
    <div id="pdfContainer" class="d-flex flex-column justify-content-center align-items-center w-100" style="min-height: 200px;">
        <img id="loader-img" class="loader_img_style" src="{{ asset('public/backEnd/img/demo_wait.gif') }}" alt="loader">
        <p id="loader-text">Loading please wait...</p>
    </div>
</template>

<script>
    function showModal(button) {
        $('#resultModal').on('hidden.bs.modal', function() {
            button.disabled = false;
        });

        // Reference the modal elements
        var $modal = $("#resultModal"); // Reference the modal element
        var $title = $modal.find(".modal-title"); // Reference the modal title
        var $body = $modal.find(".modal-body"); // Reference the modal body
        var $footer = $modal.find(".modal-footer");; // Reference the modal footer
        var $input = $modal.find('.primary_input')
        var $loader = $('#pdfLoader')
        var $publishForm = $("#publishForm");; // Reference the modal footer

        $.ajax({
            type: "POST"
            , url: $(button).data("path")
            , data: {
                student: @json($student_info)
                , exam_id: $(button).data("exam-id")
            }
            , beforeSend: function() {
                $footer.hide()
                $body.html($loader.html());
                $body.removeClass('p-1');
                $modal.modal("show");
                button.disabled = true;
            }
            , success: function(result) {
                console.log(result);
                $publishForm.attr("action", result.url);
                $title.text(result.title || "Modal Title");
                if (result.preview) {
                    $body.find('#loader-text').text('Generating Result PDF please wait...')
                    $body.addClass('p-1');
                    $footer.show()
                    $publishForm.prepend(result.content);
                    preview(result.pdfUrl)
                    return
                }
                $body.html(result.content);
                $('#resultModal').off('shown.bs.modal');
            }
            , error: function(xhr, status, error) {
                console.error("Error:", error);
                button.disabled = false;
                $modal.modal("hide");
                toastr.error('An error occurred. Please try again.', 'Failed')
            }
        , });
    }

</script>


<script>
    let pdfDocument = null;
    let currentPage = 1; // Start at the first page
    let totalPages = 0;
    const container = document.getElementById('pdfContainer');

    // Load PDF.js and render the PDF when the modal is shown
    function preview(url) {
        const loadingTask = pdfjsLib.getDocument(url);
        loadingTask.promise.then(function(pdf) {
            pdfDocument = pdf; // Save the PDF document
            totalPages = pdf.numPages; // Get total number of pages
            renderPage(currentPage); // Render the first page
        }).catch(function(error) {
            console.error('Error loading PDF:', error);
            container.innerHTML = '<strong>Error loading PDF</strong>';
        });
    }

    function renderPage(pageNum) {
        if (!pdfDocument) return;
        pdfDocument.getPage(pageNum).then(function(page) {
            // Get viewport dimensions
            const viewport = page.getViewport({
                scale: 1
            });
            const modalDialogWidth = $('#resultModal .modal-body').outerWidth(true);
            const scale = (modalDialogWidth * 0.98) / viewport.width;
            const scaledViewport = page.getViewport({
                scale
            });

            // Higher resolution settings (2x for better clarity)
            const scaleFactor = 4;
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = scaledViewport.width * scaleFactor;
            canvas.height = scaledViewport.height * scaleFactor;

            // Adjust the rendering context for higher resolution
            const renderContext = {
                canvasContext: context
                , viewport: page.getViewport({
                    scale: scale * scaleFactor
                })
            , };

            // Set canvas style to match the scaled viewport
            canvas.style.width = `${scaledViewport.width}px`;
            canvas.style.height = `${scaledViewport.height}px`;

            // Render the page
            page.render(renderContext).promise.then(function() {
                // Clear previous content and append the new canvas
                const container = document.getElementById('pdfContainer');
                document.getElementById('modalBody').scrollTop = 0;
                container.innerHTML = '';
                container.appendChild(canvas);

                // Update the page info
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


    document.getElementById('prevPage').addEventListener('click', prevPage);
    document.getElementById('nextPage').addEventListener('click', nextPage);

</script>
