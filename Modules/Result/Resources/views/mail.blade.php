<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Notification</title>

    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap');

        p,
        h1,
        h2,
        h3 {
            font-family: 'Poppins';
        }

    </style>
</head>
<body style="margin: 0; padding: 0; width: 100%; background-color: #f9f9f9; font-family: 'Poppins', sans-serif; -webkit-text-size-adjust: 100%;">
    <div style="max-width: 600px; margin: auto; background-color: #ffffff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; color: #333;">
        <!-- Hero Section -->
        <table role="presentation" cellspacing="0" cellpadding="0" style="width: 100%; background-color: #f7931e; text-align: center; color: white; padding: 20px 0; border-radius: 8px;">
            <tr>
                <td align="center">
                    <img src="{{ $student->logo }}" alt="{{ $student->school_name }}" style="max-width: 250px; height: auto;">
                </td>
            </tr>
            <tr>

                <td align="center">
                    {{-- <img src="{{ base64_encode(file_get_contents(public_path('uploads/settings/illustration.svg'))) }}" alt="Hero" style="max-width: 50%; height: auto;"> --}}
                    <div style="font-size: 1.2em; font-weight: 700; color: white; text-align: center;">
                        <p>{{ $student->title }}</p>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Content Section -->
        <table role="presentation" cellspacing="0" cellpadding="0" style="width: 100%; padding: 20px;">
            <tr>
                <td style="font-size: 1em; line-height: 1.6;">
                    <p>Dear <strong>{{ $student->receiver_name }}</strong>,</p>
                    @if(!empty($student->links))
                    <p>
                        We are pleased to share <strong>{{ $student->full_name }}</strong>'s results for
                        <strong>{{ $student->session }} Academic Session</strong>.
                        You can access the results at the following links:
                    </p>
                    <ul>
                        @foreach($student->links as $link)
                        <li>
                            <a href="{{ $link['url'] }}" target="_blank" style="color: #f7931e; text-decoration: none;">
                                <u>{{ $link['label'] }}</u>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p>
                        We are pleased to share <strong>{{ $student->full_name }}</strong>'s results for <strong>{{ $student->term }}</strong>.
                        Please find the PDF attached to this email with a detailed breakdown of {{ strtok($student->full_name, ' ') }}'s performance.
                    </p>
                    @endif

                    <p>
                        Should you have any questions, feel free to
                        <a href="https://wa.me/{{ $student->support }}" target="_blank" style="color: #f7931e; text-decoration: none;">
                            Contact us on WhatsApp
                        </a>.
                        Thank you for your continued support!
                    </p>
                    <p>Best regards,</p>
                    <p>
                        <strong>{{ $student->principal }}</strong><br>
                        Head Teacher, <strong>{{ $student->school_name }}</strong><br>
                        <strong>Contact:</strong> <a href="tel:{{ $student->contact }}" style="color: #f7931e; text-decoration: none;">{{ $student->contact }}</a><br>
                    </p>
                </td>
            </tr>
        </table>

        <!-- Footer -->
        <table role="presentation" cellspacing="0" cellpadding="0" style="width: 100%; text-align: center; margin-top: 20px; font-size: 0.9em; color: #777;">
            <tr>
                <td>
                    <p>&copy; {{ date('Y') }} <strong>{{ $student->school_name }}</strong>. All rights reserved.</p>


                </td>
            </tr>
        </table>

    </div>

</body>
</html>
