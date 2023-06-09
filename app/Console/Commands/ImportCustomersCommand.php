<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;


class ImportCustomersCommand extends Command
{
    protected $signature = 'import:customers';

    protected $description = 'Import customers from CSV file';

    public function handle()
    {
        $url = 'https://backend-developer.view.agentur-loop.com/customers.csv';

        $data = readCSVFileAndExtract($url, 'customers');

        if (!$data) {
            $this->error('Failed to fetch the CSV file.');
            Log::channel('import_logs')->info("Customer Import => Failed to fetch the CSV file.");
        }

        $totalRows = count($data);
        $successCount = 0;
        $failedCount = 0;
        $bar = $this->output->createProgressBar($totalRows);

        $bar->start();
        foreach ($data as $customerData) {
            try {
                Customer::create([
                    'job_title' => $customerData['Job Title'],
                    'email' => $customerData['Email Address'],
                    'full_name' => $customerData['FirstName LastName'],
                    'registered_since' => Carbon::parse($customerData['registered_since'])->format('Y-m-d'),
                    'phone' => $customerData['phone'],
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line('');

        $this->info('Customers imported successfully.');
        $this->info('Total rows: ' . $totalRows);
        $this->info('Successful imports: ' . $successCount);
        $this->info('Failed imports: ' . $failedCount);
        Log::channel('import_logs')->info("Customer Import => Total rows: $totalRows, Successfully imported rows: $successCount, Failed imports: $failedCount");

    }
}
