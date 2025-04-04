<!-- resources/views/template/score.blade.php -->
<script lang="ts"></script>

<table class="min-w-max w-full table-fixed mb-5 rounded print:break-inside-avoid">
    <tbody class="align-baseline">
        <tr class="border-b">
            <td class="print:bg-violet-900 whitespace-nowrap capitalize btn btn-xs border print:text-slate-300 cursor-default rounded-full">
                <span> Total Score </span>
            </td>
            <td class="py-2 text-xs print:text-slate-500">{{ $score->total }}</td>
            <td class="print:bg-violet-900 whitespace-nowrap capitalize btn btn-xs border print:text-slate-300 cursor-default rounded-full">
                <span> Average Score </span>
            </td>
            <td class="py-2 text-xs print:text-slate-500 px-5">{{ $score->average }}</td>
            <td class="print:bg-violet-900 whitespace-nowrap capitalize btn btn-xs border print:text-slate-300 cursor-default rounded-full">
                <span> High Class Average </span>
            </td>
            <td class="py-2 text-xs print:text-slate-500 px-10">{{ $score->max_average->value ?? 'N/A' }}</td>
            <td class="print:bg-violet-900 whitespace-nowrap capitalize btn btn-xs border print:text-slate-300 cursor-default rounded-full">
                <span> Low Class Average </span>
            </td>
            <td class="py-2 text-xs print:text-slate-500 px-10">{{ $score->min_average->value ?? 'N/A' }}</td>

        </tr>
        <tr class="border-b">
            <td class="print:bg-violet-900 whitespace-nowrap capitalize btn btn-xs border print:text-slate-300 cursor-default rounded-full">
                <span> Grading System </span>
            </td>
            <td colspan="4" class="py-2 px-5 text-xs print:text-slate-500 uppercase">
                @if ($student->type !== 'GRADERS')
                Emerging(0-80) Expected(81-90) Exceeding(91-100)
                @else
                A(94-100) B(86-93) C(77-85) D(70-76) E(0-69)
                @endif
            </td>
        </tr>
    </tbody>
</table>

