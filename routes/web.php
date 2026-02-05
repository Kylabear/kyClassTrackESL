<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect()->route('schedule.index');
});

// Schedule routes
Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
Route::post('/schedule/save', [ScheduleController::class, 'save'])->name('schedule.save');
Route::post('/schedule/unlock', [ScheduleController::class, 'unlock'])->name('schedule.unlock');

// Report routes
Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
