<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\OpenAIController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // User Routes
    Route::get('/users/faculty', [UserController::class, 'getFaculty']);
    Route::get('/users/student', [UserController::class, 'getStudent']);
    Route::get('/users/admin', [UserController::class, 'getAdmin']);
    Route::put('/users/update-profile', [UserController::class, 'updateProfile']);

    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Repository Routes
    Route::get('/repository', [RepositoryController::class, 'index']);

    // Projects Routes
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects/create-project', [ProjectController::class, 'store']);
    Route::put('/projects/{projectId}/update', [ProjectController::class, 'update']);
    Route::delete('/projects/{projectId}/delete', [ProjectController::class, 'destroy']);

    // Activities Routes
    Route::get('/activities', [ActivityController::class, 'index']);

    // Group Routes
    Route::get('/groups', [GroupController::class, 'index']);
    Route::get('/groups/{groupId}', [GroupController::class, 'show']);
    Route::post('/groups/create-group', [GroupController::class, 'store']);
    Route::put('/groups/{groupId}/update', [GroupController::class, 'update']);
    Route::delete('/groups/{groupId}/delete', [GroupController::class, 'destroy']);

    // Task Routes
    Route::get('/tasks/documentation', [TaskController::class, 'getDocumentationTasks']);
    Route::get('/tasks/development', [TaskController::class, 'getDevelopmentTasks']);
    Route::post('/tasks/create-development-task', [TaskController::class, 'store']);
    Route::put('/tasks/{taskId}/update', [TaskController::class, 'update']);
    Route::delete('/tasks/{taskId}/delete', [TaskController::class, 'destroy']);

    // Bayesian Routes
    Route::post('/projects/{projectId}/predict', [OpenAIController::class, 'predict']);
    Route::post('/projects/predict', [OpenAIController::class, 'predictAll']);
});
