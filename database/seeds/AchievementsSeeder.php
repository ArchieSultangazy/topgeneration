<?php

use Illuminate\Database\Seeder;
use App\Models\Achievement;

class AchievementsSeeder extends Seeder
{
    private $table = 'achievements';
    /**
     * @return bool
     */
    public function run()
    {
        $achievementsDatasetPath = database_path('seeds/datasets/achievements.json');

        if ($achievementsDataset = file_get_contents($achievementsDatasetPath)) {
            if (!$data = json_decode($achievementsDataset, true)) {
                return false;
            }

            Achievement::truncate();

            $insert = collect($data)->map(function ($item) {
                return [
                    'ru_name'   => $item['ru_name'],
                    'kk_name'   => $item['kk_name'],
                    'en_name'   => $item['en_name'],
                    'key'       => $item['key'],
                    'points'    => $item['points'],
                ];
            })->toArray();

            try {
                \DB::table($this->table)->insert($insert);
            } catch (Exception $e) {
                Log::info($e);
                dump($this->table . ': ' . $e);
            }
        }
    }
}
