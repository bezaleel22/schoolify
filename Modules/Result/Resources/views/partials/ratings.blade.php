 @php
 $params = ['id'=> $student->id, 'exam_id'=>$exam_id];
 @endphp


 <div class="container-fluid">
     <input type="hidden" name="student_id" id="studentId" value="{{ $student->id }}">
     <input type="hidden" id="examTypeId" name="exam_type_id" value="{{ $exam_id }}">

     <div class="row">
         <div class="col-lg-12">
             <!-- Attendance Data -->
             <div class="row mt-10">
                 <div class="col-lg-4">
                     <label>@lang('result::student.opened') <span>*</span></label>
                     <input id="opened" value="{{ $student->opened }}" class="primary_input_field form-control" type="number" name="opened">
                 </div>
                 <div class="col-lg-4">
                     <label>@lang('result::student.present') <span>*</span></label>
                     <input id="present" value="{{ $student->present }}" class="primary_input_field form-control" type="number" name="present">
                 </div>
                 <div class="col-lg-4">
                     <label>@lang('result::student.absent') <span>*</span></label>
                     <input id="absent" value="{{ $student->absent }}" class="primary_input_field form-control" type="number" name="absent">
                 </div>
             </div>

             <!-- Attribute and Rating Selection -->
             <div class="row mt-25">
                 <div class="col-lg-6">
                     <div class="input-effect">
                         <label>@lang('result::student.select_attribute') <span>*</span></label>
                         <select class="primary_input_field form-control" id="attributeSelect">
                             <option value="">Select Attribute</option>
                             @foreach($attributes as $attribute)
                             <option value="{{ $attribute }}">{{ $attribute }}</option>
                             @endforeach
                         </select>
                         <span class="focus-border"></span>
                         <span class="text-danger" role="alert" id="attribute_error"></span>
                     </div>
                 </div>
                 <div class="col-lg-6">
                     <div class="input-effect">
                         <label>@lang('result::student.select_rating') <span>*</span></label>
                         <select class="primary_input_field form-control" id="ratingSelect">
                             <option value="">Select Rating</option>
                             <option value="5">5-Excellent</option>
                             <option value="4">4-Good</option>
                             <option value="3">3-Average</option>
                             <option value="2">2-Below Average</option>
                             <option value="1">1-Poor</option>
                         </select>
                         <span class="focus-border"></span>
                         <span class="text-danger" role="alert" id="rating_error"></span>
                     </div>
                 </div>
             </div>

             <div class="col-lg-12 mt-3">
                 <button id="addRatingButton" type="button" class="primary-btn fix-gr-bg">@lang('common.add')</button>
             </div>

             <!-- Display Existing Ratings -->
             <div class="col-lg-12 mt-4">
                 <h5>@lang('result::student.added_ratings')</h5>
                 <div id="addedRatingsContainer" class="d-flex flex-wrap align-items-center">
                     @foreach($ratings as $rating)
                     <div class="tag d-inline-flex align-items-center m-2 p-2 bg-light border rounded">
                         <span class="mr-2"><strong>{{ $rating->attribute }}</strong>: {{ $rating->rate }}</span>
                         <button type="button" class="btn btn-sm text-danger remove-rating" data-attribute="{{ $rating->attribute }}" onclick="removeRating(this)">&times;</button>
                         <input type="hidden" name="ratings[{{ $rating->attribute }}][attribute]" value="{{ $rating->attribute }}">
                         <input type="hidden" name="ratings[{{ $rating->attribute }}][rate]" value="{{ $rating->rate }}">
                     </div>
                     @endforeach
                 </div>
             </div>

             <div class="col-lg-12 text-center mt-40">
                 <div class="mt-40 d-flex justify-content-between">
                     <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>
                     <button class="primary-btn fix-gr-bg submit" type="submit">@lang('common.save')</button>
                 </div>
             </div>
         </div>
     </div>
 </div>

 <script>
     $(document).ready(function() {
         $('#addRatingButton').click(function() {
             const attributeSelect = document.getElementById('attributeSelect');
             const ratingSelect = document.getElementById('ratingSelect');
             const attribute = attributeSelect.value;
             const rating = ratingSelect.value;

             if (attribute && rating) {
                 // Create inline tag for added rating
                 const tag = document.createElement('div');
                 tag.className = 'tag d-inline-flex align-items-center m-2 p-2 bg-light border rounded';
                 tag.innerHTML = `
           <span class="mr-2"><strong>${attribute}</strong>: ${rating}</span>
           <button type="button" class="btn btn-sm text-danger remove-rating" data-attribute="${attribute}">&times;</button>
           <input type="hidden" name="ratings[${attribute}][attribute]" value="${attribute}">
           <input type="hidden" name="ratings[${attribute}][rate]" value="${rating}">
           `;
                 document.getElementById('addedRatingsContainer').appendChild(tag);

                 // Disable the selected attribute from the dropdown
                 const selectedOption = attributeSelect.querySelector(`option[value="${attribute}"]`);
                 selectedOption.disabled = true;

                 // Clear selections
                 attributeSelect.value = '';
                 ratingSelect.value = '';

             } else {
                 alert('Please select both attribute and rating.');
             }

         });
     });

     document.getElementById('addedRatingsContainer').addEventListener('click', function(e) {
         if (e.target.classList.contains('remove-rating')) {
             const attribute = e.target.getAttribute('data-attribute');
             const tag = e.target.parentElement;

             // Remove tag
             tag.remove();

             // Enable the removed attribute back in the dropdown
             const optionToEnable = document.querySelector(`#attributeSelect option[value="${attribute}"]`);
             if (optionToEnable) {
                 optionToEnable.disabled = false;
             }

             // Remove hidden inputs for the removed rating
             document.querySelectorAll(`[name="ratings[${attribute}][attribute]"], [name="ratings[${attribute}][rate]"]`).forEach(input => input.remove());
         }
     });

 </script>
