<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\GradingComponent;
use App\Http\Controllers\FileController;

Route::middleware(['auth'])->group(function () {
    Route::get('/grade/{project}', GradingComponent::class)->name('grading.component');
    Route::get('/serve-task-file/{taskId}', [FileController::class, 'serveTaskFile'])->name('view.task.file');
});
