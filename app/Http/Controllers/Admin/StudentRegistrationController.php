<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StudentRegistrationController extends Controller
{
    public function create()
    {
        return view('admin.register-student');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'psu_id' => 'required|string|unique:users',
            'email' => 'required|string|email|unique:users',
            'course' => 'required|string',
            'password' => 'required|string|confirmed|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'psu_id' => $request->psu_id,
            'email' => $request->email,
            'course' => $request->course,
            'role' => 'student',
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Student account created successfully.');
    }
}
