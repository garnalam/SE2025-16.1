<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowerController extends Controller
{
    public function store(User $user)
    {
        if (Auth::id() !== $user->id) {
            Auth::user()->followings()->attach($user->id);
        }
        return back();
    }

    public function destroy(User $user)
    {
        Auth::user()->followings()->detach($user->id);
        return back();
    }
}