<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository
{
    public function forUser($user)
    {
        return Order::where('user_id', $user->id)->with('items.product')->latest()->get();
    }

    public function find($id)
    {
        return Order::with(['items.product', 'billingAddress', 'shippingAddress'])->find($id);
    }
}
