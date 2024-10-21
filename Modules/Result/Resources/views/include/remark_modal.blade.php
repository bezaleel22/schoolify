<div class="modal fade admin-query" id="add_remark_modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('result::student.add_remark')</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="input-effect mb-3">
                                <label>Select Tag <span>*</span></label>
                                <select class="primary_input_field form-control" id="remarkTagSelect">
                                    <option value="">Select Tag</option>
                                    @foreach($tags as $tag)
                                    <option value="{{ $tag->tag }}">{{ $tag->tag }}</option>
                                    @endforeach
                                </select>
                                <span class="focus-border"></span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="input-effect mb-3">
                                <label>Type <span>*</span></label>
                                <select class="primary_input_field form-control" id="remarkTypeSelect">
                                    <option value="">Select Type</option>
                                    <option value="positive">Positive</option>
                                    <option value="negative">Negative</option>
                                </select>
                                <span class="focus-border"></span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-0">
                        <button type="button" class="primary-btn small fix-gr-bg" id="filterRemarksButton">Filter Remarks</button>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="remarkFlagged">
                            <label class="form-check-label" for="remarkFlagged">Show Flagged Only</label>
                        </div>
                    </div>

                    <form action="{{ route('result.remark') }}" method="POST">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $student_detail->id }}">
                        <input type="hidden" id="remarkExamTypeId" name="exam_type_id">
                        <div class="row">
                            <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">@lang('result::student.filtered_remarks')</h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <ul id="filteredRemarksContainer" class="list-unstyled">
                                    <!-- Filtered remarks will be displayed here -->
                                </ul>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-lg-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="mb-0">@lang('result::student.remark')<span></span></label>
                                    <button type="button" class="primary-btn btn-sm tr-bg text-uppercase px-1" id="shuffleRemarksButton" title="Shuffle Remarks">
                                        <span class="pl ti-control-shuffle"></span> Shuffle
                                    </button>
                                </div>

                                <textarea id="selectedRemark" name="teacher_remark" class="form-control" rows="4" readonly></textarea>
                                <span class="text-danger" role="alert" id="remark_error"></span>
                            </div>
                        </div>

                        <div class="col-lg-12 text-center mt-40">
                            <div class="mt-40 d-flex justify-content-between">
                                <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>
                                <button class="primary-btn fix-gr-bg submit" type="submit">@lang('common.submit')</button>
                            </div>
                        </div>
                        {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom styles for filtered remarks */
    #filteredRemarksContainer li {
        border: 1px solid #007bff;
        /* Border color */
        border-radius: 8px;
        /* Rounded corners */
        padding: 10px;
        /* Padding */
        margin-bottom: 10px;
        /* Spacing between items */
        cursor: pointer;
        /* Pointer cursor on hover */
        transition: background-color 0.3s;
        /* Smooth background color change */
    }

    #filteredRemarksContainer li:hover {
        background-color: #e7f0ff;
        /* Light blue background on hover */
    }

</style>

