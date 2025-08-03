<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NPCService;
use App\Models\Player;
use App\Models\User;

class TestNPCSystem extends Command
{
    protected $signature = 'game:test-npc {--create-player} {--simulate-migration} {--train-skills} {--specialization}';
    protected $description = 'Test NPC system with skill trees and village specialization';

    public function handle()
    {
        $npcService = app(NPCService::class);

        $this->info('=== NPC SYSTEM TEST ===');

        // Create or get test player
        $player = $this->getTestPlayer();
        
        $this->line("Testing with player: {$player->character_name} (ID: {$player->id})");

        if ($this->option('create-player') || $this->option('simulate-migration')) {
            $this->testNPCGeneration($npcService, $player);
        }

        if ($this->option('train-skills')) {
            $this->testNPCSkillTraining($npcService, $player);
        }

        if ($this->option('specialization')) {
            $this->testVillageSpecialization($npcService, $player);
        }

        // Always show current village status
        $this->showVillageStatus($npcService, $player);

        $this->info('NPC system test completed successfully!');
    }

    private function getTestPlayer(): Player
    {
        // Find or create a test user and player
        $user = User::firstOrCreate(
            ['email' => 'test@grassland.com'],
            [
                'name' => 'Test Player',
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]
        );

        return $user->player ?? Player::create([
            'user_id' => $user->id,
            'character_name' => 'Test Hero',
            'level' => 5,
            'experience' => 500,
            'persistent_currency' => 2000,
            'hp' => 50,
            'max_hp' => 50,
            'ac' => 12,
            'str' => 14,
            'dex' => 12,
            'con' => 13,
            'int' => 11,
            'wis' => 15,
            'cha' => 10
        ]);
    }

    private function testNPCGeneration(NPCService $npcService, Player $player): void
    {
        $this->line('=== NPC GENERATION TEST ===');

        // Generate several NPCs
        $npcsToCreate = 8;
        $this->line("Generating {$npcsToCreate} NPCs...");

        for ($i = 0; $i < $npcsToCreate; $i++) {
            $npc = $npcService->generateRandomNPC($player);
            $this->line("Created: {$npc->name} ({$npc->profession}, {$npc->personality})");
            
            // Simulate some arriving at village
            if (rand(1, 100) <= 70) { // 70% chance to arrive
                $npcService->arriveNPCAtVillage($npc);
                $this->line("  → Arrived at village");
            }
        }

        $this->line("Generated NPCs with various professions and personalities.");
    }

    private function testNPCSkillTraining(NPCService $npcService, Player $player): void
    {
        $this->line('=== NPC SKILL TRAINING TEST ===');

        $settledNPCs = $player->getSettledNPCs();
        
        if ($settledNPCs->isEmpty()) {
            $this->warn("No settled NPCs found. Generate some NPCs first.");
            return;
        }

        foreach ($settledNPCs->take(3) as $npc) {
            $this->line("Training skills for: {$npc->name} ({$npc->profession})");
            
            // Get available skills for this NPC's profession
            $skillTrees = [
                'blacksmith' => ['basic_repair', 'advanced_repair', 'master_crafting'],
                'healer' => ['first_aid', 'advanced_medicine', 'divine_healing'],
                'trader' => ['local_goods', 'regional_network', 'international_commerce'],
                'scholar' => ['basic_knowledge', 'spell_research', 'ancient_wisdom'],
                'guard' => ['basic_training', 'advanced_tactics', 'elite_training'],
                'farmer' => ['basic_farming', 'advanced_agriculture', 'magical_crops']
            ];

            $availableSkills = $skillTrees[$npc->profession] ?? ['basic_skill'];
            
            foreach ($availableSkills as $skill) {
                if ($npc->canLearnSkill($skill)) {
                    $success = $npcService->trainNPCSkill($npc, $skill, 50);
                    if ($success) {
                        $this->line("  ✓ Learned: {$skill}");
                    } else {
                        $this->line("  ✗ Failed to learn: {$skill} (insufficient currency or prerequisites)");
                    }
                } else {
                    $this->line("  - Already knows: {$skill}");
                }
            }
        }
    }

