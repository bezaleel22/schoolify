@extends('backEnd.master')
@section('title')
@lang('communicate.email_sms_log')
@endsection
@section('mainContent')
<section class="sms-breadcrumb mb-20">
    <div class="container-fluid">
        <div class="row justify-content-between">
            <h1>@lang('communicate.email_sms_log_list') </h1>
            <div class="bc-pages">
                <a href="{{route('dashboard')}}">@lang('common.dashboard')</a>
                <a href="#">@lang('communicate.communicate')</a>
                <a href="#">@lang('communicate.email_sms_log')</a>
            </div>
        </div>
    </div>
</section>

<section class="admin-visitor-area up_admin_visitor">
    <div class="container-fluid p-0">
        <div class="white-box">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex">
                        <div class=" no-gutters mb-2 mr-2">
                            <a href="{{route('send-email-sms-view')}}" class="primary-btn small fix-gr-bg">
                                <span class="ti-plus pr-2"></span>
                                @lang('communicate.send_email_sms')
                            </a>
                        </div>
                        <div class=" no-gutters mb-2 mr-2">
                            <a href="{{route('result.send_emails')}}" class="primary-btn small fix-gr-bg">
                                Send Emails
                            </a>
                        </div>
                        <div class=" no-gutters mb-2 mr-2">
                            <a href="{{route('result.resend_emails')}}" class="primary-btn small fix-gr-bg">
                                Resend Emails
                            </a>
                        </div>
                        <div class=" no-gutters mb-2">
                            <button id="refresh-delivery-status" class="primary-btn small fix-gr-bg">
                                <span class="ti-reload pr-2"></span>
                                Refresh Delivery Status
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <x-table>
                                <table id="table_id" class="table data-table" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th> @lang('common.sl')</th>
                                            <th> @lang('common.title')</th>
                                            <th> @lang('common.description')</th>
                                            <th> @lang('common.date')</th>
                                            <th> @lang('common.type')</th>
                                            <th> Gmail Message ID</th>
                                            <th> Delivery Status</th>
                                            @if(moduleStatusCheck('University'))
                                            <th>@lang('common.session')</th>
                                            <th>@lang('university::un.faculty')</th>
                                            <th>@lang('university::un.department')</th>
                                            <th>@lang('common.academic_year')</th>
                                            <th>@lang('university::un.semester')</th>
                                            <th>@lang('university::un.semester_label')</th>
                                            <th>@lang('common.section')</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </x-table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@include('backEnd.partials.data_table_js')
@include('backEnd.partials.server_side_datatable')
@push('script')
@if(moduleStatusCheck('University'))
<script>
    // DataTables initialisation
    $(document).ready(function() {
        $('.data-table').DataTable({
            processing: true
            , serverSide: true
            , "ajax": $.fn.dataTable.pipeline({
                url: "{{url('university/ajax-email-sms-log')}}"
                , data: {

                }
                , pages: "{{generalSetting()->ss_page_load}}" // number of pages to cache

            })
            , columns: [{
                    data: 'DT_RowIndex'
                    , name: 'id'
                }
                , {
                    data: 'title'
                    , name: 'title'
                }
                , {
                    data: 'description'
                    , name: 'description'
                }
                , {
                    data: 'date'
                    , name: 'date'
                }
                , {
                    data: 'send_via'
                    , name: 'type'
                }
                , {
                    data: 'gmail_message_id'
                    , name: 'gmail_message_id'
                    , defaultContent: '-'
                }
                , {
                    data: 'delivery_status'
                    , name: 'delivery_status'
                    , defaultContent: 'unknown'
                }
                , @if(moduleStatusCheck('University')) {
                    data: 'un_session'
                    , name: 'un_session'
                }
                , {
                    data: 'un_faculty'
                    , name: 'un_faculty'
                }
                , {
                    data: 'un_department'
                    , name: 'un_department'
                }
                , {
                    data: 'un_academic'
                    , name: 'un_academic'
                }
                , {
                    data: 'un_semester'
                    , name: 'un_semester'
                }
                , {
                    data: 'un_semester_label'
                    , name: 'un_semester_label'
                }
                , {
                    data: 'un_section'
                    , name: 'un_section'
                }
                , @endif
            ]
            , bLengthChange: false
            , bDestroy: true
            , language: {
                search: "<i class='ti-search'></i>"
                , searchPlaceholder: window.jsLang('quick_search')
                , paginate: {
                    next: "<i class='ti-arrow-right'></i>"
                    , previous: "<i class='ti-arrow-left'></i>"
                , }
            , }
            , dom: "Bfrtip"
            , buttons: [{
                    extend: "copyHtml5"
                    , text: '<i class="fa fa-files-o"></i>'
                    , title: $("#logo_title").val()
                    , titleAttr: window.jsLang('copy_table')
                    , exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    }
                , }
                , {
                    extend: "excelHtml5"
                    , text: '<i class="fa fa-file-excel-o"></i>'
                    , titleAttr: window.jsLang('export_to_excel')
                    , title: $("#logo_title").val()
                    , margin: [10, 10, 10, 0]
                    , exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    }
                , }
                , {
                    extend: "csvHtml5"
                    , text: '<i class="fa fa-file-text-o"></i>'
                    , titleAttr: window.jsLang('export_to_csv')
                    , exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    }
                , }
                , {
                    extend: "pdfHtml5"
                    , text: '<i class="fa fa-file-pdf-o"></i>'
                    , title: $("#logo_title").val()
                    , titleAttr: window.jsLang('export_to_pdf')
                    , exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    }
                    , orientation: "landscape"
                    , pageSize: "A4"
                    , margin: [0, 0, 0, 12]
                    , alignment: "center"
                    , header: true
                    , customize: function(doc) {
                        doc.content[1].margin = [100, 0, 100, 0]; //left, top, right, bottom
                        doc.content.splice(1, 0, {
                            margin: [0, 0, 0, 12]
                            , alignment: "center"
                            , image: "data:image/png;base64," + $("#logo_img").val()
                        , });
                        doc.defaultStyle = {
                            font: 'DejaVuSans'
                        }
                    }
                , }
                , {
                    extend: "print"
                    , text: '<i class="fa fa-print"></i>'
                    , titleAttr: window.jsLang('print')
                    , title: $("#logo_title").val()
                    , exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    }
                , }
                , {
                    extend: "colvis"
                    , text: '<i class="fa fa-columns"></i>'
                    , postfixButtons: ["colvisRestore"]
                , }
            , ]
            , columnDefs: [{
                visible: false
            , }, ]
            , responsive: true
        , });
    });

