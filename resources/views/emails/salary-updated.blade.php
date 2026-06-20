<x-mail::message>
# Your Salary Has Been Updated

Dear **{{ $employee->name }}**,

Your salary details have been updated by the administrator, effective **{{ $salary->effective_from->format('d M Y') }}**.

<x-mail::panel>
**Basic Salary:** ₹ {{ number_format($salary->basic, 2) }}
**HRA:** ₹ {{ number_format($salary->hra, 2) }}
**Transport:** ₹ {{ number_format($salary->transport, 2) }}
**Medical:** ₹ {{ number_format($salary->medical, 2) }}
**Other Allowance:** ₹ {{ number_format($salary->other_allowance, 2) }}
---
**PF Deduction:** ₹ {{ number_format($salary->pf_deduction, 2) }}
**Tax Deduction:** ₹ {{ number_format($salary->tax_deduction, 2) }}
**Other Deduction:** ₹ {{ number_format($salary->other_deduction, 2) }}
---
**Gross Salary:** ₹ {{ number_format($salary->gross_salary, 2) }}
**Net Salary:** ₹ {{ number_format($salary->net_salary, 2) }}
</x-mail::panel>

@if($salary->note)
**Note from HR:** {{ $salary->note }}
@endif

<x-mail::button :url="url('/employee/profile')" color="primary">
View My Profile
</x-mail::button>

Regards,
**{{ config('app.name') }} Team**
</x-mail::message>