<script>
    const remarks = [{
            id: "13195"
            , text: "The students have shown great improvement throughout the semester with their English speaking abilities and I am confident that the growth will continue."
            , flagged: false
            , type: "positive"
            , tags: ["speaking"]
        }
        , {
            id: "13196"
            , text: "Outstanding participation in class activities. Keep it up!"
            , flagged: false
            , type: "positive"
            , tags: ["speaking"]

        }
        , {
            id: "13197"
            , text: "Great teamwork demonstrated during group projects. It's encouraging to see everyone collaborating effectively."
            , flagged: false
            , type: "positive"
            , tags: ["teamwork"]
        }
        , {
            id: "13198"
            , text: "The student has shown excellent improvement in mathematics. Their hard work is truly paying off."
            , flagged: false
            , type: "positive"
            , tags: ["speaking"]

        }
        , {
            id: "13199"
            , text: "Consistent effort in homework assignments has led to a deeper understanding of the material."
            , flagged: false
            , type: "positive"
            , tags: ["speaking"]

        }
        , {
            id: "13200"
            , text: "Struggles with time management have affected overall performance. A plan is needed to improve this area."
            , flagged: true
            , type: "negative"
            , tags: ["time management"]
        }
        , {
            id: "13201"
            , text: "There have been issues with respect towards peers. Encouraging positive interactions is essential."
            , flagged: true
            , type: "negative"
            , tags: ["behavior"]
        }
        , {
            id: "13202"
            , text: "Frequent absences have impacted learning. Regular attendance is crucial for success."
            , flagged: true
            , type: "negative"
            , tags: ["attendance"]
        }
        , {
            id: "13203"
            , text: "The student demonstrates a lack of effort in class. They need to be encouraged to engage more actively."
            , flagged: false
            , type: "negative"
            , tags: ["engagement"]
        }
        , {
            id: "13204"
            , text: "Excellent critical thinking skills shown in recent projects. Keep pushing those boundaries!"
            , flagged: false
            , type: "positive"
            , tags: ["critical thinking"]
        }
        , {
            id: "13205"
            , text: "The creativity in their artwork is impressive and reflects a unique style."
            , flagged: false
            , type: "positive"
            , tags: ["speaking"]
        }
        , {
            id: "13206"
            , text: "The student needs to improve their writing skills to express ideas more clearly."
            , flagged: true
            , type: "negative"
            , tags: ["writing"]
        }
        , {
            id: "13207"
            , text: "Participation in class discussions has improved significantly. It's great to see more confidence."
            , flagged: false
            , type: "positive"
            , tags: ["speaking"]
        }
        , {
            id: "13208"
            , text: "The student has a great attitude towards learning and often helps others in class."
            , flagged: false
            , type: "positive"
            , tags: ["speaking"]
        }
        , {
            id: "13209"
            , text: "There are concerns regarding homework completion. More consistency is required."
            , flagged: true
            , type: "negative"
            , tags: ["homework"]
        }
        , {
            id: "13210"
            , text: "The student's enthusiasm for science is commendable. They ask great questions during lessons."
            , flagged: false
            , type: "positive"
            , tags: ["speaking"]

        }
    ];



    // Function to display filtered remarks
    function displayFilteredRemarks(filterTags) {
        const filteredRemarksContainer = document.getElementById('filteredRemarksContainer');
        filteredRemarksContainer.innerHTML = ''; // Clear previous remarks
        const filteredRemarks = remarks.filter(remark => {
            const isTagMatch = filterTags.some(tag => remark.tags.includes(tag));
            return isTagMatch && !remark.flagged; // Apply filter conditions
        });

        // Shuffle filtered remarks
        const shuffledRemarks = filteredRemarks.sort(() => Math.random() - 0.5).slice(0, 5); // Shuffle and limit to 5
        shuffledRemarks.forEach(remark => {
            const remarkItem = document.createElement('li');
            remarkItem.className = 'remark-item';
            remarkItem.textContent = remark.text;

            // Adding click event listener to each remark item
            remarkItem.addEventListener('click', function() {
                document.getElementById('selectedRemark').value = remark.text; // Populate textarea
            });

            filteredRemarksContainer.appendChild(remarkItem);
        });

        if (shuffledRemarks.length === 0) {
            const noRemarksItem = document.createElement('li');
            noRemarksItem.className = 'text-danger';
            noRemarksItem.textContent = 'No remarks available for the selected tag.';
            filteredRemarksContainer.appendChild(noRemarksItem);
        }
    }

    // Filter button event listener
    document.getElementById('filterRemarksButton').addEventListener('click', function() {
        const selectedTag = document.getElementById('remarkTagSelect').value; // Get selected tag
        if (selectedTag) {
            displayFilteredRemarks([selectedTag]); // Filter remarks with the selected tag
        } else {
            alert('Please select a tag to filter remarks.'); // Alert if no tag is selected
        }
    });

    // Shuffle button event listener
    document.getElementById('shuffleRemarksButton').addEventListener('click', function() {
        const selectedTag = document.getElementById('remarkTagSelect').value; // Get selected tag
        if (selectedTag) {
            displayFilteredRemarks([selectedTag]); // Shuffle and display filtered remarks
        } else {
            alert('Please select a tag to shuffle remarks.'); // Alert if no tag is selected
        }
    });

</script>
