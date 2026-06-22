<?php

namespace App\Exports;

use App\Services\AddressFormatterService;
use App\Services\CurrencyFormatterService;
use App\Services\SettingsService;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrderExport implements FromCollection, WithHeadings
{
    private $repository;

    private $shop_id;

    private AddressFormatterService $addressFormatter;

    private CurrencyFormatterService $currencyFormatter;

    private SettingsService $settingsService;

    public function __construct(
        $repository,
        $shop_id,
        AddressFormatterService $addressFormatter,
        CurrencyFormatterService $currencyFormatter,
        SettingsService $settingsService
    ) {
        $this->repository = $repository;
        $this->shop_id = $shop_id;
        $this->addressFormatter = $addressFormatter;
        $this->currencyFormatter = $currencyFormatter;
        $this->settingsService = $settingsService;
    }

    public function collection()
    {
        $results = [];

        if (! empty($this->shop_id)) {
            $orders = $this->repository->where('shop_id', $this->shop_id)->get();
        } else {
            $orders = $this->repository->whereNull('parent_id')->get();
        }

        if ($orders->isEmpty()) {
            return collect($results);
        }

        $language = request()->input('language', config('shop.default_language', 'en'));
        $settings = $this->settingsService->getSettings($language);
        $currency = $settings['options']['currency'] ?? config('shop.default_currency', 'USD');
        $currencyOptions = $settings['options']['currencyOptions'] ?? [];
        $locale = $currencyOptions['formation'] ?? config('notice.default_currency_formation', 'en-US');

        foreach ($orders as $order) {
            $results[] = [
                'id' => '#'.$order->id.' '.($order->customer->name ?? $order->customer_name ?? ''),
                'customer_email' => $order->customer->email ?? 'Guest User',
                'created_at' => Carbon::parse($order->created_at)->format('Y-m-d'),
                'delivery_time' => $order->delivery_time,
                'status' => $order->order_status,
                'tracking_number' => $order->tracking_number,
                'shop' => $order->shop->name ?? '',
                'coupon_id' => $order->coupon_id,
                'amount' => $this->currencyFormatter->format($order->amount, $currency, $locale),
                'discount' => $this->currencyFormatter->format($order->discount, $currency, $locale),
                'paid_amount' => $this->currencyFormatter->format($order->paid_total, $currency, $locale),
                'total' => $this->currencyFormatter->format($order->total, $currency, $locale),
                'sales_tax' => $this->currencyFormatter->format($order->sales_tax, $currency, $locale),
                'delivery_fee' => $this->currencyFormatter->format($order->delivery_fee, $currency, $locale),
                'payment_id' => $order->payment_id,
                'payment_gateway' => $order->payment_gateway,
                'billing_address' => $this->addressFormatter->formatAddress($order->billing_address),
                'shipping_address' => $this->addressFormatter->formatAddress($order->shipping_address),
                'customer_contact' => $order->customer_contact,
                'customer_name' => $order->customer_name,
                'logistics_provider' => $order->logistics_provider,
            ];
        }

        return collect($results);
    }

    public function headings(): array
    {
        return [
            'Order Id',
            'Email',
            'Order Date',
            'Delivery Time',
            'Order Status',
            'Tracking No.',
            'Shop',
            'Coupon ID',
            'Amount',
            'Discount',
            'Paid',
            'Total',
            'Sales Tax',
            'Delivery Fee',
            'Payment Id',
            'Payment Gateway',
            'Billing Address',
            'Shipping Address',
            'Customer Contact',
            'Logistics Provider',
        ];
    }
}
