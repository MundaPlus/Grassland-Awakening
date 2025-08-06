@extends("frontend.layouts.app")

@section("title")
    Grassland Awakening - Fantasy RPG Adventure
@endsection

@section("content")
    <!-- Hero Section -->
    <section class="hero-section relative overflow-hidden min-h-screen">
        <!-- Dynamic Background -->
        <div class="hero-background"></div>
        <div class="hero-overlay"></div>

        <div class="relative mx-auto max-w-screen-xl px-4 py-24 text-center sm:px-12 flex items-center min-h-screen z-10">
            <div class="w-full">
                <!-- Game Logo/Title -->
                <div class="mb-8 flex justify-center items-center">
                    <div class="hero-title-card">
                        <div class="hero-character-wrapper">
                            <img src="{{ asset('img/player_male.png') }}" alt="Hero" class="hero-character">
                        </div>
                        <h1 class="hero-title">Grassland Awakening</h1>
                        <p class="hero-subtitle">A Fantasy RPG Adventure</p>
                        <div class="hero-decoration">
                            <span class="decoration-element">⚔️</span>
                            <span class="decoration-element">🏰</span>
                            <span class="decoration-element">✨</span>
                        </div>
                    </div>
                </div>

                <!-- Game Description -->
                <div class="bg-black bg-opacity-50 backdrop-blur-sm rounded-lg p-8 mx-auto max-w-4xl mb-10 text-white">
                    <p class="text-xl leading-relaxed mb-6">
                        Embark on an epic journey through the mystical grasslands! Build your village, recruit NPCs, 
                        explore procedurally generated adventures, and master the art of turn-based combat in this 
                        immersive fantasy RPG experience.
                    </p>
                    
                    <div class="feature-grid">
                        <div class="feature-card">
                            <div class="feature-icon-wrapper">
                                <img src="{{ asset('img/npc_vendor_male.png') }}" alt="Village Building" class="feature-icon">
                            </div>
                            <h3 class="feature-title village-feature">Village Building</h3>
                            <p class="feature-description">Create and manage your own thriving village</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon-wrapper">
                                <img src="{{ asset('img/enemies/goblin.png') }}" alt="Combat" class="feature-icon">
                            </div>
                            <h3 class="feature-title combat-feature">Epic Combat</h3>
                            <p class="feature-description">Engage in strategic turn-based battles</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon-wrapper">
                                <i class="fas fa-map text-4xl text-blue-300"></i>
                            </div>
                            <h3 class="feature-title adventure-feature">Endless Adventures</h3>
                            <p class="feature-description">Discover procedurally generated quests</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="hero-actions">
                    @auth
                        <a href="{{ route('game.dashboard') }}" class="hero-button primary-button">
                            <i class="fas fa-play-circle mr-3"></i>
                            Enter Your Village
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="hero-button primary-button">
                            <i class="fas fa-user-plus mr-3"></i>
                            Start Your Adventure
                        </a>
                        <a href="{{ route('login') }}" class="hero-button secondary-button">
                            <i class="fas fa-sign-in-alt mr-3"></i>
                            Continue Journey
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- Game Features Section -->
    <section class="bg-gradient-to-b from-green-50 to-blue-50 py-20" style="display: none;" id="features-section">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Game Features</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Discover a rich RPG experience with deep mechanics and endless possibilities
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1: Village Management -->
                <div class="features-card village-card">
                    <div class="features-icon-container">
                        <img src="{{ asset('img/village.png') }}" alt="Village Management" class="features-icon">
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
    <section class="bg-gray-900 py-20" style="display: none;" id="preview-section">
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
    <section class="bg-gradient-to-r from-green-600 to-blue-600 py-16" style="display: none;" id="cta-section">
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
        /* Hero Section Styles */
        .hero-section {
            position: relative;
            background: linear-gradient(135deg, #2d3748 0%, #4a5568 50%, #2d3748 100%);
            overflow: hidden;
        }
        
        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6)), url('{{ asset('img/village.png') }}') center/cover no-repeat;
            background-attachment: fixed;
            z-index: 1;
        }
        
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(139, 69, 19, 0.2), rgba(160, 82, 45, 0.3));
            z-index: 2;
        }
        
        .hero-title-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border: 3px solid #d4a574;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: perspective(1000px) rotateX(5deg);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .hero-title-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #d4a574, #f4e4bc, #d4a574);
            z-index: -1;
            border-radius: 20px;
        }
        
        .hero-title-card:hover {
            transform: perspective(1000px) rotateX(0deg) scale(1.02);
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4);
        }
        
        .hero-character-wrapper {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 1.5rem;
        }
        
        .hero-character {
            width: 80px;
            height: 80px;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
        }
        
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #8b4513, #d4a574, #8b4513);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            font-family: Georgia, "Times New Roman", Times, serif;
        }
        
        .hero-subtitle {
            color: #8b4513;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .hero-decoration {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        
        .decoration-element {
            font-size: 1.5rem;
            animation: pulse 2s ease-in-out infinite alternate;
        }
        
        .decoration-element:nth-child(2) {
            animation-delay: 0.5s;
        }
        
        .decoration-element:nth-child(3) {
            animation-delay: 1s;
        }
        
        .hero-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
        }
        
        .hero-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
            min-width: 200px;
        }
        
        .primary-button {
            background: linear-gradient(135deg, #8b4513, #a0522d);
            color: white;
            border: 2px solid #d4a574;
        }
        
        .primary-button:hover {
            background: linear-gradient(135deg, #a0522d, #bc6c42);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 69, 19, 0.4);
            color: white;
        }
        
        .secondary-button {
            background: rgba(255, 255, 255, 0.9);
            color: #8b4513;
            border: 2px solid #d4a574;
        }
        
        .secondary-button:hover {
            background: rgba(212, 165, 116, 0.2);
            transform: translateY(-2px);
            color: #8b4513;
        }
        
        /* Feature Cards in Hero */
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            border: 2px solid rgba(212, 165, 116, 0.6);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border-color: #d4a574;
        }
        
        .feature-icon-wrapper {
            margin-bottom: 1rem;
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.2));
        }
        
        .feature-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .village-feature { color: #d69e2e; }
        .combat-feature { color: #e53e3e; }
        .adventure-feature { color: #3182ce; }
        
        .feature-description {
            color: #4a5568;
            font-size: 0.9rem;
        }
        
        /* Features Section */
        .features-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(212, 165, 116, 0.3);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .features-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #d4a574, #f4e4bc, #d4a574);
        }
        
        .features-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-color: #d4a574;
        }
        
        .features-icon-container {
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .features-icon {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 10px;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.2));
        }
        
        /* Game Preview */
        .game-preview img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            object-position: center;
            border-radius: 8px;
        }
        
        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.1);
            }
        }
        
        .animate-pulse {
            animation: pulse 2s ease-in-out infinite;
        }
        
        /* Responsive Design */
        @media (min-width: 640px) {
            .hero-actions {
                flex-direction: row;
                gap: 1.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .hero-background {
                background-attachment: scroll;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-character {
                width: 60px;
                height: 60px;
            }
            
            .feature-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .hero-title-card {
                padding: 1.5rem;
                transform: none;
            }
            
            .hero-title-card:hover {
                transform: scale(1.02);
            }
        }
        
        @media (max-width: 480px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-button {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
                min-width: 180px;
            }
        }
        
        @media (prefers-reduced-motion: reduce) {
            .animate-bounce,
            .animate-pulse,
            .animate-float,
            .decoration-element {
                animation: none;
            }
            
            .hero-title-card,
            .features-card,
            .hero-button {
                transition: none;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .hero-title-card {
                background: rgba(45, 55, 72, 0.95);
                color: white;
            }
            
            .hero-subtitle {
                color: #d4a574;
            }
            
            .features-card {
                background: rgba(45, 55, 72, 0.95);
                color: white;
            }
        }
        
        /* Using system fonts for CSP compliance */
    </style>
@endsection