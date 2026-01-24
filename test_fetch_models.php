<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$service = new \App\Services\OpenRouterService();

echo "=== Test Image Generation ===\n\n";

// Fetch models
echo "1. Fetching image models...\n";
$models = $service->fetchImageModels(true);
echo "   Found " . count($models) . " models\n";

if (!empty($models)) {
    echo "\n2. Available models:\n";
    foreach (array_slice($models, 0, 10) as $m) {
        echo "   - {$m['id']} (image_config: " . ($m['supports_image_config'] ? 'Yes' : 'No') . ")\n";
    }
}

// Lấy style và test
$style = \App\Models\Style::first();
if ($style) {
    echo "\n3. Testing with style: {$style->name}\n";
    echo "   Model: {$style->openrouter_model_id}\n";
    
    echo "\n4. Generating image...\n";
    $result = $service->generateImage($style);
    
    if ($result['success']) {
        echo "   SUCCESS!\n";
        echo "   Image length: " . strlen($result['image_base64']) . " chars\n";
    } else {
        echo "   FAILED: " . ($result['error'] ?? 'Unknown') . "\n";
    }
} else {
    echo "\nNo style found in database\n";
}
