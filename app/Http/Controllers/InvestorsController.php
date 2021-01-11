<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Google\Cloud\BigQuery\BigQueryClient;
use Carbon\Carbon;

class FundingsController extends Controller
{
    /**
     * Call the funding Api and import the data in BigQuery transactions table
     *
     */
    public function index()
    {
        $client = new Client(['base_uri' => 'https://api.dealroom.co']);
        $response = $client->request('POST', '/api/v1/transactions/bulk', ['auth' => ['111dealroomTesting~env', '']], ['form_params' => [
            'must' => ['last_updated' => '2016-12-10 00:00:00']
        ]]);

        $items = array();
        $items = json_decode($response->getBody()->getContents(),true)['items'];

        if ($items) {

            $date = Carbon::now()->toDateString();
            $downloaded_file = 'data/fundings-' . $date . '.ndjson';

            foreach ($items as $key => $value) {

                $data = $this->preProcess($value);
                $newline = json_encode($data) . "\n";
                file_put_contents(public_path($downloaded_file), $newline, FILE_APPEND);

            }

        }

        $keyFilePath = config('bigquery.google_application_credentials');

        $bigQuery = new BigQueryClient([
            'projectId' => 'rising-method-281311',
            'keyFilePath' => $keyFilePath
        ]);

// Get an instance of a previously created table.
        $dataset = $bigQuery->dataset('dealroom_api');
        $table = $dataset->table('fundings');
        $file = public_path($downloaded_file);

// Begin a job to import data from a CSV file into the table.
        $loadJobConfig = $table->load(
            fopen($file, 'r')
        )->sourceFormat('NEWLINE_DELIMITED_JSON');

        $job = $table->runJob($loadJobConfig);
    }

    public function preProcess($data)
    {

        /*$helper = new Helper();*/

        if ($data['company']) {
            unset($data['company']);
        }

        if ($data['investors']) {
            unset($data['investors']);
        }

        /*        if ($data['company']['images']) {
                    $data['company']['images'] = $helper->replace_image_key($data['company']['images']);
                }

                if ($data['company']['hq_locations']) {
                    $data['company']['hq_locations'] = $helper->replace_street_number_type($data['company']['hq_locations']);
                }

                if ($data['investors']) {

                    foreach ($data['investors'] as $key => $investor) {

                        foreach ($investor as $key2 => $value2) {

                            if ($key2 === 'images') {

                                $data['investors'][$key]['images'] = $helper->replace_image_key($data['investors'][$key]['images']);

                            }
                        }
                    }
                }*/

        if ($data['last_updated']) {

            $date = date('Y-m-d H:i:s', strtotime($data['last_updated']));
            $data['last_updated'] = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d H:i:s');

        }

        return $data;

    }

}
