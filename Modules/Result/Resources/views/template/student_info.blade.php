<!-- resources/views/template/student_info.blade.php -->
<div class="flex w-full p-2">
    <div class="w-full grid grid-rows-3 grid-flow-col">
        <div class="border-b grid grid-cols-12 py-1">
            <div class="col-span-5">
                <span class="print:bg-violet-900 uppercase btn btn-xs border print:text-slate-300 rounded-full">
                    Name
                </span>
                <span class="py-2 pl-2 text-xs print:text-slate-500 uppercase">{{ $student->full_name }}</span>
            </div>

            <div class="col-span-7">
                <span class="print:bg-violet-900 uppercase btn btn-xs border print:text-slate-300 rounded-full">
                    Term
                </span>
                <span class="py-2 pl-2 text-xs print:text-slate-500 uppercase">{{ $student->term }}</span>
            </div>
        </div>

        <div class="border-b grid grid-cols-3 py-1">
            <div>
                <span class="print:bg-violet-900 uppercase btn btn-xs border print:text-slate-300 rounded-full">
                    Class
                </span>
                <span class="py-2 pl-2 text-xs print:text-slate-500 uppercase">
                    {{ $student->class_name }} ({{ $student->section_name }})
                </span>
            </div>

            <div>
                <span class="print:bg-violet-900 uppercase btn btn-xs border print:text-slate-300 rounded-full">
                    Admission No
                </span>
                <span class="py-2 pl-2 text-xs print:text-slate-500 uppercase">{{ $student->admin_no }}</span>
            </div>

            <div>
                <span class="print:bg-violet-900 uppercase btn btn-xs border print:text-slate-300 rounded-full">
                    Academic Year
                </span>
                <span class="py-2 pl-2 text-xs print:text-slate-500 uppercase">{{ $student->session_year }}</span>
            </div>
        </div>

        <div class="border-b grid grid-cols-3 py-1">
            <div>
                <span class="print:bg-violet-900 uppercase btn btn-xs border print:text-slate-300 rounded-full">
                    Days Opened
                </span>
                <span class="py-2 pl-2 text-xs print:text-slate-500 uppercase">{{ $student->opened }}</span>
            </div>

            <div>
                <span class="print:bg-violet-900 uppercase btn btn-xs border print:text-slate-300 rounded-full">
                    Days Absent
                </span>
                <span class="py-2 pl-2 text-xs print:text-slate-500 uppercase">{{ $student->absent }}</span>
            </div>

            <div>
                <span class="print:bg-violet-900 uppercase btn btn-xs border print:text-slate-300 rounded-full">
                    Days Present
                </span>
                <span class="py-2 pl-2 text-xs print:text-slate-500 uppercase">{{ $student->present }}</span>
            </div>
        </div>
    </div>

    <div class="avatar flex flex-col justify-center items-center ml-3">
        <div class="w-24 rounded-full ring ring-neutral print:ring-violet-900 ring-offset-2 mb-4">
            <img src="https://llacademy.ng/{{ $student->student_photo }}" alt="Student Photo" />
        </div>
    </div>
</div>

