<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Hotel;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        /** @var Hotel $hotel */
        $hotel = $request->attributes->get('assigned_hotel');

        $query = Expense::forHotel($hotel->id)->with('creator')->latest('expense_date');

        if ($request->filled('type')) {
            $query->type($request->type);
        }

        $expenses = $query->paginate(20)->withQueryString();

        $summary = [
            'total_expenses' => Expense::forHotel($hotel->id)->type(Expense::TYPE_EXPENSE)->sum('amount'),
            'total_payouts'  => Expense::forHotel($hotel->id)->type(Expense::TYPE_PAYOUT)->sum('amount'),
        ];

        return view('accountant.expenses.index', compact('hotel', 'expenses', 'summary'));
    }

    public function store(Request $request)
    {
        /** @var Hotel $hotel */
        $hotel = $request->attributes->get('assigned_hotel');

        $data = $request->validate([
            'type'         => ['required', 'in:expense,payout'],
            'category'     => ['nullable', 'string', 'max:100'],
            'payee'        => ['nullable', 'string', 'max:150'],
            'description'  => ['required', 'string', 'max:255'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        $data['hotel_id']   = $hotel->id;
        $data['created_by'] = auth()->id();

        Expense::create($data);

        return back()->with('success', 'Entry recorded.');
    }

    public function update(Request $request, Expense $expense)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($expense->hotel_id === $hotel->id, 403);

        $data = $request->validate([
            'type'         => ['required', 'in:expense,payout'],
            'category'     => ['nullable', 'string', 'max:100'],
            'payee'        => ['nullable', 'string', 'max:150'],
            'description'  => ['required', 'string', 'max:255'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        $expense->update($data);

        return back()->with('success', 'Entry updated.');
    }

    public function destroy(Request $request, Expense $expense)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($expense->hotel_id === $hotel->id, 403);

        $expense->delete();

        return back()->with('success', 'Entry removed.');
    }
}
