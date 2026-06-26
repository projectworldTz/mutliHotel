<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class InvoiceService
{
    /**
     * Create (or retrieve) the invoice record for a booking.
     * Called right after a Booking is created.
     */
    public function generateForBooking(Booking $booking): Invoice
    {
        if ($existing = $booking->invoice) {
            return $existing;
        }

        return Invoice::create([
            'booking_id'     => $booking->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'subtotal'       => $booking->sub_total,
            'tax_total'      => $booking->tax_total,
            'discount_total' => $booking->discount_total,
            'grand_total'    => $booking->grand_total,
            'currency'       => $booking->currency,
            'status'         => 'draft',
        ]);
    }

    /**
     * Mark the invoice as issued (called after payment is received / booking confirmed).
     */
    public function issue(Invoice $invoice): Invoice
    {
        $invoice->update([
            'status'    => 'issued',
            'issued_at' => now(),
            'due_at'    => now()->addDays(7),
        ]);

        return $invoice;
    }

    /**
     * Mark the invoice as paid.
     */
    public function markPaid(Invoice $invoice): Invoice
    {
        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        return $invoice;
    }

    /**
     * Render the invoice as a PDF and return the raw string content.
     * The Blade view resources/views/pdf/invoice.blade.php must exist (Phase 7).
     */
    public function toPdf(Invoice $invoice): string
    {
        $invoice->loadMissing(['booking.hotel', 'booking.user', 'booking.rooms.roomType']);

        $pdf = Pdf::loadView('pdf.invoice', [
                'invoice' => $invoice,
                'booking' => $invoice->booking,
            ])->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * Stream the PDF directly to the browser as a download.
     */
    public function download(Invoice $invoice): \Illuminate\Http\Response
    {
        $content  = $this->toPdf($invoice);
        $filename = "invoice-{$invoice->invoice_number}.pdf";

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Stream the PDF to the browser inline (preview).
     */
    public function stream(Invoice $invoice): \Illuminate\Http\Response
    {
        $content  = $this->toPdf($invoice);
        $filename = "invoice-{$invoice->invoice_number}.pdf";

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }
}
