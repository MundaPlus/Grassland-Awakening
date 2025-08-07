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
use App\Models\Equipment;
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

        // Auto-complete adventures that have reached 100% progress
        foreach ($activeAdventures as $adventure) {
            if ($adventure->getCurrentProgress() >= 1.0) {
                $adventure->markCompleted();
            }
        }

        // Refresh active adventures list after auto-completion
        $activeAdventures = $player->adventures()->where('status', 'active')->get();
        $availableAdventures = $player->adventures()->where('status', 'available')->get();
        $currentWeather = $weatherService->getCurrentWeather($player);

        $data = [
            'player' => $player,
            'activeAdventures' => $activeAdventures,
            'availableAdventures' => $availableAdventures,
            'weatherEffects' => $currentWeather['effects'] ?? null,
            'completedAdventures' => $player->adventures()->whereIn('status', ['completed', 'failed'])->latest()->take(3)->get()
        ];

        return view('game.adventures', $data);
    }

    public function generateAdventure(Request $request)
    {
        try {
            $request->validate([
                'seed' => 'nullable|string',
                'difficulty' => 'nullable|in:,easy,normal,hard,nightmare',
                'road_type' => 'nullable|string'
            ]);

            $player = $this->getOrCreatePlayer();

            // Check if player has enough currency
            $cost = 10;
            if ($player->persistent_currency < $cost) {
                return back()->with('error', 'Not enough gold to generate adventure. Cost: ' . $cost . ' gold.');
            }

            // Use database transaction to ensure atomicity
            return \DB::transaction(function () use ($request, $player, $cost) {
                // Deduct cost
                $player->decrement('persistent_currency', $cost);

                $adventureService = app(AdventureGenerationService::class);
                $seed = $request->seed ?: 'player_' . $player->id . '_' . time();

                // Determine difficulty based on player level if not specified
                $difficulty = $request->difficulty;
                if (!$difficulty || $difficulty === '') {
                    if ($player->level >= 10) {
                        $difficulty = 'hard';
                    } elseif ($player->level >= 5) {
                        $difficulty = 'normal';
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

                if (!$adventureData) {
                    throw new \Exception('Failed to generate adventure data');
                }

                // Create adventure record
                $adventure = $player->adventures()->create([
                    'seed' => $seed,
                    'road' => $roadType,
                    'difficulty' => $difficulty,
                    'generated_map' => $adventureData,
                    'current_level' => 1,
                    'current_node_id' => '1-1',
                    'status' => 'available',
                    'title' => $adventureData['title'] ?? 'Generated Adventure',
                    'description' => $adventureData['description'] ?? 'A procedurally generated adventure'
                ]);

                if (!$adventure) {
                    throw new \Exception('Failed to create adventure record');
                }

                return redirect()->route('game.adventures')
                    ->with('success', 'Adventure generated successfully! Cost: ' . $cost . ' gold.');
            });
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Adventure generation error: ' . $e->getMessage(), [
                'player_id' => $player->id ?? null,
                'request_data' => $request->all(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('game.adventures')
                ->with('error', 'Failed to generate adventure. Please try again.');
        }
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

        // Reset player to full health for new adventure
        $player->hp = $player->max_hp;
        $player->save();

        // Start the adventure
        $adventure->update([
            'status' => 'active',
            'current_level' => 1,
            'current_node_id' => '1-1'
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
        $achievementService = app(\App\Services\AchievementService::class);

        // Get player achievements from the service
        $achievementData = $achievementService->getPlayerAchievements($player, false);

        // Get recent achievements (last 7 days) - using the new unlocked achievements data
        $recentAchievements = collect();
        $recentDate = now()->subDays(7);
        foreach ($achievementData['unlocked'] as $unlockedAchievement) {
            $unlockedAt = \Carbon\Carbon::parse($unlockedAchievement['unlocked_at']);
            if ($unlockedAt->gte($recentDate)) {
                $achievement = (object) $unlockedAchievement;
                $achievement->id = $unlockedAchievement['id'];
                $achievement->pivot = (object) ['unlocked_at' => $unlockedAt];
                // Add missing properties for template consistency
                $achievement->rewards = null;
                $recentAchievements->push($achievement);
            }
        }
        $recentAchievements = $recentAchievements->sortByDesc(function($achievement) {
            return $achievement->pivot->unlocked_at;
        });

        // Combine unlocked and progress achievements for the view
        $allAchievementsForView = collect();

        // Add unlocked achievements
        foreach ($achievementData['unlocked'] as $unlockedAchievement) {
            $achievement = (object) $unlockedAchievement;
            $achievement->id = $unlockedAchievement['id']; // Use the achievement ID from the data
            $achievement->pivot = (object) [
                'unlocked_at' => \Carbon\Carbon::parse($unlockedAchievement['unlocked_at'])
            ];
            // Add missing properties expected by the template
            $achievement->is_progress_based = false; // Most achievements are not progress-based
            $achievement->target_value = null;
            $achievement->hints = null;
            $achievement->rewards = null;
            $allAchievementsForView->push($achievement);
        }

        // Add progress achievements (not unlocked yet)
        foreach ($achievementData['progress'] as $progressAchievement) {
            $achievement = (object) $progressAchievement;
            $achievement->id = $progressAchievement['id']; // Use the achievement ID from the data
            $achievement->pivot = null; // Not unlocked

            // Add missing properties expected by the template
            $achievement->is_progress_based = true; // Progress achievements are trackable

            // Get the highest requirement value as target and current progress
            $targetValue = 100; // default
            $currentProgress = 0;
            if (!empty($progressAchievement['requirements'])) {
                $targetValue = max(array_column($progressAchievement['requirements'], 'required'));
                $currentProgress = max(array_column($progressAchievement['requirements'], 'current'));
            }
            $achievement->target_value = $targetValue;
            $achievement->current_progress = $currentProgress;
            $achievement->completion_percentage = $progressAchievement['completion_percentage'];

            $achievement->hints = null;
            $achievement->rewards = null;
            $allAchievementsForView->push($achievement);
        }

        $totalCount = count($achievementData['unlocked']) + count($achievementData['progress']);

        $data = [
            'player' => $player,
            'achievements' => $allAchievementsForView,
            'recentAchievements' => $recentAchievements,
            'unlockedCount' => $achievementData['achievement_count'],
            'totalCount' => $totalCount > 0 ? $totalCount : 1, // Prevent division by zero
            'totalPoints' => $achievementData['total_points']
        ];

        return view('game.achievements', $data);
    }

    public function skills()
    {
        $player = $this->getOrCreatePlayer();
        $skillService = app(\App\Services\SkillService::class);

        // Get all player skills data
        $skillData = $skillService->getPlayerSkills($player);

        // Get skill statistics
        $skillStats = $skillService->getPlayerSkillStats($player);

        $data = [
            'player' => $player,
            'skillData' => $skillData,
            'skillStats' => $skillStats
        ];

        return view('game.skills', $data);
    }

    public function learnSkill(Request $request, $id)
    {
        $player = $this->getOrCreatePlayer();
        $skill = \App\Models\Skill::findOrFail($id);
        $skillService = app(\App\Services\SkillService::class);

        $result = $skillService->learnSkillWithPoints($player, $skill);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
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

    public function character()
    {
        $player = $this->getOrCreatePlayer();
        $player->load(['equipment.item', 'playerItems.item']);

        $data = [
            'player' => $player,
            'equipment' => $this->getMergedEquipment($player),
            'equipmentSlots' => Equipment::getAllSlots(),
            'armorSlots' => Equipment::getArmorSlots(),
            'weaponSlots' => Equipment::getWeaponSlots(),
            'accessorySlots' => Equipment::getAccessorySlots(),
            'totalStats' => [
                'str' => $player->getTotalStat('str'),
                'dex' => $player->getTotalStat('dex'),
                'con' => $player->getTotalStat('con'),
                'int' => $player->getTotalStat('int'),
                'wis' => $player->getTotalStat('wis'),
                'cha' => $player->getTotalStat('cha')
            ],
            'equipmentBonuses' => [
                'str' => $player->getEquipmentStatModifier('str'),
                'dex' => $player->getEquipmentStatModifier('dex'),
                'con' => $player->getEquipmentStatModifier('con'),
                'int' => $player->getEquipmentStatModifier('int'),
                'wis' => $player->getEquipmentStatModifier('wis'),
                'cha' => $player->getEquipmentStatModifier('cha'),
                'weapon_damage' => $player->getWeaponDamageBonus(),
                'ac' => $player->getEquipmentACBonus()
            ],
            'totalAC' => $player->getTotalAC(),
            'baseAC' => $player->ac,
            'equipmentAC' => $player->getEquipmentACBonus(),
            'maxDamage' => $player->getMaxDamage(),
            // Add inventory data for the new system
            'inventory' => [
                'weapons' => $player->getAvailableItemsByType('weapon'),
                'armor' => $player->getAvailableItemsByType('armor'),
                'accessories' => $player->getAvailableItemsByType('accessory'),
                'consumables' => $player->getAvailableItemsByType('consumable'),
                'materials' => $player->getAvailableItemsByType('material')
            ],
            'equippedItems' => $player->equippedItems()->with('item')->get(),
            'allPlayerItems' => $player->playerItems()->with('item')->get(),
            'inventoryItems' => $player->playerItems()
                ->with('item')
                ->where('is_equipped', false)
                ->whereHas('item', function($query) {
                    $query->whereIn('type', ['weapon', 'armor', 'accessory']);
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];

        return view('game.character', $data);
    }

    public function allocateStats(Request $request)
    {
        try {
            $request->validate([
                'str_points' => 'required|integer|min:0',
                'dex_points' => 'required|integer|min:0',
                'con_points' => 'required|integer|min:0',
                'int_points' => 'required|integer|min:0',
                'wis_points' => 'required|integer|min:0',
                'cha_points' => 'required|integer|min:0'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input data.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        $player = $this->getOrCreatePlayer();

        if (!$player->hasUnallocatedStatPoints()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No stat points available to allocate.'
                ]);
            }
            return redirect()->back()->with('error', 'No stat points available to allocate.');
        }

        $allocatedPoints = [
            'str' => (int)$request->str_points,
            'dex' => (int)$request->dex_points,
            'con' => (int)$request->con_points,
            'int' => (int)$request->int_points,
            'wis' => (int)$request->wis_points,
            'cha' => (int)$request->cha_points
        ];

        $totalPointsToAllocate = array_sum($allocatedPoints);

        if ($totalPointsToAllocate <= 0) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must allocate at least one stat point.'
                ]);
            }
            return redirect()->back()->with('error', 'You must allocate at least one stat point.');
        }

        if ($totalPointsToAllocate > $player->unallocated_stat_points) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot allocate more points than available.'
                ]);
            }
            return redirect()->back()->with('error', 'Cannot allocate more points than available.');
        }

        // Apply the stat increases
        $player->str += $allocatedPoints['str'];
        $player->dex += $allocatedPoints['dex'];
        $player->con += $allocatedPoints['con'];
        $player->int += $allocatedPoints['int'];
        $player->wis += $allocatedPoints['wis'];
        $player->cha += $allocatedPoints['cha'];

        // Reduce unallocated points
        $player->unallocated_stat_points -= $totalPointsToAllocate;

        // Update max HP if Constitution was increased
        if ($allocatedPoints['con'] > 0) {
            $conModifierIncrease = floor($allocatedPoints['con'] / 2); // Each 2 points of CON = +1 HP per level
            $hpIncrease = $conModifierIncrease * $player->level;
            $player->max_hp += $hpIncrease;
            $player->hp += $hpIncrease; // Also heal the player
        }

        $player->save();

        $allocatedStats = [];
        foreach ($allocatedPoints as $stat => $points) {
            if ($points > 0) {
                $allocatedStats[] = strtoupper($stat) . " +" . $points;
            }
        }

        $message = "Successfully allocated {$totalPointsToAllocate} stat points: " . implode(', ', $allocatedStats);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'allocated_points' => $totalPointsToAllocate,
                'remaining_points' => $player->unallocated_stat_points
            ]);
        }

        return redirect()->route('game.character')->with('success', $message);
    }

    public function levelUp(Request $request)
    {
        $player = $this->getOrCreatePlayer();

        if (!$player->canLevelUp()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have enough experience to level up.'
            ]);
        }

        $oldLevel = $player->level;
        $player->levelUp();

        return response()->json([
            'success' => true,
            'message' => "🎉 Level Up! You are now level {$player->level}!",
            'old_level' => $oldLevel,
            'new_level' => $player->level,
            'stat_points_gained' => 2,
            'hp_gained' => 5 + $player->getStatModifier('con')
        ]);
    }

    public function changeGender(Request $request)
    {
        try {
            $request->validate([
                'gender' => 'required|in:male,female'
            ]);

            $player = $this->getOrCreatePlayer();
            $player->gender = $request->gender;
            $player->save();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Character gender updated successfully!',
                    'new_gender' => $player->gender,
                    'character_image' => $player->getCharacterImagePath()
                ]);
            }

            return redirect()->route('game.character')->with('success', 'Character gender updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating gender: ' . $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }

    public function inventory(Request $request)
    {
        $player = $this->getOrCreatePlayer();
        $player->load('inventory.item');

        $sortBy = $request->get('sort', 'name');

        $inventorySlots = $player->getInventorySlots();

        // Sort items in each category
        foreach ($inventorySlots as $category => $items) {
            $inventorySlots[$category] = match($sortBy) {
                'rarity' => $items->sortByDesc(fn($item) => array_search($item->item->rarity, ['common', 'uncommon', 'rare', 'epic', 'legendary'])),
                'value' => $items->sortByDesc(fn($item) => $item->item->value ?? 0),
                'name' => $items->sortBy(fn($item) => $item->item->name),
                default => $items->sortBy(fn($item) => $item->item->name)
            };
        }

        $totalItems = $player->getTotalInventoryItems();
        $totalValue = $player->getInventoryValue();
        $recentItems = $player->inventory()->with('item')->latest()->limit(5)->get();

        // Get items by rarity for stats
        $itemsByRarity = $player->inventory()->with('item')->get()->groupBy(fn($item) => $item->item->rarity)->map(fn($items) => $items->count());

        $data = [
            'player' => $player,
            'inventorySlots' => $inventorySlots,
            'totalItems' => $totalItems,
            'totalValue' => $totalValue,
            'recentItems' => $recentItems,
            'itemsByRarity' => $itemsByRarity,
            'sortBy' => $sortBy
        ];

        return view('game.inventory', $data);
    }

    public function getInventoryItemDetail($id)
    {
        $player = $this->getOrCreatePlayer();

        // Try to find the item in both inventory systems
        $inventoryItem = $player->inventory()->with('item')->find($id);
        if (!$inventoryItem) {
            $inventoryItem = $player->playerItems()->with('item')->findOrFail($id);
        }

        $html = view('game.inventory.item-detail', ['inventoryItem' => $inventoryItem])->render();
        $actions = view('game.inventory.item-actions', ['inventoryItem' => $inventoryItem])->render();

        return response()->json([
            'html' => $html,
            'actions' => $actions
        ]);
    }

    public function equipFromInventory(Request $request, $id)
    {
        $player = $this->getOrCreatePlayer();
        $slot = $request->input('slot');

        // Try to find the item in both inventory systems
        $inventoryItem = $player->inventory()->with('item')->find($id);
        $playerItem = $player->playerItems()->with('item')->find($id);

        if (!$inventoryItem && !$playerItem) {
            return redirect()->route('game.character')
                ->with('error', 'Item not found in inventory.');
        }

        $item = $inventoryItem ? $inventoryItem->item : $playerItem->item;

        if (!$slot) {
            // Auto-determine slot from item
            $slot = $item->getEquipmentSlot();
        }

        // Handle null slots for items that need special slot selection
        if (!$slot) {
            $slot = $this->determineEquipmentSlot($item, $player);
        }

        if (!$slot) {
            return redirect()->route('game.character')
                ->with('error', 'Unable to determine equipment slot for this item.');
        }

        $success = false;

        if ($inventoryItem) {
            // Use old inventory system method
            $success = $player->equipItemFromInventory($id, $slot);
        } else if ($playerItem) {
            // Use new PlayerItem system method
            $success = $this->equipPlayerItemMethod($player, $playerItem, $slot);
        }

        if ($success) {
            return redirect()->route('game.character')
                ->with('success', 'Item equipped successfully!');
        } else {
            return redirect()->route('game.character')
                ->with('error', 'Unable to equip item. Check level requirements and item type.');
        }
    }

    private function equipPlayerItemMethod(Player $player, $playerItem, string $slot): bool
    {
        if (!$playerItem->item->canEquip($player)) {
            return false;
        }

        if ($playerItem->is_equipped) {
            return false; // Already equipped
        }

        // Handle two-handed weapons and conflicting slots
        if ($slot === Equipment::SLOT_TWO_HANDED_WEAPON) {
            $this->unequipPlayerItemSlot($player, Equipment::SLOT_WEAPON_1);
            $this->unequipPlayerItemSlot($player, Equipment::SLOT_WEAPON_2);
            $this->unequipPlayerItemSlot($player, Equipment::SLOT_SHIELD);
        } elseif (in_array($slot, [Equipment::SLOT_WEAPON_1, Equipment::SLOT_WEAPON_2, Equipment::SLOT_SHIELD])) {
            $this->unequipPlayerItemSlot($player, Equipment::SLOT_TWO_HANDED_WEAPON);
        }

        // Unequip current item in slot
        $this->unequipPlayerItemSlot($player, $slot);

        // Equip the new item
        $playerItem->is_equipped = true;
        $playerItem->equipment_slot = $slot;
        $playerItem->save();

        return true;
    }

    private function unequipPlayerItemSlot(Player $player, string $slot): void
    {
        $currentItem = $player->playerItems()
            ->where('is_equipped', true)
            ->where('equipment_slot', $slot)
            ->first();

        if ($currentItem) {
            $currentItem->is_equipped = false;
            $currentItem->equipment_slot = null;
            $currentItem->save();
        }
    }

    private function determineEquipmentSlot(\App\Models\Item $item, ?Player $player = null): ?string
    {
        // Handle one-handed weapons - check both slots and use first empty
        if (in_array($item->subtype, [\App\Models\Item::SUBTYPE_SWORD, \App\Models\Item::SUBTYPE_AXE, \App\Models\Item::SUBTYPE_MACE, \App\Models\Item::SUBTYPE_DAGGER])) {
            if ($player) {
                // Check if weapon_1 is empty
                $weapon1Equipped = $player->playerItems()
                    ->where('is_equipped', true)
                    ->where('equipment_slot', 'weapon_1')
                    ->exists();
                
                if (!$weapon1Equipped) {
                    return 'weapon_1';
                }
                
                // Check if weapon_2 is empty
                $weapon2Equipped = $player->playerItems()
                    ->where('is_equipped', true)
                    ->where('equipment_slot', 'weapon_2')
                    ->exists();
                
                if (!$weapon2Equipped) {
                    return 'weapon_2';
                }
            }
            
            // Default to weapon_1 if no player context or both slots occupied
            return 'weapon_1';
        }

        // Handle rings - check both ring slots and use first empty
        if ($item->subtype === \App\Models\Item::SUBTYPE_RING) {
            if ($player) {
                // Check if ring_1 is empty
                $ring1Equipped = $player->playerItems()
                    ->where('is_equipped', true)
                    ->where('equipment_slot', 'ring_1')
                    ->exists();
                
                if (!$ring1Equipped) {
                    return 'ring_1';
                }
                
                // Check if ring_2 is empty
                $ring2Equipped = $player->playerItems()
                    ->where('is_equipped', true)
                    ->where('equipment_slot', 'ring_2')
                    ->exists();
                
                if (!$ring2Equipped) {
                    return 'ring_2';
                }
            }
            
            // Default to ring_1 if no player context or both slots occupied
            return 'ring_1';
        }

        return null;
    }

    public function unequipToInventory($slot)
    {
        $player = $this->getOrCreatePlayer();
        $success = $player->unequipToInventory($slot);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Item unequipped and moved to inventory!'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No item equipped in this slot.'
            ]);
        }
    }

    public function useInventoryItem($id)
    {
        $player = $this->getOrCreatePlayer();
        $inventoryItem = $player->inventory()->with('item')->findOrFail($id);

        if ($inventoryItem->item->type !== 'consumable') {
            return response()->json([
                'success' => false,
                'message' => 'This item cannot be used.'
            ]);
        }

        // Simple consumable logic - health potions
        if (str_contains(strtolower($inventoryItem->item->name), 'health')) {
            $healAmount = rand(10, 25);
            $player->hp = min($player->max_hp, $player->hp + $healAmount);
            $player->save();

            // Remove one from inventory
            $player->removeItemFromInventory($inventoryItem->item, 1);

            return response()->json([
                'success' => true,
                'message' => "Used {$inventoryItem->item->name} and recovered {$healAmount} HP!"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'This consumable is not yet implemented.'
        ]);
    }

    public function repairAllItems()
    {
        $player = $this->getOrCreatePlayer();
        $damagedItems = $player->inventory()->where('current_durability', '<', 'max_durability')->with('item')->get();

        if ($damagedItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No items need repair.'
            ]);
        }

        $totalRepairCost = $damagedItems->sum(function($item) {
            $durabilityLost = $item->max_durability - $item->current_durability;
            return (int) (($item->item->value ?? 10) * 0.1 * ($durabilityLost / 100));
        });

        if ($player->persistent_currency < $totalRepairCost) {
            return response()->json([
                'success' => false,
                'message' => "Not enough gold to repair all items. Cost: {$totalRepairCost} gold."
            ]);
        }

        // Repair all items
        foreach ($damagedItems as $item) {
            $item->repair();
            $item->save();
        }

        $player->persistent_currency -= $totalRepairCost;
        $player->save();

        return response()->json([
            'success' => true,
            'message' => "Repaired {$damagedItems->count()} items for {$totalRepairCost} gold!"
        ]);
    }

    public function combat(Request $request, $adventureId)
    {
        $player = $this->getOrCreatePlayer();
        $adventure = $player->adventures()->findOrFail($adventureId);

        if ($adventure->status !== 'active') {
            return redirect()->route('game.adventures')
                ->with('error', 'This adventure is no longer active.');
        }

        // Get the node ID from request
        $nodeId = $request->input('node');
        if (!$nodeId) {
            return redirect()->route('game.adventure-map', $adventureId)
                ->with('error', 'No combat node specified.');
        }

        // Check if we have existing combat data in session
        $existingCombatData = session('combat_data');
        if ($existingCombatData && $existingCombatData['status'] === 'active') {
            // Resume existing combat
            $data = [
                'player' => $player,
                'adventure' => $adventure,
                'combat_data' => $existingCombatData,
                'enemy' => $existingCombatData['enemy_data'] ?? null,
                'weather_effects' => $existingCombatData['weather_effects'] ?? []
            ];
            return view('game.combat', $data);
        }

        // Find the node details from the adventure map
        $nodeDetails = null;
        $mapData = $adventure->generated_map;
        if ($mapData && isset($mapData['map']['nodes'])) {
            foreach ($mapData['map']['nodes'] as $level => $levelNodes) {
                foreach ($levelNodes as $node) {
                    if ($node['id'] === $nodeId) {
                        $nodeDetails = $node;
                        break 2;
                    }
                }
            }
        }

        if (!$nodeDetails || ($nodeDetails['type'] !== 'combat' && $nodeDetails['type'] !== 'boss')) {
            return redirect()->route('game.adventure-map', $adventureId)
                ->with('error', 'Invalid combat node.');
        }

        // Generate enemy based on node data
        $combatService = app(CombatService::class);
        $enemy = $this->generateEnemyFromNode($nodeDetails, $player->level);

        // Start combat
        $combatData = $combatService->initiateCombat($player, $enemy, $adventure);

        // Store combat data in session
        session(['combat_data' => $combatData]);

        $data = [
            'player' => $player,
            'adventure' => $adventure,
            'combat_data' => $combatData,
            'enemy' => $enemy,
            'weather_effects' => $combatData['weather_effects'] ?? []
        ];

        return view('game.combat', $data);
    }

    public function processCombatAction(Request $request)
    {
        try {
            $request->validate([
                'action' => 'required|in:attack,defend,special,use_item',
                'target' => 'nullable|string',
                'node' => 'nullable|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid combat action.',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $player = $this->getOrCreatePlayer();
            $combatData = session('combat_data');

            if (!$combatData || $combatData['status'] !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'No active combat found. Please start a new adventure.',
                    'redirect' => route('game.adventures')
                ]);
            }

        // For attack actions on multiple enemies, require target selection
        if ($request->action === 'attack' && isset($combatData['enemies']) && !$request->target) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a target first.'
            ]);
        }

        // Update selected target in combat data
        if ($request->target && isset($combatData['enemies'])) {
            $combatData['selected_target'] = $request->target;
        }

        $this->processPlayerTurn($combatData, $request->action, $request->target);

        // Process enemy turns if player didn't end combat
        if ($combatData['status'] === 'active') {
            $this->processEnemyTurns($combatData);
        }

        // Update session
        session(['combat_data' => $combatData]);

        // Check combat status and return appropriate response
        if ($combatData['status'] === 'victory') {
            // Handle combat victory - complete the node and advance
            $nodeId = $request->input('node');
            if ($nodeId) {
                $adventure = $player->adventures()->where('status', 'active')->first();
                if ($adventure) {
                    // Get node details for item drops
                    $nodeDetails = $this->findNodeInAdventure($adventure, $nodeId);

                    // Mark the combat node as completed
                    $adventure->addCompletedNode($nodeId);

                    // Award experience and currency
                    $expReward = 50 * count($combatData['enemies'] ?? []);
                    $currencyReward = 25 * count($combatData['enemies'] ?? []);

                    $experienceResult = $player->addExperience($expReward);
                    $player->persistent_currency += $currencyReward;
                    $player->save();

                    // Store level up info for notifications
                    if (!empty($experienceResult['levels_gained'])) {
                        session(['combat_level_up' => $experienceResult]);
                    }

                    $adventure->addCurrencyEarned($currencyReward);

                    $message = "Victory! You earned {$expReward} XP and {$currencyReward} gold!";

                    // Check for item drops
                    if ($nodeDetails && ($nodeDetails['has_item_drop'] ?? false)) {
                        $itemGenerationService = app(\App\Services\ItemGenerationService::class);
                        $enemyType = $nodeDetails['enemy_type'] ?? 'generic';
                        $item = $itemGenerationService->generateCombatLoot($nodeDetails['level'], $enemyType, $adventure->difficulty);

                        if ($item) {
                            $this->addItemToPlayerInventory($player, $item);
                            $message .= " You looted: {$item->name}!";
                        }
                    }

                    // Try to discover recipe from this combat victory
                    if ($nodeDetails) {
                        $recipeDiscovery = $this->tryDiscoverRecipeFromNode($adventure, $nodeDetails);
                        if ($recipeDiscovery) {
                            $message .= " " . $recipeDiscovery['message'];
                        }
                    }

                    // Move to next accessible node
                    $this->moveToNextNode($adventure);

                    // Clear combat session data
                    session()->forget('combat_data');

                    return response()->json([
                        'success' => true,
                        'status' => 'victory',
                        'message' => $message,
                        'redirect' => route('game.adventure-map', $adventure->id)
                    ]);
                }
            }

            // Fallback if no node specified
            session()->forget('combat_data');
            return response()->json([
                'success' => true,
                'status' => 'victory',
                'message' => 'All enemies defeated!',
                'redirect' => route('game.adventures')
            ]);
        } elseif ($combatData['status'] === 'defeat') {
            // Mark adventure as failed and reset player HP
            $adventure = $player->adventures()->where('status', 'active')->first();
            if ($adventure) {
                $adventure->markFailed();
            }

            // Reset player HP to full for next adventure
            $player->hp = $player->max_hp;
            $player->save();

            // Clear combat session data on defeat
            session()->forget('combat_data');
            return response()->json([
                'success' => true,
                'status' => 'death',
                'message' => 'You have been defeated... Your health has been restored.',
                'redirect' => route('game.dashboard')
            ]);
        } else {
            return response()->json([
                'success' => true,
                'reload' => true
            ]);
        }
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Combat action error: ' . $e->getMessage(), [
                'player_id' => $player->id ?? null,
                'action' => $request->action ?? null,
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during combat. Please try again or return to adventures.',
                'redirect' => route('game.adventures')
            ], 500);
        }
    }

    private function processPlayerTurn(array &$combatData, string $action, ?string $target)
    {
        // Add to combat log
        $this->addCombatLog($combatData, "Player chooses to $action", 'player');

        if ($action === 'attack') {
            $this->processPlayerAttack($combatData, $target);
        } elseif ($action === 'defend') {
            $this->processPlayerDefend($combatData);
        } elseif ($action === 'special') {
            $this->processPlayerSpecial($combatData);
        } elseif ($action === 'use_item') {
            // Get item_id from request if provided
            $itemId = $request->input('item_id');
            if ($itemId) {
                $this->processPlayerUseItem($combatData, $itemId);
            } else {
                // Return available consumables for selection
                $player = $this->getOrCreatePlayer();
                $consumables = $this->getAvailableConsumables($player);

                return response()->json([
                    'success' => false,
                    'show_item_selection' => true,
                    'available_items' => $consumables,
                    'message' => 'Select an item to use'
                ]);
            }
        }

        // Advance round
        $combatData['round'] = ($combatData['round'] ?? 1) + 1;
    }

    private function processPlayerAttack(array &$combatData, ?string $target): void
    {
        $player = $this->getOrCreatePlayer();

        // Get adventure difficulty for bonuses
        $adventure = $player->adventures()->where('status', 'active')->first();
        $difficultyBonus = $this->getDifficultyBonus($adventure->difficulty ?? 'normal');

        if (isset($combatData['enemies'])) {
            // Multiple enemies
            if (!$target || !isset($combatData['enemies'][$target]) || $combatData['enemies'][$target]['status'] !== 'alive') {
                $this->addCombatLog($combatData, "Attack missed - invalid target!", 'system');
                return;
            }

            $enemy = &$combatData['enemies'][$target];

            // D&D Combat Rules: Attack Roll (1d20 + STR modifier + proficiency bonus + difficulty bonus + equipment bonuses)
            $attackRoll = rand(1, 20);
            $totalStr = $player->getTotalStat('str'); // Use equipment-enhanced strength
            $strModifier = floor(($totalStr - 10) / 2);
            $proficiencyBonus = max(2, floor(($player->level - 1) / 4) + 2); // Proficiency starts at +2
            $totalAttack = $attackRoll + $strModifier + $proficiencyBonus + $difficultyBonus;

            $enemyAC = $enemy['ac'] ?? 12;

            if ($totalAttack >= $enemyAC) {
                // Hit! Calculate damage using equipment
                $weaponDamageBonus = $player->getWeaponDamageBonus();
                $damageRoll = rand(1, 8); // Default damage dice, should be from weapon
                $damage = $damageRoll + $strModifier + $weaponDamageBonus;
                $damage = max(1, $damage); // Minimum 1 damage

                $enemy['hp'] = max(0, $enemy['hp'] - $damage);
                $enemy['health'] = $enemy['hp'];

                $this->addCombatLog($combatData, "Player rolls {$attackRoll} + {$strModifier} + {$proficiencyBonus} + {$difficultyBonus} = {$totalAttack} vs AC {$enemyAC} - HIT!", 'player');
                $this->addCombatLog($combatData, "Player deals {$damage} damage to {$enemy['name']} ({$damageRoll} + {$strModifier} STR + {$weaponDamageBonus} weapon)!", 'player');

                if ($enemy['hp'] <= 0) {
                    $enemy['status'] = 'dead';
                    $this->addCombatLog($combatData, "{$enemy['name']} has been defeated!", 'system');

                    // If current target died, auto-select next alive enemy
                    if ($combatData['selected_target'] === $target) {
                        $aliveEnemies = array_filter($combatData['enemies'], fn($e) => $e['status'] === 'alive');
                        if (!empty($aliveEnemies)) {
                            $combatData['selected_target'] = array_key_first($aliveEnemies);
                        } else {
                            $combatData['selected_target'] = null;
                        }
                    }

                    // Check if all enemies are dead
                    $aliveEnemies = array_filter($combatData['enemies'], fn($e) => $e['status'] === 'alive');
                    if (empty($aliveEnemies)) {
                        $combatData['status'] = 'victory';
                    }
                }
            } else {
                // Miss!
                $this->addCombatLog($combatData, "Player rolls {$attackRoll} + {$strModifier} + {$proficiencyBonus} + {$difficultyBonus} = {$totalAttack} vs AC {$enemyAC} - MISS!", 'player');
            }
        } else {
            // Single enemy (backward compatibility with D&D rules)
            $attackRoll = rand(1, 20);
            $totalStr = $player->getTotalStat('str'); // Use equipment-enhanced strength
            $strModifier = floor(($totalStr - 10) / 2);
            $proficiencyBonus = max(2, floor(($player->level - 1) / 4) + 2);
            $totalAttack = $attackRoll + $strModifier + $proficiencyBonus + $difficultyBonus;

            $enemyAC = $combatData['enemy']['ac'] ?? 12;

            if ($totalAttack >= $enemyAC) {
                $weaponDamageBonus = $player->getWeaponDamageBonus();
                $damageRoll = rand(1, 8);
                $damage = $damageRoll + $strModifier + $weaponDamageBonus;
                $damage = max(1, $damage);

                $combatData['enemy']['current_hp'] = max(0, $combatData['enemy']['current_hp'] - $damage);

                $this->addCombatLog($combatData, "Player hits for {$damage} damage!", 'player');

                if ($combatData['enemy']['current_hp'] <= 0) {
                    $combatData['status'] = 'victory';
                }
            } else {
                $this->addCombatLog($combatData, "Player attack misses!", 'player');
            }
        }
    }

    private function processPlayerDefend(array &$combatData): void
    {
        $combatData['player_defending'] = true;
        $this->addCombatLog($combatData, "Player defends, reducing incoming damage next turn.", 'player');
    }

    private function processPlayerSpecial(array &$combatData): void
    {
        $this->addCombatLog($combatData, "Player uses a special ability!", 'player');
        // Implement special ability logic here
    }

    private function processPlayerUseItem(array &$combatData, int $itemId): void
    {
        $player = $this->getOrCreatePlayer();

        // Find the item in player's inventory
        $consumable = $this->findConsumableItem($player, $itemId);

        if (!$consumable) {
            $this->addCombatLog($combatData, "Item not found or not usable in combat.", 'system');
            return;
        }

        // Use the item and apply its effects
        $effects = $this->applyConsumableEffects($player, $consumable);

        // Update combat session data with new player stats
        $combatData['player']['hp'] = $player->hp;
        $combatData['player']['current_hp'] = $player->hp;

        // Remove item from inventory
        $this->removeConsumableFromInventory($player, $consumable);

        $this->addCombatLog($combatData, "Player uses {$consumable['name']} - {$effects}", 'player');
    }

    private function processEnemyTurns(array &$combatData): void
    {
        if (isset($combatData['enemies'])) {
            // Multiple enemies
            foreach ($combatData['enemies'] as $enemyId => $enemy) {
                if ($enemy['status'] !== 'alive') continue;

                $this->processEnemyAttack($combatData, $enemyId, $enemy);

                if ($combatData['status'] !== 'active') break; // Player died
            }
        } else {
            // Single enemy (backward compatibility)
            if ($combatData['enemy']['current_hp'] > 0) {
                $this->processEnemyAttack($combatData, 'enemy', $combatData['enemy']);
            }
        }
    }

    private function processEnemyAttack(array &$combatData, string $enemyId, array $enemy): void
    {
        $player = $this->getOrCreatePlayer();

        // D&D Combat Rules: Enemy Attack Roll (1d20 + STR modifier)
        $attackRoll = rand(1, 20);
        $enemyStrModifier = floor(($enemy['str'] - 10) / 2);
        $enemyAttackBonus = max(2, floor(($enemy['level'] ?? 1) / 4) + 2); // Enemy proficiency
        $totalAttack = $attackRoll + $enemyStrModifier + $enemyAttackBonus;

        $playerAC = $player->getTotalAC(); // Use equipment-enhanced AC

        if ($totalAttack >= $playerAC) {
            // Hit! Calculate damage (1d6 + STR modifier for basic enemy attack)
            $damageRoll = rand(1, 6);
            $damage = $damageRoll + max(0, $enemyStrModifier);
            $damage = max(1, $damage); // Minimum 1 damage

            // Reduce damage if player is defending
            if ($combatData['player_defending'] ?? false) {
                $originalDamage = $damage;
                $damage = max(1, intval($damage / 2));
                $this->addCombatLog($combatData, "Player's defense reduces damage from {$originalDamage} to {$damage}!", 'system');
                $combatData['player_defending'] = false; // Reset defending
            }

            $player->hp = max(0, $player->hp - $damage);
            $player->save();

            // Update combat session data with new HP
            $combatData['player']['hp'] = $player->hp;
            $combatData['player']['current_hp'] = $player->hp;

            $this->addCombatLog($combatData, "{$enemy['name']} rolls {$attackRoll} + {$enemyStrModifier} + {$enemyAttackBonus} = {$totalAttack} vs AC {$playerAC} - HIT!", 'enemy');
            $this->addCombatLog($combatData, "{$enemy['name']} deals {$damage} damage to player ({$damageRoll} + {$enemyStrModifier})!", 'enemy');

            if ($player->hp <= 0) {
                $combatData['status'] = 'defeat';
                $this->addCombatLog($combatData, "Player has been defeated!", 'system');
            }
        } else {
            // Miss!
            $this->addCombatLog($combatData, "{$enemy['name']} rolls {$attackRoll} + {$enemyStrModifier} + {$enemyAttackBonus} = {$totalAttack} vs AC {$playerAC} - MISS!", 'enemy');

            // Still reset defending even on miss
            if ($combatData['player_defending'] ?? false) {
                $combatData['player_defending'] = false;
            }
        }
    }

    private function addCombatLog(array &$combatData, string $message, string $type = 'system'): void
    {
        $combatData['log'][] = [
            'message' => $message,
            'type' => $type,
            'round' => $combatData['round'] ?? 1
        ];
    }

    private function getDifficultyBonus(string $difficulty): int
    {
        return match($difficulty) {
            'easy' => 4,      // +4 bonus for easy (rarely miss)
            'normal' => 0,    // No bonus for normal
            'hard' => -2,     // -2 penalty for hard
            'nightmare' => -4, // -4 penalty for nightmare
            default => 0
        };
    }

    public function showAdventureMap($adventureId)
    {
        $player = $this->getOrCreatePlayer();
        $adventure = $player->adventures()->with('nodes')->findOrFail($adventureId);

        if ($adventure->status !== 'active') {
            return redirect()->route('game.adventures')
                ->with('error', 'This adventure is no longer active.');
        }

        // Get current node details
        $currentNode = null;
        if ($adventure->current_node_id) {
            $mapData = $adventure->generated_map;
            if ($mapData && isset($mapData['map']['nodes'])) {
                foreach ($mapData['map']['nodes'] as $level => $levelNodes) {
                    foreach ($levelNodes as $node) {
                        if ($node['id'] === $adventure->current_node_id) {
                            $currentNode = $node;
                            break 2;
                        }
                    }
                }
            }
        }

        $data = [
            'player' => $player,
            'adventure' => $adventure,
            'mapData' => $adventure->generated_map ?? [],
            'currentNode' => $currentNode
        ];

        return view('game.adventure-map', $data);
    }

    public function getNodeDetails($adventureId, $nodeId)
    {
        $player = $this->getOrCreatePlayer();
        $adventure = $player->adventures()->findOrFail($adventureId);

        $mapData = $adventure->generated_map;
        $nodeDetails = null;

        if ($mapData && isset($mapData['map']['nodes'])) {
            foreach ($mapData['map']['nodes'] as $level => $levelNodes) {
                foreach ($levelNodes as $node) {
                    if ($node['id'] === $nodeId) {
                        $nodeDetails = $node;
                        break 2;
                    }
                }
            }
        }

        if (!$nodeDetails) {
            return response()->json([
                'success' => false,
                'message' => 'Node not found'
            ]);
        }

        $html = view('game.partials.node-details', [
            'node' => $nodeDetails,
            'adventure' => $adventure,
            'isCompleted' => in_array($nodeId, $adventure->completed_nodes ?? [])
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    public function processNodeAction(Request $request, $adventureId, $nodeId)
    {
        $request->validate([
            'action' => 'required|string'
        ]);

        $player = $this->getOrCreatePlayer();
        $adventure = $player->adventures()->findOrFail($adventureId);
        $action = $request->input('action');

        if ($adventure->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Adventure is not active'
            ]);
        }

        // Get node details
        $mapData = $adventure->generated_map;
        $nodeDetails = null;

        if ($mapData && isset($mapData['map']['nodes'])) {
            foreach ($mapData['map']['nodes'] as $level => $levelNodes) {
                foreach ($levelNodes as $node) {
                    if ($node['id'] === $nodeId) {
                        $nodeDetails = $node;
                        break 2;
                    }
                }
            }
        }

        if (!$nodeDetails) {
            return response()->json([
                'success' => false,
                'message' => 'Node not found'
            ]);
        }

        // Check if node is accessible based on connections
        $isAccessible = false;

        if ($nodeId === '1-1') {
            // Start node is always accessible
            $isAccessible = true;
        } elseif ($nodeId === $adventure->current_node_id || in_array($nodeId, $adventure->completed_nodes ?? [])) {
            // Current or completed nodes are accessible
            $isAccessible = true;
        } else {
            // Check if any COMPLETED node connects to this node (must be completed, not just current)
            $connections = $mapData['map']['connections'] ?? [];
            foreach ($connections as $fromNodeId => $toNodeIds) {
                if (in_array($nodeId, $toNodeIds) &&
                    in_array($fromNodeId, $adventure->completed_nodes ?? [])) {
                    $isAccessible = true;
                    break;
                }
            }
        }

        if (!$isAccessible) {
            return response()->json([
                'success' => false,
                'message' => 'This node is not accessible yet'
            ]);
        }

        // Check if player has already entered a different node on the same level
        $nodeLevel = intval(explode('-', $nodeId)[0]);
        if ($adventure->hasEnteredLevel($nodeLevel) && !in_array($nodeId, $adventure->entered_nodes ?? [])) {
            return response()->json([
                'success' => false,
                'message' => 'You have already chosen a path on this level and cannot go back'
            ]);
        }

        // Mark this node as entered when taking an action
        $adventure->addEnteredNode($nodeId);

        $result = $this->executeNodeAction($player, $adventure, $nodeDetails, $action);

        return response()->json($result);
    }

    private function executeNodeAction($player, $adventure, $nodeDetails, $action)
    {
        switch ($action) {
            case 'search_treasure':
                return $this->processTreasureNode($player, $adventure, $nodeDetails);

            case 'explore_event':
                return $this->processEventNode($player, $adventure, $nodeDetails);

            case 'rest':
                return $this->processRestNode($player, $adventure, $nodeDetails);

            case 'explore':
                return $this->processGenericNode($player, $adventure, $nodeDetails);

            case 'enter_combat':
                return $this->prepareForCombat($player, $adventure, $nodeDetails);

            case 'gather_resources':
                return $this->processResourceGatheringNode($player, $adventure, $nodeDetails);

            default:
                // Handle NPC interactions with dialogue choices like 'interact_npc:greet'
                if (strpos($action, 'interact_npc:') === 0) {
                    return $this->processNPCEncounter($player, $adventure, $nodeDetails, $action);
                }
                return [
                    'success' => false,
                    'message' => 'Unknown action: ' . $action
                ];
        }
    }

    private function processTreasureNode($player, $adventure, $nodeDetails)
    {
        $currencyReward = $nodeDetails['currency_reward'] ?? rand(50, 150);
        $player->persistent_currency += $currencyReward;
        $player->save();

        $adventure->addCurrencyEarned($currencyReward);

        $message = "You found {$currencyReward} gold!";

        // Check for item drops
        if ($nodeDetails['has_item_drop'] ?? false) {
            $itemGenerationService = app(\App\Services\ItemGenerationService::class);
            $item = $itemGenerationService->generateTreasureLoot($nodeDetails['level'], $adventure->difficulty);

            if ($item) {
                $this->addItemToPlayerInventory($player, $item);
                $message .= " You also discovered: {$item->name}!";
            }
        }

        // Try to discover recipe from this treasure node
        $recipeDiscovery = $this->tryDiscoverRecipeFromNode($adventure, $nodeDetails);
        if ($recipeDiscovery) {
            $message .= " " . $recipeDiscovery['message'];
        }

        $adventure->addCompletedNode($nodeDetails['id']);

        // Move to next accessible node
        $this->moveToNextNode($adventure);

        return [
            'success' => true,
            'message' => $message,
            'redirect' => route('game.adventure-map', $adventure->id)
        ];
    }

    private function processEventNode($player, $adventure, $nodeDetails)
    {
        $outcomes = $nodeDetails['outcomes'] ?? [];

        // Determine success or failure (70% success chance)
        $isSuccess = mt_rand(1, 100) <= 70;
        $outcomeKey = $isSuccess ? 'success' : 'failure';

        $outcome = $outcomes[$outcomeKey] ?? [
            'currency' => rand(25, 75),
            'description' => 'You discover something interesting.'
        ];

        $message = $nodeDetails['event_type'] ?
            "You investigate the {$nodeDetails['event_type']}. " : "";

        if ($isSuccess) {
            $message .= "Your investigation is successful! ";
            if (isset($outcome['currency']) && $outcome['currency'] > 0) {
                $player->persistent_currency += $outcome['currency'];
                $player->save();
                $adventure->addCurrencyEarned($outcome['currency']);
                $message .= "You gain {$outcome['currency']} gold.";
            }

            // Check for item drops on successful events
            if ($nodeDetails['has_item_drop'] ?? false) {
                $itemGenerationService = app(\App\Services\ItemGenerationService::class);
                $item = $itemGenerationService->generateEventLoot($nodeDetails['level']);

                if ($item) {
                    $this->addItemToPlayerInventory($player, $item);
                    $message .= " You also found: {$item->name}!";
                }
            }
        } else {
            $message .= "Your investigation fails. ";
            if (isset($outcome['damage']) && $outcome['damage'] > 0) {
                $damage = min($outcome['damage'], $player->hp - 1);
                $player->hp -= $damage;
                $player->save();
                $message .= "You take {$damage} damage.";
            }
        }

        // Try to discover recipe from this event node
        $recipeDiscovery = $this->tryDiscoverRecipeFromNode($adventure, $nodeDetails);
        if ($recipeDiscovery) {
            $message .= " " . $recipeDiscovery['message'];
        }

        $adventure->addCompletedNode($nodeDetails['id']);
        $this->moveToNextNode($adventure);

        return [
            'success' => true,
            'message' => $message,
            'redirect' => route('game.adventure-map', $adventure->id)
        ];
    }

    private function processRestNode($player, $adventure, $nodeDetails)
    {
        $healingAmount = $nodeDetails['healing_amount'] ?? rand(10, 25);
        $player->hp = min($player->max_hp, $player->hp + $healingAmount);
        $player->save();

        $adventure->addCompletedNode($nodeDetails['id']);
        $this->moveToNextNode($adventure);

        return [
            'success' => true,
            'message' => "You rest peacefully and recover {$healingAmount} HP.",
            'redirect' => route('game.adventure-map', $adventure->id)
        ];
    }

    private function processResourceGatheringNode($player, $adventure, $nodeDetails)
    {
        $resourceType = $nodeDetails['resource_type'] ?? 'foraging';
        $resourceAmount = $nodeDetails['resource_amount'] ?? 2;
        $gatheringDifficulty = $nodeDetails['gathering_difficulty'] ?? 12;
        $hasSpecialMaterials = $nodeDetails['special_materials'] ?? false;
        $currencyReward = intval($nodeDetails['currency_reward'] ?? 15);

        $message = "You search the area for resources... ";

        // Determine skill for gathering
        $skill = match($resourceType) {
            'mining' => 'str',
            'herbalism' => 'wis',
            'logging' => 'str',
            'foraging' => 'wis',
            default => 'wis'
        };

        // Make skill check
        $skillValue = $player->getTotalStat($skill);
        $roll = rand(1, 20);
        $total = $roll + floor(($skillValue - 10) / 2);

        $success = $total >= $gatheringDifficulty;
        $message .= "You make a {$resourceType} check (rolled {$roll} + " . floor(($skillValue - 10) / 2) . " = {$total} vs DC {$gatheringDifficulty}). ";

        if ($success) {
            $message .= "Success! ";

            // Award currency
            $player->persistent_currency += $currencyReward;
            $player->save();
            $adventure->addCurrencyEarned($currencyReward);
            $message .= "You earn {$currencyReward} gold. ";

            // Generate resources based on type and level
            $resources = $this->generateResourceMaterials($resourceType, $nodeDetails['level'], $resourceAmount, $hasSpecialMaterials);

            foreach ($resources as $resource) {
                $this->addItemToPlayerInventory($player, $resource['item'], $resource['quantity']);
                $message .= "You gathered {$resource['quantity']}x {$resource['item']->name}. ";
            }
        } else {
            $message .= "Failure. You only manage to gather a small amount of common materials. ";

            // Give small consolation reward
            $consolationGold = intval($currencyReward * 0.3);
            $player->persistent_currency += $consolationGold;
            $player->save();
            $adventure->addCurrencyEarned($consolationGold);
            $message .= "You earn {$consolationGold} gold for your effort.";
        }

        // Try to discover recipe from this resource node
        $recipeDiscovery = $this->tryDiscoverRecipeFromNode($adventure, $nodeDetails);
        if ($recipeDiscovery) {
            $message .= " " . $recipeDiscovery['message'];
        }

        $adventure->addCompletedNode($nodeDetails['id']);
        $this->moveToNextNode($adventure);

        return [
            'success' => true,
            'message' => $message,
            'redirect' => route('game.adventure-map', $adventure->id)
        ];
    }

    private function processGenericNode($player, $adventure, $nodeDetails)
    {
        $adventure->addCompletedNode($nodeDetails['id']);
        $this->moveToNextNode($adventure);

        return [
            'success' => true,
            'message' => "You explore the area and find nothing of interest.",
            'redirect' => route('game.adventure-map', $adventure->id)
        ];
    }

    private function prepareForCombat($player, $adventure, $nodeDetails)
    {
        // Just mark as entered - the actual combat will be handled on the combat page
        // Don't complete the node yet, it will be completed after combat
        return [
            'success' => true,
            'message' => 'Preparing for combat...'
        ];
    }

    private function processNPCEncounter($player, $adventure, $nodeDetails, $action)
    {
        $npcData = $nodeDetails['npc_data'] ?? [];
        $dialogueOptions = $nodeDetails['dialogue_options'] ?? [];
        $skillChecks = $nodeDetails['skill_checks'] ?? [];
        $rewards = $nodeDetails['rewards'] ?? [];

        // Extract the dialogue choice from the action (format: "interact_npc:greet")
        $dialogueChoice = explode(':', $action)[1] ?? 'greet';

        if (!isset($dialogueOptions[$dialogueChoice])) {
            return [
                'success' => false,
                'message' => 'Invalid dialogue option.'
            ];
        }

        $choice = $dialogueOptions[$dialogueChoice];
        $message = "You encounter {$npcData['name']}, {$npcData['background']}. ";
        $message .= "{$npcData['current_situation']} ";

        // Check if player meets requirements for this dialogue option
        $canAttempt = true;
        $requirements = $choice['requirements'] ?? [];

        foreach ($requirements as $stat => $minValue) {
            if ($player->getTotalStat($stat) < $minValue) {
                $canAttempt = false;
                $message .= "You attempt to {$choice['text']}, but you lack the required {$stat} ({$minValue} needed, you have {$player->getTotalStat($stat)}). ";
                break;
            }
        }

        if ($canAttempt) {
            // Successful dialogue attempt
            $outcome = $choice['outcomes'][array_rand($choice['outcomes'])];
            $message .= $this->processDialogueOutcome($player, $adventure, $outcome, $npcData, $rewards);

            // Process any skill checks
            if (!empty($skillChecks)) {
                $skillCheck = $skillChecks[0]; // Take first skill check
                $skillValue = $player->getTotalStat($skillCheck['skill']);
                $roll = rand(1, 20);
                $total = $roll + floor(($skillValue - 10) / 2); // D&D modifier calculation

                $message .= " You attempt to {$skillCheck['description']} (rolled {$roll} + " . floor(($skillValue - 10) / 2) . " = {$total} vs DC {$skillCheck['difficulty']}).";

                if ($total >= $skillCheck['difficulty']) {
                    $message .= " Success! " . $this->processSkillCheckSuccess($player, $adventure, $skillCheck, $rewards);
                } else {
                    $message .= " Failure. " . $this->processSkillCheckFailure($player, $adventure, $skillCheck);
                }
            }
        }

        // Try to discover recipe from this NPC encounter
        $recipeDiscovery = $this->tryDiscoverRecipeFromNode($adventure, $nodeDetails);
        if ($recipeDiscovery) {
            $message .= " " . $recipeDiscovery['message'];
        }

        // Complete the node
        $adventure->addCompletedNode($nodeDetails['id']);
        $this->moveToNextNode($adventure);

        // Check if NPC should migrate to village
        if (($rewards['potential_recruitment'] ?? false) && $canAttempt) {
            $migrationMessage = $this->attemptNPCMigration($player, $npcData, $outcome);
            if ($migrationMessage) {
                $message .= $migrationMessage;
            }
        }

        return [
            'success' => true,
            'message' => $message,
            'redirect' => route('game.adventure-map', $adventure->id)
        ];
    }

    private function processDialogueOutcome($player, $adventure, $outcome, $npcData, $rewards)
    {
        $message = "";

        switch ($outcome) {
            case 'positive_reaction':
                $message = "{$npcData['name']} responds warmly to your greeting.";
                break;

            case 'grateful_response':
            case 'grateful_reward':
                $currencyReward = $rewards['currency'] ?? 0;
                $expReward = $rewards['experience'] ?? 0;

                if ($currencyReward > 0) {
                    $player->persistent_currency += $currencyReward;
                    $message .= " In gratitude, {$npcData['name']} gives you {$currencyReward} gold.";
                }

                if ($expReward > 0) {
                    $experienceResult = $player->addExperience($expReward);
                    $message .= " You gain {$expReward} experience from this encounter.";

                    if (!empty($experienceResult['levels_gained'])) {
                        session(['npc_level_up' => $experienceResult]);
                    }
                }

                $player->save();
                break;

            case 'useful_information':
                $info = $rewards['information'] ?? [];
                $message .= " {$npcData['name']} shares valuable information: {$info['description']}.";
                break;

            case 'special_reward':
                $message .= " {$npcData['name']} offers you a special reward for your kindness.";
                break;

            case 'intimidated_compliance':
                $message .= " {$npcData['name']} reluctantly complies with your demands.";
                break;

            case 'defiant_resistance':
                $message .= " {$npcData['name']} refuses to be intimidated and stands their ground.";
                break;

            default:
                $message .= " The encounter proceeds peacefully.";
        }

        return $message;
    }

    private function processSkillCheckSuccess($player, $adventure, $skillCheck, $rewards)
    {
        $message = "Your {$skillCheck['skill']} proves sufficient. ";

        // Award bonus rewards for skill success
        $bonusCurrency = ($rewards['currency'] ?? 0) * 0.5;
        $bonusExp = ($rewards['experience'] ?? 0) * 0.3;

        if ($bonusCurrency > 0) {
            $player->persistent_currency += $bonusCurrency;
            $message .= "You earn an additional {$bonusCurrency} gold. ";
        }

        if ($bonusExp > 0) {
            $player->experience += $bonusExp;
            $message .= "You gain {$bonusExp} bonus experience. ";
        }

        $player->save();

        return $message;
    }

    private function processSkillCheckFailure($player, $adventure, $skillCheck)
    {
        $message = "Despite your efforts, you don't quite succeed.";

        // Minor consequences for failure
        if (rand(1, 100) <= 30) { // 30% chance of minor damage
            $damage = rand(1, 3);
            $player->hp = max(1, $player->hp - $damage);
            $player->save();
            $message .= " You take {$damage} damage in the process.";
        }

        return $message;
    }

    private function attemptNPCMigration($player, $npcData, $outcome)
    {
        // Only migrate on positive outcomes
        $positiveOutcomes = ['grateful_response', 'grateful_reward', 'charmed_cooperation', 'special_reward'];

        if (in_array($outcome, $positiveOutcomes)) {
            // Create NPC in database for the player's village
            $npc = $player->npcs()->create([
                'name' => $npcData['name'],
                'personality' => $npcData['disposition'] ?? 'neutral',
                'profession' => $this->determineNPCProfession($npcData),
                'relationship_score' => 25, // Start with positive relationship due to good encounter
                'village_status' => 'migrating', // Will become 'settled' when they arrive
                'arrived_at' => now()->addHours(rand(2, 8)), // They arrive in 2-8 hours
                'conversation_history' => [],
                'available_services' => $this->determineNPCServices($npcData)
            ]);

            // Also store in session for immediate feedback
            $migratedNPCs = session('migrated_npcs', []);
            $migratedNPCs[] = [
                'name' => $npcData['name'],
                'type' => $npcData['disposition'],
                'background' => $npcData['background'],
                'migrated_at' => now(),
                'player_id' => $player->id
            ];
            session(['migrated_npcs' => $migratedNPCs]);

            // Return message about NPC migration
            return " {$npcData['name']} is so impressed by your kindness that they decide to follow you to your village!";
        }

        return null; // No migration occurred
    }

    /**
     * Determine the NPC's profession based on their background
     */
    private function determineNPCProfession($npcData): string
    {
        $background = $npcData['background'] ?? '';

        if (str_contains($background, 'merchant')) {
            return 'merchant';
        } elseif (str_contains($background, 'scholar')) {
            return 'scholar';
        } elseif (str_contains($background, 'guard') || str_contains($background, 'soldier')) {
            return 'guard';
        } elseif (str_contains($background, 'artisan') || str_contains($background, 'craft')) {
            return 'artisan';
        } elseif (str_contains($background, 'guide')) {
            return 'guide';
        } elseif (str_contains($background, 'pilgrim')) {
            return 'cleric';
        } else {
            return 'laborer';
        }
    }

    /**
     * Determine what services the NPC can provide
     */
    private function determineNPCServices($npcData): array
    {
        $profession = $this->determineNPCProfession($npcData);

        return match($profession) {
            'merchant' => ['trade', 'appraise_items'],
            'scholar' => ['research', 'identify_items', 'lore'],
            'guard' => ['training', 'defense', 'patrol'],
            'artisan' => ['crafting', 'repair_equipment'],
            'guide' => ['scouting', 'navigation'],
            'cleric' => ['healing', 'blessing'],
            default => ['basic_labor']
        };
    }

    private function moveToNextNode($adventure)
    {
        // Don't automatically move the player to next nodes
        // Let the player choose their path from the available connected nodes
        // The accessibility logic in processNodeAction will handle which nodes are available

        $mapData = $adventure->generated_map;
        if (!$mapData || !isset($mapData['map']['nodes'])) {
            return;
        }

        // Check if adventure is complete (all levels completed)
        $totalLevels = count($mapData['map']['nodes']);
        $completedLevels = $adventure->getCompletedLevelsCount();

        if ($completedLevels >= $totalLevels) {
            // Discover recipes from completed adventure
            $recipeDiscoveryService = app(\App\Services\RecipeDiscoveryService::class);
            $discoveredRecipes = $recipeDiscoveryService->discoverRecipesFromAdventure($adventure->player, $adventure);

            // Store discovered recipes in session for display
            if (!empty($discoveredRecipes)) {
                session(['discovered_recipes' => $discoveredRecipes]);
            }

            $adventure->markCompleted();
        }
    }

    /**
     * Try to discover a recipe from completing a node
     */
    private function tryDiscoverRecipeFromNode($adventure, $nodeDetails): ?array
    {
        if (!$nodeDetails || !isset($nodeDetails['type'])) {
            return null;
        }

        $recipeDiscoveryService = app(\App\Services\RecipeDiscoveryService::class);
        $nodeLevel = intval(explode('-', $nodeDetails['id'])[0]);

        $discoveredRecipe = $recipeDiscoveryService->tryDiscoverRecipeFromNode(
            $adventure->player,
            $adventure,
            $nodeDetails['type'],
            $nodeLevel
        );

        if ($discoveredRecipe) {
            return [
                'recipe' => $discoveredRecipe,
                'message' => "You discovered a new crafting recipe: {$discoveredRecipe->name}!"
            ];
        }

        return null;
    }

    /**
     * Get node details from adventure map
     */
    private function findNodeInAdventure($adventure, $nodeId): ?array
    {
        $mapData = $adventure->generated_map;
        if (!$mapData || !isset($mapData['map']['nodes'])) {
            return null;
        }

        foreach ($mapData['map']['nodes'] as $level => $levelNodes) {
            foreach ($levelNodes as $node) {
                if ($node['id'] === $nodeId) {
                    return $node;
                }
            }
        }

        return null;
    }

    /**
     * Add item to player's inventory
     */
    private function addItemToPlayerInventory($player, $item, $quantity = 1): void
    {
        // Add item to the new PlayerItem inventory system
        $player->addItemToPlayerInventory($item, $quantity);
    }

    /**
     * Equip an item from PlayerItem inventory
     */
    public function equipPlayerItem(Request $request, $id)
    {
        $player = $this->getOrCreatePlayer();
        $playerItem = $player->playerItems()->with('item')->findOrFail($id);

        if (!$playerItem->canEquip()) {
            return response()->json(['error' => 'Cannot equip this item'], 400);
        }

        $item = $playerItem->item;

        // Determine the equipment slot
        $slot = $playerItem->getEquipmentSlot();
        if (!$slot) {
            return response()->json(['error' => 'Item cannot be equipped in any slot'], 400);
        }

        // Handle special slots that might conflict
        $slotsToUnequip = [];
        if ($slot === 'two_handed_weapon') {
            $slotsToUnequip = ['weapon_1', 'weapon_2', 'shield'];
        } elseif (in_array($slot, ['weapon_1', 'weapon_2', 'shield'])) {
            $slotsToUnequip = ['two_handed_weapon'];
        }

        // Unequip conflicting items
        foreach ($slotsToUnequip as $conflictSlot) {
            $conflictingItem = $player->getEquippedPlayerItem($conflictSlot);
            if ($conflictingItem) {
                $conflictingItem->update([
                    'is_equipped' => false,
                    'equipment_slot' => null
                ]);
            }
        }

        // Unequip current item in this slot
        $currentEquipped = $player->getEquippedPlayerItem($slot);
        if ($currentEquipped) {
            $currentEquipped->update([
                'is_equipped' => false,
                'equipment_slot' => null
            ]);
        }

        // Equip the new item
        $playerItem->update([
            'is_equipped' => true,
            'equipment_slot' => $slot
        ]);

        return response()->json([
            'success' => true,
            'message' => "Equipped {$item->name} successfully!",
            'item' => $item,
            'slot' => $slot
        ]);
    }

    /**
     * Unequip a PlayerItem
     */
    public function unequipPlayerItem(Request $request, $id)
    {
        $player = $this->getOrCreatePlayer();
        $playerItem = $player->playerItems()->with('item')->findOrFail($id);

        if (!$playerItem->canUnequip()) {
            return redirect()->route('game.character')
                ->with('error', 'Cannot unequip this item.');
        }

        $playerItem->update([
            'is_equipped' => false,
            'equipment_slot' => null
        ]);

        return redirect()->route('game.character')
            ->with('success', "Unequipped {$playerItem->item->name} successfully!");
    }

    /**
     * Merge old Equipment system with new PlayerItem system for display
     */
    private function getMergedEquipment($player): array
    {
        // Start with the old equipment system
        $equipment = $player->getAllEquipment();

        // Get equipped PlayerItems
        $equippedPlayerItems = $player->equippedItems()->with('item')->get();

        // Create fake Equipment objects for PlayerItems to maintain compatibility
        foreach ($equippedPlayerItems as $playerItem) {
            $slot = $playerItem->equipment_slot;
            if ($slot) {
                // Create a fake Equipment object with the necessary properties
                $fakeEquipment = new class {
                    public $id;
                    public $item;
                    public $durability;
                    public $max_durability;
                    public $slot;

                    public function getSlotDisplayName() {
                        return ucfirst(str_replace('_', ' ', $this->slot ?? ''));
                    }

                    public function getEffectiveACBonus() {
                        return $this->item->ac_bonus ?? 0;
                    }

                    public function getEffectiveStatModifier($stat) {
                        return $this->item->getStatModifier($stat);
                    }

                    public function isDamaged() {
                        return $this->durability < $this->max_durability;
                    }

                    public function getDurabilityPercentage() {
                        if ($this->max_durability <= 0) return 100;
                        return ($this->durability / $this->max_durability) * 100;
                    }
                };

                $fakeEquipment->id = $playerItem->id;
                $fakeEquipment->item = $playerItem->item;
                $fakeEquipment->durability = $playerItem->current_durability ?? $playerItem->item->max_durability ?? 100;
                $fakeEquipment->max_durability = $playerItem->item->max_durability ?? 100;
                $fakeEquipment->slot = $slot;

                $equipment[$slot] = $fakeEquipment;
            }
        }

        return $equipment;
    }

    public function saveAdventureProgress($adventureId)
    {
        $player = $this->getOrCreatePlayer();
        $adventure = $player->adventures()->findOrFail($adventureId);

        // Adventure progress is automatically saved, so this is just a confirmation
        return response()->json([
            'success' => true,
            'message' => 'Progress saved successfully!'
        ]);
    }

    public function abandonAdventure(Request $request, $adventureId)
    {
        $player = $this->getOrCreatePlayer();
        $adventure = $player->adventures()->findOrFail($adventureId);

        if ($adventure->status !== 'active') {
            return redirect()->route('game.adventures')
                ->with('error', 'Adventure is not active and cannot be abandoned.');
        }

        $adventure->abandon();

        return redirect()->route('game.adventures')
            ->with('success', 'Adventure abandoned successfully!');
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

    // Crafting Methods

    public function crafting()
    {
        $player = $this->getOrCreatePlayer();
        
        $craftingService = app(\App\Services\CraftingService::class);
        $availableRecipes = $craftingService->getAvailableRecipesForPlayer($player);
        $materials = $player->inventoryItems()->where('item_type', 'material')->with('item')->get();
        
        return view('game.crafting', [
            'player' => $player,
            'availableRecipes' => $availableRecipes,
            'materials' => $materials
        ]);
    }

    public function getCraftingRecipes(Request $request)
    {
        $player = $this->getOrCreatePlayer();
        $category = $request->get('category', null);

        $craftingService = app(\App\Services\CraftingService::class);
        $recipes = $craftingService->getAvailableRecipesForPlayer($player, $category === 'all' ? null : $category);

        return response()->json([
            'success' => true,
            'recipes' => $recipes
        ]);
    }

    public function craftItem(Request $request)
    {
        $request->validate([
            'recipe_id' => 'required|integer|exists:crafting_recipes,id'
        ]);

        $player = $this->getOrCreatePlayer();
        $recipeId = $request->get('recipe_id');

        $recipe = \App\Models\CraftingRecipe::with(['resultItem', 'materials.materialItem'])->findOrFail($recipeId);

        try {
            $craftingService = app(\App\Services\CraftingService::class);
            $result = $craftingService->craftItem($player, $recipe);

            return response()->json([
                'success' => true,
                'message' => 'Item crafted successfully!',
                'item' => $result['item'],
                'quantity' => $result['quantity'],
                'experience_gained' => $result['experience_gained'],
                'gold_spent' => $result['gold_spent'],
                'player_gold' => $player->fresh()->persistent_currency
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function upgradeItem(Request $request)
    {
        $request->validate([
            'recipe_id' => 'required|integer|exists:crafting_recipes,id',
            'base_item_id' => 'required|integer|exists:player_items,id'
        ]);

        $player = $this->getOrCreatePlayer();
        $recipeId = $request->get('recipe_id');
        $baseItemId = $request->get('base_item_id');

        $recipe = \App\Models\CraftingRecipe::with(['resultItem', 'materials.materialItem'])->findOrFail($recipeId);
        $baseItem = $player->playerItems()->findOrFail($baseItemId);

        try {
            $craftingService = app(\App\Services\CraftingService::class);
            $result = $craftingService->upgradeItem($player, $recipe, $baseItem);

            return response()->json([
                'success' => true,
                'message' => 'Item upgraded successfully!',
                'upgraded_item' => $result['upgraded_item'],
                'quantity' => $result['quantity'],
                'experience_gained' => $result['experience_gained'],
                'gold_spent' => $result['gold_spent'],
                'player_gold' => $player->fresh()->persistent_currency
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function learnRecipe(Request $request)
    {
        $request->validate([
            'recipe_id' => 'required|integer|exists:crafting_recipes,id',
            'discovery_method' => 'nullable|string'
        ]);

        $player = $this->getOrCreatePlayer();
        $recipeId = $request->get('recipe_id');
        $discoveryMethod = $request->get('discovery_method', 'manual');

        $recipe = \App\Models\CraftingRecipe::findOrFail($recipeId);

        $craftingService = app(\App\Services\CraftingService::class);
        $learned = $craftingService->discoverRecipe($player, $recipe, $discoveryMethod);

        if ($learned) {
            return response()->json([
                'success' => true,
                'message' => "You learned the recipe: {$recipe->name}!"
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'You already know this recipe.'
            ], 400);
        }
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
                'unallocated_stat_points' => 5
            ]);
        }

        return $user->player;
    }

    private function generateEnemyFromNode(array $nodeDetails, int $playerLevel): array
    {
        $enemyType = $nodeDetails['enemy_type'] ?? 'goblin';
        $enemyCount = $nodeDetails['enemy_count'] ?? 1;

        // Base enemy stats based on type
        $enemyTypes = [
            'goblin' => ['name' => 'Goblin', 'hp' => 15, 'str' => 8, 'int' => 6, 'wis' => 8, 'ac' => 12],
            'orc' => ['name' => 'Orc', 'hp' => 25, 'str' => 12, 'int' => 6, 'wis' => 6, 'ac' => 13],
            'skeleton' => ['name' => 'Skeleton', 'hp' => 20, 'str' => 10, 'int' => 4, 'wis' => 8, 'ac' => 13],
            'wolf' => ['name' => 'Wolf', 'hp' => 18, 'str' => 11, 'int' => 3, 'wis' => 12, 'ac' => 11],
            'bandit' => ['name' => 'Bandit', 'hp' => 22, 'str' => 10, 'int' => 9, 'wis' => 9, 'ac' => 14],
        ];

        $baseEnemy = $enemyTypes[$enemyType] ?? $enemyTypes['goblin'];

        // Scale enemy stats based on player level
        $levelMultiplier = 1 + ($playerLevel - 1) * 0.1;

        // Generate individual enemies
        $enemies = [];
        for ($i = 0; $i < $enemyCount; $i++) {
            $enemyId = $enemyType . '_' . ($i + 1);
            $hp = intval($baseEnemy['hp'] * $levelMultiplier);

            $enemies[$enemyId] = [
                'id' => $enemyId,
                'name' => $baseEnemy['name'] . ($enemyCount > 1 ? ' #' . ($i + 1) : ''),
                'type' => ucfirst($enemyType),
                'hp' => $hp,
                'max_hp' => $hp,
                'health' => $hp,
                'max_health' => $hp,
                'str' => intval($baseEnemy['str'] * $levelMultiplier),
                'int' => intval($baseEnemy['int'] * $levelMultiplier),
                'wis' => intval($baseEnemy['wis'] * $levelMultiplier),
                'strength' => intval($baseEnemy['str'] * $levelMultiplier),
                'intelligence' => intval($baseEnemy['int'] * $levelMultiplier),
                'wisdom' => intval($baseEnemy['wis'] * $levelMultiplier),
                'ac' => $baseEnemy['ac'],
                'level' => $playerLevel,
                'status' => 'alive'
            ];
        }

        return [
            'enemies' => $enemies,
            'total_count' => $enemyCount,
            'enemy_type' => $enemyType,
            'alive_count' => $enemyCount
        ];
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

    /**
     * Generate resource materials based on gathering type and level
     */
    private function generateResourceMaterials(string $resourceType, int $level, int $amount, bool $hasSpecialMaterials): array
    {
        $resources = [];

        // Define material pools based on resource type
        $materialPools = [
            'mining' => [
                'common' => ['Copper Ore', 'Rough Quartz'],
                'uncommon' => ['Bronze Ingot', 'Polished Amethyst'],
                'rare' => ['Iron Ingot', 'Emerald Shard'],
                'epic' => ['Mithril Ore', 'Sapphire Crystal'],
                'legendary' => ['Adamantium Shard', 'Perfect Diamond']
            ],
            'herbalism' => [
                'common' => ['Healing Moss', 'Blue Sage'],
                'uncommon' => ['Red Clover', 'Mystic Thistle'],
                'rare' => ['Golden Root', 'Arcane Lotus']
            ],
            'logging' => [
                'common' => ['Oak Wood'],
                'uncommon' => ['Enchanted Willow'],
                'rare' => ['Ancient Heartwood']
            ],
            'foraging' => [
                'common' => ['Healing Moss', 'Blue Sage', 'Oak Wood'],
                'uncommon' => ['Red Clover', 'Mystic Thistle', 'Enchanted Willow'],
                'rare' => ['Golden Root', 'Arcane Lotus', 'Ancient Heartwood']
            ]
        ];

        $pool = $materialPools[$resourceType] ?? $materialPools['foraging'];

        // Preload all possible items to avoid N+1 queries
        $allMaterialNames = collect($pool)->flatten()->unique()->values()->toArray();
        $itemsLookup = \App\Models\Item::whereIn('name', $allMaterialNames)->get()->keyBy('name');

        // Determine rarity based on level and special materials
        $rarityWeights = [
            'common' => max(10, 70 - ($level * 5)),
            'uncommon' => min(40, 20 + ($level * 2)),
            'rare' => min(25, $level * 2),
            'epic' => max(0, $level - 8),
            'legendary' => max(0, $level - 12)
        ];

        if ($hasSpecialMaterials) {
            // Boost higher rarities
            $rarityWeights['rare'] *= 2;
            $rarityWeights['epic'] *= 3;
            $rarityWeights['legendary'] *= 4;
        }

        // Generate materials
        for ($i = 0; $i < $amount; $i++) {
            $rarity = $this->weightedRandomRarity($rarityWeights);

            if (isset($pool[$rarity]) && !empty($pool[$rarity])) {
                $materialName = $pool[$rarity][array_rand($pool[$rarity])];
                $item = $itemsLookup->get($materialName);

                if ($item) {
                    $quantity = rand(1, 3); // 1-3 materials per gathering action
                    $resources[] = [
                        'item' => $item,
                        'quantity' => $quantity
                    ];
                }
            }
        }

        return $resources;
    }

    private function weightedRandomRarity(array $weights): string
    {
        $totalWeight = array_sum($weights);
        if ($totalWeight <= 0) return 'common';

        $random = mt_rand(1, $totalWeight);

        foreach ($weights as $rarity => $weight) {
            $random -= $weight;
            if ($random <= 0) {
                return $rarity;
            }
        }

        return 'common';
    }

    /**
     * Get available consumable items for combat
     */
    private function getAvailableConsumables(Player $player): array
    {
        $consumables = [];

        // Get from old inventory system
        $oldConsumables = $player->inventory()->with('item')
            ->whereHas('item', function($query) {
                $query->where('type', 'consumable');
            })
            ->where('quantity', '>', 0)
            ->get();

        foreach ($oldConsumables as $inventoryItem) {
            if ($inventoryItem->item) {
                $consumables[] = [
                    'id' => $inventoryItem->item->id,
                    'name' => $inventoryItem->item->name,
                    'description' => $inventoryItem->item->description,
                    'quantity' => $inventoryItem->quantity,
                    'system' => 'old'
                ];
            }
        }

        // Get from new PlayerItem system
        $newConsumables = $player->playerItems()->with('item')
            ->where('is_equipped', false)
            ->whereHas('item', function($query) {
                $query->where('type', 'consumable');
            })
            ->where('quantity', '>', 0)
            ->get();

        foreach ($newConsumables as $playerItem) {
            if ($playerItem->item) {
                $consumables[] = [
                    'id' => $playerItem->item->id,
                    'name' => $playerItem->item->name,
                    'description' => $playerItem->item->description,
                    'quantity' => $playerItem->quantity,
                    'system' => 'new'
                ];
            }
        }

        return $consumables;
    }

    /**
     * Find a specific consumable item in player's inventory
     */
    private function findConsumableItem(Player $player, int $itemId): ?array
    {
        // Check old inventory system
        $oldItem = $player->inventory()->with('item')
            ->where('item_id', $itemId)
            ->whereHas('item', function($query) {
                $query->where('type', 'consumable');
            })
            ->first();

        if ($oldItem && $oldItem->quantity > 0) {
            return [
                'id' => $oldItem->item->id,
                'name' => $oldItem->item->name,
                'item' => $oldItem->item,
                'inventory_item' => $oldItem,
                'system' => 'old'
            ];
        }

        // Check new PlayerItem system
        $newItem = $player->playerItems()->with('item')
            ->where('item_id', $itemId)
            ->where('is_equipped', false)
            ->whereHas('item', function($query) {
                $query->where('type', 'consumable');
            })
            ->first();

        if ($newItem && $newItem->quantity > 0) {
            return [
                'id' => $newItem->item->id,
                'name' => $newItem->item->name,
                'item' => $newItem->item,
                'player_item' => $newItem,
                'system' => 'new'
            ];
        }

        return null;
    }

    /**
     * Apply consumable item effects to player
     */
    private function applyConsumableEffects(Player $player, array $consumable): string
    {
        $effects = [];
        $item = $consumable['item'];

        // Apply healing effects
        if ($item->heal_amount && $item->heal_amount > 0) {
            $healAmount = $item->heal_amount;
            $oldHP = $player->hp;
            $player->hp = min($player->max_hp, $player->hp + $healAmount);
            $actualHeal = $player->hp - $oldHP;
            $effects[] = "healed {$actualHeal} HP";
        }

        // Apply other effects (can be extended later)
        if ($item->effects) {
            // Handle custom effects from item data
            $itemEffects = is_string($item->effects) ? json_decode($item->effects, true) : $item->effects;
            if (is_array($itemEffects)) {
                foreach ($itemEffects as $effect => $value) {
                    // Add more effect types as needed
                    $effects[] = "{$effect}: {$value}";
                }
            }
        }

        $player->save();

        return implode(', ', $effects) ?: 'applied unknown effects';
    }

    /**
     * Remove consumable item from player's inventory
     */
    private function removeConsumableFromInventory(Player $player, array $consumable): void
    {
        if ($consumable['system'] === 'old' && isset($consumable['inventory_item'])) {
            $inventoryItem = $consumable['inventory_item'];
            if ($inventoryItem->quantity <= 1) {
                $inventoryItem->delete();
            } else {
                $inventoryItem->quantity -= 1;
                $inventoryItem->save();
            }
        } elseif ($consumable['system'] === 'new' && isset($consumable['player_item'])) {
            $playerItem = $consumable['player_item'];
            if ($playerItem->quantity <= 1) {
                $playerItem->delete();
            } else {
                $playerItem->quantity -= 1;
                $playerItem->save();
            }
        }
    }
}
