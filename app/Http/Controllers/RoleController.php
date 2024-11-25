<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class RoleController extends Controller
{
    public function attach(Request $request, User $user)
    {
        $roled = request()->input('role');
        $user->roles()->attach($roled);
        return back();
    }

    public function detach(Request $request, User $user)
    {
        $roled = request()->input('role');
        $user->roles()->detach($roled);
        return back();
    }
}
