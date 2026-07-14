<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Hotel;
use App\Models\Invoice;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var Hotel $hotel */
        $hotel = $request->attributes->get('assigned_hotel');

        $invoiceQuery = fn () => Invoice::whereHas('booking', fn ($q) => $q->where('hotel_id', $hotel->id));

        $stats = [
            'outstanding_invoices' => $invoiceQuery()->whereIn('status', ['draft', 'issued'])->count(),
            'revenue_this_month'   => (float) $invoiceQuery()->paid()
                ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('grand_total'),
            'refunded_this_month'  => (float) $invoiceQuery()->refunded()
                ->whereBetween('refunded_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('refund_amount'),
        ];

        $recentExpenses = Expense::forHotel($hotel->id)->latest('expense_date')->take(5)->get();
        $recentInvoices = $invoiceQuery()->with('booking.user')->latest()->take(5)->get();

        return view('accountant.dashboard.index', compact('hotel', 'stats', 'recentExpenses', 'recentInvoices'));
    }
}
