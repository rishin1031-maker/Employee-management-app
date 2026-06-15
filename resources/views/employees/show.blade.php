@extends('layouts.app')
@section('title', $employee->name)
@section('page-title', 'Employee Details')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-400 px-6 py-8 flex items-center gap-5">
            <img src="{{ $employee->image_url }}" alt="{{ $employee->name }}"
                 class="w-20 h-20 rounded-full object-cover border-4 border-white shadow">
            <div class="text-white">
                <h2 class="text-xl font-bold">{{ $employee->name }}</h2>
                <p class="text-indigo-100 text-sm">{{ $employee->designation->name ?? '—' }}</p>
                <span class="mt-1 inline-block px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $employee->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ ucfirst($employee->status) }}
                </span>
            </div>
        </div>

        {{-- Details --}}
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
            @php
                $fields = [
                    'Email'       => $employee->email,
                    'Phone'       => $employee->phone ?? '—',
                    'Gender'      => ucfirst($employee->gender),
                    'Date of Birth' => $employee->dob ? \Carbon\Carbon::parse($employee->dob)->format('d M Y') : '—',
                    'Department'  => $employee->department->name ?? '—',
                    'Designation' => $employee->designation->name ?? '—',
                    'Member Since'=> $employee->created_at->format('d M Y'),
                ];
            @endphp
            @foreach($fields as $label => $value)
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-0.5">{{ $label }}</p>
                <p class="text-gray-800 font-medium">{{ $value }}</p>
            </div>
            @endforeach
        </div>

        <div class="px-6 pb-6 flex gap-3">
            <a href="{{ route('employees.edit', $employee) }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                <i class="fas fa-pen-to-square mr-1.5"></i> Edit
            </a>
            <a href="{{ route('employees.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-5 py-2 rounded-lg transition">
                Back to List
            </a>
        </div>
    </div>
</div>
@endsection