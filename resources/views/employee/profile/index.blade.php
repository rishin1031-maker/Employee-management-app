@extends('employee.layouts.app')
@section('title','My Profile')
@section('page-title','My Profile')

@section('content')
@php
    use App\Services\AttendanceTimeCalculator;
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Profile card --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-teal-600 to-teal-400 px-6 py-8 text-center">
            <img src="{{ $employee->image_url }}" class="w-20 h-20 rounded-full object-cover border-4 border-white shadow mx-auto mb-3">
            <h2 class="text-white font-bold text-lg">{{ $employee->name }}</h2>
            <p class="text-teal-100 text-sm">{{ $employee->employee_id }}</p>
        </div>
        <div class="p-5 space-y-3 text-sm">
            @php
                $fields = [
                    'Email'       => $employee->email,
                    'Department'  => $employee->department->name ?? '—',
                    'Designation' => $employee->designation->name ?? '—',
                    'Gender'      => ucfirst($employee->gender),
                    'DOB'         => $employee->dob ? $employee->dob->format('d M Y') : '—',
                    'Status'      => ucfirst($employee->status),
                    'Joined'      => $employee->created_at->format('d M Y'),
                ];
            @endphp
            @foreach($fields as $label => $value)
            <div class="flex justify-between">
                <span class="text-gray-500">{{ $label }}</span>
                <span class="font-medium text-gray-800">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="lg:col-span-2 space-y-5">
        {{-- Update phone --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Update phone number</h3>
            <form method="POST" action="{{ route('employee.profile.phone') }}" class="flex gap-3">
                @csrf
                <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}"
                       class="flex-1 px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('phone') border-red-400 @else border-gray-300 @enderror"
                       placeholder="+91 98765 43210">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">Update</button>
            </form>
            @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Salary card --}}
        @if($employee->salary)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Current salary</h3>
            @if($earnedSalary)
            <div class="mb-5 p-4 rounded-xl border {{ $earnedSalary['is_full_month'] ? 'bg-green-50 border-green-200' : 'bg-amber-50 border-amber-200' }}">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                    <p class="text-sm font-medium text-gray-800">This month's earned pay</p>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $earnedSalary['is_full_month'] ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $earnedSalary['progress_percent'] }}% of full salary
                    </span>
                </div>
                <p class="text-2xl font-bold text-green-700">₹ {{ number_format($earnedSalary['earned_net'], 2) }}</p>
                <p class="text-xs text-gray-600 mt-1">
                    {{ AttendanceTimeCalculator::formatHoursAndMinutes($earnedSalary['work_hours']) }} worked
                    of {{ $earnedSalary['target_hours'] }}h monthly target
                    @if(!$earnedSalary['is_full_month'])
                        · {{ AttendanceTimeCalculator::formatHoursAndMinutes($earnedSalary['remaining_hours']) }} remaining for full pay
                    @endif
                </p>
                <div class="h-2 bg-white/70 rounded-full overflow-hidden mt-3">
                    <div class="h-full rounded-full {{ $earnedSalary['is_full_month'] ? 'bg-green-500' : 'bg-teal-500' }}"
                         style="width: {{ $earnedSalary['progress_percent'] }}%"></div>
                </div>
            </div>
            @endif
            <div class="grid grid-cols-2 gap-3 text-sm">
                @php
                    $sal = $employee->salary;
                    $items = [
                        'Basic'           => $sal->basic,
                        'HRA'             => $sal->hra,
                        'Transport'       => $sal->transport,
                        'Medical'         => $sal->medical,
                        'Other allowance' => $sal->other_allowance,
                        'PF deduction'    => '-' . $sal->pf_deduction,
                        'Tax deduction'   => '-' . $sal->tax_deduction,
                        'Other deduction' => '-' . $sal->other_deduction,
                    ];
                @endphp
                @foreach($items as $label => $val)
                <div class="flex justify-between py-1.5 border-b border-gray-50">
                    <span class="text-gray-500">{{ $label }}</span>
                    <span class="font-medium {{ str_starts_with((string)$val,'-') ? 'text-red-600' : 'text-gray-800' }}">
                        ₹ {{ number_format($val, 2) }}
                    </span>
                </div>
                @endforeach
                <div class="col-span-2 flex justify-between pt-2 border-t-2 border-gray-200 font-semibold">
                    <span>Gross salary</span><span>₹ {{ number_format($sal->gross_salary, 2) }}</span>
                </div>
                <div class="col-span-2 flex justify-between font-bold text-green-700">
                    <span>Full net salary</span><span>₹ {{ number_format($sal->net_salary, 2) }}</span>
                </div>
                <p class="col-span-2 text-xs text-gray-500 mt-1">
                    Full pay requires {{ AttendanceTimeCalculator::TARGET_MONTHLY_HOURS }} net work hours per month.
                </p>
            </div>
        </div>
        @endif

        {{-- Salary history --}}
        @if($employee->salaryHistories->count())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Salary increment history</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-gray-700">
                    <thead class="text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="pb-2 text-left">Effective from</th>
                            <th class="pb-2 text-right">Gross</th>
                            <th class="pb-2 text-right">Net</th>
                            <th class="pb-2 text-left pl-4">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($employee->salaryHistories as $h)
                        <tr>
                            <td class="py-2">{{ $h->effective_from->format('d M Y') }}</td>
                            <td class="py-2 text-right">₹ {{ number_format($h->gross_salary, 2) }}</td>
                            <td class="py-2 text-right font-medium text-green-700">₹ {{ number_format($h->net_salary, 2) }}</td>
                            <td class="py-2 pl-4 text-gray-400 text-xs">{{ $h->note ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Change password link --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 flex items-center justify-between">
            <div>
                <p class="font-medium text-gray-800 text-sm">Password</p>
                <p class="text-xs text-gray-500 mt-0.5">Change your account password</p>
            </div>
            <a href="{{ route('employee.password.change') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">
                Change password
            </a>
        </div>
    </div>
</div>
@endsection
