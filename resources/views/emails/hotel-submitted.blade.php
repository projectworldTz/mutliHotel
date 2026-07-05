<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Hotel Pending Approval</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #334155; background: #f1f5f9; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #122D59 0%, #1B3A6B 50%, #2A5BAD 100%); padding: 40px 40px 32px; text-align: center; }
        .header-icon { width: 56px; height: 56px; background: rgba(201,162,39,0.2); border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; }
        .header h1 { color: #ffffff; font-size: 24px; font-weight: 700; margin-bottom: 4px; }
        .header p { color: rgba(255,255,255,0.8); font-size: 14px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 17px; font-weight: 600; color: #1e293b; margin-bottom: 12px; }
        .para { color: #475569; margin-bottom: 20px; font-size: 14px; }
        .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin-bottom: 12px; }
        .detail-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 24px; }
        .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .detail-row:last-child { border-bottom: none; padding-bottom: 0; }
        .detail-label { color: #64748b; }
        .detail-value { font-weight: 600; color: #1e293b; text-align: right; }
        .btn { display: inline-block; background: #C9A227; color: #ffffff !important; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 700; font-size: 15px; margin: 8px 4px; }
        .actions { text-align: center; margin: 28px 0; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 40px; text-align: center; font-size: 12px; color: #94a3b8; }
        .footer a { color: #1B3A6B; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="header-icon">
            <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#C9A227" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <h1>New Hotel Awaiting Approval</h1>
        <p>A hotel owner just submitted a listing</p>
    </div>

    <div class="body">
        <p class="greeting">Hi Admin,</p>
        <p class="para">
            <strong>{{ $hotel->owner->name ?? 'A hotel owner' }}</strong> has submitted a new hotel that needs your review before it can go live.
        </p>

        <p class="section-title">Hotel Details</p>
        <div class="detail-box">
            <div class="detail-row">
                <span class="detail-label">Name</span>
                <span class="detail-value">{{ $hotel->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">City</span>
                <span class="detail-value">{{ $hotel->city }}, {{ $hotel->country }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Owner</span>
                <span class="detail-value">{{ $hotel->owner->name ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Owner Email</span>
                <span class="detail-value">{{ $hotel->owner->email ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Submitted</span>
                <span class="detail-value">{{ $hotel->created_at->format('d M Y H:i') }}</span>
            </div>
        </div>

        <div class="actions">
            <a href="{{ route('admin.hotels.show', $hotel) }}" class="btn">Review Hotel</a>
        </div>
    </div>

    <div class="footer">
        <p style="margin-bottom: 8px;">
            <strong style="color: #1B3A6B;">{{ config('app.name') }}</strong>
        </p>
        <p style="margin-top: 12px; color: #cbd5e1;">
            You received this email because you're a super admin on {{ config('app.name') }}.
        </p>
    </div>
</div>
</body>
</html>
