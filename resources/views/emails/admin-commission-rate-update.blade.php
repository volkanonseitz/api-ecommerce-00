@component('mail::message')
# Admin: Commission Rate Update

The commission rate for shop "{{ $shopName }}" has been updated.

Total Earnings: {{ $totalEarnings }}
Current Balance: {{ $currentBalance }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent