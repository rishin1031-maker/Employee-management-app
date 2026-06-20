<x-mail::message>
# New Leave Request Submitted

**{{ $leaveRequest->employee->name }}** ({{ $leaveRequest->employee->employee_id }}) has submitted a new leave request that requires your approval.

<x-mail::panel>
**Type:** {{ ucfirst($leaveRequest->type) }} Leave
**From:** {{ $leaveRequest->from_date->format('d M Y') }}
**To:** {{ $leaveRequest->to_date->format('d M Y') }}
**Days:** {{ $leaveRequest->days }} day(s)
**Reason:** {{ $leaveRequest->reason }}
</x-mail::panel>

<x-mail::button :url="url('/admin/leave/' . $leaveRequest->id)" color="primary">
Review Request
</x-mail::button>

Regards,
**{{ config('app.name') }} System**
</x-mail::message>