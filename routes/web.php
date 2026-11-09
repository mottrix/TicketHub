<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\messageController;
use App\Http\Controllers\ticketController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'create'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->name('register.post');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [ticketController::class, 'index'])->name('index');
    Route::get('/create', [ticketController::class, 'create'])->name('create');
    Route::post('/tickets', [ticketController::class, 'store'])->name('ticket.store');

    Route::get('/tickets/{ticket}', [ticketController::class, 'show'])->name('ticket.show'); // we need to get message

    Route::get('/edit/{ticket}', [ticketController::class, 'edit'])->name('ticket.edit');
    Route::put('/tickets/{ticket}', [ticketController::class, 'update'])->name('ticket.update');
    Route::delete('/tickets/{ticket}', [ticketController::class, 'destroy'])->name('ticket.destroy');
    Route::patch('/tickets/{ticket}/status', [ticketController::class, 'updateStatus'])
        ->name('ticket.status.update');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout.destroy');

    Route::post('/tickets/{ticket}/messages', [messageController::class, 'store'])
        ->name('ticket.message.store');
});
