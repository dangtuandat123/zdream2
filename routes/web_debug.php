<?php

use App\Services\ModelManager;

Route::get('/debug/models', function () {
    $modelManager = app(ModelManager::class);
    
    // Fetch models
    $models = $modelManager->fetchModels(true); // Force refresh
    $grouped = $modelManager->groupByProvider($models);
    
    return response()->json([
        'total_models' => count($models),
        'providers' => array_keys($grouped),
        'provider_counts' => array_map('count', $grouped),
        'sample_model' => $models[0] ?? null,
        'all_models' => $models,
    ], 200, [], JSON_PRETTY_PRINT);
});
