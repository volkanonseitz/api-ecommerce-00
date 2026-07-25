@component('mail::message')
# Order Status Changed

The status of your order #{{ $order->tracking_number }} has been changed to {{ $order->order_status }}.

[View Order]({{ $url }})

Thanks,<br>
{{ config('app.name') }}
@endcomponent