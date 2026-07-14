<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>How was your stay?</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #334155; background: #f1f5f9; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #122D59 0%, #1B3A6B 50%, #2A5BAD 100%); padding: 32px 40px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 20px; font-weight: 700; }
        .body { padding: 36px 40px; text-align: center; }
        .greeting { font-size: 17px; font-weight: 600; color: #1e293b; margin-bottom: 12px; }
        .message { color: #475569; font-size: 14px; margin-bottom: 24px; }
        .stars { font-size: 32px; letter-spacing: 6px; margin-bottom: 24px; }
        .btn { display: inline-block; background: #C9A227; color: #ffffff !important; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 700; font-size: 15px; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 40px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>{{ $survey->hotel->name }}</h1>
    </div>

    <div class="body">
        <p class="greeting">Hi {{ $survey->user->name }},</p>
        <p class="message">
            Thanks for staying with us! We'd love to hear about your experience —
            it only takes a minute and helps us improve.
        </p>
        <div class="stars">⭐⭐⭐⭐⭐</div>
        <a href="{{ route('survey.show', $survey->token) }}" class="btn">Share Your Feedback</a>
    </div>

    <div class="footer">
        <p>{{ config('app.name') }} &middot; on behalf of {{ $survey->hotel->name }}</p>
    </div>
</div>
</body>
</html>
