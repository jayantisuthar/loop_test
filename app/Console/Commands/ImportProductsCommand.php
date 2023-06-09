<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;



class ImportProductsCommand extends Command
{
    protected $signature = 'import:products';

    protected $description = 'Import products from CSV file';

    public function handle()
    {
        $url = 'https://backend-developer.view.agentur-loop.com/products.csv';

        $data = readCSVFileAndExtract($url, 'products');

        if (!$data) {
            $this->error('Failed to fetch the CSV file.');
            Log::channel('import_logs')->info("Product Import => Failed to fetch the CSV file.");
        }

        $totalRows = count($data);
        $successCount = 0;
        $failedCount = 0;
        $bar = $this->output->createProgressBar($totalRows);

        $bar->start();
        foreach ($data as $productData) {
            try {
                Product::create([
                    'productname' => $productData['productname'],
                    'price' => $productData['price']
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line('');

        $this->info('Products imported successfully.');
        $this->info('Total rows: ' . $totalRows);
        $this->info('Successful imports: ' . $successCount);
        $this->info('Failed imports: ' . $failedCount);
        Log::channel('import_logs')->info("Product Import => Total rows: $totalRows, Successfully imported rows: $successCount, Failed imports: $failedCount");

    }
}