</script>
@else
<script>
    // DataTables initialisation
    $(document).ready(function() {
        $('.data-table').DataTable({
            processing: true
            , serverSide: true
            , "ajax": $.fn.dataTable.pipeline({
                url: "{{url('email-sms-log-ajax')}}"
                , data: {

                }
                , pages: "{{generalSetting()->ss_page_load}}" // number of pages to cache

            })
            , columns: [{
                    data: 'id'
                    , name: 'id'
                }
                , {
                    data: 'title'
                    , name: 'title'
                }
                , {
                    data: 'description'
                    , name: 'description'
                }
                , {
                    data: 'date'
                    , name: 'date'
                }
                , {
                    data: 'send_via'
                    , name: 'send_via'
                }
                , {
                    data: 'gmail_message_id'
                    , name: 'gmail_message_id'
                    , defaultContent: '-'
                }
                , {
                    data: 'delivery_status'
                    , name: 'delivery_status'
                    , defaultContent: 'unknown'
                }
            , ]
            , bLengthChange: false
            , bDestroy: true
            , language: {
                search: "<i class='ti-search'></i>"
                , searchPlaceholder: window.jsLang('quick_search')
                , paginate: {
                    next: "<i class='ti-arrow-right'></i>"
                    , previous: "<i class='ti-arrow-left'></i>"
                , }
            , }
            , dom: "Bfrtip"
            , buttons: [{
                    extend: "copyHtml5"
                    , text: '<i class="fa fa-files-o"></i>'
                    , title: $("#logo_title").val()
                    , titleAttr: window.jsLang('copy_table')
                    , exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    }
                , }
                , {
                    extend: "excelHtml5"
                    , text: '<i class="fa fa-file-excel-o"></i>'
                    , titleAttr: window.jsLang('export_to_excel')
                    , title: $("#logo_title").val()
                    , margin: [10, 10, 10, 0]
                    , exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    }
                , }
                , {
                    extend: "csvHtml5"
                    , text: '<i class="fa fa-file-text-o"></i>'
                    , titleAttr: window.jsLang('export_to_csv')
                    , exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    }
                , }
                , {
                    extend: "pdfHtml5"
                    , text: '<i class="fa fa-file-pdf-o"></i>'
                    , title: $("#logo_title").val()
                    , titleAttr: window.jsLang('export_to_pdf')
                    , exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    }
                    , orientation: "landscape"
                    , pageSize: "A4"
                    , margin: [0, 0, 0, 12]
                    , alignment: "center"
                    , header: true
                    , customize: function(doc) {
                        doc.content[1].margin = [100, 0, 100, 0]; //left, top, right, bottom
                        doc.content.splice(1, 0, {
                            margin: [0, 0, 0, 12]
                            , alignment: "center"
                            , image: "data:image/png;base64," + $("#logo_img").val()
                        , });
                        doc.defaultStyle = {
                            font: 'DejaVuSans'
                        }
                    }
                , }
                , {
                    extend: "print"
                    , text: '<i class="fa fa-print"></i>'
                    , titleAttr: window.jsLang('print')
                    , title: $("#logo_title").val()
                    , exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    }
                , }
                , {
                    extend: "colvis"
                    , text: '<i class="fa fa-columns"></i>'
                    , postfixButtons: ["colvisRestore"]
                , }
            , ]
            , columnDefs: [{
                visible: false
            , }, ]
            , responsive: true
        , });
    });

</script>

<script>
$(document).ready(function() {
    // Refresh Delivery Status functionality
    $('#refresh-delivery-status').on('click', function() {
        var button = $(this);
        var originalText = button.html();
        
        // Disable button and show loading state
        button.prop('disabled', true);
        button.html('<span class="ti-reload"></span> Checking...');
        
        // Make AJAX request to refresh delivery status
        $.ajax({
            url: '{{ route("result.refresh_delivery_status") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Reload DataTable to show updated status
                    $('.data-table').DataTable().ajax.reload();
                    
                    // Show success message
                    toastr.success(response.message || 'Delivery status updated successfully');
                } else {
                    toastr.error(response.message || 'Failed to update delivery status');
                }
            },
            error: function(xhr, status, error) {
                toastr.error('Error refreshing delivery status: ' + error);
            },
            complete: function() {
                // Re-enable button and restore original text
                button.prop('disabled', false);
                button.html(originalText);
            }
        });
    });
});
</script>
@endif
@endpush
