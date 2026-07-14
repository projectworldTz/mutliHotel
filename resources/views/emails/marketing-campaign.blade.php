<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $campaign->subject }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #334155; background: #f1f5f9; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #122D59 0%, #1B3A6B 50%, #2A5BAD 100%); padding: 32px 40px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 20px; font-weight: 700; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 17px; font-weight: 600; color: #1e293b; margin-bottom: 16px; }
        .message { color: #475569; font-size: 14px; white-space: pre-line; }
        .actions { text-align: center; margin: 28px 0; }
        .btn { display: inline-block; background: #C9A227; color: #ffffff !important; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 700; font-size: 15px; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 40px; text-align: center; font-size: 12px; color: #94a3b8; }
        .footer a { color: #1B3A6B; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>{{ $campaign->hotel->name }}</h1>
    </div>

    <div class="body">
        <p class="greeting">Hi {{ $recipient->name }},</p>
        <p class="message">{{ $campaign->body }}</p>

        <div class="actions">
            <a href="{{ route('hotels.show', $campaign->hotel) }}" class="btn">Visit {{ $campaign->hotel->name }}</a>
        </div>
    </div>

    <div class="footer">
        <p style="margin-bottom: 8px;">
            <strong style="color: #1B3A6B;">{{ $campaign->hotel->name }}</strong>
            &middot; sent via {{ config('app.name') }}
        </p>
        <p>
            <a href="{{ route('account.profile') }}">Manage email preferences</a>
        </p>
        <p style="margin-top: 12px; color: #cbd5e1;">
            You received this because you've booked or stayed with {{ $campaign->hotel->name }} and opted in to promotional email.
        </p>
    </div>
</div>
</body>
</html>
