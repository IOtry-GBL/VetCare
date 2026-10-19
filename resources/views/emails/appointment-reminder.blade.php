@component('mail::message')
# Appointment Reminder

Hello {{ $recipientName }},

This is a reminder that {{ $petName }} has an appointment {{ $reminderLabel }}.

**Appointment Details:**
- **Date and time:** {{ $appointmentAt->format('M d, Y h:i A') }}
- **Visit type:** {{ $consultationMode }}
- **Reason:** {{ $reasonForVisit }}

Please contact the clinic if you need to make changes to this appointment.

Thanks,<br>
{{ config('app.name') }}

@component('mail::subcopy')
This is an automated reminder from your veterinary clinic. Please do not reply to this email.
@endcomponent
@endcomponent
