// Utility View JavaScript Functions

// File upload functionality
const CHUNK_SIZE = 1 * 1024 * 1024; // 2MB per chunk
let filename = $("#dataUploadFileInput");
let fileChunks = [];
let fileHash;

// Initialize when document is ready
$(document).ready(function() {
    checkGmailStatus();
    initializeApiLimitsCheck();
});

// File upload event listener
document.getElementById('upload_student_file').addEventListener('change', function(e) {
    file = e.target.files[0];
    if (file) {
        let totalChunks = Math.ceil(file.size / CHUNK_SIZE);
        fileChunks = [];
        $('#file_loader').addClass('pre_loader');
        $('#file_loader').removeClass('loader');

        // Compute the hash of the entire file
        computeFileHash(file).then((hash) => {
            fileHash = hash;
            console.log('File Hash (SHA-256):', fileHash); // Log the hash
            filename.val(file.name)
            $('#file_loader').removeClass('pre_loader');
            $('#file_loader').addClass('loader');

            // Prepare the file chunks for upload
            for (let i = 0; i < totalChunks; i++) {
                fileChunks.push({
                    chunk: file.slice(i * CHUNK_SIZE, (i + 1) * CHUNK_SIZE),
                    chunkIndex: i,
                    totalChunks: totalChunks
                });
            }
        });
    }
});

// Form upload event listener
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    uploadChunk(0);
});

// Upload chunk function
function uploadChunk(chunkIndex) {
    let chunk = fileChunks[chunkIndex];
    let totalChunks = chunk.totalChunks
    let formData = new FormData();
    formData.append('file', chunk.chunk);
    formData.append('chunkIndex', chunk.chunkIndex);
    formData.append('totalChunks', totalChunks);
    formData.append('filename', file.name);
    formData.append('fileHash', fileHash);

    $.ajax({
        url: window.uploadUrl || '/result/upload', // Use global variable or fallback
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            console.log(`Chunk ${chunkIndex + 1}/${totalChunks}:`, response);

            // Update progress bar
            const progress = Math.round(((chunkIndex + 1) / totalChunks) * 100);
            $('#progressBar').css('width', progress + '%').text(progress + '%');

            if (response.success) {
                $('#progressBar').css('width', '100%').text('100%');
                toastr.success(response.message, 'Success')
                return;
            } else {
                uploadChunk(response.nextIndex);
            }
        },
        error: function(error) {
            console.error(`Error uploading chunk ${chunkIndex + 1}:`, error);
            toastr.error('An error occurred while uploading the file.', 'Failed')
        }
    });
}

// Compute file hash function
function computeFileHash(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const buffer = e.target.result;
            crypto.subtle.digest('SHA-256', buffer).then(function(hashBuffer) {
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                const hashHex = hashArray.map(byte => byte.toString(16).padStart(2, '0')).join('');
                resolve(hashHex); // Return the SHA-256 hash
            }).catch(reject);
        };
        reader.readAsArrayBuffer(file);
    });
}

// Gmail Integration Status Check
function checkGmailStatus() {
    $.get(window.gmailStatusUrl || '/result/gmail/status', function(data) {
        const card = $('#gmail-integration-card');
        const title = card.find('h1');
        
        if (data.configured) {
            title.text('Gmail Ready');
            card.removeClass('green').addClass('cyan');
            card.attr('href', '#');
            card.off('click').on('click', function(e) {
                e.preventDefault();
                toastr.success('Gmail is properly configured and ready to send emails!', 'Gmail Status');
            });
        } else if (data.has_credentials) {
            title.text('Authorize Gmail');
            card.removeClass('green').addClass('violet');
            card.attr('href', window.gmailAuthUrl || '/result/gmail/auth');
        } else {
            title.text('Configure Gmail');
            card.removeClass('green').addClass('fuchsia');
            card.attr('href', '#');
            card.off('click').on('click', function(e) {
                e.preventDefault();
                toastr.error('Please add GMAIL_CLIENT_ID and GMAIL_CLIENT_SECRET to your .env file first.', 'Configuration Required');
            });
        }
    }).fail(function() {
        const card = $('#gmail-integration-card');
        const title = card.find('h1');
        title.text('Gmail Error');
        card.removeClass('green').addClass('red');
    });
}

// API Limits Check functionality
function initializeApiLimitsCheck() {
    $('#api-limits-card').on('click', function(e) {
        e.preventDefault();
        const card = $(this);
        const title = card.find('h1');
        const originalText = title.text();
        
        // Show loading state
        title.text('Checking...');
        card.addClass('loading');
        
        // Make request to check API limits
        $.get(window.apiLimitsUrl || '/result/openrouter/limits')
            .done(function(data) {
                if (data.success) {
                    const apiData = data.data;
                    let message = 'OpenRouter API Status:<br>';
                    message += '💰 Usage: $' + (apiData.usage ? apiData.usage.toFixed(6) : '0') + '<br>';
                    message += '🎯 Limit: ' + (apiData.limit || 'Unlimited') + '<br>';
                    message += '👤 Account: ' + (apiData.is_free_tier ? 'Free Tier' : 'Paid') + '<br>';
                    if (apiData.rate_limit) {
                        message += '⚡ Rate Limit: ' + apiData.rate_limit.requests + ' requests per ' + apiData.rate_limit.interval + '<br>';
                    }
                    if (apiData.limit_remaining !== null) {
                        message += '📊 Remaining: $' + apiData.limit_remaining;
                    }
                    
                    toastr.success(message, 'API Status Check', {
                        "escapeHtml": false
                    });
                } else {
                    toastr.error(data.message, 'API Check Failed');
                }
            })
            .fail(function(xhr) {
                let errorMsg = 'Failed to check API limits.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                toastr.error(errorMsg, 'Connection Error');
            })
            .always(function() {
                // Restore card state
                title.text(originalText);
                card.removeClass('loading');
            });
    });
}

// Select all checkboxes functionality
function selectAllCheckboxes() {
    $('#selectAll').click(function() {
        $('input:checkbox').prop('checked', this.checked);
    });
}

// Image input change handler
function handleImageInputChange() {
    $(document).on('change', '#imgInpBac', function(event) {
        getFileName($(this).val(), '#placeholderFileFourName');
        imageChangeWithFile($(this)[0], '#blahImg');
    });
}
