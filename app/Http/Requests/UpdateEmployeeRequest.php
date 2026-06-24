<?php

namespace App\Http\Requests;

use App\Models\Designation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge([
                'phone' => preg_replace('/[\s\-().]/', '', $this->phone),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'email'          => ['required', 'email', Rule::unique('employees', 'email')->ignore($this->route('employee'))],
            'phone'          => ['required', 'string', 'regex:/^\+?[0-9]{10,15}$/', Rule::unique('employees', 'phone')->ignore($this->route('employee'))],
            'gender'         => 'required|in:male,female,other',
            'dob'            => 'nullable|date|before:today|after:1900-01-01',
            'department_id'  => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'status'         => 'required|in:active,inactive',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (!$this->department_id || !$this->designation_id) {
                return;
            }

            $designation = Designation::find($this->designation_id);

            if ($designation && (int) $designation->department_id !== (int) $this->department_id) {
                $validator->errors()->add(
                    'designation_id',
                    'The selected designation does not belong to the chosen department.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'Employee name is required.',
            'email.required'          => 'Email address is required.',
            'email.email'             => 'Please enter a valid email address.',
            'email.unique'            => 'This email is already registered.',
            'phone.required'          => 'Phone number is required.',
            'phone.regex'             => 'Enter a valid phone number (10–15 digits, optional + prefix).',
            'phone.unique'            => 'This phone number is already registered.',
            'gender.required'         => 'Please select a gender.',
            'dob.before'              => 'Date of birth must be in the past.',
            'dob.after'               => 'Please enter a valid date of birth.',
            'department_id.required'  => 'Please select a department.',
            'department_id.exists'    => 'Selected department is invalid.',
            'designation_id.required' => 'Please select a designation.',
            'designation_id.exists'   => 'Selected designation is invalid.',
            'status.required'         => 'Please select a status.',
            'image.image'             => 'The file must be an image.',
            'image.mimes'             => 'Only JPG, PNG, or WebP images are allowed.',
            'image.max'               => 'Image size must not exceed 2MB.',
        ];
    }
}
