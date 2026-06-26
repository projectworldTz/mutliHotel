<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #{{ $booking->booking_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; color: #1e293b; line-height: 1.5; }
        .header { background: #1B3A6B; color: white; padding: 28px 36px; display: flex; justify-content: space-between; align-items: flex-start; }
        .logo { font-size: 20px; font-weight: 700; }
        .logo span { color: #C9A227; }
        .header-right { text-align: right; font-size: 12px; color: rgba(255,255,255,0.8); }
        .invoice-title { font-size: 24px; font-weight: 700; color: white; }
        .body { padding: 32px 36px; }
        .parties { display: flex; justify-content: space-between; margin-bottom: 28px; }
        .party-block { font-size: 12px; }
        .party-block h3 { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 6px; }
        .party-block p { margin: 2px 0; }
        .meta-row { display: flex; gap: 32px; margin-bottom: 24px; padding: 14px 0; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; font-size: 12px; }
        .meta-item span { font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px; }
        th { background: #f1f5f9; padding: 9px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; }
        td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        .totals { margin-left: auto; width: 260px; }
        .total-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 13px; }
        .total-row.grand { font-weight: 700; font-size: 15px; border-top: 2px solid #1B3A6B; margin-top: 6px; padding-top: 10px; color: #1B3A6B; }
        .footer { margin-top: 40px; text-align: center; font-size: 11px; color: #94a3b8; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-confirmed { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef9c3; color: #854d0e; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
<div class="header">
    <div>
        <div class="logo">{{ config('app.name') }} <span>★</span></div>
        <div class="invoice-title" style="margin-top: 6px;">INVOICE</div>
    </div>
    <div class="header-right">
        <div style="font-size: 18px; font-weight: 700; color: white;">#{{ $booking->booking_number }}</div>
        <div>{{ $booking->created_at->format('d M Y') }}</div>
    </div>
</div>

<div class="body">
    <div class="parties">
        <div class="party-block">
            <h3>Invoice To</h3>
            <p><strong>{{ $booking->user->name ?? 'Guest' }}</strong></p>
            <p>{{ $booking->user->email ?? '' }}</p>
            @if($booking->user->phone ?? null)<p>{{ $booking->user->phone }}</p>@endif
        </div>
        <div class="party-block" style="text-align: right;">
            <h3>Accommodation</h3>
            <p><strong>{{ $booking->hotel->name ?? 'Hotel' }}</strong></p>
            <p>{{ $booking->hotel->address ?? '' }}</p>
            <p>{{ ($booking->hotel->city ?? '') . ', ' . ($booking->hotel->country ?? '') }}</p>
        </div>
    </div>

    <div class="meta-row">
        <div class="meta-item">Booking: <span>#{{ $booking->booking_number }}</span></div>
        <div class="meta-item">Status: <span>{{ ucfirst($booking->status) }}</span></div>
        <div class="meta-item">Payment: <span class="capitalize">{{ $booking->payment_method ?? 'N/A' }}</span></div>
        @if($booking->coupon_code)
        <div class="meta-item">Coupon: <span>{{ $booking->coupon_code }}</span></div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Nights</th>
                <th>Rate/Night</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($booking->rooms as $item)
            <tr>
                <td>{{ $item->roomType->name ?? 'Room' }}</td>
                <td>{{ \Carbon\Carbon::parse($item->check_in)->format('d M Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($item->check_out)->format('d M Y') }}</td>
                <td>{{ $item->nights }}</td>
                <td>TZS {{ number_format($item->nightly_rate ?? 0, 0) }}</td>
                <td style="text-align: right;">TZS {{ number_format($item->sub_total ?? 0, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="total-row"><span>Subtotal</span><span>TZS {{ number_format($booking->sub_total ?? 0, 0) }}</span></div>
        @if(($booking->discount_total ?? 0) > 0)
        <div class="total-row" style="color: #16a34a;"><span>Discount</span><span>−TZS {{ number_format($booking->discount_total, 0) }}</span></div>
        @endif
        <div class="total-row"><span>Tax ({{ $booking->tax_rate }}%)</span><span>TZS {{ number_format($booking->tax_total ?? 0, 0) }}</span></div>
        <div class="total-row grand"><span>Total</span><span>TZS {{ number_format($booking->grand_total ?? 0, 0) }}</span></div>
    </div>

    <div class="footer">
        <p>Thank you for choosing {{ config('app.name') }}. We hope to welcome you again soon.</p>
        <p style="margin-top: 4px;">Generated {{ now()->format('d M Y, H:i') }} UTC</p>
    </div>
</div>
</body>
</html>
