<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        /** @var Hotel $hotel */
        $hotel = $request->attributes->get('assigned_hotel');

        $query = Invoice::whereHas('booking', fn ($q) => $q->where('hotel_id', $hotel->id))
            ->with('booking.user')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(20)->withQueryString();

        return view('accountant.invoices.index', compact('hotel', 'invoices'));
    }

    public function show(Request $request, Invoice $invoice)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        $invoice->load('booking.user', 'booking.rooms.roomType');
        abort_unless($invoice->booking->hotel_id === $hotel->id, 403);

        return view('accountant.invoices.show', compact('hotel', 'invoice'));
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($invoice->booking->hotel_id === $hotel->id, 403);

        $this->invoiceService->markPaid($invoice);

        return back()->with('success', "Invoice {$invoice->invoice_number} marked as paid.");
    }

    public function refund(Request $request, Invoice $invoice)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($invoice->booking->hotel_id === $hotel->id, 403);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $invoice->grand_total],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->invoiceService->refund($invoice, (float) $data['amount'], $data['reason'] ?? null);

        return back()->with('success', "Refund issued for invoice {$invoice->invoice_number}.");
    }
}
