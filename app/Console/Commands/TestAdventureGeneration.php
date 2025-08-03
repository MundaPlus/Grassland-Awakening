<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AdventureGenerationService;

class TestAdventureGeneration extends Command
{
    protected $signature = 'game:test-adventure {--seed=test123} {--road=north} {--difficulty=normal}';
    protected $description = 'Test adventure generation system';

    public function handle()
    {
        $adventureService = app(AdventureGenerationService::class);
        
        $seed = $this->option('seed');
        $road = $this->option('road');
        $difficulty = $this->option('difficulty');
        
        $this->info("Generating adventure with seed: {$seed}, road: {$road}, difficulty: {$difficulty}");
        
        $adventure = $adventureService->generateAdventure($seed, $road, $difficulty);
        
        $this->line('=== ADVENTURE DETAILS ===');
        $this->table(['Property', 'Value'], [
            ['Seed', $adventure['seed']],
            ['Road', $adventure['road']],
            ['Difficulty', $adventure['difficulty']],
            ['Weather Type', $adventure['weather']['type'] ?? 'Unknown'],
            ['Weather Name', $adventure['weather']['name'] ?? 'Unknown'],
            ['Season', $adventure['season']['season'] ?? 'Unknown'],
            ['Theme', $adventure['specialization']['theme']],
            ['Focus', $adventure['specialization']['focus']],
            ['Modifier', $adventure['modifier']['name'] ?? 'None'],
            ['Duration', $adventure['metadata']['estimated_duration'] . ' minutes'],
            ['Recommended Level', $adventure['metadata']['recommended_level']]
        ]);
        
        $this->line('=== NODE MAP ===');
        foreach ($adventure['map']['nodes'] as $level => $nodes) {
            $this->line("Level {$level}:");
            foreach ($nodes as $node) {
                $this->line("  - {$node['id']}: {$node['type']}");
            }
        }
        
        $this->line('=== CONNECTIONS ===');
        foreach ($adventure['map']['connections'] as $nodeId => $connections) {
            $this->line("{$nodeId} → " . implode(', ', $connections));
        }
        
        $this->line('=== SPECIALIZATION DETAILS ===');
        $spec = $adventure['specialization'];
        $this->line("Enemies: " . implode(', ', $spec['enemies']));
        $this->line("Materials: " . implode(', ', $spec['materials']));
        
        if ($adventure['modifier']) {
            $this->line('=== MODIFIER EFFECTS ===');
            $mod = $adventure['modifier'];
            unset($mod['name'], $mod['chance'], $mod['key']);
            foreach ($mod as $effect => $value) {
                $this->line("{$effect}: {$value}");
            }
        }

        $this->line('=== WEATHER DETAILS ===');
        $weather = $adventure['weather'];
        $this->line("Type: {$weather['type']}");
        $this->line("Name: {$weather['name']}");
        $this->line("Description: {$weather['description']}");
        $this->line("Visibility: " . ($weather['visibility'] * 100) . "%");
        $this->line("Movement Speed: " . ($weather['movement_speed'] * 100) . "%");
        $this->line("Duration: {$weather['duration_hours']} hours");
        
        if (!empty($weather['effects'])) {
            $this->line('Weather Effects:');
            foreach ($weather['effects'] as $effect => $value) {
                $this->line("  - {$effect}: {$value}");
            }
        }

        $this->line('=== SEASONAL EFFECTS ===');
        $season = $adventure['season'];
        $this->line("Current Season: {$season['name']}");
        $this->line("Duration: {$season['duration_weeks']} weeks");
        foreach ($season['effects'] as $effect => $value) {
            $this->line("  - {$effect}: {$value}");
        }
        
        $this->info('Adventure generation test completed successfully!');
    }
}
