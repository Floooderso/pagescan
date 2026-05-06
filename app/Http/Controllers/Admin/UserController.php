<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount('leads')->orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['role' => 'Du kannst deine eigene Rolle nicht ändern.']);
        }

        $request->validate([
            'role' => 'required|in:admin,agent',
        ]);

        $user->update(['role' => $request->role]);

        return back()->with('success', 'Rolle von ' . $user->name . ' wurde aktualisiert.');
    }
}
