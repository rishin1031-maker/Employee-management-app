<x-mail::message>
# Welcome to {{ config('app.name') }}, {{ $employee->name }}!

Your employee account has been created. Below are your login credentials.

<x-mail::panel>
**Employee ID:** {{ $employee->employee_id }}
**Password:** {{ $plainPassword }}
**Login URL:** {{ url('/login') }}
</x-mail::panel>

**Important:** You will be required to change your password on your first login.

Here are your details:

| Field | Value |
|---|---|
| Department | {{ $employee->department->name ?? '—' }} |
| Designation | {{ $employee->designation->name ?? '—' }} |
| Email | {{ $employee->email }} |

<x-mail::button :url="url('/login')" color="primary">
Login Now
</x-mail::button>

If you have any questions, please contact your HR administrator.

Regards,
**{{ config('app.name') }} Team**
</x-mail::message>