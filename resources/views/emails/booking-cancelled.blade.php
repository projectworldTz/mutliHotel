<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Cancelled</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #334155; background: #f1f5f9; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #991b1b 0%, #dc2626 100%); padding: 40px 40px 32px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 24px; font-weight: 700; margin-bottom: 4px; }
        .header p { color: rgba(255,255,255,0.85); font-size: 14px; }
        .badge-number { display: inline-block; background: rgba(255,255,255,0.2); color: #fff; padding: 4px 14px; border-radius: 999px; font-size: 13px; font-weight: 700; font-family: monospace; margin-top: 10px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 17px; font-weight: 600; color: #1e293b; margin-bottom: 12px; }
        .para { color: #475569; margin-bottom: 20px; font-size: 14px; }
        .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin-bottom: 12px; }
        .detail-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 24px; }
        .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .detail-row:last-child { border-bottom: none; padding-bottom: 0; }
        .detail-label { color: #64748b; }
        .detail-value { font-weight: 600; color: #1e293b; text-align: right; }
        .note { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 14px 18px; font-size: 13px; color: #9a3412; margin-bottom: 24px; }
        .btn { display: inline-block; background: #1B3A6B; color: #ffffff !important; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 700; font-size: 15px; margin: 8px 4px; }
        .actions { text-align: center; margin: 28px 0; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 40px; text-align: center; font-size: 12px; color: #94a3b8; }
        .footer a { color: #1B3A6B; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div style="width:56px;height:56px;background:rgba(255,255,255,0.2);border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;">
            <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1>Booking Cancelled</h1>
        <p>Your reservation has been cancelled</p>
        <div class="badge-number">#{{ $booking->booking_number }}</div>
    </div>

    <div class="body">
        <p class="greeting">Hi {{ $booking->user->name ?? 'there' }},</p>
        <p class="para">
            We're sorry to see you go. Your booking at <strong>{{ $booking->hotel->name }}</strong>
            has been cancelled as requested.
        </p>

        <p class="section-title">Cancelled Booking</p>
        <div class="detail-box">
            <div class="detail-row">
                <span class="detail-label">Booking #</span>
                <span class="detail-value">{{ $booking->booking_number }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Hotel</span>
                <span class="detail-value">{{ $booking->hotel->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Check-in</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($booking->check_in)->format('D, d M Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Check-out</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($booking->check_out)->format('D, d M Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Charged</span>
                <span class="detail-value">${{ number_format($booking->grand_total, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Cancelled On</span>
                <span class="detail-value">{{ ($booking->cancelled_at ?? now())->format('d M Y, H:i') }}</span>
            </div>
        </div>

        @if($booking->cancellation_reason)
        <div class="note">
            <strong>Cancellation Reason:</strong> {{ $booking->cancellation_reason }}
        </div>
        @endif

        <p class="para">
            If a refund is applicable per our cancellation policy, it will be processed within
            5–10 business days to your original payment method.
        </p>

        <div class="actions">
            <a href="{{ route('hotels.index') }}" class="btn">Browse Hotels Again</a>
        </div>

        <p class="para" style="font-size: 13px; text-align: center; color: #94a3b8;">
            Questions about your refund? Contact us at
            <a href="mailto:{{ config('mail.from.address') }}" style="color: #1B3A6B;">{{ config('mail.from.address') }}</a>.
        </p>
    </div>

    <div class="footer">
        <p style="margin-bottom: 8px;">
            <strong style="color: #1B3A6B;">{{ config('app.name') }}</strong>
        </p>
        <p>
            <a href="{{ route('home') }}">Visit Site</a> ·
            <a href="{{ route('hotels.index') }}">Browse Hotels</a>
        </p>
        <p style="margin-top: 12px; color: #cbd5e1;">
            You received this email because you cancelled a booking on {{ config('app.name') }}.
        </p>
    </div>
</div>
</body>
</html>
