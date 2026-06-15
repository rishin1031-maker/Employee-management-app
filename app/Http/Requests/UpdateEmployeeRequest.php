<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'email'          => [
                'required',
                'email',
                Rule::unique('employees', 'email')->ignore($this->route('employee')),
            ],
            'phone'          => 'nullable|string|max:20',
            'gender'         => 'required|in:male,female,other',
            'dob'            => 'nullable|date|before:today',
            'department_id'  => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'status'         => 'required|in:active,inactive',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}