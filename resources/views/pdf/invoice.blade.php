<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice?->invoice_number ?? $booking->booking_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page { size: A4 portrait; margin: 0; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #1a1a2e;
            line-height: 1.4;
            background: #fff;
        }

        /* Lock to exactly one A4 page */
        .page {
            width: 210mm;
            height: 297mm;
            overflow: hidden;
            background: #fff;
            position: relative;
        }

        /* ── Status badges ── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .b-paid      { background:#D1FAE5; color:#065F46; }
        .b-issued    { background:#DBEAFE; color:#1E40AF; }
        .b-draft     { background:#F3F4F6; color:#374151; }
        .b-cancelled { background:#FEE2E2; color:#991B1B; }
        .b-pending   { background:#FEF9C3; color:#854D0E; }
        .b-confirmed { background:#D1FAE5; color:#065F46; }
        .b-checked_in  { background:#DBEAFE; color:#1E40AF; }
        .b-checked_out { background:#F3F4F6; color:#374151; }
        .b-no_show   { background:#FCE7F3; color:#9D174D; }
        .b-refunded  { background:#EDE9FE; color:#5B21B6; }

        /* ── Header ── */
        .hdr {
            background: #0F2147;
            padding: 13px 24px 12px;
        }
        .hdr table { width: 100%; border-collapse: collapse; }
        .hdr td { padding: 0; vertical-align: middle; }

        .h-badge {
            width: 40px; height: 40px;
            background: #C9A227;
            border-radius: 8px;
            text-align: center;
            line-height: 40px;
            font-size: 17px;
            font-weight: bold;
            color: #0F2147;
            display: inline-block;
        }
        .h-name    { font-size: 15px; font-weight: bold; color: #fff; line-height: 1.2; }
        .h-sub     { font-size: 8.5px; color: rgba(255,255,255,0.6); margin-top: 2px; }
        .h-stars   { color: #C9A227; font-size: 10px; }
        .h-contact { font-size: 8px; color: rgba(255,255,255,0.65); margin-top: 5px; line-height: 1.7; }

        .inv-box {
            background: rgba(255,255,255,0.08);
            border-radius: 7px;
            padding: 10px 14px;
            text-align: right;
        }
        .inv-word { font-size: 18px; font-weight: bold; color: #C9A227; letter-spacing: 0.1em; text-transform: uppercase; }
        .inv-num  { font-size: 10px; color: rgba(255,255,255,0.9); font-weight: bold; margin-top: 2px; }
        .inv-meta { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .inv-meta td { font-size: 8px; padding: 1.5px 0; }
        .inv-meta .lbl { color: rgba(255,255,255,0.45); }
        .inv-meta .val { color: rgba(255,255,255,0.9); font-weight: bold; text-align: right; }

        /* ── Gold stripe ── */
        .stripe { height: 3px; background: #C9A227; }

        /* ── Body ── */
        .body { padding: 12px 24px 10px; }

        /* Section label */
        .sec-lbl {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #0F2147;
            border-bottom: 1.5px solid #0F2147;
            padding-bottom: 3px;
            margin-bottom: 7px;
        }

        /* Info panels — two-column table layout */
        .info-wrap { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info-wrap td { vertical-align: top; padding: 0; }
        .panel {
            background: #F5F7FA;
            border-radius: 7px;
            padding: 9px 12px;
        }
        .panel table { width: 100%; border-collapse: collapse; }
        .panel table td { padding: 2px 0; font-size: 9.5px; vertical-align: top; }
        .panel .pl { color: #6B7280; width: 42%; font-size: 8.5px; }
        .panel .pv { color: #1a1a2e; font-weight: bold; text-align: right; }

        /* Charges table */
        .ctbl { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .ctbl thead tr { background: #0F2147; color: #fff; }
        .ctbl thead th {
            padding: 7px 10px;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: bold;
        }
        .ctbl thead th:first-child { text-align: left; }
        .ctbl thead th:not(:first-child) { text-align: right; }
        .ctbl tbody tr { border-bottom: 1px solid #E5E7EB; }
        .ctbl tbody tr:nth-child(even) { background: #F9FAFB; }
        .ctbl tbody td { padding: 6px 10px; font-size: 9.5px; vertical-align: middle; }
        .ctbl tbody td:not(:first-child) { text-align: right; }

        /* Totals */
        .ttbl { width: 100%; border-collapse: collapse; }
        .ttbl td { padding: 3px 10px; font-size: 9.5px; }
        .ttbl .tl { color: #374151; }
        .ttbl .tv { text-align: right; font-weight: bold; }
        .grand-row td {
            background: #0F2147;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            padding: 7px 10px;
        }
        .grand-row .tv { color: #C9A227; }

        /* Payment panel */
        .pay-panel {
            background: #F5F7FA;
            border-radius: 7px;
            padding: 9px 12px;
        }
        .pay-panel table { width: 100%; border-collapse: collapse; }
        .pay-panel td { font-size: 9px; padding: 2px 0; vertical-align: top; }
        .pay-lbl { color: #6B7280; font-size: 8px; }
        .pay-val { font-weight: bold; color: #0F2147; font-size: 10px; }

        /* Cancellation box */
        .cbox {
            border: 1px solid #FCA5A5;
            border-radius: 7px;
            padding: 8px 12px;
            margin-top: 10px;
            background: #FFF7F7;
        }
        .cbox .ch {
            font-size: 8px; font-weight: bold; text-transform: uppercase;
            letter-spacing: 0.07em; color: #B91C1C; margin-bottom: 6px;
        }
        .cbox table { width: 100%; border-collapse: collapse; }
        .cbox td { font-size: 9.5px; padding: 2px 0; }
        .cv { text-align: right; font-weight: bold; }

        /* Footer */
        .ftr {
            background: #0F2147;
            padding: 10px 24px;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
        }
        .ftr table { width: 100%; border-collapse: collapse; }
        .ftr td { vertical-align: middle; padding: 0; }
        .f-thank   { font-size: 10px; font-weight: bold; color: #C9A227; }
        .f-contact { font-size: 8px; color: rgba(255,255,255,0.6); margin-top: 3px; line-height: 1.7; }
        .f-tnc     { font-size: 7.5px; color: rgba(255,255,255,0.35); margin-top: 5px; line-height: 1.5; }

        /* Stamp circle */
        .stamp {
            width: 60px; height: 60px;
            border: 2px solid rgba(201,162,39,0.4);
            border-radius: 50%;
            text-align: center;
            margin-left: auto;
            padding-top: 10px;
        }
        .stamp-icon { font-size: 20px; line-height: 1; }
        .stamp-lbl  { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px; }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
@php
    $invoice  ??= $booking->invoice;
    $hotel     = $booking->hotel;
    $guest     = $booking->user;
    $payment   = $booking->payment;
    $rooms     = $booking->rooms;

    $words    = array_filter(explode(' ', $hotel->name));
    $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice($words, 0, 2)));
    $stars    = str_repeat('★', (int) $hotel->star_rating) . str_repeat('☆', max(0, 5 - (int) $hotel->star_rating));

    $invoiceStatus = $invoice?->status ?? ($booking->status === 'cancelled' ? 'cancelled' : 'issued');
    $statusLabel   = strtoupper(match($invoiceStatus) {
        'paid' => 'PAID', 'issued' => 'ISSUED', 'draft' => 'DRAFT',
        'cancelled' => 'CANCELLED', default => $invoiceStatus,
    });
    $statusClass = 'b-' . $invoiceStatus;

    $subtotal      = (float) ($invoice?->subtotal        ?? $booking->sub_total    ?? 0);
    $taxTotal      = (float) ($invoice?->tax_total       ?? $booking->tax_total    ?? 0);
    $discountTotal = (float) ($invoice?->discount_total  ?? $booking->discount_total ?? 0);
    $grandTotal    = (float) ($invoice?->grand_total     ?? $booking->grand_total  ?? 0);
    $taxRate       = (float) ($booking->tax_rate ?? 0);
    $currency      = $booking->currency ?? config('app.currency');

    $amountPaid = (float) ($payment?->amount ?? ($invoiceStatus === 'paid' ? $grandTotal : 0));
    $balanceDue = max(0, $grandTotal - $amountPaid);
    $isCancelled = $invoice && $invoice->isCancelled() && $invoice->cancellation_deduction;

    $issuedDate = ($invoice?->issued_at ?? $booking->confirmed_at ?? $booking->created_at)?->format('d M Y');

    // Single contact line for header
    $contactLine = implode('   ', array_filter([
        $hotel->phone  ? '✆ '.$hotel->phone  : null,
        $hotel->email  ? '✉ '.$hotel->email  : null,
        $hotel->website ? '⊕ '.$hotel->website : null,
    ]));
@endphp

<div class="page">

{{-- ══ HEADER ══ --}}
<div class="hdr">
    <table>
        <tr>
            <td style="width:58%;">
                <table style="border-collapse:collapse;">
                    <tr>
                        <td style="width:48px; padding-right:10px; vertical-align:middle;">
                            <div class="h-badge">{{ $initials }}</div>
                        </td>
                        <td style="vertical-align:middle;">
                            <div class="h-name">{{ $hotel->name }}</div>
                            <div class="h-sub">
                                <span class="h-stars">{{ $stars }}</span>
                                &nbsp;·&nbsp;
                                {{ implode(', ', array_filter([$hotel->city, $hotel->country])) }}
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="h-contact" style="padding-left:58px; margin-top:6px;">
                    @if($hotel->address){{ $hotel->address }}<br>@endif
                    {{ $contactLine }}
                </div>
            </td>
            <td style="width:42%; vertical-align:top;">
                <div class="inv-box">
                    <div class="inv-word">Invoice</div>
                    <div class="inv-num">{{ $invoice?->invoice_number ?? $booking->booking_number }}</div>
                    <table class="inv-meta">
                        <tr>
                            <td class="lbl">Date Issued</td>
                            <td class="val">{{ $issuedDate }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Booking Ref</td>
                            <td class="val">{{ $booking->booking_number }}</td>
                        </tr>
                        <tr>
                            <td class="lbl" style="vertical-align:middle; padding-top:3px;">Status</td>
                            <td class="val" style="padding-top:3px;">
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>
<div class="stripe"></div>

{{-- ══ BODY ══ --}}
<div class="body">

    {{-- Guest + Stay info panels --}}
    <table class="info-wrap">
        <tr>
            <td style="width:49%;">
                <div class="panel">
                    <div class="sec-lbl">Billed To</div>
                    <table>
                        <tr>
                            <td class="pl">Guest Name</td>
                            <td class="pv">{{ $guest?->name ?? '—' }}</td>
                        </tr>
                        @if($guest?->email)
                        <tr>
                            <td class="pl">Email</td>
                            <td class="pv" style="font-size:8.5px;">{{ $guest->email }}</td>
                        </tr>
                        @endif
                        @if($guest?->phone)
                        <tr>
                            <td class="pl">Phone</td>
                            <td class="pv">{{ $guest->phone }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="pl">Payment</td>
                            <td class="pv">{{ $booking->payment_method ? ucwords(str_replace('_',' ',$booking->payment_method)) : '—' }}</td>
                        </tr>
                        @if($booking->special_requests)
                        <tr>
                            <td colspan="2" style="padding-top:4px; border-top:1px solid #E5E7EB; font-size:8px; color:#6B7280; font-style:italic;">
                                {{ Str::limit($booking->special_requests, 90) }}
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </td>
            <td style="width:2%;"></td>
            <td style="width:49%;">
                <div class="panel">
                    <div class="sec-lbl">Stay Details</div>
                    <table>
                        @foreach($rooms as $item)
                        <tr>
                            <td class="pl">Room Type</td>
                            <td class="pv">{{ $item->roomType?->name ?? '—' }}</td>
                        </tr>
                        @if($item->room?->room_number)
                        <tr>
                            <td class="pl">Room No.</td>
                            <td class="pv">{{ $item->room->room_number }}{{ $item->room->floor ? ' · Fl.'.$item->room->floor : '' }}</td>
                        </tr>
                        @endif
                        @endforeach
                        <tr>
                            <td class="pl">Check-in</td>
                            <td class="pv">{{ $booking->check_in?->format('d M Y') }} <span style="font-weight:normal; color:#6B7280; font-size:8px;">{{ $hotel->check_in_time ?? '14:00' }}</span></td>
                        </tr>
                        <tr>
                            <td class="pl">Check-out</td>
                            <td class="pv">{{ $booking->check_out?->format('d M Y') }} <span style="font-weight:normal; color:#6B7280; font-size:8px;">{{ $hotel->check_out_time ?? '11:00' }}</span></td>
                        </tr>
                        <tr>
                            <td class="pl">Duration</td>
                            <td class="pv">{{ $booking->nights }} Night{{ $booking->nights != 1 ? 's' : '' }}</td>
                        </tr>
                        <tr>
                            <td class="pl">Guests</td>
                            <td class="pv">
                                {{ $booking->guests_adults ?? 1 }} Adult{{ ($booking->guests_adults ?? 1) != 1 ? 's' : '' }}{{ ($booking->guests_children ?? 0) ? ' · '.$booking->guests_children.' Child'.($booking->guests_children!=1?'ren':'') : '' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Room Charges table --}}
    <div class="sec-lbl">Room Charges</div>
    <table class="ctbl">
        <thead>
            <tr>
                <th style="text-align:left; width:35%;">Description</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Nights</th>
                <th>Rate / Night</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rooms as $item)
            <tr>
                <td>
                    <strong style="color:#0F2147;">{{ $item->roomType?->name ?? 'Room' }}</strong>
                    @if($item->room?->room_number)
                    &nbsp;<span style="font-size:8px; color:#6B7280;">Rm {{ $item->room->room_number }}</span>
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($item->check_in)->format('d M Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($item->check_out)->format('d M Y') }}</td>
                <td>{{ $item->nights }}</td>
                <td>{{ $currency }} {{ number_format((float)$item->nightly_rate, 0) }}</td>
                <td><strong>{{ $currency }} {{ number_format((float)$item->sub_total, 0) }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; color:#9CA3AF; font-style:italic; padding:10px;">No charges recorded.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Totals + Payment --}}
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            {{-- Payment info --}}
            <td style="width:52%; vertical-align:top; padding-right:16px;">
                @if($payment)
                <div class="pay-panel">
                    <div class="sec-lbl" style="margin-bottom:6px;">Payment</div>
                    <table>
                        <tr>
                            <td style="width:50%; vertical-align:top;">
                                <div class="pay-lbl">Method</div>
                                <div class="pay-val">{{ $booking->payment_method ? ucwords(str_replace('_',' ',$booking->payment_method)) : '—' }}</div>
                            </td>
                            <td style="width:50%; vertical-align:top;">
                                <div class="pay-lbl">Status</div>
                                <div style="margin-top:2px;">
                                    <span class="badge b-{{ $payment->status ?? 'pending' }}">{{ strtoupper($payment->status ?? 'PENDING') }}</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                    @if($payment->transaction_id)
                    <div style="margin-top:6px; padding-top:5px; border-top:1px solid #E5E7EB; font-size:8.5px;">
                        <span style="color:#6B7280;">Txn Ref:</span>
                        <strong style="color:#0F2147;">{{ $payment->transaction_id }}</strong>
                    </div>
                    @endif
                </div>
                @endif
                <div style="font-size:7.5px; color:#9CA3AF; margin-top:6px;">
                    Generated {{ now()->format('d M Y, H:i') }} · {{ config('app.name') }}
                </div>
            </td>

            {{-- Totals --}}
            <td style="width:48%; vertical-align:top;">
                <table class="ttbl">
                    <tr>
                        <td class="tl">Subtotal</td>
                        <td class="tv">{{ $currency }} {{ number_format($subtotal, 0) }}</td>
                    </tr>
                    @if($discountTotal > 0)
                    <tr style="color:#16A34A;">
                        <td>Discount</td>
                        <td class="tv">−{{ $currency }} {{ number_format($discountTotal, 0) }}</td>
                    </tr>
                    @endif
                    @if($taxTotal > 0)
                    <tr>
                        <td class="tl">Tax{{ $taxRate > 0 ? ' ('.number_format($taxRate,0).'%)' : '' }}</td>
                        <td class="tv">{{ $currency }} {{ number_format($taxTotal, 0) }}</td>
                    </tr>
                    @endif
                    <tr><td colspan="2" style="padding:2px 10px;"><div style="height:1px; background:#E5E7EB;"></div></td></tr>
                    <tr class="grand-row">
                        <td class="tl" style="border-radius:5px 0 0 5px;">TOTAL</td>
                        <td class="tv" style="border-radius:0 5px 5px 0; text-align:right;">{{ $currency }} {{ number_format($grandTotal, 0) }}</td>
                    </tr>
                    @if($amountPaid > 0)
                    <tr>
                        <td class="tl" style="padding-top:5px;">Amount Paid</td>
                        <td class="tv" style="padding-top:5px; color:#16A34A;">{{ $currency }} {{ number_format($amountPaid, 0) }}</td>
                    </tr>
                    @endif
                    @if($balanceDue > 0)
                    <tr>
                        <td class="tl" style="color:#DC2626; font-weight:bold;">Balance Due</td>
                        <td class="tv" style="color:#DC2626;">{{ $currency }} {{ number_format($balanceDue, 0) }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- Emergency Cancellation --}}
    @if($isCancelled)
    <div class="cbox">
        <div class="ch">⚠ Emergency Cancellation — Partial Refund Applied</div>
        <table>
            <tr>
                <td style="color:#6B7280;">Amount Originally Charged</td>
                <td class="cv">{{ $currency }} {{ number_format($grandTotal, 0) }}</td>
            </tr>
            <tr style="color:#DC2626;">
                <td>Deduction ({{ number_format((float)$invoice->deduction_percentage, 0) }}%)</td>
                <td class="cv">−{{ $currency }} {{ number_format((float)$invoice->cancellation_deduction, 0) }}</td>
            </tr>
            <tr><td colspan="2" style="padding:2px 0;"><div style="height:1px; background:#FCA5A5;"></div></td></tr>
            <tr style="color:#16A34A;">
                <td style="font-weight:bold; font-size:10px;">Refund Due ({{ number_format(100-(float)$invoice->deduction_percentage, 0) }}%)</td>
                <td class="cv" style="font-size:10px; color:#16A34A;">{{ $currency }} {{ number_format((float)$invoice->refund_amount, 0) }}</td>
            </tr>
        </table>
        @if($invoice->cancelled_at)
        <div style="font-size:7.5px; color:#9CA3AF; margin-top:4px;">Cancelled: {{ $invoice->cancelled_at->format('d M Y, H:i') }}</div>
        @endif
    </div>
    @endif

</div>{{-- /body --}}

{{-- ══ FOOTER (pinned to bottom) ══ --}}
<div class="ftr">
    <table>
        <tr>
            <td style="width:75%; vertical-align:middle;">
                <div class="f-thank">Thank you for choosing {{ $hotel->name }}!</div>
                <div class="f-contact">
                    {{ implode('   ', array_filter([$hotel->phone ? '✆ '.$hotel->phone : null, $hotel->email ? '✉ '.$hotel->email : null, $hotel->website ? $hotel->website : null])) }}
                </div>
                <div class="f-tnc">
                    {{ Str::limit($hotel->cancellation_policy ?? 'Cancellation subject to hotel policy.', 180) }}
                    · This invoice is system-generated. For disputes contact the hotel directly.
                </div>
            </td>
            <td style="width:25%; text-align:right; vertical-align:middle;">
                <div class="stamp">
                    @if(in_array($booking->status, ['checked_out','confirmed']))
                        <div class="stamp-icon" style="color:#C9A227;">✓</div>
                        <div class="stamp-lbl" style="color:#C9A227;">{{ $booking->status === 'checked_out' ? 'Completed' : 'Confirmed' }}</div>
                    @elseif($booking->status === 'cancelled')
                        <div class="stamp-icon" style="color:#EF4444;">✗</div>
                        <div class="stamp-lbl" style="color:#EF4444;">Cancelled</div>
                    @else
                        <div class="stamp-icon" style="color:#C9A227;">★</div>
                        <div class="stamp-lbl" style="color:#C9A227;">{{ ucfirst(str_replace('_',' ',$booking->status)) }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

</div>{{-- /page --}}
</body>
</html>
