<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Notification</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0; width: 100%; background-color: #f9f9f9; font-family: 'Poppins', sans-serif; -webkit-text-size-adjust: 100%;">

    <div style="max-width: 600px; margin: auto; background-color: #ffffff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; color: #333;">

        <!-- Hero Section with Inline Styles -->
        <table role="presentation" cellspacing="0" cellpadding="0" style="width: 100%; background-color: #f7931e; text-align: center; color: white; padding: 20px 0; border-radius: 8px;">
            <tr>
                <td align="center">
                    <img src="{{ asset(generalSetting()->logo) }}" alt="{{ generalSetting()->school_name }}" style="max-width: 250px; height: auto;">
                </td>
            </tr>
            <tr>
                <td align="center" style="position: relative;">
                    <img src="{{ asset('public/images/illustration.svg') }}" alt="Academic Illustration" style="max-width: 50%; height: auto;">
                    <div style="font-size: 1.2em; font-weight: 700; color: white; text-align: center;">
                        <p>{{ $student->title }}</p>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Content Section with Inline Styles -->
        <table role="presentation" cellspacing="0" cellpadding="0" style="width: 100%; padding: 20px;">
            <tr>
                <td style="font-size: 1em; line-height: 1.6;">
                    <p>Dear <strong>{{ $student->parent_name }}</strong>,</p>
                    <p>
                        We are pleased to share <strong>{{ $student->full_name }}</strong>'s results for <strong>{{ $student->term }}</strong>. Please find the PDF attached to this email with a detailed breakdown of
                        {{ $student->gender == 'Male' ? 'his' : 'her' }} performance.
                    </p>
                    <p>
                        Should you have any questions, feel free to
                        <a href="https://wa.me/{{ $student->support }}" target="_blank" style="color: #f7931e; text-decoration: none;">Contact us on WhatsApp</a>.
                        Thank you for your continued support!
                    </p>
                    <p>Best regards,</p>
                    <p>
                        <strong>{{ $student->admin }}</strong><br>
                        Admin, <strong>{{ generalSetting()->school_name }}</strong><br>
                    </p>
                </td>
            </tr>
        </table>

        <!-- Footer with Inline Styles -->
        <table role="presentation" cellspacing="0" cellpadding="0" style="width: 100%; text-align: center; margin-top: 20px; font-size: 0.9em; color: #777;">
            <tr>
                <td>
                    <p>&copy; {{ date('Y') }} <strong>{{ generalSetting()->school_name }}</strong>. All rights reserved.</p>
                </td>
            </tr>
        </table>

    </div>

</body>
</html>
