<?php

namespace App\Services;

use App\Repositories\OrderRepository;

class OrderService
{
    public function __construct(private OrderRepository $repository)
    {
    }

    public function getUserOrders($user)
    {
        return $this->repository->forUser($user);
    }

    public function getOrder($id)
    {
        return $this->repository->find($id);
    }
}
