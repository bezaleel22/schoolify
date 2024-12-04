@extends('backEnd.master')
@section('title')
@lang('system_settings.utilities')
@endsection
@section('mainContent')

<section class="sms-breadcrumb mb-20">
    <div class="container-fluid">
        <div class="row justify-content-between">
            <h1>@lang('system_settings.utilities') </h1>
            <div class="bc-pages">
                <a href="{{route('dashboard')}}">@lang('common.dashboard')</a>
                <a href="#">@lang('system_settings.system_settings')</a>
                <a href="#">@lang('system_settings.utilities') </a>
            </div>
        </div>
    </div>
</section>

<section class="admin-visitor-area up_admin_visitor empty_table_tab">
    <div class="container-fluid p-0">
        <div class="white-box">
            <div class="row row-gap-24">
                <div class="col-md-4 col-lg-3 col-sm-6">
                    <a class="white-box single-summery cyan d-block btn-ajax" href="{{ route('utilities','optimize_clear') }}">
                        <div class="d-block mt-10 text-center ">
                            <h3><i class="ti-cloud font_30"></i></h3>
                            <h1 class="gradient-color2 total_purchase"> Clear Cache </h1>
                        </div>
                    </a>
                </div>

                <div class="col-md-4 col-lg-3 col-sm-6">
                    <a class="white-box single-summery violet d-block btn-ajax" href="{{ route('utilities','clear_log') }}">
                        <div class="d-block mt-10 text-center ">
                            <h3><i class="ti-receipt font_30"></i></h3>
                            <h1 class="gradient-color2 total_purchase">Clear Log</h1>
                        </div>
                    </a>
                </div>

                <div class="col-md-4 col-lg-3 col-sm-6">
                    <a class="white-box single-summery blue d-block btn-ajax" href="{{ route('utilities','change_debug') }}">
                        <div class="d-block mt-10 text-center ">
                            <h3><i class="ti-blackboard font_30"></i></h3>
                            <h1 class="gradient-color2 total_purchase"> {{ __((env('APP_DEBUG') ? "Disable" : "Enable" ).' App Debug') }}</h1>
                        </div>
                    </a>
                </div>


                <div class="col-md-4 col-lg-3 col-sm-6">
                    <a class="white-box single-summery fuchsia d-block btn-ajax" href="{{ route('utilities', 'force_https') }}">
                        <div class="d-block mt-10 text-center ">
                            <h3><i class="ti-lock font_30"></i></h3>
                            <h1 class="gradient-color2 total_purchase"> {{ __((env('FORCE_HTTPS') ? "Disable" : "Enable" ).' Force HTTPS') }}</h1>
                        </div>
                    </a>
                </div>

                <div class="col-md-4 col-lg-3 col-sm-6">
                    <a class="white-box single-summery fuchsia d-block btn-ajax" href="{{ route('result.send_emails') }}">
                        <div class="d-block mt-10 text-center ">
                            <h3><i class="ti-import font_30"></i></h3>
                            <h1 class="gradient-color2 total_purchase">Send Result Emails</h1>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="row mt-40">
            <div class="col-lg-12">
                <div class="white-box">
                    <h3 class="text-center">Student Data Uplaod</h3>
                    <hr>
                    <form id="uploadForm">
                        @csrf
                        <div class="row justify-content-center">
                            <div class="col-lg-6 col-md-8">
                                <div class="row mb-20">
                                    <div class="col-lg-12 mt-15">
                                        <div class="primary_input">
                                            <div class="primary_file_uploader">
                                                <input class="primary_input_field form-control" readonly="true" type="text" placeholder="File" id="dataUploadFileInput">
                                                <div class="d-flex">
                                                    <div class="pull-right loader" id="file_loader">
                                                        <img class="loader_img_style" src="{{ asset('public/backEnd/img/demo_wait.gif') }}" alt="loader">
                                                    </div>
                                                    <button class="" type="button">
                                                        <label class="primary-btn small fix-gr-bg" for="upload_student_file">Browse</label>
                                                        <input type="file" value="somefile.zip" class="d-none" name="student_file" id="upload_student_file">
                                                    </button>
                                                </div>

                                                <code>
                                                    (jpg,png,jpeg,pdf,doc,docx,txt,xlsx,rar,zip are allowed forupload)
                                                </code>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-lg-12 text-center">
                                        <button class="primary-btn fix-gr-bg">
                                            <span class="ti-check"></span>
                                            Upload
                                        </button>
                                        <div id="progressBar" class="progress-bar violet mt-10" role="progressbar" style="width: 0%;"></div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row mt-40">
            <div class="col-lg-12">
                <div class="white-box">
                    <form method="post" action="{{route('updateMaintenance')}}" enctype='multipart/form-data'>
                        @csrf
                        <div class="row p-0">
                            <div class="col-lg-12">
                                <h3 class="text-center">@lang('system_settings.maintenance_mode_setting')</h3>
                                <hr>
                                <div class="row mb-40 mt-40">
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="col-lg-5 d-flex">
                                                <p class="text-uppercase fw-500 mb-10">@lang('system_settings.maintenance_mode_')</p>
                                            </div>
                                            <div class="col-lg-7">
                                                <div class="d-flex radio-btn-flex flex-wrap">

                                                    <div class="mr-30">
                                                        <input type="radio" name="maintenance_mode" id="via_sms" class="common-radio relationButton copy_per_th" {{@$setting->maintenance_mode == 1? 'checked':''}} value="1">
                                                        <label for="via_sms" id="via_sms">@lang('common.enable')</label>
                                                    </div>

                                                    <div class="mr-30">
                                                        <input type="radio" name="maintenance_mode" id="via_email" class="common-radio relationButton copy_per_th" {{@$setting->maintenance_mode == 0? 'checked':''}} value="0">
                                                        <label for="via_email" id="via_email">@lang('common.disable')</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="primary_input">
                                            <label class="primary_input_label" for="">@lang('common.title') </label>
                                            <input class="primary_input_field form-control" type="text" name="title" autocomplete="off" value="{{isset($setting)? $setting->title:''}}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-40 mt-40">
                                    <div class="col-lg-12">
                                        <div class="primary_input">
                                            <label class="primary_input_label" for="">@lang('front_settings.sub_title') </label>
                                            <input class="primary_input_field form-control" type="text" name="sub_title" autocomplete="off" value="{{isset($setting)? $setting->sub_title:''}}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-40 mt-40">
                                    <div class="col-lg-12">
                                        <div class="row">
                                            <div class="col-lg-3 d-flex">
                                                <p class="text-uppercase fw-500 mb-10">@lang('auth.applicable_for')</p>
                                            </div>
                                            <div class="col-lg-9">
                                                <div class="d-flex radio-btn-flex gap-10 flex-column flex-sm-row flex-wrap">
                                                    @foreach ($roles as $role)
                                                    <div class="mr-30">
                                                        <input type="checkbox" name="applicable_for[]" id="applicable_for_{{$role->id}}" class="common-radio relationButton copy_per_th" value="{{$role->id}}" @if(is_null($setting->applicable_for) || in_array($role->id,$setting->applicable_for)) checked @endif>
                                                        <label for="applicable_for_{{$role->id}}" id="applicable_for_{{$role->id}}">{{$role->name}}</label>
                                                    </div>
                                                    @endforeach

                                                    <div class="mr-30">
                                                        <input type="checkbox" name="applicable_for[]" id="applicable_for_front" class="common-radio relationButton copy_per_th" value="front" @if(is_null($setting->applicable_for) || in_array('front', $setting->applicable_for)) checked @endif>
                                                        <label for="applicable_for_front" id="applicable_for_front">Frontend/Website</label>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-15">
                                    <script src="{{asset('public/backEnd/')}}/vendors/js/print/2.1.1_jquery.min.js"></script>
                                    <div class="col-lg-6 mt-40">
                                        <img src="{{isset($setting) && $setting->image ? $setting->image : asset('/public/backEnd/img/503.png')}}" style="width: 100%; height: auto;" alt="{{isset($setting)? $setting->title:''}}" id="blahImg">


                                        <div class="row mt-40">
                                            <div class="col-lg-12 mt-15">
                                                <div class="primary_input">
                                                    <div class="primary_file_uploader">
                                                        <input class="primary_input_field" type="text" id="placeholderFileFourName" placeholder="@lang('system_settings.upload_image')" readonly="">
                                                        <button class="" type="button">
                                                            <label class="primary-btn small fix-gr-bg" for="imgInpBac">{{ __('common.browse') }}</label>
                                                            <input type="file" class="d-none" name="image" id="imgInpBac">
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($errors->has('image'))
                                            <strong class="error text-danger">{{ $errors->first('image') }}
                                                @endif
                                        </div>
                                    </div>
                                </div>
                                @if(userPermission('two_factor_auth_setting'))
                                <div class="row mt-40">
                                    <div class="col-lg-12 text-center">
                                        <button class="primary-btn fix-gr-bg">
                                            <span class="ti-check"></span>
                                            @lang('common.update')
                                        </button>
                                    </div>
                                </div>
                                @endif

                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>


