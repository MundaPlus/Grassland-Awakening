<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\AdventureGenerationService;
use App\Models\Player;
use App\Models\Adventure;
use Illuminate\Support\Str;

class GameController extends Controller
{
    protected AdventureGenerationService $adventureService;

    public function __construct(AdventureGenerationService $adventureService)
    {
        $this->adventureService = $adventureService;
    }

    public function dashboard()
    {
        $user = auth()->user();
        $player = $user->player ?? $this->createPlayerForUser($user);
        
        return view('game.dashboard', compact('player'));
    }

    public function createAdventure(Request $request): JsonResponse
    {
        $request->validate([
            'road' => 'required|in:north,south,east,west',
            'difficulty' => 'sometimes|in:easy,normal,hard,nightmare',
            'custom_seed' => 'sometimes|string|max:50'
        ]);

        $user = auth()->user();
        $player = $user->player ?? $this->createPlayerForUser($user);

        // Check if player already has an active adventure
        if ($player->activeAdventure()) {
            return response()->json([
                'error' => 'You already have an active adventure. Complete or abandon it first.'
            ], 400);
        }

        $seed = $request->custom_seed ?? $this->generateRandomSeed();
        $difficulty = $request->difficulty ?? 'normal';
        $road = $request->road;

        // Generate adventure structure
        $adventureData = $this->adventureService->generateAdventure($seed, $road, $difficulty);

        // Create adventure record
        $adventure = Adventure::create([
            'player_id' => $player->id,
            'road' => $road,
            'seed' => $seed,
            'difficulty' => $difficulty,
            'status' => 'active',
            'current_level' => 1,
            'current_node_id' => '1-1',
            'completed_nodes' => [],
            'collected_loot' => [],
            'currency_earned' => 0
        ]);

        // Update player position
        $player->update([
            'current_road' => $road,
            'current_level' => 1,
            'current_node_id' => '1-1'
        ]);

        return response()->json([
            'success' => true,
            'adventure' => $adventure,
            'adventure_data' => $adventureData,
            'message' => "Adventure created on the {$road} road!"
        ]);
    }

    public function getAdventureMap(Adventure $adventure): JsonResponse
    {
        // Regenerate map from seed
        $adventureData = $this->adventureService->generateAdventure(
            $adventure->seed,
            $adventure->road,
            $adventure->difficulty
        );

        return response()->json([
            'map' => $adventureData['map'],
            'current_position' => [
                'level' => $adventure->current_level,
                'node_id' => $adventure->current_node_id
            ],
            'completed_nodes' => $adventure->completed_nodes ?? [],
            'weather' => $adventureData['weather'],
            'specialization' => $adventureData['specialization'],
            'modifier' => $adventureData['modifier']
        ]);
    }

    public function moveToNode(Request $request, Adventure $adventure): JsonResponse
    {
        $request->validate([
            'node_id' => 'required|string'
        ]);

        if (!$adventure->isActive()) {
            return response()->json(['error' => 'Adventure is not active'], 400);
        }

        // Get current adventure map
        $adventureData = $this->adventureService->generateAdventure(
            $adventure->seed,
            $adventure->road,
            $adventure->difficulty
        );

        $nodeId = $request->node_id;
        $connections = $adventureData['map']['connections'];
        $currentNodeId = $adventure->current_node_id;

        // Validate movement
        if (!isset($connections[$currentNodeId]) || !in_array($nodeId, $connections[$currentNodeId])) {
            return response()->json(['error' => 'Invalid movement'], 400);
        }

        // Find the target node
        $targetNode = $this->findNodeById($adventureData['map']['nodes'], $nodeId);
        if (!$targetNode) {
            return response()->json(['error' => 'Node not found'], 400);
        }

        // Update adventure position
        $adventure->updatePosition($targetNode['level'], $nodeId);

        // Update player position
        $adventure->player->update([
            'current_level' => $targetNode['level'],
            'current_node_id' => $nodeId
        ]);

        return response()->json([
            'success' => true,
            'current_node' => $targetNode,
            'adventure' => $adventure->fresh()
        ]);
    }

    public function completeNode(Request $request, Adventure $adventure): JsonResponse
    {
        $request->validate([
            'node_id' => 'required|string',
            'outcome' => 'required|array'
        ]);

        if (!$adventure->isActive()) {
            return response()->json(['error' => 'Adventure is not active'], 400);
        }

        $nodeId = $request->node_id;
        $outcome = $request->outcome;

        // Mark node as completed
        $adventure->addCompletedNode($nodeId);

        // Process rewards
        if (isset($outcome['currency'])) {
            $adventure->addCurrencyEarned($outcome['currency']);
            $adventure->player->increment('persistent_currency', $outcome['currency']);
        }

        if (isset($outcome['loot'])) {
            $adventure->addCollectedLoot($outcome['loot']);
        }

        if (isset($outcome['experience'])) {
            $adventure->player->increment('experience', $outcome['experience']);
            
            // Check for level up
            while ($adventure->player->canLevelUp()) {
                $adventure->player->levelUp();
            }
        }

        // Check if adventure is complete (reached boss and completed it)
        if ($nodeId === '10-boss') {
            $adventure->markCompleted();
            
            // Return player to village
            $adventure->player->update([
                'current_road' => null,
                'current_level' => null,
                'current_node_id' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'adventure' => $adventure->fresh(),
            'player' => $adventure->player->fresh()
        ]);
    }

    public function abandonAdventure(Adventure $adventure): JsonResponse
    {
        if (!$adventure->isActive()) {
            return response()->json(['error' => 'Adventure is not active'], 400);
        }

        $adventure->abandon();
        
        // Return player to village
        $adventure->player->update([
            'current_road' => null,
            'current_level' => null,
            'current_node_id' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Adventure abandoned. You have returned to the village.'
        ]);
    }

    public function getPlayerStatus(): JsonResponse
    {
        $user = auth()->user();
        $player = $user->player ?? $this->createPlayerForUser($user);
        
        return response()->json([
            'player' => $player,
            'position' => $player->getCurrentPosition(),
            'active_adventure' => $player->activeAdventure()
        ]);
    }

    private function createPlayerForUser($user): Player
    {
        return Player::create([
            'user_id' => $user->id,
            'character_name' => $user->name . "'s Character",
            'level' => 1,
            'experience' => 0,
            'persistent_currency' => 0,
            'hp' => 20,
            'max_hp' => 20,
            'ac' => 10,
            'str' => 10,
            'dex' => 10,
            'con' => 10,
            'int' => 10,
            'wis' => 10,
            'cha' => 10,
            'unallocated_stat_points' => 0
        ]);
    }

    private function generateRandomSeed(): string
    {
        return Str::random(10);
    }

    private function findNodeById(array $nodes, string $nodeId): ?array
    {
        foreach ($nodes as $level => $levelNodes) {
            foreach ($levelNodes as $node) {
                if ($node['id'] === $nodeId) {
                    return $node;
                }
            }
        }
        return null;
    }
}
