@extends('layouts.app')
@section('title','Salary Management')
@section('page-title','Salary Management')

@section('content')
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" class="flex gap-3 items-end">
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-600 mb-1">Search employee</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or Employee ID…"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg transition">Search</button>
        <a href="{{ route('admin.payroll.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
            <i class="fas fa-chart-bar text-xs"></i> Payroll report
        </a>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">Employee</th>
                    <th class="px-5 py-3 text-right">Basic</th>
                    <th class="px-5 py-3 text-right">Gross</th>
                    <th class="px-5 py-3 text-right">Net salary</th>
                    <th class="px-5 py-3 text-left">Effective from</th>
                    <th class="px-5 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($employees as $emp)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ $emp->name }}</p>
                        <p class="text-xs text-gray-400">{{ $emp->employee_id }} · {{ $emp->department->name ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3 text-right">{{ $emp->salary ? '₹ '.number_format($emp->salary->basic,2) : '—' }}</td>
                    <td class="px-5 py-3 text-right">{{ $emp->salary ? '₹ '.number_format($emp->salary->gross_salary,2) : '—' }}</td>
                    <td class="px-5 py-3 text-right font-medium text-green-700">{{ $emp->salary ? '₹ '.number_format($emp->salary->net_salary,2) : '—' }}</td>
                    <td class="px-5 py-3">{{ $emp->salary ? $emp->salary->effective_from->format('d M Y') : '—' }}</td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.salary.create', $emp) }}" class="p-1.5 rounded-lg text-gray-500 hover:bg-indigo-50 hover:text-indigo-600" title="Manage salary">
                                <i class="fas fa-pen-to-square text-sm"></i>
                            </a>
                            <a href="{{ route('admin.salary.history', $emp) }}" class="p-1.5 rounded-lg text-gray-500 hover:bg-purple-50 hover:text-purple-600" title="History">
                                <i class="fas fa-clock-rotate-left text-sm"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">No employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">{{ $employees->links() }}</div>
    @endif
</div>
@endsection
