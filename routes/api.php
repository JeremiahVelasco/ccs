<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\OpenAIController;
use App\Http\Controllers\PanelistController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectPredictionController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\RubricController;
use App\Http\Controllers\StudentGroupController;
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

    // Bayesian Network Prediction Routes
    Route::prefix('predictions')->group(function () {
        Route::get('/projects/{project}/predict', [ProjectPredictionController::class, 'predict']);
        Route::post('/projects/{project}/refresh', [ProjectPredictionController::class, 'refreshPrediction']);
        Route::get('/projects/{project}/history', [ProjectPredictionController::class, 'getPredictionHistory']);
        Route::post('/projects/bulk-predict', [ProjectPredictionController::class, 'bulkPredict']);
        Route::get('/system/health', [ProjectPredictionController::class, 'getSystemHealth']);
    });

    // User Routes
    Route::get('/users/faculty', [UserController::class, 'getFaculty']);
    Route::get('/users/students', [UserController::class, 'getStudents']);
    Route::get('/users/admin', [UserController::class, 'getAdmin']);
    Route::put('/users/update-profile', [UserController::class, 'updateProfile']);

    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Repository Routes
    Route::get('/repository', [RepositoryController::class, 'index']);
    Route::get('/repository/{projectId}', [RepositoryController::class, 'show']);

    // Projects Routes
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{projectId}', [ProjectController::class, 'show']);
    Route::post('/projects/create-project', [ProjectController::class, 'store']);
    Route::put('/projects/{projectId}/update', [ProjectController::class, 'update']);
    Route::delete('/projects/{projectId}/delete', [ProjectController::class, 'destroy']);

    // Activities Routes
    Route::get('/activities', [ActivityController::class, 'index']);
    Route::get('/activities/{activityId}', [ActivityController::class, 'show']);

    // Group Routes
    Route::get('/groups', [GroupController::class, 'index']);
    Route::get('/groups/{groupId}', [GroupController::class, 'show']);
    Route::post('/groups/create-group', [GroupController::class, 'store']);
    Route::put('/groups/{groupId}/update', [GroupController::class, 'update']);
    Route::delete('/groups/{groupId}/delete', [GroupController::class, 'destroy']);

    // Student's Group Routes
    Route::get('/my-group', [StudentGroupController::class, 'index']);
    Route::post('/my-group/create-group', [StudentGroupController::class, 'store']);
    Route::put('/my-group/update', [StudentGroupController::class, 'update']);
    Route::delete('/my-group/delete', [StudentGroupController::class, 'destroy']);
    Route::get('/my-group/students-without-group', [StudentGroupController::class, 'getStudentsWithoutGroup']);
    Route::post('/my-group/add-member', [StudentGroupController::class, 'addMember']);
    Route::post('/my-group/remove-member', [StudentGroupController::class, 'removeMember']);
    Route::post('/my-group/join-group', [StudentGroupController::class, 'joinGroup']);
    Route::post('/my-group/leave-group', [StudentGroupController::class, 'leaveGroup']);

    // Task Routes
    Route::get('/tasks/documentation', [TaskController::class, 'getDocumentationTasks']);
    Route::get('/tasks/development', [TaskController::class, 'getDevelopmentTasks']);
    Route::get('/tasks/filter/{id}/{type}/{status}', [TaskController::class, 'filterTasks']);
    Route::post('/tasks/create-development-task', [TaskController::class, 'store']);
    Route::put('/tasks/{taskId}/update', [TaskController::class, 'update']);
    Route::delete('/tasks/{taskId}/delete', [TaskController::class, 'destroy']);

    // Rubric Management Routes
    Route::prefix('rubrics')->group(function () {
        Route::get('/', [RubricController::class, 'index']);
        Route::post('/', [RubricController::class, 'store']);
        Route::get('/{id}', [RubricController::class, 'show']);
        Route::get('/type/{type}', [RubricController::class, 'getByType']);
    });

    // Panelist Management Routes
    Route::prefix('panelists')->group(function () {
        Route::get('/', [PanelistController::class, 'index']);
        Route::post('/', [PanelistController::class, 'store']);
        Route::get('/{id}', [PanelistController::class, 'show']);
        Route::put('/{id}', [PanelistController::class, 'update']);
        Route::get('/{id}/dashboard', [PanelistController::class, 'getDashboard']);
    });

    // Evaluation Routes
    Route::prefix('evaluations')->group(function () {
        Route::get('/', [EvaluationController::class, 'index']);
        Route::post('/', [EvaluationController::class, 'store']);
        Route::get('/{id}', [EvaluationController::class, 'show']);
        Route::put('/{id}', [EvaluationController::class, 'update']);

        // Panelist-specific evaluation routes
        Route::get('/panelist/{panelistId}', [EvaluationController::class, 'getForPanelist']);
        Route::post('/form', [EvaluationController::class, 'createEvaluationForm']);

        // Evaluation summaries
        Route::get('/summary/{evaluableType}/{evaluableId}', [EvaluationController::class, 'getEvaluationSummary']);
    });
});
