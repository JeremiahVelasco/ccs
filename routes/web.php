<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\GradingComponent;

Route::middleware(['auth'])->group(function () {
    Route::get('/grade/{project}', GradingComponent::class)->name('grading.component');
});
