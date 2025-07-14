<div class="container-fluid">
    <input type="hidden" name="exam_id" value="{{ $exam_id }}">
    <input type="hidden" name="student_id" value="{{ $student_id }}">

    <!-- Upload Type Selection -->
    <div class="row">
        <div class="col-lg-12">
            <div class="input-effect">
                <div class="d-flex mb-10">
                    <div class="mr-30">
                        <input type="radio" name="upload_type" id="image_type" value="image" checked>
                        <label for="image_type" class="ml-10">Image File</label>
                    </div>
                    <div>
                        <input type="radio" name="upload_type" id="csv_type" value="csv">
                        <label for="csv_type" class="ml-10">CSV Text</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CSV Input Section -->
    <div class="row" id="csv_input_section" style="display: none;">
        <div class="col-lg-12">
            <div class="input-effect">
                <label>CSV Data <span class="text-danger">*</span></label>
                <textarea class="primary_input_field form-control" name="csv_data" id="csv_data" rows="5" placeholder="Format:&#10;subject_id,subject_code,MT1,MT2,CA,EXAM&#10;10,L-ART,26,8,8,35&#10;11,MATH,30,10,10,49&#10;9,SCIENCE,29.5,9,10,45&#10;&#10;">
                </textarea>
                <span class="focus-border textarea"></span>
            </div>
        </div>
    </div>

    <!-- Image Upload Section -->
    <div class="row" id="image_input_section">
        {{-- <div class="col-lg-12">
            <div class="input-effect">
                <label>Upload Marks Image <span class="text-danger">*</span></label>
                <input type="file" class="primary_input_field form-control" name="marks_image" id="marks_image" accept="image/jpeg,image/jpg,image/png,image/gif">
                <small class="text-muted mt-10 d-block">
                    Upload an image containing marks data. AI will extract the CSV data from the image.
                    Supported formats: JPEG, PNG, GIF
                </small>
                <span class="focus-border"></span>
            </div>
        </div> --}}

        <div class="col-lg-12">
            <div class="primary_input">
                <div class="primary_file_uploader">
                    <input class="primary_input_field form-control " type="text" placeholder="Upload Marks Image" readonly="" id="placeholderUploadMarks">
                    <button class="" type="button" id="browseMarksImage">
                        <label class="primary-btn small fix-gr-bg" for="marks_image">Browse</label>
                        <input type="file" class="d-none form-control" name="marks_image" id="marks_image" accept="image/jpeg,image/jpg,image/png,image/gif">
                    </button>
                    <small class="text-muted mt-10 d-block">
                        Upload an image containing marks data. AI will extract the CSV data from the image.
                        Supported formats: JPEG, PNG, GIF
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-12 mt-15">
            <div class="d-flex align-items-center">
                <input type="checkbox" name="force_reextraction" id="force_reextraction" value="1" class="mr-10">
                <label for="force_reextraction" class="mb-0">
                    <small class="text-muted">Force re-extraction (ignore cache)</small>
                </label>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 text-center">
            <div class="mt-40 d-flex justify-content-between">
                <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>
                <button class="primary-btn fix-gr-bg submit" type="submit">@lang('common.save')</button>
            </div>
        </div>
    </div>

    <script>
        // Initialize the upload toggle functionality
        function initializeUploadToggle() {
            const imageRadio = document.getElementById('image_type');
            const csvRadio = document.getElementById('csv_type');
            const csvSection = document.getElementById('csv_input_section');
            const imageSection = document.getElementById('image_input_section');
            const csvTextarea = document.getElementById('csv_data');
            const imageInput = document.getElementById('marks_image');

            // for upload marks image
            if (imageInput) {
                imageInput.addEventListener("change", showFileName);

                function showFileName(event) {
                    var imageFileInput = event.srcElement;
                    var fileName = imageFileInput.files[0].name;
                    document.getElementById("placeholderUploadMarks").placeholder = fileName;
                }
            }

            if (!imageRadio || !csvRadio) {
                console.error('Radio buttons not found');
                return;
            }

            function toggleUploadSections() {
                console.log('Toggle called - Image checked:', imageRadio.checked, 'CSV checked:', csvRadio.checked);

                if (imageRadio.checked) {
                    // Show image section, hide CSV section
                    if (imageSection) imageSection.style.display = 'block';
                    if (csvSection) csvSection.style.display = 'none';
                    if (imageInput) imageInput.setAttribute('required', 'required');
                    if (csvTextarea) csvTextarea.removeAttribute('required');
                    // Clear CSV input when switching to image
                    if (csvTextarea) csvTextarea.value = '';
                    console.log('Switched to image upload mode');
                } else if (csvRadio.checked) {
                    // Show CSV section, hide image section
                    if (csvSection) csvSection.style.display = 'block';
                    if (imageSection) imageSection.style.display = 'none';
                    if (csvTextarea) csvTextarea.setAttribute('required', 'required');
                    if (imageInput) imageInput.removeAttribute('required');
                    // Clear image input when switching to CSV
                    if (imageInput) imageInput.value = '';
                    console.log('Switched to CSV input mode');
                }
            }

            // Remove any existing event listeners to prevent duplicates
            imageRadio.removeEventListener('change', toggleUploadSections);
            csvRadio.removeEventListener('change', toggleUploadSections);

            // Add event listeners for radio button changes
            imageRadio.addEventListener('change', toggleUploadSections);
            csvRadio.addEventListener('change', toggleUploadSections);

            // Initialize on page load - default to image since it's checked
            toggleUploadSections();

            // Add form validation before submit
            const form = imageRadio.closest('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const uploadType = document.querySelector('input[name="upload_type"]:checked');
                    if (!uploadType) {
                        e.preventDefault();
                        alert('Please select an upload type');
                        return false;
                    }

                    const uploadTypeValue = uploadType.value;
                    const csvData = csvTextarea ? csvTextarea.value.trim() : '';
                    const imageFile = imageInput ? imageInput.files[0] : null;

                    console.log('Form submit validation:', {
                        uploadType: uploadTypeValue
                        , csvData: !!csvData
                        , imageFile: !!imageFile
                    });

                    if (uploadTypeValue === 'csv' && !csvData) {
                        e.preventDefault();
                        alert('Please enter CSV data');
                        return false;
                    }

                    if (uploadTypeValue === 'image' && !imageFile) {
                        e.preventDefault();
                        alert('Please select an image file');
                        return false;
                    }

                    // Show loading indicator
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = uploadTypeValue === 'image' ? 'Processing Image...' : 'Saving...';
                    }
                });
            }
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeUploadToggle);
        } else {
            initializeUploadToggle();
        }

        // Also initialize when modal content is loaded (for AJAX loaded content)
        setTimeout(initializeUploadToggle, 100);

    </script>
</div>