    private function testVillageSpecialization(NPCService $npcService, Player $player): void
    {
        $this->line('=== VILLAGE SPECIALIZATION TEST ===');

        $specialization = $npcService->evaluateVillageSpecialization($player);
        
        if ($specialization) {
            $this->line("✓ Village specialization unlocked: {$specialization->getSpecializationName()}");
            $this->line("  Description: {$specialization->getSpecializationDescription()}");
            $this->line("  Level: {$specialization->level}");
            
            $bonuses = $specialization->getBonuses();
            if (!empty($bonuses)) {
                $this->line("  Bonuses:");
                foreach ($bonuses as $bonus => $value) {
                    $this->line("    - {$bonus}: {$value}");
                }
            }

            $features = $specialization->getUnlockedFeatures();
            if (!empty($features)) {
                $this->line("  Unlocked Features: " . implode(', ', $features));
            }

            // Test upgrading specialization
            if ($specialization->canUpgrade() && $player->persistent_currency >= $specialization->getUpgradeCost()) {
                $oldLevel = $specialization->level;
                $success = $npcService->upgradeVillageSpecialization($specialization);
                if ($success) {
                    $this->line("  ✓ Upgraded specialization from level {$oldLevel} to {$specialization->level}");
                }
            }
        } else {
            $settledCount = $player->getSettledNPCs()->count();
            $this->line("✗ No specialization available (need 6+ NPCs, have {$settledCount})");
            
            if ($settledCount >= 6) {
                $professions = $player->getSettledNPCs()->groupBy('profession')->map->count();
                $this->line("  Current professions: " . $professions->map(fn($count, $prof) => "{$prof}({$count})")->implode(', '));
                $this->line("  Need: guard+blacksmith (Military), trader+farmer (Trading), or scholar+healer (Magical)");
            }
        }
    }

    private function showVillageStatus(NPCService $npcService, Player $player): void
    {
        $this->line('=== VILLAGE STATUS ===');

        $villageInfo = $npcService->getVillageInfo($player);
        
        $this->table(['Property', 'Value'], [
            ['Village Type', $villageInfo['type']],
            ['Village Level', $villageInfo['level']],
            ['NPC Count', $villageInfo['npc_count']],
            ['Specializations', count($villageInfo['specializations'])],
            ['Available Services', count($villageInfo['available_services'])],
            ['Next Milestone', $villageInfo['next_milestone']['milestone']],
            ['NPCs Needed', $villageInfo['next_milestone']['npcs_needed']]
        ]);

        if (!empty($villageInfo['npcs'])) {
            $this->line('=== VILLAGE NPCS ===');
            foreach ($villageInfo['npcs'] as $npc) {
                $skillCount = $npc->skills->count();
                $skillLevel = $npc->getSkillLevel();
                $relationship = $npc->getRelationshipStatus();
                $services = implode(', ', $npc->getAvailableServices());
                
                $this->line("{$npc->name} ({$npc->profession}, {$npc->personality})");
                $this->line("  Relationship: {$relationship} ({$npc->relationship_score})");
                $this->line("  Skills: {$skillCount} skills, level {$skillLevel}");
                $this->line("  Services: {$services}");
                $this->line("  Days in village: {$npc->getDaysInVillage()}");
            }
        }

        if (!empty($villageInfo['specializations'])) {
            $this->line('=== VILLAGE SPECIALIZATIONS ===');
            foreach ($villageInfo['specializations'] as $spec) {
                $this->line("{$spec->getSpecializationName()} (Level {$spec->level})");
                $this->line("  " . $spec->getSpecializationDescription());
                
                $bonuses = $spec->getBonuses();
                if (!empty($bonuses)) {
                    foreach ($bonuses as $bonus => $value) {
                        $this->line("  {$bonus}: {$value}");
                    }
                }
            }
        }

        if (!empty($villageInfo['village_bonuses'])) {
            $this->line('=== VILLAGE BONUSES ===');
            foreach ($villageInfo['village_bonuses'] as $bonus => $value) {
                $this->line("{$bonus}: {$value}");
            }
        }
    }
}