<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:employees,email',
            'phone'          => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'gender'         => 'required|in:male,female,other',
            'dob'            => 'nullable|date|before:today|after:1900-01-01',
            'department_id'  => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'status'         => 'required|in:active,inactive',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required'           => 'Employee name is required.',
            'email.required'          => 'Email address is required.',
            'email.email'             => 'Please enter a valid email address.',
            'email.unique'            => 'This email is already registered.',
            'phone.regex'             => 'Phone number can only contain digits, spaces, +, - and ().',
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