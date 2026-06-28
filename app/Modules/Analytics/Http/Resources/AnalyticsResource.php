<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin array{
 *     totalRevenue: float,
 *     totalRefunds: float,
 *     totalShops: int,
 *     totalVendors: int,
 *     todaysRevenue: float,
 *     totalOrders: int,
 *     newCustomers: int,
 *     totalYearSaleByMonth: array<int, array{month: string, total: float}>,
 *     todayTotalOrderByStatus: array,
 *     weeklyTotalOrderByStatus: array,
 *     monthlyTotalOrderByStatus: array,
 *     yearlyTotalOrderByStatus: array,
 * }
 */
class AnalyticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_revenue' => $this['totalRevenue'],
            'total_refunds' => $this['totalRefunds'],
            'total_shops' => $this['totalShops'],
            'total_vendors' => $this['totalVendors'],
            'todays_revenue' => $this['todaysRevenue'],
            'total_orders' => $this['totalOrders'],
            'new_customers' => $this['newCustomers'],
            'total_year_sale_by_month' => $this['totalYearSaleByMonth'],
            'today_total_order_by_status' => $this['todayTotalOrderByStatus'],
            'weekly_total_order_by_status' => $this['weeklyTotalOrderByStatus'],
            'monthly_total_order_by_status' => $this['monthlyTotalOrderByStatus'],
            'yearly_total_order_by_status' => $this['yearlyTotalOrderByStatus'],
        ];
    }
}
