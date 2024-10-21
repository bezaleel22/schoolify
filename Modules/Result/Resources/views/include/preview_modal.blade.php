<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<div class="modal fade" id="pdfPreviewModal" tabindex="-1" role="dialog" aria-labelledby="pdfPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfPreviewModalLabel">@lang('result::student.preview_title')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-1">
                <div id="pdfPreview">Loading PDF...</div>
            </div>

            <!-- Form with hidden inputs and buttons -->
            <form action="{{ route('result.publish') }}" method="POST">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student_detail->id }}">
                <input type="hidden" id="previewExamTypeId" name="exam_type_id" value="">

                <div class="modal-footer d-flex justify-content-between align-items-center mb-0">
                    <div class="pdf-navigation">
                        <button type="button" id="prevPage" class="primary-btn fix-gr-bg">
                            <i class="ti-arrow-left"></i>
                        </button>
                        <button type="button" id="nextPage" class="primary-btn fix-gr-bg">
                            <i class="ti-arrow-right"></i>
                        </button>
                        <span id="pageInfo"></span>
                    </div>
                    <div>
                        <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>
                        <button type="submit" class="primary-btn fix-gr-bg">@lang('result::student.publish')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    let pdfDocument = null;
    let currentPage = 1; // Start at the first page
    let totalPages = 0;

    // Load PDF.js and render the PDF when the modal is shown
    $('#pdfPreviewModal').on('shown.bs.modal', function(event) {
        const button = $(event.relatedTarget); // Button that triggered the modal
        const url = button.data('pdf-url'); // Get the PDF URL from the button's data attribute

        // Clear any previously loaded PDF
        const pdfPreview = document.getElementById('pdfPreview');
        pdfPreview.innerHTML = 'Loading PDF...';

        // Load PDF.js document
        const loadingTask = pdfjsLib.getDocument(url);
        loadingTask.promise.then(function(pdf) {
            pdfDocument = pdf; // Save the PDF document
            totalPages = pdf.numPages; // Get total number of pages
            renderPage(currentPage); // Render the first page
        }).catch(function(error) {
            console.error('Error loading PDF:', error);
            pdfPreview.innerHTML = 'Error loading PDF';
        });
    });

    // Render the specified page
    function renderPage(pageNum) {
        if (!pdfDocument) return;

        // Fetch the specified page
        pdfDocument.getPage(pageNum).then(function(page) {
            const viewport = page.getViewport({
                scale: 1
            });
            const modalDialogWidth = $('#pdfPreviewModal .modal-body').outerWidth(true);
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
                document.getElementById('pdfPreview').innerHTML = '';
                document.getElementById('pdfPreview').appendChild(canvas);
                document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages}`;
            });
        });
    }

    // Navigation button event listeners
    document.getElementById('prevPage').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            renderPage(currentPage);
        }
    });

    document.getElementById('nextPage').addEventListener('click', function() {
        if (currentPage < totalPages) {
            currentPage++;
            renderPage(currentPage);
        }
    });

    // Optional: Clean up on modal hide 
    $('#pdfPreviewModal').on('hide.bs.modal', function() {
        pdfDocument = null; // Clear the PDF document reference
        currentPage = 1; // Reset to the first page
        document.getElementById('pdfPreview').innerHTML = ''; // Clear the PDF preview
        document.getElementById('pageInfo').textContent = ''; // Clear page info
    });

</script>
