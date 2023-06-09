<?php 
use App\Http\Responses\ApiResponse;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


/**
 * @param string $message
 * @param array $data
 * @param int $statusCode
 * @return \Illuminate\Http\JsonResponse
 */
function successResponse($message = 'Success', $data = [], int $statusCode = Response::HTTP_OK): \Illuminate\Http\JsonResponse
{
    return ApiResponse::success($message, $data, $statusCode);
}


/**
 * @param string $message
 * @param array $errors
 * @param int $statusCode
 * @return \Illuminate\Http\JsonResponse
 */
function errorResponse($message = 'Error', $errors = [], int $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY): \Illuminate\Http\JsonResponse
{
    return ApiResponse::error($message, $errors, $statusCode);
}


/**
 * @param $csv_url
 * @param $key
 * @return false|void
 */
function readCSVFileAndExtract($csv_url, $key) {

    $username = config('services.loop.username');
    $password =  config('services.loop.password');

    $tempFilePath = storage_path("app/temp/{$key}.csv");

    $response = Http::withBasicAuth($username, $password)->withOptions(['verify' => false])->get($csv_url);

    if (!$response->successful())
    {
        return false;
    }

    Storage::put("temp/{$key}.csv", $response->body());

    $csvData = file_get_contents($tempFilePath);

    $lines = explode("\n", $csvData);

    $headers = str_getcsv(array_shift($lines));
    $data = [];
    foreach ($lines as $line) {
        $values = str_getcsv($line);
        $rowData = array_combine($headers, $values);
        $data[] = $rowData;
    }

    Storage::delete("temp/{$key}.csv");

    return $data;
}
