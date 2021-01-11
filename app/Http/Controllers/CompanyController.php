<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Google\Cloud\BigQuery\BigQueryClient;
use Carbon\Carbon;
use App\Companies;

class CompanyController extends Controller {

    protected $_bigQuery;
    protected $_dataset;

    public function __construct() {

        $keyFilePath = config('bigquery.google_application_credentials');
        $this->_bigQuery = new BigQueryClient([
            'projectId' => 'rising-method-281311',
            'keyFilePath' => $keyFilePath
        ]);
        $this->_dataset = $this->_bigQuery->dataset('dealroom_api');

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $client = new Client(['base_uri' => 'https://api.dealroom.co']);
        $response = $client->request('POST', '/api/v1/companies/bulk',
            ['auth' => ['111dealroomTesting~env', ''],
            'form_params' => [
                'fields' => 'id,name,type,path,tagline,about,url,website_url,twitter_url,facebook_url,linkedin_url,google_url,crunchbase_url,angellist_url,playmarket_app_id,appstore_app_id,images,employees,employees_latest,industries,sub_industries,corporate_industries,service_industries,technologies,income_streams,growth_stage,traffic_summary,hq_locations,tg_locations,client_focus,revenues,tags,payments,achievements,delivery_method,launch_year,launch_month,has_strong_founder,has_super_founder,total_funding,total_funding_source,last_funding,last_funding_source,company_status,last_updated,last_updated_utc,facebook_likes_chart,alexa_rank_chart,twitter_tweets_chart,twitter_followers_chart,twitter_favorites_chart,employees_chart,similarweb_3_months_growth_unique,similarweb_3_months_growth_percentile,similarweb_3_months_growth_relative,similarweb_3_months_growth_delta,similarweb_6_months_growth_unique,similarweb_6_months_growth_percentile,similarweb_6_months_growth_relative,similarweb_6_months_growth_delta,similarweb_12_months_growth_unique,similarweb_12_months_growth_percentile,similarweb_12_months_growth_relative,similarweb_12_months_growth_delta,app_3_months_growth_unique,app_3_months_growth_percentile,app_3_months_growth_relative,app_6_months_growth_unique,app_6_months_growth_percentile,app_6_months_growth_relative,app_12_months_growth_unique,app_12_months_growth_percentile,app_12_months_growth_relative,employee_3_months_growth_unique,employee_3_months_growth_percentile,employee_3_months_growth_relative,employee_3_months_growth_delta,employee_6_months_growth_unique,employee_6_months_growth_percentile,employee_6_months_growth_relative,employee_6_months_growth_delta,employee_12_months_growth_unique,employee_12_months_growth_percentile,employee_12_months_growth_relative,employee_12_months_growth_delta,kpi_summary,team,investors,fundings,traffic,similarweb_chart',
                'limit' => 100,
                'must' => ['last_updated' => '2020-06-26 00:00:00']]
            ]
        );

        $result = json_decode($response->getBody()->getContents(),true);

        $items = array();
        $items = $result['items'];
        $next_page_id = $result['next_page_id'];

        if ($items) {

            $date = Carbon::now()->toDateString();
            $downloaded_file = 'data/companies-' . $date . '.ndjson';

            $companies = new Companies();
            $addCompanies = $companies->add_companies($items);

        }
    }

}
