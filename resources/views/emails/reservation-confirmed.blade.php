@component('mail::message')
{{-- Branding/Header --}}
<table width="100%" style="background:#f8fafc;padding:20px 0 10px 0;text-align:center;">
    <tr>
        <td><img src="{{ asset('images/logo.png') }}" alt="Vheego Logo" height="48" style="margin-bottom:10px;"></td>
    </tr>
    <tr>
        <td><h2 style="color:#1e293b;margin:0;">Reservation Confirmed!</h2></td>
    </tr>
</table>

{{-- Booking Summary --}}
@component('mail::panel')
<b>Car:</b> {{ $reservation->car->make }} {{ $reservation->car->model }} ({{ $reservation->car->year }})<br>
<b>Agency:</b> {{ $reservation->car->agency->name }}<br>
<b>Rental Dates:</b> {{ $reservation->start_date }} to {{ $reservation->end_date }}<br>
<b>Total Price:</b> ${{ number_format($reservation->total_price, 2) }}<br>
@endcomponent

{{-- Contact Info --}}
**Your Contact:**<br>
Name: {{ $user->name }}<br>
Email: {{ $user->email }}<br>

**Agency Contact:**<br>
Name: {{ $reservation->car->agency->name }}<br>
Email: {{ $reservation->car->agency->contact_email }}<br>
Phone: {{ $reservation->car->agency->contact_phone }}<br>

{{-- Next Steps --}}
@component('mail::panel')
- Please contact the agency if you have any questions about your booking.
- Bring your ID and payment confirmation when picking up the car.
- For support, reply to this email or visit our website.
@endcomponent

{{-- Footer --}}
---
<div style="text-align:center;color:#64748b;font-size:13px;margin-top:20px;">
    &copy; {{ date('Y') }} Vheego. All rights reserved.<br>
    Need help? <a href="mailto:support@vheego.com">Contact Support</a>
</div>
@endcomponent 