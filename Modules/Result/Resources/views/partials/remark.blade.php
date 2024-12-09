 @php
 $params = ['id'=> $id, 'exam_id'=>$exam_id];
 @endphp

 <div class="container-fluid">
     <div class="row">
         <div class="col-lg-3 col-md-4">
             <div class="mb-3">
                 <label>
                     <h5 class="mb-0">Type <span>*</span></h5>
                 </label>
                 <select class="primary_input_field form-control" id="remarkTypeSelect">
                     <option value="positive">Positive</option>
                     <option value="negative">Negative</option>
                     <option value="neutral">Neutral</option>
                 </select>
                 <span class="focus-border"></span>
             </div>
             <div class="form-check mb-3">
                 <input type="checkbox" class="form-check-input" id="remarkFlagged">
                 <label class="form-check-label" for="remarkFlagged">Show Flagged Only</label>
             </div>
             <div class="mb-3">
                 <label>
                     <h5 class="mb-0">Attributes <span>*</span></h5>
                 </label>
                 @foreach($tags as $tag)
                 <div class="form-check">
                     <input class="form-check-input" type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" id="tag_{{ $tag->id }}">
                     <label class="form-check-label" for="tag_{{ $tag->id }}">
                         {{ $tag->tag }}
                     </label>
                 </div>
                 @endforeach
                 <span class="focus-border"></span>
             </div>

             <button onclick="getComments(this)" data-path="{{ route('result.comment', $params) }}" type="button" class="primary-btn small fix-gr-bg" id="filterRemarks">Search</button>
         </div>

         <div class="col-lg-9 col-md-8">
             <input type="hidden" name="student_id" value="{{ $id }}">
             <input type="hidden" id="remarkExamTypeId" name="exam_type_id" value="{{ $exam_id }}">

             <div class="row">
                 <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                     <h5 class="mb-0">@lang('result::student.filtered_remarks')</h5>
                 </div>
             </div>

             <div class="row">
                 <div id="filteredComments" class="col-lg-12">
                     <ul class="list-unstyled">
                         @foreach($comments as $comment)
                         <li class="remark-item">
                             <button onclick="setComment(`{{ $comment->text }}`)" type="button" class="btn btn-link btn-sm">
                                 {{ $comment->text }}
                             </button>
                         </li>
                         @endforeach
                     </ul>
                 </div>
             </div>

             <div class="row mt-3">
                 <div class="col-lg-12">
                     <label class="mb-0">@lang('result::student.remark')<span></span></label>
                     <textarea id="selectedRemark" name="teacher_remark" class="form-control" rows="4">
                     {{ trim($remark->remark) }}
                     </textarea>
                     <span class="text-danger" role="alert" id="remark_error"></span>
                 
                 </div>
             </div>

             <div class="col-lg-12 text-center mt-40">
                 <div class="mt-40 d-flex justify-content-between">
                     <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>
                     <button class="primary-btn fix-gr-bg" type="submit">@lang('common.submit')</button>
                 </div>
             </div>
         </div>
     </div>
 </div>

 <style>
     .remark-item {
         border: 1px solid #ccc;
         border-radius: 8px;
         padding: 10px;
         margin: 5px 0;
         display: block;
         text-align: left;
         background-color: #f9f9f9;
     }

     .remark-item button {
         border: none;
         background: none;
         text-align: left;
         padding: 0;
         cursor: pointer;
     }

 </style>

 <script>
     function setComment(comment) {
         remark = $("#selectedRemark");
         remark.html(comment);
     }

     function getComments(button) {
         var comment = $("#filteredComments");
         var url = $(button).data("path");

         var formData = {
             student: @json($student)
             , tag_ids: $('input[name="tag_ids[]"]:checked').map(function() {
                 return Number(this.value);
             }).get()
             , type: $('#remarkTypeSelect').val()
             , is_flagged: $('#remarkFlagged').is(':checked') ? 1 : 0
         };

         console.log(formData);
         $.ajax({
             type: "POST"
             , url: url
             , data: formData
             , success: function(result) {
                 comment.html(result.content);
             }
             , error: function(xhr, status, error) {
                 console.error("Error:", error);
                 alert("An error occurred. Please try again.");
             }
         });

     }

 </script>
