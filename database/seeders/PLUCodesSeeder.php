<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PLUCode;
use Illuminate\Support\Facades\File;

class PLUCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('seeders/plu_codes.csv');

        // Check if the CSV file exists
        if (!File::exists($csvFile)) {
            $this->command->error("CSV file not found at path: $csvFile");
            return;
        }

        // Open the CSV file
        if (($handle = fopen($csvFile, 'r')) !== false) {
            $header = null;
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                if (!$header) {
                    // Capture the header row
                    $header = $row;
                    continue;
                }

                // Combine header and row to associative array
                $data = array_combine($header, $row);

                // Map CSV columns to database columns
                $pluCodeData = [
                    'id' => (int) trim($data['id'], '"'),
                    'plu' => trim($data['Plu'], '"'),
                    'type' => trim($data['Type'], '"'),
                    'category' => trim($data['Category'], '"'),
                    'commodity' => trim($data['Commodity'], '"'),
                    'variety' => trim($data['Variety'], '"'),
                    'size' => trim($data['Size'], '"') ?: null,
                    'measures_na' => trim($data['Measures_na'], '"') ?: null,
                    'measures_row' => trim($data['Measures_row'], '"') ?: null,
                    'restrictions' => trim($data['Restrictions'], '"') ?: null,
                    'botanical' => trim($data['Botanical'], '"') ?: null,
                    'aka' => trim($data['Aka'], '"') ?: null,
                    'status' => trim($data['Status'], '"'),
                    'link' => trim($data['Link'], '"') ?: null,
                    'notes' => trim($data['Notes'], '"') ?: null,
                    'updated_by' => trim($data['Updated_by'], '"'),
                    'language' => trim($data['Language'], '"') ?: null,
                    // 'created_at' and 'updated_at' are handled by Eloquent
                ];

                // Handle 'created_at' and 'updated_at' manually if provided
                if (isset($data['Created_at']) && !empty($data['Created_at'])) {
                    $pluCodeData['created_at'] = trim($data['Created_at'], '"');
                }

                if (isset($data['Updated_at']) && !empty($data['Updated_at'])) {
                    $pluCodeData['updated_at'] = trim($data['Updated_at'], '"');
                }

                // Handle 'deleted_at' if present
                if (isset($data['Deleted_at']) && !empty($data['Deleted_at'])) {
                    $pluCodeData['deleted_at'] = trim($data['Deleted_at'], '"');
                }

                // // Insert or update the PLU code
                // PLUCode::updateOrCreate(
                //     ['id' => $pluCodeData['id']],
                //     $pluCodeData
                // );
                try {
                    PLUCode::updateOrCreate(
                        ['plu' => $pluCodeData['plu']],  // Use 'plu' as the unique identifier
                        $pluCodeData  // The rest of the data
                    );
                } catch (\Exception $e) {
                    // Log the error or handle it as needed

                }
            }

            fclose($handle);
            $this->command->info('PLU Codes imported successfully.');
        } else {
            $this->command->error("Failed to open the CSV file at path: $csvFile");
        }
    }
}
