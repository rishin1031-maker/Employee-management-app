@extends('layouts.app')
@section('title', 'Salary History')
@section('page-title')Salary History — {{ $employee->name }}@endsection

@section('content')
<div class="max-w-4xl">

    {{-- Employee Header --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-5 flex items-center gap-4">
        <img src="{{ $employee->image_url }}" class="w-14 h-14 rounded-full object-cover border-2 border-gray-200">
        <div>
            <h2 class="font-semibold text-gray-900">{{ $employee->name }}</h2>
            <p class="text-sm text-gray-500">
                <span class="font-medium text-indigo-600">{{ $employee->employee_id }}</span>
                · {{ $employee->designation->name ?? '—' }}
                · {{ $employee->department->name ?? '—' }}
            </p>
        </div>
        <a href="{{ route('admin.salary.create', $employee) }}"
           class="ml-auto bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> Update Salary
        </a>
    </div>

    {{-- History Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Salary increment history</h3>
            <span class="text-xs text-gray-400">{{ $history->total() }} record(s)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-700">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">Effective From</th>
                        <th class="px-6 py-3 text-right">Basic</th>
                        <th class="px-6 py-3 text-right">HRA</th>
                        <th class="px-6 py-3 text-right">Transport</th>
                        <th class="px-6 py-3 text-right">Medical</th>
                        <th class="px-6 py-3 text-right">Other Allow.</th>
                        <th class="px-6 py-3 text-right border-l border-gray-100">PF</th>
                        <th class="px-6 py-3 text-right">Tax</th>
                        <th class="px-6 py-3 text-right">Other Deduct.</th>
                        <th class="px-6 py-3 text-right border-l border-gray-100">Gross</th>
                        <th class="px-6 py-3 text-right">Net</th>
                        <th class="px-6 py-3 text-left">Note</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($history as $h)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 font-medium text-gray-900 whitespace-nowrap">
                            {{ $h->effective_from->format('d M Y') }}
                            @if($loop->first)
                                <span class="ml-1.5 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Current</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right">₹ {{ number_format($h->basic, 2) }}</td>
                        <td class="px-6 py-3 text-right">₹ {{ number_format($h->hra, 2) }}</td>
                        <td class="px-6 py-3 text-right">₹ {{ number_format($h->transport, 2) }}</td>
                        <td class="px-6 py-3 text-right">₹ {{ number_format($h->medical, 2) }}</td>
                        <td class="px-6 py-3 text-right">₹ {{ number_format($h->other_allowance, 2) }}</td>
                        <td class="px-6 py-3 text-right text-red-500 border-l border-gray-100">₹ {{ number_format($h->pf_deduction, 2) }}</td>
                        <td class="px-6 py-3 text-right text-red-500">₹ {{ number_format($h->tax_deduction, 2) }}</td>
                        <td class="px-6 py-3 text-right text-red-500">₹ {{ number_format($h->other_deduction, 2) }}</td>
                        <td class="px-6 py-3 text-right font-medium border-l border-gray-100">₹ {{ number_format($h->gross_salary, 2) }}</td>
                        <td class="px-6 py-3 text-right font-bold text-green-700 whitespace-nowrap">₹ {{ number_format($h->net_salary, 2) }}</td>
                        <td class="px-6 py-3 text-gray-400 text-xs">{{ $h->note ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="px-6 py-12 text-center text-gray-400">
                            No salary history found for this employee.
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                @if($history->count())
                {{-- Summary footer for current salary --}}
                @php $latest = $history->first(); @endphp
                <tfoot class="bg-indigo-50 border-t-2 border-indigo-100 text-xs font-semibold text-indigo-700">
                    <tr>
                        <td class="px-6 py-3">Current salary</td>
                        <td class="px-6 py-3 text-right">₹ {{ number_format($latest->basic, 2) }}</td>
                        <td colspan="4"></td>
                        <td class="px-6 py-3 text-right text-red-500 border-l border-indigo-100">
                            -₹ {{ number_format($latest->pf_deduction + $latest->tax_deduction + $latest->other_deduction, 2) }}
                        </td>
                        <td colspan="2"></td>
                        <td class="px-6 py-3 text-right border-l border-indigo-100">₹ {{ number_format($latest->gross_salary, 2) }}</td>
                        <td class="px-6 py-3 text-right text-green-700">₹ {{ number_format($latest->net_salary, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($history->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $history->links() }}</div>
        @endif
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.salary.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            ← Back to salary list
        </a>
    </div>
</div>
@endsection