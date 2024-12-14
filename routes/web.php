<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FriendshipsController;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    if (Auth::check()) {
        // call the controller ChatController method index
        return redirect()->route('dashboard');
    }
    else {
        return view('auth.login');
    }
});



Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [ChatController::class, 'index'])->name('dashboard');
    Route::get('/chat/{discussion}/messages', [ChatController::class, 'getMessages']);
    Route::post('/chat/{discussion}/messages', [ChatController::class, 'storeMessage']);
    Route::post('/chat/{discussion}/capsule', [ChatController::class, 'storeCapsule']);
    Route::delete('/chat/{discussion}/leave', [ChatController::class, 'leaveChat']);
    Route::post('/chats', [ChatController::class, 'storeChat'])->name('chats.store');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('friends', FriendshipsController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/friends/pending', [FriendshipsController::class, 'pending'])->name('friends.pending');
    Route::post('/friends/{friendship_id}/accept', [FriendshipsController::class, 'accept'])->name('friends.accept');
    Route::post('/friends/{friendship_id}/decline', [FriendshipsController::class, 'decline'])->name('friends.decline');
    Route::post('/friends/{friend}/block', [FriendshipsController::class, 'block'])->name('friends.block');


});


require __DIR__.'/auth.php';
