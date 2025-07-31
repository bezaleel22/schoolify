<!-- resources/views/index.blade.php -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $student->full_name }} - Results</title>

    <style>
        /* Add explicit font fallbacks for Prince PDF generation */
        body,
        html {
            font-family: "DejaVu Sans", "Liberation Sans", Arial, sans-serif !important;
        }

        .bg-custom {
            background-color: #ffffff;
            /* Position watermark for better PDF output */
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='152' height='152' viewBox='0 0 152 152'%3E%3Cg fill-rule='evenodd'%3E%3Cg id='temple' fill='%23ff9000' fill-opacity='0.03'%3E%3Cpath d='M152 150v2H0v-2h28v-8H8v-20H0v-2h8V80h42v20h20v42H30v8h90v-8H80v-42h20V80h42v40h8V30h-8v40h-42V50H80V8h40V0h2v8h20v20h8V0h2v150zm-2 0v-28h-8v20h-20v8h28zM82 30v18h18V30H82zm20 18h20v20h18V30h-20V10H82v18h20v20zm0 2v18h18V50h-18zm20-22h18V10h-18v18zm-54 92v-18H50v18h18zm-20-18H28V82H10v38h20v20h38v-18H48v-20zm0-2V82H30v18h18zm-20 22H10v18h18v-18zm54 0v18h38v-20h20V82h-18v20h-20v20H82zm18-20H82v18h18v-18zm2-2h18V82h-18v18zm20 40v-18h18v18h-18zM30 0h-2v8H8v20H0v2h8v40h42V50h20V8H30V0zm20 48h18V30H50v18zm18-20H48v20H28v20H10V30h20V10h38v18zM30 50h18v18H30V50zm-2-40H10v18h18V10z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            background-position: center center;
            background-repeat: repeat;
            background-attachment: fixed;
        }

        /* Ensure Prince-specific styling for better PDF output */
        @media print {
            .bg-custom {
                background-attachment: scroll;
            }
        }

        /* Prince-specific CSS for print optimization */
        @media print {
            .bg-custom {
                background-attachment: scroll;
            }
        }

    </style>

    @include('result::template.css')
</head>

<body class="w-full h-full bg-custom">
    <header class="lg:flex lg:justify-between mb-8 w-full font-normal print:flex print:justify-between print:space-x-3">
        @include('result::template.header', ['school' => $school])
    </header>
    <div class="flex flex-row">
        @include('result::template.student_info', ['student' => $student])
    </div>
    @include('result::template.record', ['records' => $records])
    @include('result::template.score', ['score' => $score])

    @if ($student->type == 'GRADERS')
    @include('result::template.rating', ['ratings' => $ratings])
    @endif

    @include('result::template.remark', ['remark' => $remark])
</body>

</html>
