<!-- resources/views/template/record.blade.php -->
<script></script>

<table class="min-w-max w-full table-auto mb-4 overflow-hidden" aria-label="Student Records">
    <caption class="sr-only">Student Records for {{ $student->full_name }}</caption>
    <thead>
        <tr class="print:bg-violet-900 bg-neutral text-neutral-content tezprimary-content uppercase print:text-slate-300 text-xs leading-normal">
            @if ($student->type == 'GRADERS')
            <th class="py-1 px-6 text-left">Subjects</th>
            <th class="py-1 px-6 text-left">MTA1</th>
            <th class="py-1 px-6 text-left">MTA2</th>
            <th class="py-1 px-6 text-left">Oral Report</th>
            <th class="py-1 px-6 text-left">Exam</th>
            <th class="py-1 px-6 text-left">Score</th>
            <th class="py-1 px-6 text-left">Grade</th>
            @else
            <th class="py-1 px-6 text-left">Learning Areas</th>
            <th class="py-1 px-6 text-left">Objectives</th>
            <th class="py-1 px-6 text-left">Score</th>
            <th class="py-1 px-6 text-left">Outcome</th>
            @endif
        </tr>
    </thead>

    <tbody class="print:text-gray-600 text-sm font-light">
        @foreach ($records as $record)
        <tr class="border-b border-gray-200 hover:bg-base-300">
            <td class="py-3 px-6 text-left max-w-xs whitespace-normal print:w-24">{{ $record->subject }}</td>

            @if ($student->type == 'GRADERS')
            @foreach ($record->marks as $mark)
            <td class="py-3 px-6 text-center whitespace-nowrap">{{ $mark }}</td>
            @endforeach

            <td class="py-3 px-6 text-center whitespace-nowrap">{{ $record->total_score }}</td>
            <td class="py-3 px-6 text-center">
                <span class="{{ $record->color }} text-violet-600 py-1 px-3 rounded-full text-xs">
                    {{ $record->grade }}
                </span>
            </td>
            @else
            <td class="py-3 px-6 max-w-xs">
                <ul class="list-disc">
                    @foreach ($record->objectives as $objective)
                    <li>{{ $objective }}</li>
                    @endforeach
                </ul>
            </td>
            <td class="py-3 px-6 text-center">
                <span class="{{ $record->color }} text-violet-600 py-1 px-3 rounded-full text-xs">
                    {{ $record->grade }}
                </span>
            </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
