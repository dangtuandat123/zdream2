<?php

namespace Database\Seeders;

use App\Models\Inspiration;
use Illuminate\Database\Seeder;

class InspirationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = base_path('output3.json');

        if (!file_exists($jsonPath)) {
            $this->command->error('output3.json not found!');
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        if (!$data) {
            $this->command->error('Failed to parse JSON file!');
            return;
        }

        $count = 0;
        foreach ($data as $item) {
            Inspiration::create([
                'image_url' => $item['image_url'],
                'prompt' => $item['prompt'],
                'ref_images' => $item['ref_images'] ?? [],
                'is_active' => true,
            ]);
            $count++;
        }

        $this->command->info("Imported {$count} inspirations successfully!");
    }
}
