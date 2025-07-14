<div class="container-fluid">
    <input type="hidden" name="exam_id" value="{{ $exam_id }}">
    <input type="hidden" name="student_id" value="{{ $student_id }}">

    <!-- Upload Type Selection -->
    <div class="row">
        <div class="col-lg-12">
            <div class="input-effect">
                <div class="d-flex mb-10 align-items-center justify-content-between">
                    <div class="d-flex">
                        <div class="mr-30">
                            <input type="radio" name="upload_type" id="image_type" value="image" checked>
                            <label for="image_type" class="ml-10">Image File</label>
                        </div>
                        <div>
                            <input type="radio" name="upload_type" id="csv_type" value="csv">
                            <label for="csv_type" class="ml-10">CSV Text</label>
                        </div>
                    </div>
                    <div id="image_quality_container">
                        <select id="image_quality" class="form-control form-control-sm" style="width: auto;">
                            <option value="0.95" selected>Image Quality: Very High (95%)</option>
                            <option value="0.9">High (90%)</option>
                            <option value="0.8">Good (80%)</option>
                            <option value="0.7">Medium (70%)</option>
                            <option value="0.6">Low (60%)</option>
                            <option value="0.5">Very Low (50%)</option>
                        </select>
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
            <div class="row">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <input type="checkbox" name="force_reextraction" id="force_reextraction" value="1" class="mr-10">
                        <label for="force_reextraction" class="mb-0">
                            <small class="text-muted">Force re-extraction (ignore cache)</small>
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div id="image_preview_link" class="text-right"></div>
                </div>
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
        // Image optimization constants
        var IMAGE_COMPRESSION = {
            TRIGGER_SIZE: 200 * 1024, // 200KB - trigger compression above this
            MAX_DIMENSION: 800, // Maximum width/height in pixels
            QUALITY: 0.95, // JPEG quality (70%)
            OUTPUT_FORMAT: 'image/jpeg'
        };

        // Initialize the upload toggle functionality
        function initializeUploadToggle() {
            const imageRadio = document.getElementById('image_type');
            const csvRadio = document.getElementById('csv_type');
            const csvSection = document.getElementById('csv_input_section');
            const imageSection = document.getElementById('image_input_section');
            const csvTextarea = document.getElementById('csv_data');
            const imageInput = document.getElementById('marks_image');

            // for upload marks image with basic compression
            if (imageInput) {
                imageInput.addEventListener("change", handleImageUpload);

                function handleImageUpload(event) {
                    var file = event.target.files[0];
                    if (!file) return;

                    var placeholderInput = document.getElementById("placeholderUploadMarks");
                    var previewLinkContainer = document.getElementById("image_preview_link");

                    placeholderInput.placeholder = file.name + " (" + (file.size / 1024).toFixed(0) + "KB)";

                    // Clear any existing preview link
                    previewLinkContainer.innerHTML = '';

                    // Create preview link for original image
                    var originalUrl = URL.createObjectURL(file);
                    previewLinkContainer.innerHTML = '<a href="' + originalUrl + '" target="_blank" class="btn btn-sm btn-outline-primary">View Original</a>';

                    // Simple compression for large files
                    if (file.size > IMAGE_COMPRESSION.TRIGGER_SIZE) {
                        placeholderInput.placeholder = file.name + " - Optimizing...";

                        var canvas = document.createElement('canvas');
                        var ctx = canvas.getContext('2d');
                        var img = new Image();

                        img.onload = function() {
                            // Calculate new dimensions using max dimension constant
                            var width = img.width;
                            var height = img.height;

                            if (width > IMAGE_COMPRESSION.MAX_DIMENSION || height > IMAGE_COMPRESSION.MAX_DIMENSION) {
                                if (width > height) {
                                    height = (height * IMAGE_COMPRESSION.MAX_DIMENSION) / width;
                                    width = IMAGE_COMPRESSION.MAX_DIMENSION;
                                } else {
                                    width = (width * IMAGE_COMPRESSION.MAX_DIMENSION) / height;
                                    height = IMAGE_COMPRESSION.MAX_DIMENSION;
                                }
                            }

                            canvas.width = width;
                            canvas.height = height;
                            ctx.drawImage(img, 0, 0, width, height);

                            // Get selected quality from dropdown
                            var selectedQuality = parseFloat(document.getElementById('image_quality').value);

                            canvas.toBlob(function(blob) {
                                if (blob) {
                                    var compressedFile = new File([blob], file.name, {
                                        type: IMAGE_COMPRESSION.OUTPUT_FORMAT
                                        , lastModified: Date.now()
                                    });

                                    // Replace file in input
                                    var dt = new DataTransfer();
                                    dt.items.add(compressedFile);
                                    imageInput.files = dt.files;

                                    var qualityPercent = Math.round(selectedQuality * 100);
                                    placeholderInput.placeholder = file.name + " (Optimized: " + (compressedFile.size / 1024).toFixed(0) + "KB @ " + qualityPercent + "%)";
                                    console.log('Image optimized:', (file.size / 1024).toFixed(0) + 'KB → ' + (compressedFile.size / 1024).toFixed(0) + 'KB @ ' + qualityPercent + '% quality');

                                    // Create preview link for optimized image
                                    var optimizedUrl = URL.createObjectURL(blob);
                                    previewLinkContainer.innerHTML = '<a href="' + optimizedUrl + '" target="_blank" class="btn btn-sm btn-success">View Optimized</a>';
                                } else {
                                    placeholderInput.placeholder = file.name + " (" + (file.size / 1024).toFixed(0) + "KB)";
                                }
                            }, IMAGE_COMPRESSION.OUTPUT_FORMAT, selectedQuality);
                        };

                        img.onerror = function() {
                            placeholderInput.placeholder = file.name + " (" + (file.size / 1024).toFixed(0) + "KB)";
                        };

                        img.src = URL.createObjectURL(file);
                    }
                }
            }

            if (!imageRadio || !csvRadio) {
                console.error('Radio buttons not found');
                return;
            }

            function toggleUploadSections() {
                console.log('Toggle called - Image checked:', imageRadio.checked, 'CSV checked:', csvRadio.checked);

                var qualityContainer = document.getElementById('image_quality_container');

                if (imageRadio.checked) {
                    // Show image section, hide CSV section
                    if (imageSection) imageSection.style.display = 'block';
                    if (csvSection) csvSection.style.display = 'none';
                    if (imageInput) imageInput.setAttribute('required', 'required');
                    if (csvTextarea) csvTextarea.removeAttribute('required');
                    // Show quality selector for image uploads
                    if (qualityContainer) qualityContainer.style.display = 'block';
                    // Clear CSV input when switching to image
                    if (csvTextarea) csvTextarea.value = '';
                    console.log('Switched to image upload mode');
                } else if (csvRadio.checked) {
                    // Show CSV section, hide image section
                    if (csvSection) csvSection.style.display = 'block';
                    if (imageSection) imageSection.style.display = 'none';
                    if (csvTextarea) csvTextarea.setAttribute('required', 'required');
                    if (imageInput) imageInput.removeAttribute('required');
                    // Hide quality selector for CSV uploads
                    if (qualityContainer) qualityContainer.style.display = 'none';
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

                    // Show loading indicator with compression info
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        if (uploadTypeValue === 'image') {
                            const fileSize = imageFile ? (imageFile.size / 1024).toFixed(0) : 0;
                            submitBtn.textContent = `Processing Optimized Image (${fileSize}KB)...`;
                        } else {
                            submitBtn.textContent = 'Saving...';
                        }
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
