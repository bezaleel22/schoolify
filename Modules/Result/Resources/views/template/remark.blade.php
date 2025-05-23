<!-- resources/views/template/remark.blade.php -->
<div class="border-b grid grid-cols-5 px-1 py-1">
    <div>
        <span class="print:bg-violet-900 uppercase btn btn-xs border print:text-slate-300 rounded-full">
            Teacher's Remark
        </span>
    </div>
    <span class="col-span-4 pl-2 text-xs print:text-slate-500 leading-7">
        {{ $remark->remark ?? 'No comment' }}
    </span>
</div>

