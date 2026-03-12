<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\ConversationController;
use App\Http\Controllers\Dashboard\DashboardController;

// Redirige la raíz al login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas para usuarios NO autenticados (guest)
Route::middleware('guest')->group(function () {

    // Autenticación
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

    // Registro
    Route::get('/register', [LoginController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [LoginController::class, 'register'])->name('register.submit');

    // Recuperar contraseña
    Route::get('/forgot-password', fn () => view('auth.forgot-password'))->name('password.request');
    Route::post('/forgot-password', [LoginController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [LoginController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [LoginController::class, 'resetPassword'])->name('password.update');
});

// Rutas para usuarios autenticados
Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Chat y conversaciones
    Route::post('/chat', [ConversationController::class, 'createConversation'])->name('chat.create');
    Route::post('/chat/{chatId}/message', [ConversationController::class, 'storeMessage'])->name('chat.message');
    Route::get('/chat/{chatId}/messages', [ConversationController::class, 'getMessages'])->name('chat.messages');
});
