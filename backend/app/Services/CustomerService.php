<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Receivable;
use App\Models\Order;

class CustomerService
{
    public function getCustomerMetrics(int $customerId): array
    {
        $customer = Customer::findOrFail($customerId);

        $totalOrdersCount = Order::where('customer_id', $customer->id)->count();
        $totalPaid = Order::where('customer_id', $customer->id)->sum('grand_total');
        
        $totalOutstandingReceivable = Receivable::where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->sum('remaining_amount');

        return [
            'customer' => $customer,
            'total_orders' => $totalOrdersCount,
            'total_spent' => $totalPaid,
            'outstanding_receivable' => $totalOutstandingReceivable,
        ];
    }
}
