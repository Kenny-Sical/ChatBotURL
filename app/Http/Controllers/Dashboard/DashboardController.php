<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $chats = $this->getChats();

        return view('dashboard', compact('chats'));
    }

    public function getChats()
    {
        return Chat::where('user_id', Auth::id())
            ->orderByDesc('updated_at')
            ->get(['id', 'title', 'updated_at']);
    }
}
