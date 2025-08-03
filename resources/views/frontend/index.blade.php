@extends("frontend.layouts.app")

@section("title")
    Grassland Awakening - Fantasy RPG Adventure
@endsection

@section("content")
    <!-- Hero Section -->
    <section class="relative overflow-hidden" style="background: linear-gradient(135deg, #2c5530 0%, #5a8a3a 100%); min-height: 100vh;">

        <div class="relative mx-auto max-w-screen-xl px-4 py-24 text-center sm:px-12 flex items-center min-h-screen">
            <div class="w-full">
                <!-- Game Logo/Title -->
                <div class="mb-8 flex justify-center items-center">
                    <div class="bg-white bg-opacity-90 rounded-lg p-6 shadow-2xl backdrop-blur-sm">
                        <img src="{{ asset('pixel_art/player_1.png') }}" alt="Hero" class="hero-character mx-auto mb-4 pixelated">
                        <h1 class="text-5xl font-bold text-green-800 mb-2">Grassland Awakening</h1>
                        <p class="text-green-600 text-lg">A Fantasy RPG Adventure</p>
                    </div>
                </div>

                <!-- Game Description -->
                <div class="bg-black bg-opacity-50 backdrop-blur-sm rounded-lg p-8 mx-auto max-w-4xl mb-10 text-white">
                    <p class="text-xl leading-relaxed mb-6">
                        Embark on an epic journey through the mystical grasslands! Build your village, recruit NPCs, 
                        explore procedurally generated adventures, and master the art of turn-based combat in this 
                        immersive fantasy RPG experience.
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                        <div class="text-center">
                            <img src="{{ asset('pixel_art/village_2.png') }}" alt="Village Building" class="feature-icon mx-auto mb-3 pixelated">
                            <h3 class="font-bold text-lg text-yellow-300">Village Building</h3>
                            <p class="text-sm text-gray-300">Create and manage your own thriving village</p>
                        </div>
                        <div class="text-center">
                            <img src="{{ asset('pixel_art/npc__enemy_goblin.png') }}" alt="Combat" class="feature-icon mx-auto mb-3 pixelated">
                            <h3 class="font-bold text-lg text-red-300">Epic Combat</h3>
                            <p class="text-sm text-gray-300">Engage in strategic turn-based battles</p>
                        </div>
                        <div class="text-center">
                            <img src="{{ asset('pixel_art/bg_field_day.png') }}" alt="Exploration" class="feature-icon mx-auto mb-3 pixelated">
                            <h3 class="font-bold text-lg text-blue-300">Endless Adventures</h3>
                            <p class="text-sm text-gray-300">Discover procedurally generated quests</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col space-y-4 sm:flex-row sm:justify-center sm:space-y-0 sm:space-x-6">
                    @auth
                        <a href="{{ route('game.dashboard') }}" 
                           class="inline-flex items-center justify-center rounded-lg bg-green-600 px-8 py-4 text-xl font-bold text-white hover:bg-green-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                            <img src="{{ asset('pixel_art/player_2.png') }}" alt="Play" class="button-icon mr-3 pixelated">
                            Enter Game
                        </a>
                    @else
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center justify-center rounded-lg bg-green-600 px-8 py-4 text-xl font-bold text-white hover:bg-green-700 transform hover:scale-105 transition-all duration-200 shadow-lg">
                            <img src="{{ asset('pixel_art/player_3.png') }}" alt="Start" class="button-icon mr-3 pixelated">
                            Start Your Adventure
                        </a>
                        <a href="{{ route('login') }}" 
                           class="inline-flex items-center justify-center rounded-lg border-2 border-white bg-transparent px-8 py-4 text-xl font-bold text-white hover:bg-white hover:text-green-600 transform hover:scale-105 transition-all duration-200 shadow-lg">
                            <img src="{{ asset('pixel_art/player_4.png') }}" alt="Login" class="button-icon mr-3 pixelated">
                            Continue Journey
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- Game Features Section -->
    <section class="bg-gradient-to-b from-green-50 to-blue-50 py-20">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Game Features</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Discover a rich RPG experience with deep mechanics and endless possibilities
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1: Village Management -->
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="text-center mb-4">
                        <img src="{{ asset('pixel_art/village_3.png') }}" alt="Village Management" class="feature-icon mx-auto pixelated">
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Village Management</h3>
                    <p class="text-gray-600 mb-4">Build and customize your village with unique NPCs, each with their own skills and personalities. Watch your settlement grow and specialize based on your choices.</p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li>• Recruit diverse NPCs with unique professions</li>
                        <li>• Train NPCs to unlock new abilities</li>
                        <li>• Village specialization system</li>
                    </ul>
                </div>

                <!-- Feature 2: Dynamic Weather System -->
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="text-center mb-4">
                        <img src="{{ asset('pixel_art/bg_grass_rain.png') }}" alt="Weather System" class="feature-icon mx-auto pixelated">
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Dynamic Weather</h3>
                    <p class="text-gray-600 mb-4">Experience real-world weather integration that affects gameplay. Rain, sunshine, and seasonal changes impact combat and exploration.</p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li>• Real-world weather API integration</li>
                        <li>• Weather affects combat mechanics</li>
                        <li>• Seasonal gameplay variations</li>
                    </ul>
                </div>

                <!-- Feature 3: Strategic Combat -->
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="text-center mb-4">
                        <img src="{{ asset('pixel_art/npc__enemy_demon.png') }}" alt="Combat System" class="feature-icon mx-auto pixelated">
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Strategic Combat</h3>
                    <p class="text-gray-600 mb-4">Engage in turn-based combat inspired by D&D 2024 rules. Every decision matters as you face diverse enemies with unique abilities.</p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li>• D&D-inspired combat mechanics</li>
                        <li>• Tactical decision making</li>
                        <li>• Diverse enemy types and abilities</li>
                    </ul>
                </div>

                <!-- Feature 4: Procedural Adventures -->
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="text-center mb-4">
                        <img src="{{ asset('pixel_art/bg_rocks_day.png') }}" alt="Adventures" class="feature-icon mx-auto pixelated">
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Endless Adventures</h3>
                    <p class="text-gray-600 mb-4">Explore procedurally generated adventures with seed-based reproducibility. Every journey offers unique challenges and rewards.</p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li>• Procedural quest generation</li>
                        <li>• Multiple road types and difficulties</li>
                        <li>• Reproducible adventures with seeds</li>
                    </ul>
                </div>

                <!-- Feature 5: Achievement System -->
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="text-center mb-4">
                        <img src="{{ asset('pixel_art/npc_noble_1.png') }}" alt="Achievements" class="feature-icon mx-auto pixelated">
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Achievements & Reputation</h3>
                    <p class="text-gray-600 mb-4">Track your progress with a comprehensive achievement system and build relationships with various factions across the realm.</p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li>• Extensive achievement tracking</li>
                        <li>• Multi-faction reputation system</li>
                        <li>• Unlockable rewards and benefits</li>
                    </ul>
                </div>

                <!-- Feature 6: Accessibility -->
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-1">
                    <div class="text-center mb-4">
                        <img src="{{ asset('pixel_art/npc_guard_1.png') }}" alt="Accessibility" class="feature-icon mx-auto pixelated">
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Accessible Design</h3>
                    <p class="text-gray-600 mb-4">Built with accessibility in mind, featuring screen reader support, keyboard navigation, and inclusive design principles.</p>
                    <ul class="text-sm text-gray-500 space-y-1">
                        <li>• WCAG 2.1 AA compliance</li>
                        <li>• Full keyboard navigation</li>
                        <li>• Screen reader optimized</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Screenshots/Game Preview Section -->
    <section class="bg-gray-900 py-20">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-white mb-4">Game Preview</h2>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                    Get a glimpse of the beautiful pixel art world and immersive gameplay
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Game Environment Previews -->
                <div class="bg-gray-800 rounded-lg p-4 hover:bg-gray-700 transition-colors duration-300 game-preview">
                    <img src="{{ asset('pixel_art/bg_grass_day.png') }}" alt="Grassland Day" class="rounded pixelated mb-3">
                    <h3 class="text-white font-bold">Peaceful Grasslands</h3>
                    <p class="text-gray-400 text-sm">Explore vast green meadows under the bright sun</p>
                </div>

                <div class="bg-gray-800 rounded-lg p-4 hover:bg-gray-700 transition-colors duration-300 game-preview">
                    <img src="{{ asset('pixel_art/bg_rocks_grass_day.png') }}" alt="Rocky Terrain" class="rounded pixelated mb-3">
                    <h3 class="text-white font-bold">Rocky Highlands</h3>
                    <p class="text-gray-400 text-sm">Navigate challenging terrain with hidden secrets</p>
                </div>

                <div class="bg-gray-800 rounded-lg p-4 hover:bg-gray-700 transition-colors duration-300 game-preview">
                    <img src="{{ asset('pixel_art/bg_water_day.png') }}" alt="Waterside" class="rounded pixelated mb-3">
                    <h3 class="text-white font-bold">Tranquil Waters</h3>
                    <p class="text-gray-400 text-sm">Discover riverside adventures and aquatic mysteries</p>
                </div>

                <div class="bg-gray-800 rounded-lg p-4 hover:bg-gray-700 transition-colors duration-300 game-preview">
                    <img src="{{ asset('pixel_art/village_4.png') }}" alt="Village" class="rounded pixelated mb-3">
                    <h3 class="text-white font-bold">Thriving Villages</h3>
                    <p class="text-gray-400 text-sm">Build and manage your own bustling settlement</p>
                </div>

                <div class="bg-gray-800 rounded-lg p-4 hover:bg-gray-700 transition-colors duration-300 game-preview">
                    <img src="{{ asset('pixel_art/npc__enemy_knight.png') }}" alt="Combat" class="rounded pixelated mb-3">
                    <h3 class="text-white font-bold">Epic Battles</h3>
                    <p class="text-gray-400 text-sm">Face formidable foes in strategic combat</p>
                </div>

                <div class="bg-gray-800 rounded-lg p-4 hover:bg-gray-700 transition-colors duration-300 game-preview">
                    <img src="{{ asset('pixel_art/npc_artisan_1.png') }}" alt="NPCs" class="rounded pixelated mb-3">
                    <h3 class="text-white font-bold">Diverse Characters</h3>
                    <p class="text-gray-400 text-sm">Meet unique NPCs with their own stories</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-gradient-to-r from-green-600 to-blue-600 py-16">
        <div class="container mx-auto px-4 text-center">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-4xl font-bold text-white mb-6">Ready to Begin Your Adventure?</h2>
                <p class="text-xl text-green-100 mb-8">
                    Join thousands of adventurers in the mystical grasslands. Your epic journey awaits!
                </p>
                
                @auth
                    <a href="{{ route('game.dashboard') }}" 
                       class="inline-flex items-center justify-center rounded-lg bg-white px-10 py-4 text-xl font-bold text-green-600 hover:bg-gray-100 transform hover:scale-105 transition-all duration-200 shadow-lg">
                        <img src="{{ asset('pixel_art/player_5.png') }}" alt="Enter Game" class="button-icon mr-3 pixelated">
                        Enter Your Village
                    </a>
                @else
                    <div class="space-x-4">
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center justify-center rounded-lg bg-white px-8 py-4 text-xl font-bold text-green-600 hover:bg-gray-100 transform hover:scale-105 transition-all duration-200 shadow-lg">
                            <img src="{{ asset('pixel_art/player_6.png') }}" alt="Register" class="button-icon mr-3 pixelated">
                            Create Account
                        </a>
                        <a href="{{ route('login') }}" 
                           class="inline-flex items-center justify-center rounded-lg border-2 border-white bg-transparent px-8 py-4 text-xl font-bold text-white hover:bg-white hover:text-green-600 transform hover:scale-105 transition-all duration-200">
                            Login
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </section>

    @include("frontend.includes.messages")

    <style>
        .pixelated {
            image-rendering: -moz-crisp-edges;
            image-rendering: -webkit-crisp-edges;
            image-rendering: pixelated;
            image-rendering: crisp-edges;
            width: auto;
            height: auto;
            max-width: 100%;
        }
        
        /* Fix image container sizing */
        .game-preview img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            object-position: center;
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        
        .hero-character {
            width: auto;
            height: auto;
            max-width: 80px;
            max-height: 80px;
        }
        
        .button-icon {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }
        
        /* Background animation containers */
        .bg-character {
            width: 64px;
            height: 64px;
            object-fit: contain;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% {
                transform: translate3d(0,0,0);
            }
            40%, 43% {
                transform: translate3d(0, -30px, 0);
            }
            70% {
                transform: translate3d(0, -15px, 0);
            }
            90% {
                transform: translate3d(0, -4px, 0);
            }
        }
        
        .animate-bounce {
            animation: bounce 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        /* Responsive fixes */
        @media (max-width: 768px) {
            .hero-character {
                max-width: 60px;
                max-height: 60px;
            }
            
            .feature-icon {
                width: 60px;
                height: 60px;
            }
            
            .button-icon {
                width: 24px;
                height: 24px;
            }
            
            .bg-character {
                width: 48px;
                height: 48px;
            }
        }
        
        @media (prefers-reduced-motion: reduce) {
            .animate-bounce,
            .animate-pulse,
            .animate-float {
                animation: none;
            }
        }
    </style>
@endsection