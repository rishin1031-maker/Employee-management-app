<x-mail::message>
# Your Leave Request has been {{ ucfirst($leaveRequest->status) }}

Dear **{{ $leaveRequest->employee->name }}**,

Your leave request has been reviewed by the administrator.

<x-mail::panel>
**Status:** {{ ucfirst($leaveRequest->status) }}
**Type:** {{ ucfirst($leaveRequest->type) }} Leave
**From:** {{ $leaveRequest->from_date->format('d M Y') }}
**To:** {{ $leaveRequest->to_date->format('d M Y') }}
**Days:** {{ $leaveRequest->days }} day(s)
@if($leaveRequest->admin_note)
**Admin Note:** {{ $leaveRequest->admin_note }}
@endif
</x-mail::panel>

@if($leaveRequest->status === 'approved')
Your leave has been approved. Please ensure your work is handed over before your leave starts.
@else
Your leave request has been rejected. Please contact your administrator for more information.
@endif

<x-mail::button :url="url('/employee/leave')" color="primary">
View My Leaves
</x-mail::button>

Regards,
**{{ config('app.name') }} Team**
</x-mail::message>