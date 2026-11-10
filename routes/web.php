<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ticketController;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;

Route::bind('ticket', fn (string $value): Ticket => Ticket::whereNull('parent_id')->findOrFail($value));

/*
 * Start tracing a browser action here: named routes connect Blade forms/links
 * to controller methods. The web middleware provides sessions and CSRF checks.
 * Guest routes are for signed-out users; auth routes require a login, while
 * TicketPolicy still decides which individual ticket/actions that user may access.
 * Laravel resolves each {ticket} from its primary ID before calling the controller.
 */
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'create'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('register.post');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [ticketController::class, 'index'])->name('index');
    Route::get('/create', [ticketController::class, 'create'])->name('create');
    Route::post('/tickets', [ticketController::class, 'store'])->name('ticket.store');
    Route::post('/tickets/{ticket}/replies', [ticketController::class, 'storeReply'])
        ->name('ticket.reply.store');

    Route::get('/tickets/{ticket}', [ticketController::class, 'show'])->name('ticket.show');

    Route::get('/edit/{ticket}', [ticketController::class, 'edit'])->name('ticket.edit');
    Route::put('/tickets/{ticket}', [ticketController::class, 'update'])->name('ticket.update');
    Route::delete('/tickets/{ticket}', [ticketController::class, 'destroy'])->name('ticket.destroy');
    Route::patch('/tickets/{ticket}/status', [ticketController::class, 'updateStatus'])
        ->name('ticket.status.update');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout.destroy');

});
