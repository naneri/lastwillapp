<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::orderByDesc('created_at')->paginate(20),
        ]);
    }
}
