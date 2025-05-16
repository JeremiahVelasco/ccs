<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OpenAIController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Dashboard routes
Route::get('/dashboard', [DashboardController::class, 'index']);

// Repository Routes
Route::get('/repository', [RepositoryController::class, 'index']);

// Projects Routes
Route::get('/projects', [ProjectController::class, 'index']);
Route::post('/projects/create-project', [ProjectController::class, 'store']);
Route::put('/projects/{projectId}/update', [ProjectController::class, 'update']);
Route::delete('/projects/{projectId}/delete', [ProjectController::class, 'destroy']);

// Grading Routes
// Route::post('projects/{projectId}/')

// Task Routes
Route::get('/tasks/documentation', [TaskController::class, 'getDocumentationTasks']);
Route::get('/tasks/development', [TaskController::class, 'getDevelopmentTasks']);
Route::post('/tasks/create-development-task', [TaskController::class, 'store']);
Route::put('/tasks/{taskId}/update', [TaskController::class, 'update']);
Route::delete('/tasks/{taskId}/delete', [TaskController::class, 'destroy']);

// Bayesian Routes
Route::post('/projects/{projectId}/predict', [OpenAIController::class, 'predict']);
Route::post('/projects/predict', [OpenAIController::class, 'predictAll']);

// Priority Scheduling Routes
