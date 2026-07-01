<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Confirmed</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #334155; background: #f1f5f9; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #122D59 0%, #1B3A6B 50%, #2A5BAD 100%); padding: 40px 40px 32px; text-align: center; }
        .header-icon { width: 56px; height: 56px; background: rgba(201,162,39,0.2); border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; }
        .header h1 { color: #ffffff; font-size: 24px; font-weight: 700; margin-bottom: 4px; }
        .header p { color: rgba(255,255,255,0.8); font-size: 14px; }
        .badge-number { display: inline-block; background: rgba(201,162,39,0.25); color: #C9A227; border: 1px solid rgba(201,162,39,0.4); padding: 4px 14px; border-radius: 999px; font-size: 13px; font-weight: 700; font-family: monospace; margin-top: 10px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 17px; font-weight: 600; color: #1e293b; margin-bottom: 12px; }
        .para { color: #475569; margin-bottom: 20px; font-size: 14px; }
        .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin-bottom: 12px; }
        .detail-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 24px; }
        .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .detail-row:last-child { border-bottom: none; padding-bottom: 0; }
        .detail-label { color: #64748b; }
        .detail-value { font-weight: 600; color: #1e293b; text-align: right; }
        .room-row { padding: 12px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .room-row:last-child { border-bottom: none; }
        .room-name { font-weight: 600; color: #1e293b; }
        .room-dates { color: #64748b; font-size: 13px; margin-top: 2px; }
        .room-price { font-weight: 700; color: #1B3A6B; text-align: right; }
        .total-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; color: #475569; }
        .total-row.grand { font-size: 16px; font-weight: 700; color: #1B3A6B; padding-top: 12px; border-top: 2px solid #1B3A6B; margin-top: 6px; }
        .btn { display: inline-block; background: #C9A227; color: #ffffff !important; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 700; font-size: 15px; margin: 8px 4px; }
        .btn-outline { background: transparent; color: #1B3A6B !important; border: 2px solid #1B3A6B; }
        .actions { text-align: center; margin: 28px 0; }
        .note { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 18px; font-size: 13px; color: #166534; margin-bottom: 24px; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 40px; text-align: center; font-size: 12px; color: #94a3b8; }
        .footer a { color: #1B3A6B; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="header-icon">
            <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#C9A227" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1>Booking Confirmed!</h1>
        <p>Your reservation is all set</p>
        <div class="badge-number">#{{ $booking->booking_number }}</div>
    </div>

    <div class="body">
        <p class="greeting">Hi {{ $booking->user->name ?? 'there' }},</p>
        <p class="para">
            Great news! Your booking at <strong>{{ $booking->hotel->name }}</strong> has been confirmed.
            Here's a summary of your reservation:
        </p>

        {{-- Hotel & Stay Details --}}
        <p class="section-title">Stay Details</p>
        <div class="detail-box">
            <div class="detail-row">
                <span class="detail-label">Hotel</span>
                <span class="detail-value">{{ $booking->hotel->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Location</span>
                <span class="detail-value">{{ $booking->hotel->city }}, {{ $booking->hotel->country }}</span>
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
                <span class="detail-label">Nights</span>
                <span class="detail-value">{{ $booking->nights }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Guests</span>
                <span class="detail-value">{{ $booking->guests_adults }} adult{{ $booking->guests_adults > 1 ? 's' : '' }}
                    @if($booking->guests_children > 0), {{ $booking->guests_children }} child{{ $booking->guests_children > 1 ? 'ren' : '' }}@endif
                </span>
            </div>
            @if($booking->hotel->check_in_time)
            <div class="detail-row">
                <span class="detail-label">Check-in time</span>
                <span class="detail-value">From {{ $booking->hotel->check_in_time }}</span>
            </div>
            @endif
            @if($booking->hotel->check_out_time)
            <div class="detail-row">
                <span class="detail-label">Check-out time</span>
                <span class="detail-value">By {{ $booking->hotel->check_out_time }}</span>
            </div>
            @endif
        </div>

        {{-- Rooms --}}
        @if($booking->rooms->isNotEmpty())
        <p class="section-title">Rooms</p>
        <div class="detail-box">
            @foreach($booking->rooms as $room)
            <div class="room-row">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="room-name">{{ $room->roomType->name ?? 'Room' }}</div>
                        <div class="room-dates">
                            {{ \Carbon\Carbon::parse($room->check_in)->format('d M') }} –
                            {{ \Carbon\Carbon::parse($room->check_out)->format('d M Y') }}
                            ({{ $room->nights }} nights @ {{ $booking->currency }} {{ number_format($room->nightly_rate, 2) }}/night)
                        </div>
                    </div>
                    <div class="room-price">{{ $booking->currency }} {{ number_format($room->sub_total, 2) }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Totals --}}
        <p class="section-title">Payment Summary</p>
        <div class="detail-box">
            <div class="total-row">
                <span>Subtotal</span>
                <span>{{ $booking->currency }} {{ number_format($booking->sub_total, 2) }}</span>
            </div>
            @if($booking->tax_total > 0)
            <div class="total-row">
                <span>Tax ({{ number_format($booking->tax_rate, 0) }}%)</span>
                <span>{{ $booking->currency }} {{ number_format($booking->tax_total, 2) }}</span>
            </div>
            @endif
            @if($booking->discount_total > 0)
            <div class="total-row" style="color: #16a34a;">
                <span>Discount</span>
                <span>-{{ $booking->currency }} {{ number_format($booking->discount_total, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand">
                <span>Total</span>
                <span>{{ $booking->currency }} {{ number_format($booking->grand_total, 2) }}</span>
            </div>
        </div>

        @if($booking->special_requests)
        <div class="note">
            <strong>Special Requests:</strong> {{ $booking->special_requests }}
        </div>
        @endif

        <div class="actions">
            <a href="{{ route('booking.show', $booking->booking_number) }}" class="btn">View Booking</a>
            <a href="{{ route('booking.invoice', $booking->booking_number) }}" class="btn btn-outline">Download Invoice</a>
        </div>

        <p class="para" style="font-size: 13px; text-align: center; color: #94a3b8;">
            Need to change or cancel your booking?
            <a href="{{ route('booking.show', $booking->booking_number) }}" style="color: #1B3A6B;">Visit your booking page</a>.
        </p>
    </div>

    <div class="footer">
        <p style="margin-bottom: 8px;">
            <strong style="color: #1B3A6B;">{{ config('app.name') }}</strong>
        </p>
        <p>
            <a href="{{ route('home') }}">Visit Site</a> ·
            <a href="{{ route('account.bookings') }}">My Bookings</a>
        </p>
        <p style="margin-top: 12px; color: #cbd5e1;">
            You received this email because you made a booking on {{ config('app.name') }}.
        </p>
    </div>
</div>
</body>
</html>
