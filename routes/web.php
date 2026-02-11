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
Route::get('/schedule/weekly', [ScheduleController::class, 'weekly'])->name('schedule.weekly');
Route::get('/schedule/upcoming', [ScheduleController::class, 'upcoming'])->name('schedule.upcoming');
Route::get('/schedule/past', [ScheduleController::class, 'past'])->name('schedule.past');
Route::delete('/schedule/{id}/delete', [ScheduleController::class, 'delete'])->name('schedule.delete');
// AutoSched routes
Route::post('/schedule/autosched', [ScheduleController::class, 'autosched'])->name('schedule.autosched');

// Report routes
Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
