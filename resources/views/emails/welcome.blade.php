<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vitajte v aplikácii</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial, Helvetica, sans-serif; color:#111827;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px;">
    <tr>
        <td align="center">

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border-radius:6px; overflow:hidden; border:1px solid #e5e7eb;">

                <!-- Header -->
                <tr>
                    <td style="padding:32px 32px 16px; text-align:center;">
                        <h1 style="margin:0; font-size:22px; font-weight:600;">
                            Vitajte v {{ config('app.name') }}
                        </h1>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:0 32px 24px;">

                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#374151;">
                            Dobrý deň {{ $user->first_name }},
                        </p>

                        <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#374151;">
                            váš účet bol úspešne vytvorený. Môžete sa prihlásiť a začať používať aplikáciu.
                        </p>

                        <!-- CTA -->
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;">
                            <tr>
                                <td align="center">
                                    <a href="#"
                                       style="display:inline-block; padding:14px 24px; font-size:14px; font-weight:600; color:#ffffff; background-color:#111827; text-decoration:none; border-radius:4px;">
                                        Prejsť do aplikácie
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <!-- Fallback link -->
                        <p style="margin:0 0 20px; font-size:13px; line-height:1.6; color:#6b7280; text-align:center;">
                            Alebo skopírujte tento odkaz do prehliadača:<br>
                            <a href="#" style="color:#4f46e5; word-break:break-all;">#</a>
                        </p>

                        <!-- Account info -->
                        <p style="margin:0 0 6px; font-size:13px; color:#6b7280;">
                            Registrovaný email:
                        </p>

                        <p style="margin:0 0 20px; font-size:14px; font-weight:500;">
                            {{ $user->email }}
                        </p>

                        <p style="margin:0 0 12px; font-size:13px; color:#6b7280;">
                            Ak ste sa neregistrovali Vy, tento email môžete ignorovať.
                        </p>

                        <p style="margin:0; font-size:13px; color:#6b7280;">
                            — Tím {{ config('app.name') }}
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:16px 24px; background:#f9fafb; border-top:1px solid #e5e7eb; text-align:center;">
                        <p style="margin:0 0 6px; font-size:12px; color:#9ca3af;">
                            Tento email bol odoslaný automaticky.
                        </p>
                        <p style="margin:0; font-size:12px; color:#9ca3af;">
                            Potrebujete pomoc? Kontaktujte nás na <a href="mailto:support@example.com" style="color:#6b7280;">support@example.com</a>
                        </p>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
