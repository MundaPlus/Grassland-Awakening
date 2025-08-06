<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Player;
use App\Models\Item;
use App\Models\Adventure;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'admin_users' => User::where('is_admin', true)->count(),
            'active_players' => Player::count(),
            'total_items' => Item::count(),
            'active_adventures' => Adventure::where('status', 'active')->count(),
            'completed_adventures' => Adventure::where('status', 'completed')->count(),
        ];

        $recentUsers = User::with('player')
            ->latest()
            ->take(10)
            ->get();

        $recentPlayers = Player::with('user')
            ->latest()
            ->take(10)
            ->get();

        $systemInfo = [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_type' => config('database.default'),
        ];

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentPlayers', 'systemInfo'));
    }

    /**
     * Display user management page
     */
    public function users()
    {
        $users = User::with('player')
            ->withCount(['player'])
            ->paginate(20);

        return view('admin.users', compact('users'));
    }

    /**
     * Toggle admin status for a user
     */
    public function toggleAdmin(User $user)
    {
        $user->is_admin = !$user->is_admin;
        $user->save();

        return redirect()->back()->with('success', 
            $user->is_admin ? "User {$user->name} is now an admin." : "User {$user->name} is no longer an admin."
        );
    }

    /**
     * Display players management page
     */
    public function players()
    {
        $players = Player::with('user')
            ->orderBy('level', 'desc')
            ->paginate(20);

        return view('admin.players', compact('players'));
    }

    /**
     * Display items management page
     */
    public function items()
    {
        $items = Item::orderBy('type')
            ->orderBy('rarity')
            ->orderBy('name')
            ->paginate(30);

        $itemTypes = Item::select('type')->distinct()->pluck('type');
        $rarities = ['common', 'uncommon', 'rare', 'epic', 'legendary'];

        return view('admin.items', compact('items', 'itemTypes', 'rarities'));
    }

    /**
     * Create a new item
     */
    public function createItem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:weapon,armor,accessory,consumable,crafting_material,quest_item,misc',
            'subtype' => 'nullable|string|max:255',
            'rarity' => 'required|in:common,uncommon,rare,epic,legendary',
            'base_value' => 'required|integer|min:0',
            'level_requirement' => 'nullable|integer|min:1',
        ]);

        Item::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'subtype' => $request->subtype,
            'rarity' => $request->rarity,
            'base_value' => $request->base_value,
            'level_requirement' => $request->level_requirement,
            'is_stackable' => $request->has('is_stackable'),
            'is_consumable' => $request->type === 'consumable',
            'max_stack_size' => $request->type === 'consumable' ? ($request->max_stack_size ?? 99) : 1,
        ]);

        return redirect()->back()->with('success', 'Item created successfully!');
    }

    /**
     * Delete an item
     */
    public function deleteItem(Item $item)
    {
        $item->delete();
        return redirect()->back()->with('success', 'Item deleted successfully!');
    }

    /**
     * Display adventures management page
     */
    public function adventures()
    {
        $adventures = Adventure::with('player.user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $adventureStats = [
            'active' => Adventure::where('status', 'active')->count(),
            'completed' => Adventure::where('status', 'completed')->count(),
            'failed' => Adventure::where('status', 'failed')->count(),
            'paused' => Adventure::where('status', 'paused')->count(),
        ];

        return view('admin.adventures', compact('adventures', 'adventureStats'));
    }

    /**
     * Give items to a player
     */
    public function giveItemToPlayer(Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:players,id',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1|max:999',
        ]);

        $player = Player::findOrFail($request->player_id);
        $item = Item::findOrFail($request->item_id);

        $player->addItemToPlayerInventory($item, $request->quantity);

        return redirect()->back()->with('success', 
            "Gave {$request->quantity}x {$item->name} to {$player->character_name}!"
        );
    }

    /**
     * Update player stats
     */
    public function updatePlayerStats(Request $request, Player $player)
    {
        $request->validate([
            'level' => 'required|integer|min:1|max:100',
            'experience' => 'required|integer|min:0',
            'hp' => 'required|integer|min:1',
            'max_hp' => 'required|integer|min:1',
            'str' => 'required|integer|min:1|max:30',
            'dex' => 'required|integer|min:1|max:30',
            'con' => 'required|integer|min:1|max:30',
            'int' => 'required|integer|min:1|max:30',
            'wis' => 'required|integer|min:1|max:30',
            'cha' => 'required|integer|min:1|max:30',
        ]);

        $player->update([
            'level' => $request->level,
            'experience' => $request->experience,
            'hp' => $request->hp,
            'max_hp' => $request->max_hp,
            'str' => $request->str,
            'dex' => $request->dex,
            'con' => $request->con,
            'int' => $request->int,
            'wis' => $request->wis,
            'cha' => $request->cha,
        ]);

        return redirect()->back()->with('success', 
            "Updated stats for {$player->character_name}!"
        );
    }

    /**
     * Display system settings page
     */
    public function settings()
    {
        // You can add system-wide settings here
        $settings = [
            'game_version' => '1.0.0',
            'maintenance_mode' => false,
            'max_player_level' => 100,
            'base_experience_multiplier' => 1.0,
        ];

        return view('admin.settings', compact('settings'));
    }

    /**
     * Clear application cache
     */
    public function clearCache()
    {
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('view:clear');
        
        return response()->json(['success' => true, 'message' => 'Application cache cleared successfully!']);
    }

    /**
     * Export player data to CSV
     */
    public function exportPlayerData()
    {
        $players = Player::with('user')->get();
        
        $csvData = "ID,Character Name,User Name,Level,HP,Experience,Gold,Created At\n";
        
        foreach ($players as $player) {
            $csvData .= sprintf(
                "%d,%s,%s,%d,%d,%d,%d,%s\n",
                $player->id,
                $player->character_name,
                $player->user->name,
                $player->level,
                $player->hp,
                $player->experience,
                $player->persistent_currency,
                $player->created_at->format('Y-m-d H:i:s')
            );
        }
        
        $fileName = 'player_data_' . date('Y-m-d_H-i-s') . '.csv';
        
        return response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Clean old log files
     */
    public function cleanLogs()
    {
        $logPath = storage_path('logs');
        $files = glob($logPath . '/*.log');
        $cleaned = 0;
        
        foreach ($files as $file) {
            if (filemtime($file) < strtotime('-30 days')) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return response()->json(['success' => true, 'message' => "Cleaned {$cleaned} old log files"]);
    }

    /**
     * Create database backup
     */
    public function backupDatabase()
    {
        try {
            $databaseName = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            
            $fileName = 'backup_' . $databaseName . '_' . date('Y-m-d_H-i-s') . '.sql';
            $backupPath = storage_path('app/backups');
            
            // Create backup directory if it doesn't exist
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            $fullPath = $backupPath . '/' . $fileName;
            
            // Use mysqldump to create backup
            $command = "mysqldump --user={$username} --password={$password} --host={$host} {$databaseName} > {$fullPath}";
            
            $returnVar = null;
            $output = [];
            exec($command, $output, $returnVar);
            
            if ($returnVar === 0) {
                $fileSize = round(filesize($fullPath) / 1024 / 1024, 2); // Size in MB
                return response()->json([
                    'success' => true, 
                    'message' => "Database backup created successfully! File: {$fileName} ({$fileSize} MB)",
                    'file' => $fileName
                ]);
            } else {
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to create database backup. Check server configuration.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error creating backup: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Save system settings
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'maxLevel' => 'integer|min:1|max:100',
            'maxHpPerLevel' => 'integer|min:1|max:100',
            'maxStatValue' => 'integer|min:10|max:50',
            'skillPointsPerLevel' => 'integer|min:1|max:10',
            'baseExpRequired' => 'integer|min:50|max:5000',
            'expMultiplier' => 'numeric|min:1.1|max:5.0',
            'startingGold' => 'integer|min:0|max:50000',
            'combatGoldMultiplier' => 'numeric|min:0.1|max:10.0',
            'shopTaxRate' => 'numeric|min:0|max:50',
            'repairCostMultiplier' => 'numeric|min:0.01|max:2.0',
        ]);

        // In a real implementation, you'd save these to a settings table or config file
        // For now, just log the settings that would be saved
        \Log::info('Admin settings updated', $request->all());
        
        return response()->json(['success' => true, 'message' => 'Settings saved successfully!']);
    }

    /**
     * Update player via AJAX
     */
    public function updatePlayer(Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:players,id',
            'character_name' => 'required|string|max:255',
            'level' => 'required|integer|min:1|max:100',
            'hp' => 'required|integer|min:1',
            'max_hp' => 'required|integer|min:1',
            'str' => 'required|integer|min:1|max:30',
            'dex' => 'required|integer|min:1|max:30',
            'con' => 'required|integer|min:1|max:30',
            'int' => 'required|integer|min:1|max:30',
            'wis' => 'required|integer|min:1|max:30',
            'cha' => 'required|integer|min:1|max:30',
            'experience' => 'required|integer|min:0',
            'persistent_currency' => 'required|integer|min:0',
        ]);

        $player = Player::findOrFail($request->player_id);
        $player->update($request->except(['player_id']));

        return response()->json(['success' => true, 'message' => 'Player updated successfully!']);
    }

    /**
     * QA Testing Dashboard
     */
    public function qaTesting()
    {
        $players = Player::with('user')->latest()->limit(50)->get();
        $items = \App\Models\Item::all();
        $skills = \App\Models\Skill::all();
        
        return view('admin.qa-testing', compact('players', 'items', 'skills'));
    }

    /**
     * Reset player to level 1
     */
    public function resetPlayer(Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:players,id'
        ]);

        $player = Player::findOrFail($request->player_id);
        
        // Reset player stats
        $player->update([
            'level' => 1,
            'experience' => 0,
            'hp' => 10,
            'max_hp' => 10,
            'ac' => 10,
            'str' => 10,
            'dex' => 10,
            'con' => 10,
            'int' => 10,
            'wis' => 10,
            'cha' => 10,
            'unallocated_stat_points' => 0,
            'skill_points' => 0,
            'persistent_currency' => 100, // Starting gold
            'current_road' => null,
            'current_level' => null,
            'current_node_id' => null
        ]);

        // Clear inventory
        $player->playerItems()->delete();
        $player->inventory()->delete();
        $player->equipment()->delete();

        // Reset adventures
        $player->adventures()->delete();

        // Reset skills
        $player->playerSkills()->delete();
        $player->skillCooldowns()->delete();

        // Reset achievements
        $player->achievements()->delete();

        return response()->json(['success' => true, 'message' => "Player {$player->character_name} reset to level 1!"]);
    }

    /**
     * Set player level with correct stat and skill points
     */
    public function setPlayerLevel(Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:players,id',
            'level' => 'required|integer|min:1|max:100'
        ]);

        $player = Player::findOrFail($request->player_id);
        $targetLevel = $request->level;
        $currentLevel = $player->level;

        if ($targetLevel <= $currentLevel) {
            return response()->json(['success' => false, 'message' => 'Target level must be higher than current level']);
        }

        $levelDifference = $targetLevel - $currentLevel;
        
        // Calculate stat points gained (2 per level)
        $statPointsGained = $levelDifference * 2;
        
        // Calculate skill points gained (1 per level)
        $skillPointsGained = $levelDifference;
        
        // Calculate HP gained (5 + CON modifier per level)
        $conModifier = floor(($player->con - 10) / 2);
        $hpGained = $levelDifference * (5 + $conModifier);
        
        // Calculate experience needed for target level
        $experienceNeeded = 0;
        for ($i = 1; $i < $targetLevel; $i++) {
            $experienceNeeded += $i * 100;
        }

        $player->update([
            'level' => $targetLevel,
            'experience' => $experienceNeeded,
            'unallocated_stat_points' => $player->unallocated_stat_points + $statPointsGained,
            'skill_points' => $player->skill_points + $skillPointsGained,
            'max_hp' => $player->max_hp + $hpGained,
            'hp' => $player->max_hp + $hpGained // Full heal
        ]);

        return response()->json([
            'success' => true, 
            'message' => "Player {$player->character_name} set to level {$targetLevel}! Gained {$statPointsGained} stat points, {$skillPointsGained} skill points, and {$hpGained} HP."
        ]);
    }

    /**
     * Give item to player for QA
     */
    public function giveItemToPlayerQA(Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:players,id',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'integer|min:1|max:999'
        ]);

        $player = Player::findOrFail($request->player_id);
        $item = \App\Models\Item::findOrFail($request->item_id);
        $quantity = $request->quantity ?? 1;

        $player->addItemToPlayerInventory($item, $quantity);

        return response()->json([
            'success' => true, 
            'message' => "Gave {$quantity} x {$item->name} to {$player->character_name}!"
        ]);
    }

    /**
     * Set player stats manually for testing
     */
    public function setPlayerStatsQA(Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:players,id',
            'str' => 'integer|min:1|max:30',
            'dex' => 'integer|min:1|max:30',
            'con' => 'integer|min:1|max:30',
            'int' => 'integer|min:1|max:30',
            'wis' => 'integer|min:1|max:30',
            'cha' => 'integer|min:1|max:30',
        ]);

        $player = Player::findOrFail($request->player_id);
        
        $stats = [];
        foreach (['str', 'dex', 'con', 'int', 'wis', 'cha'] as $stat) {
            if ($request->has($stat)) {
                $stats[$stat] = $request->$stat;
            }
        }

        // If CON changed, adjust HP
        if (isset($stats['con']) && $stats['con'] != $player->con) {
            $oldConMod = floor(($player->con - 10) / 2);
            $newConMod = floor(($stats['con'] - 10) / 2);
            $hpChange = ($newConMod - $oldConMod) * $player->level;
            
            $stats['max_hp'] = max(1, $player->max_hp + $hpChange);
            $stats['hp'] = max(1, $player->hp + $hpChange);
        }

        $player->update($stats);

        return response()->json([
            'success' => true, 
            'message' => "Updated stats for {$player->character_name}!"
        ]);
    }

    /**
     * Add/remove currency from player
     */
    public function addCurrencyQA(Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:players,id',
            'amount' => 'required|integer'
        ]);

        $player = Player::findOrFail($request->player_id);
        $amount = $request->amount;
        
        $player->increment('persistent_currency', $amount);

        $action = $amount >= 0 ? 'Added' : 'Removed';
        $absAmount = abs($amount);

        return response()->json([
            'success' => true, 
            'message' => "{$action} {$absAmount} gold to {$player->character_name}! New balance: {$player->persistent_currency}"
        ]);
    }

    /**
     * Clear player inventory for testing
     */
    public function clearInventoryQA(Request $request)
    {
        $request->validate([
            'player_id' => 'required|exists:players,id'
        ]);

        $player = Player::findOrFail($request->player_id);
        
        $player->playerItems()->where('is_equipped', false)->delete();
        $player->inventory()->delete();

        return response()->json([
            'success' => true, 
            'message' => "Cleared inventory for {$player->character_name}!"
        ]);
    }
}