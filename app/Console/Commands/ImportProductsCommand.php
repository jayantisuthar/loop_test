<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class ImportProductsCommand extends Command
{
    protected $signature = 'import:products';

    protected $description = 'Import products from CSV file';

    public function handle()
    {
        $url = 'https://backend-developer.view.agentur-loop.com/products.csv';
        $username = 'loop';
        $password = 'backend_dev';


        $tempFilePath = storage_path('app/temp/products.csv');

        $response = Http::withBasicAuth($username, $password)->withOptions(['verify' => false])->get($url);

        if ($response->successful()) {
            Storage::put('temp/products.csv', $response->body());

            $csvData = file_get_contents($tempFilePath);

            $lines = explode("\n", $csvData);

            $headers = str_getcsv(array_shift($lines));
            $products = [];

            foreach ($lines as $line) {
                $values = str_getcsv($line);
                $productData = array_combine($headers, $values);
                $products[] = $productData;
            }

            $totalRows = count($products);
            $successCount = 0;
            $failedCount = 0;
            $bar = $this->output->createProgressBar($totalRows);

            $bar->start();

            foreach ($products as $productData) {
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

            Storage::delete('temp/products.csv');

            $bar->finish();
            $this->line('');

            $this->info('Products imported successfully.');
            $this->info('Total rows: ' . $totalRows);
            $this->info('Successful imports: ' . $successCount);
            $this->info('Failed imports: ' . $failedCount);
            Log::channel('import_logs')->info("Product Import => Total rows: $totalRows, Successfully imported rows: $successCount, Failed imports: $failedCount");

        } else {
            $this->error('Failed to fetch the CSV file.');
            Log::channel('import_logs')->info("Product Import => Failed to fetch the CSV file.");
        }

    }
}
