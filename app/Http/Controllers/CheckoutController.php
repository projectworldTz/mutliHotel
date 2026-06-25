<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $items = $user->cartItems()->with('product')->get();

        return view('checkout.index', compact('items'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $items = $user->cartItems()->with('product')->get();

        if ($items->isEmpty()) {
            return back()->with('warning', 'Your cart is empty.');
        }

        $data = $request->validate([
            'contact_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:30'],
            'country' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', 'string', 'max:50'],
        ]);

        $address = $user->addresses()->create([
            'label' => 'Checkout Address',
            'contact_name' => $data['contact_name'],
            'phone' => $data['phone'],
            'address_line1' => $data['address_line1'],
            'address_line2' => $data['address_line2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'],
            'postal_code' => $data['postal_code'],
            'country' => $data['country'],
            'is_default' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'billing_address_id' => $address->id,
            'shipping_address_id' => $address->id,
            'status' => 'pending',
            'sub_total' => $items->sum(fn ($item) => $item->price * $item->quantity),
            'shipping_total' => 0,
            'tax_total' => 0,
            'discount_total' => 0,
            'grand_total' => $items->sum(fn ($item) => $item->price * $item->quantity),
            'notes' => $request->input('notes'),
        ]);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ]);
        }

        Payment::create([
            'order_id' => $order->id,
            'amount' => $order->grand_total,
            'method' => $data['payment_method'],
            'status' => 'pending',
        ]);

        $user->cartItems()->delete();

        return redirect()->route('account.orders')->with('success', 'Your order has been created.');
    }
}
