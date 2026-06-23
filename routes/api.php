<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\API\RecommendationController;
use App\Http\Controllers\API\TreeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes use Sanctum authentication and return JSON responses
*/

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    $user->tokens()->delete();
    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'data' => [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ],
            'token' => $token,
            'token_type' => 'Bearer'
        ]
    ]);
});

// API
Route::middleware('auth:sanctum')->group(function () {

    // User
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });

    // Recommendations
    Route::prefix('recommendations')->group(function () {
        Route::post('/', [RecommendationController::class, 'getRecommendation'])
            ->middleware('role:admin,agent');
        Route::get('/', [RecommendationController::class, 'getHistory'])
            ->middleware('role:admin,agent,client');
        Route::get('/requests/{id}', [RecommendationController::class, 'getRequestStatus'])
            ->middleware('role:admin,agent,client')
            ->name('api.recommendations.requests.show');
        Route::get('/{id}', [RecommendationController::class, 'getConsultation'])
            ->middleware('role:admin,agent,client');
        Route::put('/{id}', [RecommendationController::class, 'reviseRecommendation'])
            ->middleware('role:admin,agent');
    });

    // Tree visualization
    Route::prefix('visualizations')->group(function () {
        Route::post('/trees/{caseId}', [TreeController::class, 'generateTrees'])
            ->middleware('role:admin,agent');
        Route::get('/requests/{id}', [TreeController::class, 'getRequestStatus'])
            ->middleware('role:admin,agent')
            ->name('api.visualizations.requests.show');
        Route::get('/trees/{caseId}', [TreeController::class, 'getTreeStatus'])
            ->middleware('role:admin,agent');
    });

    // statistics
    Route::get('/statistics', [RecommendationController::class, 'getStatistics'])
        ->middleware('role:admin,agent');
});