</section>

<script>
    const CHUNK_SIZE = 1 * 1024 * 1024; // 2MB per chunk
    // const CHUNK_SIZE = 100 * 1024; // 100KB per chunk
    let filename = $("#dataUploadFileInput");
    let fileChunks = [];
    let fileHash;

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
                        chunk: file.slice(i * CHUNK_SIZE, (i + 1) * CHUNK_SIZE)
                        , chunkIndex: i
                        , totalChunks: totalChunks
                    });
                }
            });
        }
    });


    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        uploadChunk(0);
    });

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
            url: '{{ route("result.upload") }}'
            , type: 'POST'
            , data: formData
            , processData: false
            , contentType: false
            , success: function(response) {
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
            }
            , error: function(error) {
               console.error(`Error uploading chunk ${chunkIndex + 1}:`, error);
                toastr.error('An error occurred while uploading the file.', 'Failed')
            }
        });
    };


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

</script>


@endsection

@section('script')
<script language="JavaScript">
    $('#selectAll').click(function() {
        $('input:checkbox').prop('checked', this.checked);

    });

</script>
@endsection

@push('script')
<script>
    $(document).on('change', '#imgInpBac', function(event) {
        getFileName($(this).val(), '#placeholderFileFourName');
        imageChangeWithFile($(this)[0], '#blahImg');
    });

</script>
@endpush
