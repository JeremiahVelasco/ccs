<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getFaculty()
    {
        $faculty = User::where('role', 'faculty')->get();

        return response()->json($faculty);
    }

    public function getStudent()
    {
        $student = User::where('role', 'student')->get();

        return response()->json($student);
    }

    public function getAdmin()
    {
        $admin = User::where('role', 'admin')->get();

        return response()->json($admin);
    }

    public function updateProfile(Request $request)
    {
        $user = User::find($request->id);

        $user->update($request->all());

        return response()->json($user);
    }
}
