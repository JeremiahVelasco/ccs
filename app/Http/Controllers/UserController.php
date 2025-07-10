<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json($users);
    }

    public function getFaculty()
    {
        $faculty = User::role('faculty')->get();

        return response()->json($faculty);
    }

    public function getStudents()
    {
        $student = User::role('student')->get();

        return response()->json($student);
    }

    public function getAdmin()
    {
        $admin = User::role('super_admin')->get();

        return response()->json($admin);
    }

    public function updateProfile(Request $request)
    {
        $user = User::find($request->id);

        $user->update($request->all());

        return response()->json($user);
    }
}
