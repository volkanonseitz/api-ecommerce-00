@component('mail::message')
# Vendor: Your Shop Commission Rate Updated

Dear Shop Owner,

The commission rate for your shop "{{ $shopName }}" has been updated.

Your Total Earnings: {{ $totalEarnings }}
Your Current Balance: {{ $currentBalance }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent