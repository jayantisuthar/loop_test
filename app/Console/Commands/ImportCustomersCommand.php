<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class ImportCustomersCommand extends Command
{
    protected $signature = 'import:customers';

    protected $description = 'Import customers from CSV file';

    public function handle()
    {
        $url = 'https://backend-developer.view.agentur-loop.com/customers.csv';
        $username = 'loop';
        $password = 'backend_dev';


        $tempFilePath = storage_path('app/temp/customers.csv');

        $response = Http::withBasicAuth($username, $password)->withOptions(['verify' => false])->get($url);

        if ($response->successful()) {
            Storage::put('temp/customers.csv', $response->body());

            $csvData = file_get_contents($tempFilePath);

            $lines = explode("\n", $csvData);

            $headers = str_getcsv(array_shift($lines));
            $customers = [];
            foreach ($lines as $line) {
                $values = str_getcsv($line);
                $customerData = array_combine($headers, $values);
                $customers[] = $customerData;
            }

            $totalRows = count($customers);
            $successCount = 0;
            $failedCount = 0;
            $bar = $this->output->createProgressBar($totalRows);

            $bar->start();
            foreach ($customers as $customerData) {
                try{
                    Customer::create([
                        'job_title' => $customerData['Job Title'],
                        'email' => $customerData['Email Address'],
                        'full_name' => $customerData['FirstName LastName'],
                        'registered_since' => Carbon::parse($customerData['registered_since'])->format('Y-m-d'),
                        'phone' => $customerData['phone'],
                    ]);
                    $successCount++;
                }
                catch (\Exception $e){
                    $failedCount++;
                }

                $bar->advance();

            }

            Storage::delete('temp/customers.csv');

            $bar->finish();
            $this->line('');

            $this->info('Customers imported successfully.');
            $this->info('Total rows: ' . $totalRows);
            $this->info('Successful imports: ' . $successCount);
            $this->info('Failed imports: ' . $failedCount);
            Log::channel('import_logs')->info("Customer Import => Total rows: $totalRows, Successfully imported rows: $successCount, Failed imports: $failedCount");
        }
        else {
            $this->error('Failed to fetch the CSV file.');
            Log::channel('import_logs')->info("Customer Import => Failed to fetch the CSV file.");
        }
    }
}
