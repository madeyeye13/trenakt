<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0; padding:0; background-color:#F7F4EE; font-family: -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F7F4EE; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius: 8px; border: 1px solid #e5e5e0;">
                    <tr>
                        <td style="padding: 32px 40px 0 40px;">
                            <span style="font-size: 20px; font-weight: 700; color: #14130F;">
                                Tren<span style="color:#FF5A1F;">a</span>kt
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px 40px 8px 40px;">
                            <h1 style="font-size: 18px; color: #14130F; margin: 0 0 12px 0;">Verify your email</h1>
                            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 24px 0;">
                                Enter this code to confirm your email address and finish setting up your Trenakt account.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0 40px;">
                            <div style="background-color: #F7F4EE; border-radius: 6px; padding: 20px; text-align: center;">
                                <span style="font-size: 32px; font-weight: 700; letter-spacing: 8px; color: #0F8A4F;">
                                    {{ $code }}
                                </span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px 40px 32px 40px;">
                            <p style="font-size: 13px; color: #888; line-height: 1.6; margin: 0;">
                                This code expires in 10 minutes. If you didn't request this, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>
                <p style="font-size: 12px; color: #999; margin-top: 24px;">Trenakt &middot; {{ now()->year }}</p>
            </td>
        </tr>
    </table>
</body>
</html>