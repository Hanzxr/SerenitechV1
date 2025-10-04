<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StudentImportController extends Controller
{
    public function index()
    {
        return view('admin.upload-students');
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = fopen($request->file('csv_file')->getRealPath(), 'r');

        // Skip header row
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            User::create([
                'name' => $row[0],
                'email' => $row[1],
                'password' => Hash::make($row[2]),
                'psu_id' => $row[3],
                'course' => $row[4],
                'role' => 'student', // default role
            ]);
        }

        fclose($file);

        return back()->with('success', 'Students uploaded successfully.');
    }

    
}
