<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ChatbotController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Document routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('documents', DocumentController::class);

    // Chatbot routes
    Route::get('documents/{document}/chat', [ChatbotController::class, 'showChatInterface'])
        ->name('documents.chat');
    Route::post('documents/{document}/ask', [ChatbotController::class, 'askQuestion'])
        ->name('documents.ask');
});

// Chat routes
Route::middleware(['auth'])->group(function () {
    Route::get('/chatbot/questions', [ChatbotController::class, 'getAllQuestions'])->name('chatbot.questions');
    Route::inertia('/faq', 'Chatbot/Questions')->name('chatbot.faq');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
