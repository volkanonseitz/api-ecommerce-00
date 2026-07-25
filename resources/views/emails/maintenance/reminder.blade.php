@component('mail::message')
# Maintenance Reminder

The application will be under maintenance from {{ $settings['start'] ?? 'N/A' }} until {{ $settings['until'] ?? 'N/A' }}.

Message: {{ $settings['message'] ?? 'The application will be temporarily unavailable.' }}

[View Admin Settings]({{ $url }})

Thanks,<br>
{{ config('app.name') }}
@endcomponent