<?php
// Fix Campaign model references causing 500 errors

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "🔧 FIXING CAMPAIGN MODEL REFERENCES\n";
echo "=====================================\n\n";

// Check if campaigns table exists
try {
    $hasTable = \DB::select("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'campaigns')");
    $tableExists = $hasTable[0]->exists ?? false;
    
    if (!$tableExists) {
        echo "❌ Campaigns table does not exist\n";
        echo "📝 Creating campaigns table...\n";
        
        // Create the table manually
        \DB::statement("
            CREATE TABLE IF NOT EXISTS campaigns (
                id SERIAL PRIMARY KEY,
                campaign_id VARCHAR(255) UNIQUE,
                name VARCHAR(255),
                display_name VARCHAR(255),
                description TEXT,
                status VARCHAR(50) DEFAULT 'active',
                first_seen_at TIMESTAMP NULL,
                last_lead_received_at TIMESTAMP NULL,
                total_leads INTEGER DEFAULT 0,
                is_auto_created BOOLEAN DEFAULT false,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        echo "✅ Campaigns table created\n";
    } else {
        echo "✅ Campaigns table already exists\n";
        
        // Check if display_name column exists
        $hasDisplayName = \DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'campaigns' AND column_name = 'display_name'");
        if (empty($hasDisplayName)) {
            echo "  ⚠️ Missing display_name column, adding it...\n";
            \DB::statement("ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS display_name VARCHAR(255)");
            echo "  ✅ Added display_name column\n";
        }
    }
    
    // Add some default campaigns
    $defaultCampaigns = [
        ['campaign_id' => 'default', 'name' => 'Default Campaign', 'display_name' => 'Default'],
        ['campaign_id' => 'web', 'name' => 'Web Campaign', 'display_name' => 'Web'],
        ['campaign_id' => 'manual', 'name' => 'Manual Entry', 'display_name' => 'Manual'],
    ];
    
    foreach ($defaultCampaigns as $campaign) {
        try {
            \DB::table('campaigns')->updateOrInsert(
                ['campaign_id' => $campaign['campaign_id']],
                array_merge($campaign, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
            echo "  ✅ Added/Updated campaign: {$campaign['name']}\n";
        } catch (\Exception $e) {
            echo "  ⚠️ Could not add campaign {$campaign['name']}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📊 Campaign Status:\n";
    $campaigns = \DB::table('campaigns')->count();
    echo "  Total campaigns: $campaigns\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=====================================\n";
echo "🏁 CAMPAIGN FIX COMPLETE\n";
echo "\nNow clearing cache...\n";

// Clear cache
\Artisan::call('config:clear');
\Artisan::call('cache:clear');
\Artisan::call('view:clear');

echo "✅ Cache cleared\n";
echo "\n⚡ Pages should now load without 500 errors\n";
