@extends('layouts.app')
@section('title', 'Manage Salary')
@section('page-title')Manage Salary — {{ $employee->name }}@endsection

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
            <img src="{{ $employee->image_url }}" class="w-12 h-12 rounded-full object-cover border">
            <div>
                <p class="font-semibold text-gray-900">{{ $employee->name }}</p>
                <p class="text-sm text-gray-500">{{ $employee->employee_id }} · {{ $employee->designation->name ?? '—' }}</p>
            </div>
            @if($current)
            <div class="ml-auto text-right">
                <p class="text-xs text-gray-500">Current net salary</p>
                <p class="text-lg font-bold text-green-700">₹ {{ number_format($current->net_salary, 2) }}</p>
            </div>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.salary.store', $employee) }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                @php
                    $fields = [
                        ['name'=>'basic',           'label'=>'Basic salary',     'required'=>true],
                        ['name'=>'hra',             'label'=>'HRA',              'required'=>false],
                        ['name'=>'transport',       'label'=>'Transport',         'required'=>false],
                        ['name'=>'medical',         'label'=>'Medical',           'required'=>false],
                        ['name'=>'other_allowance', 'label'=>'Other allowance',   'required'=>false],
                        ['name'=>'pf_deduction',    'label'=>'PF deduction',      'required'=>false],
                        ['name'=>'tax_deduction',   'label'=>'Tax deduction',     'required'=>false],
                        ['name'=>'other_deduction', 'label'=>'Other deduction',   'required'=>false],
                    ];
                @endphp
                @foreach($fields as $f)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $f['label'] }} @if($f['required'])<span class="text-red-500">*</span>@endif
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400 text-sm">₹</span>
                        <input type="number" name="{{ $f['name'] }}" step="0.01" min="0"
                               value="{{ old($f['name'], $current?->{$f['name']} ?? 0) }}"
                               class="w-full pl-7 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               {{ $f['required'] ? 'required' : '' }}>
                    </div>
                    @error($f['name'])<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                @endforeach
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Effective from <span class="text-red-500">*</span></label>
                    <input type="date" name="effective_from" value="{{ old('effective_from', today()->toDateString()) }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note / reason</label>
                    <input type="text" name="note" value="{{ old('note') }}" placeholder="e.g. Annual increment"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-6 py-2.5 rounded-lg transition">Save Salary</button>
                <a href="{{ route('admin.salary.index') }}" class="text-sm text-gray-500 hover:text-gray-700 py-2">Cancel</a>
            </div>
        </form>
    </div>

    @if($history->count())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mt-5">
        <h3 class="font-semibold text-gray-800 mb-4 text-sm">Recent salary history</h3>
        <table class="w-full text-sm">
            <thead class="text-xs text-gray-500 uppercase"><tr>
                <th class="pb-2 text-left">Effective from</th>
                <th class="pb-2 text-right">Gross</th>
                <th class="pb-2 text-right">Net</th>
                <th class="pb-2 text-left pl-4">Note</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($history as $h)
                <tr>
                    <td class="py-2">{{ $h->effective_from->format('d M Y') }}</td>
                    <td class="py-2 text-right">₹ {{ number_format($h->gross_salary,2) }}</td>
                    <td class="py-2 text-right font-medium text-green-700">₹ {{ number_format($h->net_salary,2) }}</td>
                    <td class="py-2 pl-4 text-gray-400 text-xs">{{ $h->note ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
