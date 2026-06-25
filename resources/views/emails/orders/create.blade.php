@component('mail::message')
# Order Confirmation

Hello {{ $order->customer_name ?? $order->customer->name ?? 'Customer' }},

Thank you for your order! Your order has been successfully placed.

**Order Details:**

- **Tracking Number:** {{ $trackingNumber }}
- **Order Date:** {{ $order->created_at->format('d M Y H:i') }}
- **Total Amount:** {{ number_format($total, 2) }}

@component('mail::button', ['url' => config('app.url') . '/orders/' . $trackingNumber])
View Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent