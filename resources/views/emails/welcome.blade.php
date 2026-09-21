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
                        <td style="padding: 24px 40px 0 40px;">
                            <h1 style="font-size: 18px; color: #14130F; margin: 0 0 12px 0;">Welcome, {{ $user->name }}.</h1>
                            <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 16px 0;">
                                Your email is verified and your Trenakt account is ready.
                            </p>
                            @if ($user->hasRole('participant') && $user->hasRole('business'))
                                <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 16px 0;">
                                    You're set up to both earn from tasks and promote your own campaigns, whenever you're ready for either.
                                </p>
                            @elseif ($user->hasRole('business'))
                                <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 16px 0;">
                                    You're set up to create campaigns and reach real people. You can also start earning from tasks anytime from your account.
                                </p>
                            @else
                                <p style="font-size: 14px; color: #555; line-height: 1.6; margin: 0 0 16px 0;">
                                    You're set up to discover tasks and start earning. You can also promote your own campaigns anytime from your account.
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 40px 32px 40px;">
                            <a href="{{ route('dashboard') }}" style="display:inline-block; background-color:#0F8A4F; color:#ffffff; text-decoration:none; padding: 12px 24px; border-radius: 6px; font-size: 14px; font-weight: 600;">
                                Go to your dashboard
                            </a>
                        </td>
                    </tr>
                </table>
                <p style="font-size: 12px; color: #999; margin-top: 24px;">Trenakt &middot; {{ now()->year }}</p>
            </td>
        </tr>
    </table>
</body>
</html>