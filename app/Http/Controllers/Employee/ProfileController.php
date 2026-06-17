<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $employee = Auth::guard('employee')->user()
            ->load([
                'department',
                'designation',
                'salary',
                'salaryHistories',
                'leaveBalance',
            ]);
    
        return view('employee.profile.index', compact('employee'));
    }

    public function updatePhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20|regex:/^[0-9+\-\s()]+$/',
        ], [
            'phone.regex' => 'Phone number can only contain digits, spaces, +, - and ().',
        ]);

        Auth::guard('employee')->user()->update(['phone' => $request->phone]);

        return back()->with('success', 'Phone number updated successfully.');
    }
}
