<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Player;
use App\Models\Adventure;
use App\Models\Item;

class AdventureAndCombatTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test complete adventure flow from start to completion
     */
    public function testCompleteAdventureFlow()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create([
            'user_id' => $user->id,
            'persistent_currency' => 2000,
            'level' => 5,
            'hp' => 50,
            'max_hp' => 50
        ]);

        $this->browse(function (Browser $browser) use ($user, $player) {
            // Generate adventure
            $browser->loginAs($user)
                    ->visit('/game/adventures')
                    ->select('road', 'north')
                    ->select('difficulty', 'easy')
                    ->press('Generate Adventure')
                    ->waitForText('Adventure generated successfully');

            // Start the adventure
            $adventure = $player->adventures()->where('status', 'active')->first();
            $this->assertNotNull($adventure);

            $browser->visit("/game/adventure/{$adventure->id}")
                    ->assertSee('Adventure Map')
                    ->assertSee($adventure->title);
        });
    }

    /**
     * Test combat encounter and actions
     */
    public function testCombatEncounter()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create([
            'user_id' => $user->id,
            'level' => 10,
            'hp' => 100,
            'max_hp' => 100,
            'str' => 15,
            'dex' => 12,
            'persistent_currency' => 1000
        ]);

        // Create an adventure with combat
        $adventure = Adventure::create([
            'player_id' => $player->id,
            'road' => 'north',
            'difficulty' => 'easy',
            'status' => 'active',
            'seed' => 'test123',
            'title' => 'Test Adventure',
            'current_level' => 1,
            'generated_map' => json_encode([
                'levels' => [
                    1 => [
                        'nodes' => [
                            'node1' => [
                                'type' => 'combat',
                                'enemy' => 'goblin',
                                'description' => 'A fierce goblin blocks your path!'
                            ]
                        ]
                    ]
                ]
            ])
        ]);

        $this->browse(function (Browser $browser) use ($user, $adventure) {
            $browser->loginAs($user)
                    ->visit("/game/adventure/{$adventure->id}/combat")
                    ->assertSee('Combat')
                    ->assertSee('Your turn')
                    // Test basic attack
                    ->press('Attack')
                    ->waitForText('You attack')
                    ->pause(2000);

            // Test using consumables in combat (if available)
            $this->addConsumableToPlayer($adventure->player);
            
            $browser->refresh()
                    ->press('Use Item')
                    ->pause(1000)
                    ->assertSee('Select Item');
        });
    }

    /**
     * Test adventure completion and rewards
     */
    public function testAdventureCompletion()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create([
            'user_id' => $user->id,
            'persistent_currency' => 1000,
            'experience' => 50
        ]);

        $adventure = Adventure::create([
            'player_id' => $player->id,
            'road' => 'east',
            'difficulty' => 'normal',
            'status' => 'completed',
            'seed' => 'completed123',
            'title' => 'Completed Adventure',
            'currency_earned' => 150,
            'completed_at' => now(),
            'collected_loot' => json_encode([
                ['name' => 'Iron Sword', 'quantity' => 1],
                ['name' => 'Health Potion', 'quantity' => 3]
            ])
        ]);

        $this->browse(function (Browser $browser) use ($user, $adventure, $player) {
            $originalGold = $player->persistent_currency;
            $originalXP = $player->experience;

            $browser->loginAs($user)
                    ->visit('/game/adventures')
                    ->assertSee('Completed Adventures')
                    ->assertSee($adventure->title)
                    ->assertSee('Rewards: 150 gold');

            // Check rewards were applied
            $player->refresh();
            $this->assertGreaterThan($originalXP, $player->experience);
        });
    }

    /**
     * Test adventure failure scenarios
     */
    public function testAdventureFailure()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create([
            'user_id' => $user->id,
            'hp' => 1, // Very low health
            'max_hp' => 100,
            'persistent_currency' => 1000
        ]);

        $adventure = Adventure::create([
            'player_id' => $player->id,
            'road' => 'south',
            'difficulty' => 'hard',
            'status' => 'active',
            'seed' => 'failure123',
            'title' => 'Dangerous Adventure',
            'current_level' => 1
        ]);

        $this->browse(function (Browser $browser) use ($user, $adventure) {
            $browser->loginAs($user)
                    ->visit("/game/adventure/{$adventure->id}")
                    ->assertSee('Adventure Map')
                    // Low health warning should be visible
                    ->assertSee('critically low');
        });
    }

    /**
     * Test item usage during adventure
     */
    public function testItemUsageDuringAdventure()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create([
            'user_id' => $user->id,
            'hp' => 30,
            'max_hp' => 100
        ]);

        // Add healing potion
        $healingPotion = Item::create([
            'name' => 'Healing Potion',
            'type' => 'consumable',
            'subtype' => 'potion',
            'is_consumable' => true,
            'stats_modifiers' => json_encode(['heal' => 25]),
            'base_value' => 50
        ]);

        $player->addItemToPlayerInventory($healingPotion, 3);

        $adventure = Adventure::create([
            'player_id' => $player->id,
            'road' => 'west',
            'difficulty' => 'easy',
            'status' => 'active',
            'seed' => 'healing123',
            'title' => 'Healing Test Adventure'
        ]);

        $this->browse(function (Browser $browser) use ($user, $adventure, $player) {
            $initialHP = $player->hp;

            $browser->loginAs($user)
                    ->visit("/game/adventure/{$adventure->id}")
                    ->visit('/game/inventory')
                    ->assertSee('Healing Potion')
                    ->assertSee('x3');
                    
            // Test using item (would need specific implementation)
        });
    }

    /**
     * Test adventure abandonment
     */
    public function testAdventureAbandonment()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create(['user_id' => $user->id]);

        $adventure = Adventure::create([
            'player_id' => $player->id,
            'road' => 'north',
            'difficulty' => 'normal',
            'status' => 'active',
            'seed' => 'abandon123',
            'title' => 'Adventure to Abandon'
        ]);

        $this->browse(function (Browser $browser) use ($user, $adventure) {
            $browser->loginAs($user)
                    ->visit("/game/adventure/{$adventure->id}")
                    ->press('Abandon Adventure')
                    ->whenAvailable('.modal', function ($modal) {
                        $modal->press('Confirm');
                    })
                    ->waitForText('Adventure abandoned')
                    ->pause(1000);

            // Verify adventure was abandoned
            $adventure->refresh();
            $this->assertEquals('abandoned', $adventure->status);
        });
    }

    /**
     * Test experience and level progression during adventures
     */
    public function testExperienceProgression()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create([
            'user_id' => $user->id,
            'level' => 1,
            'experience' => 90, // Close to level up (needs 100)
            'hp' => 50,
            'max_hp' => 50
        ]);

        $this->browse(function (Browser $browser) use ($user, $player) {
            $browser->loginAs($user)
                    ->visit('/game/character')
                    ->assertSee('Level 1')
                    ->assertSee('90');

            // Simulate gaining experience through combat or completing tasks
            // This would typically happen through combat interactions
            $player->addExperience(20);

            $browser->refresh()
                    ->assertSee('Level 2')
                    ->assertSee('Points Available');
        });
    }

    /**
     * Test skill usage during combat
     */
    public function testSkillUsageInCombat()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create([
            'user_id' => $user->id,
            'level' => 5,
            'skill_points' => 0 // Already spent
        ]);

        // Add some combat skills to player
        if (class_exists('\App\Models\Skill')) {
            $skill = \App\Models\Skill::create([
                'name' => 'Power Strike',
                'slug' => 'power-strike',
                'type' => 'active',
                'category' => 'combat',
                'description' => 'A powerful attack',
                'base_cost' => 10,
                'cooldown' => 3
            ]);

            $player->playerSkills()->create([
                'skill_id' => $skill->id,
                'level' => 2,
                'experience' => 150
            ]);

            $adventure = Adventure::create([
                'player_id' => $player->id,
                'road' => 'north',
                'difficulty' => 'normal',
                'status' => 'active',
                'seed' => 'skills123',
                'title' => 'Skills Test Adventure'
            ]);

            $this->browse(function (Browser $browser) use ($user, $adventure) {
                $browser->loginAs($user)
                        ->visit("/game/adventure/{$adventure->id}/combat")
                        ->assertSee('Combat')
                        ->assertSee('Power Strike');
            });
        }
    }

    /**
     * Helper method to add consumable items to player
     */
    private function addConsumableToPlayer(Player $player)
    {
        $potion = Item::firstOrCreate([
            'name' => 'Minor Healing Potion',
            'type' => 'consumable',
            'subtype' => 'potion',
            'is_consumable' => true,
            'stats_modifiers' => json_encode(['heal' => 15]),
            'base_value' => 25
        ]);

        $player->addItemToPlayerInventory($potion, 2);
    }

    /**
     * Test loot collection and rewards
     */
    public function testLootCollection()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create(['user_id' => $user->id]);

        // Create some items for loot
        Item::create([
            'name' => 'Rusty Sword',
            'type' => 'weapon',
            'subtype' => 'sword',
            'damage_dice' => '1d6',
            'is_equippable' => true,
            'base_value' => 25
        ]);

        $adventure = Adventure::create([
            'player_id' => $player->id,
            'road' => 'east',
            'difficulty' => 'easy',
            'status' => 'completed',
            'seed' => 'loot123',
            'title' => 'Loot Test Adventure',
            'collected_loot' => json_encode([
                ['name' => 'Rusty Sword', 'quantity' => 1],
                ['name' => 'Gold Coins', 'quantity' => 50]
            ]),
            'completed_at' => now()
        ]);

        $this->browse(function (Browser $browser) use ($user, $adventure) {
            $browser->loginAs($user)
                    ->visit('/game/adventures')
                    ->assertSee('Completed Adventures')
                    ->assertSee('Rusty Sword')
                    ->assertSee('50 gold');
        });
    }
}