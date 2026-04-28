<?php

use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::post('analytics/ask', [AnalyticsController::class, 'ask'])->name('analytics.ask');
    Route::get('analytics/conversations', [AnalyticsController::class, 'conversations'])->name('analytics.conversations');
    Route::get('analytics/conversations/{conversationId}', [AnalyticsController::class, 'conversation'])->name('analytics.conversation');
});
