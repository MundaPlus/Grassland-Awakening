<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AdventureGenerationService;
use App\Services\WeatherService;
use App\Services\NPCService;
use App\Services\CombatService;
use App\Services\AchievementService;
use App\Services\ReputationService;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class GameController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function dashboard()
    {
        $player = $this->getOrCreatePlayer();
        $weatherService = app(WeatherService::class);
        $npcService = app(NPCService::class);
        $achievementService = app(AchievementService::class);
        $reputationService = app(ReputationService::class);

        $data = [
            'player' => $player,
            'weather' => $weatherService->getCurrentWeather($player),
            'season' => $weatherService->getCurrentSeason(),
            'village_info' => $npcService->getVillageInfo($player),
            'achievements' => $achievementService->getPlayerAchievements($player),
            'reputations' => $reputationService->getAllPlayerReputations($player),
            'reputation_bonuses' => $reputationService->getReputationBonuses($player)
        ];

        return view('game.dashboard', $data);
    }

    public function village()
    {
        $player = $this->getOrCreatePlayer();
        $npcService = app(NPCService::class);
        $reputationService = app(ReputationService::class);

        // Create village object from player data
        $villageData = $player->village_data ? json_decode($player->village_data, true) : [
            'name' => 'New Village',
            'level' => 1,
            'description' => 'A fresh start in the grasslands'
        ];

        $village = (object) array_merge($villageData, [
            'specializations' => collect([]), // Empty for now, will be populated later
        ]);

        $data = [
            'player' => $player,
            'village' => $village,
            'npcs' => $player->npcs ?? collect([]),
            'reputations' => $reputationService->getAllPlayerReputations($player)
        ];

        return view('game.village', $data);
    }

    public function adventures()
    {
        $player = $this->getOrCreatePlayer();
        $weatherService = app(WeatherService::class);

        // Get adventures with proper variable names
        $activeAdventures = $player->adventures()->where('status', 'active')->get();
        $availableAdventures = $player->adventures()->where('status', 'available')->get();
        $currentWeather = $weatherService->getCurrentWeather($player);

        $data = [
            'player' => $player,
            'activeAdventures' => $activeAdventures,
            'availableAdventures' => $availableAdventures,
            'weatherEffects' => $currentWeather['effects'] ?? null,
            'completedAdventures' => $player->adventures()->where('status', 'completed')->latest()->take(5)->get()
        ];

        return view('game.adventures', $data);
    }

    public function generateAdventure(Request $request)
    {
        $request->validate([
            'seed' => 'nullable|string',
            'difficulty' => 'nullable|in:easy,medium,hard,expert',
            'road_type' => 'nullable|string'
        ]);

        $player = $this->getOrCreatePlayer();
        
        // Check if player has enough currency
        $cost = 10;
        if ($player->persistent_currency < $cost) {
            return back()->with('error', 'Not enough gold to generate adventure. Cost: ' . $cost . ' gold.');
        }

        // Deduct cost
        $player->decrement('persistent_currency', $cost);

        $adventureService = app(AdventureGenerationService::class);
        $seed = $request->seed ?: 'player_' . $player->id . '_' . time();
        
        // Determine difficulty based on player level if not specified
        $difficulty = $request->difficulty;
        if (!$difficulty) {
            if ($player->level >= 10) {
                $difficulty = 'hard';
            } elseif ($player->level >= 5) {
                $difficulty = 'medium';
            } else {
                $difficulty = 'easy';
            }
        }

        $roadType = $request->road_type ?: 'forest_path';
        
        $adventureData = $adventureService->generateAdventure(
            $seed,
            $roadType,
            $difficulty,
            null,
            null,
            true // Use real weather
        );

        // Create adventure record
        $adventure = $player->adventures()->create([
            'seed' => $seed,
            'road' => $roadType,
            'difficulty' => $difficulty,
            'generated_map' => $adventureData,
            'current_level' => 1,
            'current_node_id' => 'start',
            'status' => 'available',
            'title' => $adventureData['title'] ?? 'Generated Adventure',
            'description' => $adventureData['description'] ?? 'A procedurally generated adventure'
        ]);

        return redirect()->route('game.adventures')
            ->with('success', 'Adventure generated successfully! Cost: ' . $cost . ' gold.');
    }

    public function startAdventure(Request $request, $adventureId)
    {
        $player = $this->getOrCreatePlayer();
        $adventure = $player->adventures()->findOrFail($adventureId);
        
        // Check if adventure can be started
        if ($adventure->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'This adventure is not available to start.'
            ]);
        }
        
        // Check if player already has an active adventure
        $activeAdventure = $player->adventures()->where('status', 'active')->first();
        if ($activeAdventure && $activeAdventure->id !== $adventure->id) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active adventure! Complete or abandon it first.'
            ]);
        }

        // Start the adventure
        $adventure->update([
            'status' => 'active',
            'current_level' => 1,
            'current_node_id' => 'start'
        ]);

        // Process reputation gain for starting adventure
        $reputationService = app(ReputationService::class);
        $reputationService->processGameEvent($player, 'adventure_started', ['road' => $adventure->road]);

        return response()->json([
            'success' => true,
            'message' => 'Adventure started! Good luck on the ' . ucfirst($adventure->road) . ' road.',
            'adventure_id' => $adventure->id
        ]);
    }

    public function showAdventure($adventureId)
    {
        $player = $this->getOrCreatePlayer();
        $adventure = $player->adventures()->findOrFail($adventureId);
        
        if ($adventure->status !== 'active') {
            return redirect()->route('game.adventures')
                ->with('error', 'This adventure is no longer active.');
        }

        $combatService = app(CombatService::class);
        $weatherService = app(WeatherService::class);

        // Get current weather for the adventure
        $weather = $adventure->generated_map['weather'] ?? [];
        $combatModifiers = $weatherService->getCombatModifiers($weather);

        $data = [
            'player' => $player,
            'adventure' => $adventure,
            'map_data' => $adventure->generated_map,
            'weather' => $weather,
            'combat_modifiers' => $combatModifiers,
            'current_location' => $this->getCurrentAdventureLocation($adventure)
        ];

        return view('game.adventure', $data);
    }

    public function achievements()
    {
        $player = $this->getOrCreatePlayer();
        
        // Get all achievements and player's unlocked ones
        $allAchievements = \App\Models\Achievement::all();
        $playerAchievements = $player->achievements;
        $recentAchievements = collect([]); // Empty for now, will be populated when achievements are implemented
        
        $unlockedCount = $playerAchievements->count();
        $totalCount = $allAchievements->count();
        $totalPoints = $playerAchievements->sum('points');

        $data = [
            'player' => $player,
            'achievements' => $allAchievements,
            'recentAchievements' => $recentAchievements,
            'unlockedCount' => $unlockedCount,
            'totalCount' => $totalCount,
            'totalPoints' => $totalPoints
        ];
        
        return view('game.achievements', $data);
    }

    public function reputation()
    {
        $player = $this->getOrCreatePlayer();
        
        // Get player's faction reputations
        $reputations = $player->factionReputations;
        $recentChanges = collect([]); // Empty for now, will be populated later
        $totalReputation = $reputations->sum('reputation_score');

        $data = [
            'player' => $player,
            'reputations' => $reputations,
            'recentChanges' => $recentChanges,
            'totalReputation' => $totalReputation
        ];

        return view('game.reputation', $data);
    }

    public function combat($adventureId)
    {
        $player = $this->getOrCreatePlayer();
        $adventure = $player->adventures()->findOrFail($adventureId);
        
        if ($adventure->status !== 'active') {
            return redirect()->route('game.adventures')
                ->with('error', 'This adventure is no longer active.');
        }

        // For demo purposes, generate a random enemy
        $combatService = app(CombatService::class);
        $enemy = $combatService->generateRandomEnemy($player->level);
        
        // Start combat
        $combatData = $combatService->initiateCombat($player, $enemy, $adventure);
        
        // Store combat data in session
        session(['combat_data' => $combatData]);

        $data = [
            'player' => $player,
            'adventure' => $adventure,
            'combat_data' => $combatData,
            'enemy' => $enemy,
            'weather_effects' => $combatData['weather_effects']
        ];

        return view('game.combat', $data);
    }

    public function processCombatAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:attack,defend,use_item,flee'
        ]);

        $player = $this->getOrCreatePlayer();
        $combatData = session('combat_data');
        
        if (!$combatData || $combatData['status'] !== 'active') {
            return redirect()->route('game.dashboard')
                ->with('error', 'No active combat found.');
        }

        $combatService = app(CombatService::class);
        
        // Process player action
        if ($combatData['current_actor'] === 'player') {
            $actionData = [];
            if ($request->action === 'use_item') {
                $actionData['item'] = 'health_potion';
            }
            
            $combatData = $combatService->executePlayerAction($combatData, $request->action, $actionData);
        }

        // Process enemy turn if combat is still active
        if ($combatData['status'] === 'active' && $combatData['current_actor'] === 'enemy') {
            $combatData = $combatService->executeEnemyTurn($combatData);
        }

        // Update session
        session(['combat_data' => $combatData]);

        // Handle combat end
        if ($combatData['status'] !== 'active') {
            $combatService->applyCombatResult($player, $combatData);
            
            // Process achievements and reputation
            if ($combatData['status'] === 'victory') {
                $achievementService = app(AchievementService::class);
                $reputationService = app(ReputationService::class);
                
                $achievementService->processGameEvent($player, 'combat_victory');
                $reputationService->processGameEvent($player, 'combat_victory', [
                    'enemy' => $combatData['enemy']['name']
                ]);
            }
            
            session()->forget('combat_data');
            
            return redirect()->route('game.adventures')
                ->with('success', 'Combat ended: ' . ucfirst($combatData['status']));
        }

        return redirect()->back();
    }

    public function trainNPC(Request $request, $npcId)
    {
        $request->validate([
            'skill' => 'required|string'
        ]);

        $player = $this->getOrCreatePlayer();
        $npc = $player->npcs()->findOrFail($npcId);
        
        if (!$npc->isSettled()) {
            return back()->with('error', 'NPC must be settled in the village to train skills.');
        }

        $npcService = app(NPCService::class);
        $cost = 50; // Base training cost
        
        if ($player->persistent_currency < $cost) {
            return back()->with('error', 'Not enough currency to train this skill.');
        }

        $success = $npcService->trainNPCSkill($npc, $request->skill, $cost);
        
        if ($success) {
            // Process reputation and achievements
            $reputationService = app(ReputationService::class);
            $reputationService->processGameEvent($player, 'npc_trained');
            
            return back()->with('success', "Successfully trained {$npc->name} in {$request->skill}!");
        } else {
            return back()->with('error', 'Failed to train skill. Check prerequisites and currency.');
        }
    }

    public function accessibility()
    {
        return view('game.accessibility');
    }

    private function getOrCreatePlayer(): Player
    {
        $user = Auth::user();
        
        if (!$user->player) {
            $user->player()->create([
                'character_name' => $user->name . ' (Hero)',
                'level' => 1,
                'experience' => 0,
                'persistent_currency' => 1000,
                'hp' => 25,
                'max_hp' => 25,
                'ac' => 10,
                'str' => 12,
                'dex' => 12,
                'con' => 12,
                'int' => 12,
                'wis' => 12,
                'cha' => 12,
                'unallocated_stat_points' => 0
            ]);
        }

        return $user->player;
    }

    private function getCurrentAdventureLocation($adventure): array
    {
        $mapData = $adventure->generated_map;
        $currentLevel = $adventure->current_level;
        $currentNodeId = $adventure->current_node_id;

        if ($currentNodeId === 'start') {
            return [
                'type' => 'start',
                'description' => 'You stand at the beginning of your journey.',
                'options' => ['Enter the adventure']
            ];
        }

        // In a real implementation, this would navigate the generated map
        return [
            'type' => 'exploration',
            'description' => 'You are exploring the ' . ucfirst($adventure->road) . ' road.',
            'options' => ['Continue forward', 'Rest', 'Search area']
        ];
    }
}