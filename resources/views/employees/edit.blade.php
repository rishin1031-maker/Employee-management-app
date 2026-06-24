@extends('layouts.app')
@section('title', 'Edit Employee')
@section('page-title', 'Edit Employee')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.employees.update', $employee) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf @method('PUT')

            {{-- Profile Image --}}
            <div class="flex items-center gap-5">
                <img id="preview" src="{{ $employee->image_url }}"
                     class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Profile Photo</label>
                    <input type="file" name="image" id="imageInput" accept="image/*"
                           class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                    <p class="text-xs text-gray-400 mt-1">Leave blank to keep current photo</p>
                    @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $employee->name) }}"
                           class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @else border-gray-300 @enderror">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}"
                           class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @else border-gray-300 @enderror">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone', $employee->phone ?? '') }}"
                           placeholder="e.g. 9876543210"
                           class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500
                                @error('phone') border-red-400 @else border-gray-300 @enderror">
                    @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-1">10–15 digits; spaces and dashes are allowed</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender <span class="text-red-500">*</span></label>
                    <select name="gender"
                            class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('gender') border-red-400 @else border-gray-300 @enderror">
                        <option value="">— Select —</option>
                        @foreach(['male','female','other'] as $g)
                            <option value="{{ $g }}" {{ old('gender', $employee->gender) === $g ? 'selected' : '' }}>{{ ucfirst($g) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob', $employee->dob ?? '') }}"
                        class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500
                                @error('dob') border-red-400 @else border-gray-300 @enderror">
                    @error('dob')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status"
                            class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500
                                @error('status') border-red-400 @else border-gray-300 @enderror">
                        <option value="active"   {{ old('status', $employee->status ?? 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $employee->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                @include('employees.partials.dept-desig-selects', [
                    'selectedDepartmentId'  => $employee->department_id,
                    'selectedDesignationId' => $employee->designation_id,
                ])
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-6 py-2.5 rounded-lg transition">
                    Update Employee
                </button>

                <button onclick="document.getElementById('reset-modal').classList.remove('hidden')"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                    <i class="fas fa-key mr-1.5"></i> Reset Password
                </button>

                <a href="{{ route('admin.employees.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Image preview
document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const maxSize = 2 * 1024 * 1024;
    if (file.size > maxSize) {
        alert('Image is too large. Maximum size is 2MB. Please choose a smaller image.');
        this.value = '';
        document.getElementById('preview').src = '{{ asset("images/default-avatar.png") }}';
        return;
    }

    const reader = new FileReader();
    reader.onload = (ev) => document.getElementById('preview').src = ev.target.result;
    reader.readAsDataURL(file);
});
</script>

{{-- Password reset modal --}}
<div id="reset-modal"
     class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-sm shadow-xl">
        <h3 class="font-semibold text-gray-900 mb-4">
            Reset employee password
        </h3>

        <form method="POST"
              action="{{ route('admin.employees.reset-password', $employee) }}"
              class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    New Password
                </label>
                <input type="password"
                       name="new_password"
                       required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Confirm Password
                </label>
                <input type="password"
                       name="new_password_confirmation"
                       required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium py-2.5 rounded-lg transition">
                    Reset
                </button>

                <button type="button"
                        onclick="document.getElementById('reset-modal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2.5 rounded-lg transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
