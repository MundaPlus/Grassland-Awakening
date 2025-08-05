# Grassland Awakening

A fantasy RPG web application built with Laravel featuring character progression, procedural adventures, village management, and a comprehensive crafting system.

## 🎮 Game Features

### Core Gameplay
- **Character System**: D&D-inspired stats (STR, DEX, CON, INT, WIS, CHA) with level progression
- **Adventure Generation**: Procedurally generated adventures with dynamic difficulty scaling
- **Combat System**: Turn-based tactical combat with equipment bonuses and strategic choices
- **Equipment & Inventory**: Comprehensive item management with dual inventory systems
- **Village Management**: Build and develop your village with NPCs and specializations

### Advanced Systems
- **Crafting & Economy**: Full crafting system with recipes, materials, and upgrade paths
- **NPC Interactions**: Recruit, train, and manage NPCs with unique skills and professions
- **Reputation System**: Build relationships with various factions and guilds
- **Achievement System**: Track progress and unlock rewards
- **Weather & Seasons**: Dynamic environmental effects on gameplay

## 🏗️ Technical Architecture

### Framework & Stack
- **Backend**: Laravel 10+ with PHP 8.1+
- **Frontend**: Bootstrap 5 with responsive design
- **Database**: MySQL with comprehensive migrations
- **Authentication**: Laravel Breeze with multi-provider support
- **File Management**: Laravel File Manager integration

### Key Components
- **Services Layer**: Modular business logic (AdventureGeneration, Combat, Crafting, NPC, Weather)
- **Database Design**: Normalized schema with proper relationships and constraints
- **API Integration**: JSON endpoints for dynamic frontend interactions
- **Security**: CSRF protection, input validation, and secure authentication

## 🗄️ Database Schema

### Core Tables
- `players` - Character data and progression
- `adventures` - Generated adventure instances
- `items` - Equipment, consumables, and crafting materials
- `npcs` - Village inhabitants with skills and stats

### Crafting System
- `crafting_recipes` - Recipe definitions with requirements
- `crafting_recipe_materials` - Recipe material requirements
- `player_known_recipes` - Player recipe knowledge tracking

### Progression Systems
- `equipment` & `inventories` - Dual inventory management
- `faction_reputations` - Player standing with various factions
- `achievements` - Player accomplishment tracking

## 🎯 Current Development Status

### ✅ Completed Features
- **Phase 1**: Basic character creation and stats system
- **Phase 2**: Adventure generation and exploration mechanics
- **Phase 3**: Combat system with tactical elements
- **Phase 4**: Equipment system and inventory management
- **Phase 5**: Crafting system with recipes and materials

### 🚧 In Development
- Recipe discovery through adventures
- Advanced item upgrade mechanics
- Village specialization bonuses
- Expanded NPC interaction systems

### 📋 Planned Features
- Multiplayer elements and guilds
- Advanced quest chains
- Seasonal events and challenges
- Mobile app companion

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.1+
- Composer
- MySQL 8.0+
- Node.js & NPM (for asset compilation)

### Installation Steps

1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd grassland_awakening
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**
   ```bash
   # Configure database credentials in .env
   php artisan migrate
   php artisan db:seed --class=ItemSeeder
   php artisan db:seed --class=CraftingRecipeSeeder
   ```

5. **Asset Compilation**
   ```bash
   npm run build
   ```

6. **Launch Application**
   ```bash
   php artisan serve
   ```

## 🎮 Gameplay Guide

### Getting Started
1. Create an account and character
2. Explore the dashboard to understand your stats
3. Visit the village to manage NPCs and access crafting
4. Generate your first adventure to begin exploring

### Character Progression
- **Level Up**: Gain experience through combat and crafting
- **Allocate Stats**: Spend points to customize your character build
- **Equipment**: Find and craft better gear to enhance your abilities

### Crafting System
- **Learn Recipes**: Discover crafting recipes through exploration
- **Gather Materials**: Collect crafting materials during adventures
- **Village Crafting**: Use the village crafting station to create items
- **Upgrade Items**: Enhance existing equipment with advanced recipes

### Village Management
- **Recruit NPCs**: Find companions during adventures
- **Train Skills**: Develop NPC abilities to unlock bonuses
- **Build Reputation**: Improve standing with various factions

## 🔧 Development

### Project Structure
```
app/
├── Http/Controllers/Web/    # Game controllers
├── Models/                  # Eloquent models
├── Services/               # Business logic services
└── ...

database/
├── migrations/             # Database schema
└── seeders/               # Initial data

resources/
├── views/game/            # Game interface templates
└── ...
```

### Key Services
- **AdventureGenerationService**: Procedural content creation
- **CombatService**: Battle mechanics and calculations
- **CraftingService**: Item creation and upgrade logic
- **NPCService**: Village inhabitant management
- **ReputationService**: Faction relationship tracking

### API Endpoints
- `GET /game/crafting/recipes` - Retrieve available recipes
- `POST /game/crafting/craft` - Create items from recipes
- `POST /game/adventure/{id}/combat` - Process combat actions
- `GET /game/inventory` - Access character inventory

## 🤝 Contributing

This is a personal project showcasing full-stack Laravel development with complex game mechanics. The codebase demonstrates:

- **Clean Architecture**: Separation of concerns with services layer
- **Database Design**: Normalized schema with proper relationships
- **User Experience**: Responsive interface with dynamic interactions
- **Security Best Practices**: Input validation and secure authentication
- **Scalable Structure**: Modular design for feature expansion

## 📄 License

This project is for educational and portfolio purposes. All rights reserved.

---

**Grassland Awakening** - Where every adventure tells a story, and every choice shapes your destiny.