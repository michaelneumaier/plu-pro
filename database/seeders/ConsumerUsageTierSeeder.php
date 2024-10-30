<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PLUCode;

class ConsumerUsageTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Path to your CSV file
        $csvPath = database_path('seeders/commodities_updated_with_consumer_usage_tier.csv'); // Adjust the path if necessary

        // Check if the file exists and is readable
        if (!file_exists($csvPath) || !is_readable($csvPath)) {
            $this->command->error("CSV file not found or not readable at path: {$csvPath}");
            return;
        }

        // Open the CSV file
        if (($handle = fopen($csvPath, 'r')) !== false) {
            // Get the header row
            $header = fgetcsv($handle, 1000, ",");

            // Indexes of relevant columns
            $idIndex = array_search('id', $header);
            $consumerUsageTierIndex = array_search('consumer_usage_tier', $header);

            if ($idIndex === false || $consumerUsageTierIndex === false) {
                $this->command->error("Required columns 'id' or 'consumer_usage_tier' not found in CSV.");
                fclose($handle);
                return;
            }

            // Process each row
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                $id = $row[$idIndex];
                $consumerUsageTier = $row[$consumerUsageTierIndex];

                // Update the corresponding PLUCode record
                PLUCode::where('id', $id)->update([
                    'consumer_usage_tier' => $consumerUsageTier,
                ]);
            }

            fclose($handle);
            $this->command->info('Consumer usage tiers have been successfully updated.');
        } else {
            $this->command->error("Failed to open CSV file at path: {$csvPath}");
        }
    }
}
