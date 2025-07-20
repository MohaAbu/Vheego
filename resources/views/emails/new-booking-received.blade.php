@component('mail::message')
{{-- Branding/Header --}}
<table width="100%" style="background:#f8fafc;padding:20px 0 10px 0;text-align:center;">
    <tr>
        <td><img src="{{ asset('images/logo.png') }}" alt="Vheego Logo" height="48" style="margin-bottom:10px;"></td>
    </tr>
    <tr>
        <td><h2 style="color:#1e293b;margin:0;">New Booking Received!</h2></td>
    </tr>
</table>

{{-- Booking Summary --}}
@component('mail::panel')
<b>Car:</b> {{ $reservation->car->make }} {{ $reservation->car->model }} ({{ $reservation->car->year }})<br>
<b>Rental Dates:</b> {{ $reservation->start_date }} to {{ $reservation->end_date }}<br>
<b>Total Price:</b> ${{ number_format($reservation->total_price, 2) }}<br>
@endcomponent

{{-- Customer Info --}}
**Customer Contact:**<br>
Name: {{ $reservation->customer->name }}<br>
Email: {{ $reservation->customer->email }}<br>

{{-- Next Steps --}}
@component('mail::panel')
- Please review the booking and prepare the car for the rental period.
- Contact the customer if you need to confirm any details.
- For support, reply to this email or visit our website.
@endcomponent

{{-- Footer --}}
---
<div style="text-align:center;color:#64748b;font-size:13px;margin-top:20px;">
    &copy; {{ date('Y') }} Vheego. All rights reserved.<br>
    Need help? <a href="mailto:support@vheego.com">Contact Support</a>
</div>
@endcomponent 