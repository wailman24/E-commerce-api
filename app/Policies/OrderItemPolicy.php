<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;
use App\Models\Order_item;
use Illuminate\Auth\Access\Response;

class OrderItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order_item $orderItem): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order_item $orderItem): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order_item $orderItem): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order_item $orderItem): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order_item $orderItem): bool
    {
        return false;
    }

    public function modify(User $user, Order_item $order_item)
    {
        $order = Order::where('id', $order_item->order_id)->first();

        return $user->id === $order->user_id
        ? Response::allow()
        : Response::deny('you can not modify this item');
    }
}
