<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // Display a listing of the users
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    // Show the form for creating a new user
    public function create()
    {
        // return view('users.create');
    }

    // Store a newly created user in storage
    public function store(Request $request)
    {
        $user = User::create($request->all());
        return response()->json($user, 201);
    }

    // Display the specified user
    public function show($id)
    {
        $user = User::find($id);
        return response()->json($user);
    }

    // Show the form for editing the specified user
    public function edit($id)
    {
        // return view('users.edit', compact('user'));
    }

    // Update the specified user in storage
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        $user->update($request->all());
        return response()->json($user);
    }

    // Remove the specified user from storage
    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();
        return response()->json(null, 204);
    }
}
