<?php

namespace Database\Seeders;

use App\Models\PLUCode;
use App\Models\User;
use App\Models\UserList;
use App\Models\ListItem;
use Illuminate\Database\Seeder;

class MarketplaceListsSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Creating marketplace lists...');

        // Ensure user ID 1 exists
        $user = User::find(1);
        if (!$user) {
            $this->command->error('User ID 1 not found. Please create a user first.');
            return;
        }

        // Get PLU codes for reference
        $allPluCodes = PLUCode::all()->keyBy('plu');

        // 1. MEAL PLANNING LISTS
        $this->createList($user, 'Weekly Meal Essentials', 'meal-planning', 
            'Everything you need for a week of healthy home cooking', 
            ['4011', '4099', '4065', '4078', '4062', '4064', '4225', '4068', '3338', '4087', '3082', '4063', '4069', '4958'], 
            $allPluCodes);

        $this->createList($user, 'Sunday Meal Prep Basics', 'meal-planning', 
            'Perfect ingredients for batch cooking and meal preparation', 
            ['4079', '4555', '4552', '4550', '3082', '4015', '4016', '4017', '4022', '4096', '4554', '4551'], 
            $allPluCodes);

        // 2. SEASONAL LISTS
        $this->createList($user, 'Spring Fresh Favorites', 'seasonal', 
            'Early season produce bursting with spring flavors', 
            ['4080', '4090', '4091', '4664', '4218', '3302', '4555', '4552', '4551', '4550', '4301'], 
            $allPluCodes);

        $this->createList($user, 'Summer Bounty Collection', 'seasonal', 
            'Peak summer produce for hot weather cooking', 
            ['4664', '4665', '4799', '4087', '4088', '4081', '4082', '4156', '4157', '4158', '4159'], 
            $allPluCodes);

        $this->createList($user, 'Fall Harvest Essentials', 'seasonal', 
            'Autumn favorites for cozy seasonal meals', 
            ['3283', '3284', '3285', '3286', '3287', '3288', '4012', '4013', '4014', '4419', '4420'], 
            $allPluCodes);

        $this->createList($user, 'Summer Stone Fruit Collection', 'seasonal', 
            'Peak summer stone fruits at their juiciest and most flavorful', 
            ['4037', '4038', '4035', '4036', '4041', '4042', '4039', '4040', '4218', '3302', '4045', '4258'], 
            $allPluCodes);

        $this->createList($user, 'Apple Harvest Season', 'seasonal', 
            'Classic fall apple varieties for eating, cooking, and storing', 
            ['3283', '4131', '4129', '4017', '4018', '4020', '4021', '4132', '4133', '4134', '4135', '4128', '4130'], 
            $allPluCodes);

        // 3. ORGANIC FOCUS LISTS
        $this->createList($user, 'Organic Starter Pack', 'organic', 
            'Essential organic produce for healthy living', 
            ['4011', '4131', '4225', '4064', '4087', '4555', '3082', '4096', '4062', '4079'], 
            $allPluCodes);

        $this->createList($user, 'Clean Eating Organics', 'organic', 
            'Premium organic selections for clean eating', 
            ['4555', '3082', '4550', '4551', '4552', '4554', '4958', '4959', '4960'], 
            $allPluCodes);

        // 4. BUDGET FRIENDLY LISTS
        $this->createList($user, 'Smart Shopping Staples', 'budget', 
            'High-value produce that stretches your grocery budget', 
            ['4011', '4062', '4063', '4064', '4068', '4069', '4078', '4079', '4087', '4096', '4099'], 
            $allPluCodes);

        $this->createList($user, 'Family Budget Basics', 'budget', 
            'Affordable essentials for families on a budget', 
            ['4225', '3338', '4012', '4013', '4014', '4015', '4016', '4017', '4022', '4065'], 
            $allPluCodes);

        // 5. HEALTHY EATING LISTS
        $this->createList($user, 'Superfood Selections', 'healthy', 
            'Nutrient-dense superfoods for optimal health', 
            ['4555', '3082', '4550', '4551', '4552', '4554', '4664', '4665', '4799'], 
            $allPluCodes);

        $this->createList($user, 'Rainbow Nutrition', 'healthy', 
            'Colorful produce packed with vitamins and antioxidants', 
            ['4087', '4088', '4081', '4082', '4156', '4157', '4158', '4159', '4225', '4958'], 
            $allPluCodes);

        $this->createList($user, 'Heart Healthy Choices', 'healthy', 
            'Produce that supports cardiovascular health', 
            ['4011', '4131', '4664', '4665', '4799', '4080', '4555', '4301', '3082', '4087'], 
            $allPluCodes);

        // 6. FAMILY MEALS LISTS
        $this->createList($user, 'Kid-Friendly Favorites', 'family', 
            'Sweet and mild produce that kids actually love', 
            ['4132', '4133', '4131', '4129', '4037', '4038', '4035', '4036', '4225', '4664'], 
            $allPluCodes);

        $this->createList($user, 'Family Dinner Essentials', 'family', 
            'Versatile ingredients for crowd-pleasing family meals', 
            ['4062', '4063', '4064', '4065', '4068', '4069', '4078', '4079', '4087', '4096'], 
            $allPluCodes);

        // 7. QUICK MEALS LISTS
        $this->createList($user, 'Express Cooking Supplies', 'quick-meals', 
            'Fast-cooking produce for busy weeknight dinners', 
            ['3082', '4550', '4551', '4552', '4554', '4555', '4958', '4959', '4960'], 
            $allPluCodes);

        $this->createList($user, 'No-Cook Fresh Options', 'quick-meals', 
            'Ready-to-eat produce requiring zero cooking time', 
            ['4664', '4665', '4799', '4011', '4131', '4225', '4301', '4302', '4303', '4304'], 
            $allPluCodes);

        // 8. SPECIAL DIET LISTS
        $this->createList($user, 'Low-Carb Vegetable Mix', 'special-diet', 
            'Low-carbohydrate vegetables perfect for keto and paleo diets', 
            ['3082', '4550', '4551', '4552', '4554', '4555', '4087', '4088', '4958'], 
            $allPluCodes);

        $this->createList($user, 'Anti-Inflammatory Foods', 'special-diet', 
            'Produce with natural anti-inflammatory properties', 
            ['4555', '4301', '4302', '4303', '4304', '4305', '4664', '4665', '4799', '4958'], 
            $allPluCodes);

        // 9. ENTERTAINING LISTS
        $this->createList($user, 'Party Platter Perfection', 'entertaining', 
            'Beautiful produce for impressive party spreads', 
            ['4081', '4082', '4156', '4157', '4158', '4159', '4225', '4301', '4302', '4303'], 
            $allPluCodes);

        $this->createList($user, 'Gourmet Gathering Selection', 'entertaining', 
            'Premium produce for sophisticated entertaining', 
            ['3283', '3284', '3285', '3286', '3287', '3288', '4419', '4420', '4799', '4665'], 
            $allPluCodes);

        // 10. OTHER CATEGORY LISTS
        $this->createList($user, 'Smoothie & Juice Essentials', 'other', 
            'Perfect fruits and vegetables for blending', 
            ['4011', '4131', '4664', '4665', '4799', '3082', '4550', '4012', '4013'], 
            $allPluCodes);

        $this->createList($user, 'Preserved & Dried Collection', 'other', 
            'Long-lasting produce options for extended storage', 
            ['4022', '4015', '4016', '4017', '4096', '4062', '4063', '4064', '4065', '4068'], 
            $allPluCodes);

        $this->command->info('Marketplace seeder completed successfully!');
        $this->command->info('Created 24 marketplace lists across 9 categories');
    }

    private function createList($user, $name, $category, $description, $pluCodes, $allPluCodes)
    {
        // Create the user list
        $userList = UserList::create([
            'user_id' => $user->id,
            'name' => $name,
            'marketplace_title' => $name,
            'marketplace_description' => $description,
            'is_public' => true,
            'marketplace_enabled' => true,
            'marketplace_category' => $category,
            'published_at' => now(),
            'share_code' => \Str::random(12),
        ]);

        // Add PLU items to the list
        foreach ($pluCodes as $pluCode) {
            if (isset($allPluCodes[$pluCode])) {
                ListItem::create([
                    'user_list_id' => $userList->id,
                    'plu_code_id' => $allPluCodes[$pluCode]->id,
                    'inventory_level' => 0, // Start with 0 inventory
                    'organic' => false, // Default to regular, users can switch to organic
                ]);
            }
        }

        $this->command->info("Created list: {$name} ({$category}) with " . count($pluCodes) . " items");
    }
}