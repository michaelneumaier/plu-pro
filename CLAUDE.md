# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PLUPro is a Laravel 11 application with Livewire 3 for managing Price Look-Up (PLU) codes in the produce industry. It uses SQLite by default, Tailwind CSS for styling, and includes features for PLU search, custom lists, barcode generation, and image management.

## Essential Commands

### Development
```bash
# Start all development services (recommended)
composer run dev

# Individual services
php artisan serve              # Laravel development server
npm run dev                    # Vite dev server with HMR
php artisan queue:listen       # Queue worker for background jobs
php artisan pail               # Real-time log viewer

# Build assets for production
npm run build
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run a specific test
php artisan test tests/Feature/ExampleTest.php
```

### Code Quality
```bash
# Format code using Laravel Pint
./vendor/bin/pint

# Format specific file or directory
./vendor/bin/pint app/Models/
```

### Database
```bash
# Run migrations
php artisan migrate

# Fresh migration with seeding (resets database)
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_example_table
```

## Architecture Overview

### Key Technologies
- **Backend**: Laravel 11 with PHP 8.2+
- **Frontend**: Livewire 3.0 for reactive components, Tailwind CSS 3.4
- **Database**: SQLite (default), Eloquent ORM
- **Authentication**: Laravel Jetstream with Sanctum
- **Build**: Vite 5.0 for asset compilation

### Core Models and Relationships
- **PLUCode**: Central model containing PLU information (code, commodity, variety, size, images)
- **User**: Authentication and user profiles via Jetstream
- **UserList**: Custom PLU lists created by users
- **ListItem**: Many-to-many relationship between UserList and PLUCode with additional fields (inventory_level)

### Livewire Components Structure
Livewire components in `app/Livewire/` handle dynamic UI updates:
- Components extend `Livewire\Component`
- Use `wire:model` for two-way data binding
- Public properties are reactive
- Methods can be called from the frontend with `wire:click`

### Background Jobs
Queue-based jobs in `app/Jobs/`:
- `DownloadPLUImage`: Downloads and stores product images
- Dispatched using `dispatch()` method
- Processed by queue workers

### Frontend Architecture
- **Progressive Web App (PWA)**: Full offline capability with service worker
- **Alpine.js 3.14**: Client-side reactivity with persistence and focus plugins
- **Hybrid Approach**: Alpine.js for immediate UI updates, Livewire for server sync
- **Offline-First**: Local storage with optimistic updates and conflict resolution
- **Mobile-First**: Touch-optimized interface with haptic feedback
- Blade templates in `resources/views/`
- Livewire components for server communication
- Tailwind utility classes for styling
- Virtual scrolling for large lists

### Database Seeding
The database includes comprehensive PLU data:
- CSV files in `database/seeders/data/`
- Seeder classes load and process CSV data
- Includes commodity groups, varieties, and size information

### File Storage
- Product images stored in `storage/app/public/plu-images/`
- Accessible via `/storage` URL after running `php artisan storage:link`
- Images organized by PLU code with multiple size variants

### Offline-First Inventory System
The list functionality uses a hybrid approach for robust offline capability:

#### Alpine.js Components (`resources/js/components/`)
- `inventory-manager.js`: Client-side inventory control with local persistence
- `sync-queue.js`: Manages offline changes and syncing when online
- `pwa-handler.js`: Service worker registration and PWA features
- `virtual-scroll.js`: Performance optimization for large lists

#### Key Features
- **Optimistic Updates**: Immediate UI feedback without waiting for server
- **Conflict Resolution**: Handles simultaneous edits with timestamp-based detection
- **Local Persistence**: Changes saved to localStorage and synced across browser tabs
- **Debounced Syncing**: Batches rapid changes to reduce server load
- **Mobile Optimized**: Touch-friendly with haptic feedback and proper sizing
- **Network Detection**: Automatic sync queue processing when online

#### Usage
Inventory components automatically handle:
- Increment/decrement with immediate feedback
- Half-unit adjustments (0.5 increments)
- Direct value editing with validation
- Offline queueing and online synchronization
- Race condition prevention with database locking