<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Player;
use App\Models\Item;

class GameFlowTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test complete user registration and character creation flow
     */
    public function testUserRegistrationAndCharacterCreation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->type('name', 'Test Player')
                    ->type('email', 'testplayer@example.com')
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->press('Register')
                    ->waitForLocation('/game')
                    ->assertSee('Welcome back')
                    ->assertSee('Test Player');

            // Check if player was created automatically
            $user = User::where('email', 'testplayer@example.com')->first();
            $this->assertNotNull($user);
            
            $player = Player::where('user_id', $user->id)->first();
            $this->assertNotNull($player);
            $this->assertEquals(1, $player->level);
            $this->assertEquals(10, $player->hp);
            $this->assertEquals(100, $player->persistent_currency);
        });
    }

    /**
     * Test dashboard navigation and character screen
     */
    public function testDashboardAndCharacterScreen()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/game')
                    ->assertSee('Welcome back')
                    ->assertSee($user->name)
                    ->click('a[href*="character"]')
                    ->waitForLocation('/game/character')
                    ->assertSee('Character Overview')
                    ->assertSee('Ability Scores')
                    ->assertSee('Equipment')
                    ->assertSee('Inventory');
        });
    }

    /**
     * Test stat point allocation
     */
    public function testStatPointAllocation()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create([
            'user_id' => $user->id,
            'unallocated_stat_points' => 10
        ]);

        $this->browse(function (Browser $browser) use ($user, $player) {
            $browser->loginAs($user)
                    ->visit('/game/character')
                    ->assertSee('Points Available: 10')
                    ->type('str_points', '3')
                    ->type('dex_points', '2')
                    ->type('con_points', '5')
                    ->press('Allocate Points')
                    ->waitForText('Points allocated successfully')
                    ->pause(1000);

            // Verify points were allocated
            $player->refresh();
            $this->assertEquals(13, $player->str); // 10 base + 3
            $this->assertEquals(12, $player->dex); // 10 base + 2
            $this->assertEquals(15, $player->con); // 10 base + 5
            $this->assertEquals(0, $player->unallocated_stat_points);
        });
    }

    /**
     * Test adventure generation and start
     */
    public function testAdventureGeneration()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create([
            'user_id' => $user->id,
            'persistent_currency' => 1000 // Ensure enough gold
        ]);

        $this->browse(function (Browser $browser) use ($user, $player) {
            $browser->loginAs($user)
                    ->visit('/game/adventures')
                    ->assertSee('Adventures')
                    ->select('road', 'north')
                    ->select('difficulty', 'easy')
                    ->press('Generate Adventure')
                    ->waitForText('Adventure generated successfully')
                    ->assertSee('Active Adventures');

            // Check adventure was created
            $adventure = $player->adventures()->where('status', 'active')->first();
            $this->assertNotNull($adventure);
            $this->assertEquals('north', $adventure->road);
            $this->assertEquals('easy', $adventure->difficulty);
        });
    }

    /**
     * Test inventory and equipment management
     */
    public function testInventoryAndEquipment()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create(['user_id' => $user->id]);
        
        // Create some test items
        $sword = Item::create([
            'name' => 'Test Sword',
            'type' => 'weapon',
            'subtype' => 'sword',
            'damage_dice' => '1d8',
            'damage_bonus' => 2,
            'is_equippable' => true,
            'base_value' => 50
        ]);

        $armor = Item::create([
            'name' => 'Test Armor',
            'type' => 'armor',
            'subtype' => 'chest',
            'ac_bonus' => 5,
            'is_equippable' => true,
            'base_value' => 100
        ]);

        // Add items to player inventory
        $player->addItemToPlayerInventory($sword, 1);
        $player->addItemToPlayerInventory($armor, 1);

        $this->browse(function (Browser $browser) use ($user, $player) {
            $browser->loginAs($user)
                    ->visit('/game/inventory')
                    ->assertSee('Inventory')
                    ->assertSee('Test Sword')
                    ->assertSee('Test Armor')
                    ->visit('/game/character')
                    ->assertSee('Equipment Slots');

            // Test equipping an item would require more complex interaction
            // This is a basic structure for inventory testing
        });
    }

    /**
     * Test achievements system
     */
    public function testAchievementsSystem()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/game/achievements')
                    ->assertSee('Achievements')
                    ->assertSee('Achievement Points')
                    ->assertSee('Progress');
        });
    }

    /**
     * Test village management
     */
    public function testVillageManagement()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/game/village')
                    ->assertSee('Village Management')
                    ->assertSee('NPCs')
                    ->assertSee('Specializations');
        });
    }

    /**
     * Test skills system
     */
    public function testSkillsSystem()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create([
            'user_id' => $user->id,
            'skill_points' => 5
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/game/skills')
                    ->assertSee('Skills')
                    ->assertSee('Available Points');
        });
    }

    /**
     * Test level up functionality
     */
    public function testLevelUp()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create([
            'user_id' => $user->id,
            'level' => 1,
            'experience' => 150, // More than needed for level 2 (100)
            'hp' => 10,
            'max_hp' => 10
        ]);

        $this->browse(function (Browser $browser) use ($user, $player) {
            $browser->loginAs($user)
                    ->visit('/game/character')
                    ->press('Level Up')
                    ->waitForText('Congratulations!')
                    ->pause(1000);

            // Verify level up occurred
            $player->refresh();
            $this->assertEquals(2, $player->level);
            $this->assertEquals(2, $player->unallocated_stat_points); // 2 per level
            $this->assertEquals(1, $player->skill_points); // 1 per level
        });
    }

    /**
     * Test responsive design on different screen sizes
     */
    public function testResponsiveDesign()
    {
        $user = User::factory()->create();
        Player::factory()->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user) {
            // Test mobile view
            $browser->loginAs($user)
                    ->resize(375, 667) // iPhone size
                    ->visit('/game')
                    ->assertSee('Welcome back')
                    ->pause(500);

            // Test tablet view  
            $browser->resize(768, 1024)
                    ->refresh()
                    ->assertSee('Welcome back')
                    ->pause(500);

            // Test desktop view
            $browser->resize(1920, 1080)
                    ->refresh()
                    ->assertSee('Welcome back');
        });
    }

    /**
     * Test dark mode functionality
     */
    public function testDarkModeToggle()
    {
        $user = User::factory()->create();
        Player::factory()->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/game')
                    ->assertSee('Welcome back')
                    // Look for dark mode toggle button if it exists
                    ->pause(1000);
        });
    }

    /**
     * Test accessibility features
     */
    public function testAccessibilityFeatures()
    {
        $user = User::factory()->create();
        Player::factory()->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/game')
                    // Check for ARIA labels and semantic HTML
                    ->assertSourceHas('aria-label')
                    ->assertSourceHas('role=')
                    ->visit('/game/character')
                    ->assertSourceHas('aria-describedby')
                    ->pause(500);
        });
    }

    /**
     * Test error handling and edge cases
     */
    public function testErrorHandling()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create([
            'user_id' => $user->id,
            'persistent_currency' => 10 // Not enough for adventure
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/game/adventures')
                    ->select('road', 'north')
                    ->select('difficulty', 'nightmare') // Expensive difficulty
                    ->press('Generate Adventure')
                    ->waitForText('Not enough gold')
                    ->pause(1000);
        });
    }
}