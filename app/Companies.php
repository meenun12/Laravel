<?php

    namespace App;
    use App\Helper\Helper;
    use Illuminate\Database\Eloquent\Model;
    use Google\Cloud\BigQuery\BigQueryClient;

    class Companies extends Model {

        protected $_bigQuery;
        protected $_dataset;
        protected $_time;

        public function __construct() {

            $keyFilePath = config('bigquery.google_application_credentials');
            $this->_bigQuery = new BigQueryClient([
                'projectId' => 'rising-method-281311',
                'keyFilePath' => $keyFilePath
            ]);
            $this->_dataset = $this->_bigQuery->dataset('dealroom_api');
            $this->_time = date('d.m.Y',strtotime("-90 minutes"));

        }

        public function add_companies($items) {

            $check = false;

            foreach ($items as $key => $value) {

                $id = $value['id'];
                $table = 'rising-method-281311.dealroom_api.companies';
                $check = $this->checkCompany($id, $table);
                $data = $this->preProcess($value);

                if ($check) {

                    $delete = $this->deleteCompany($id, $table);
                    if ($delete) {
                        $insert = $this->insertCompany($id, $data);
                    }

                } else {

                    $insert = $this->insertCompany($id, $data);

                }
            }
        }

        public function add_companies_employees_chart($id, $items) {

            $table = 'rising-method-281311.dealroom_api.employees_chart';
            $data = array();

            foreach ($items as $key => $value) {

                foreach ($value as $key2 => $value2) {

                    $check = $this->checkEmployeeChart($id, $table, $value2['date'], $value2['value']);

                    $data['entity_id'] = $id;
                    $data['date'] = $id;
                    $data['value'] = $id;

                    if ($check) {

                        $delete = $this->deleteEmployeeChart($id, $table, $value2['date'], $value2['value']);
                        if ($delete) {
                            $insert = $this->insertEmployeeChart($id, $table, $data);
                        }

                    } else {

                        $insert = $this->insertEmployeeChart($id, $table, $data);

                    }
                }

            }
        }

        public function add_companies_hq_locations($id, $hq_location) {

            $table = 'rising-method-281311.dealroom_api.hq_locations';
            $data = array();

            foreach ($hq_location as $key => $value) {

                $check = $this->checkHqLocation($id, $table, $value);

                if ($check) {

                    $delete = $this->deleteHqLocation($id, $table, $value);
                    if ($delete) {
                        $insert = $this->insertHqLocation($id, $table, $value);
                    }

                } else {

                    $insert = $this->insertHqLocation($id, $table, $value);

                }
            }
        }

        public function add_companies_investors($id, $investors) {

            $table = 'rising-method-281311.dealroom_api.companies_investors';
            $data = array();

            if ($investors['items']) {

                foreach ($investors['items'] as $key => $value) {

                    $check = $this->checkInvestor($id, $table, $value);

                    if ($check) {

                        $delete = $this->deleteInvestor($id, $table, $value);
                        if ($delete) {
                            $insert = $this->insertInvestor($id, $table, $value);
                        }

                    } else {

                        $insert = $this->insertInvestor($id, $table, $value);

                    }
                }

            }
        }

        public function add_companies_industries($id, $items) {

            $table = 'rising-method-281311.dealroom_api.industries';
            $data = array();

            foreach ($items as $key => $value) {

                $data['entity_id'] = $id;
                $data['industry_id'] = $value['id'];
                $data['industry_name'] = $value['name'];

                $check = $this->checkIndustries($id, $table, $data['industry_id'], $data['industry_name']);

                if ($check) {

                    $delete = $this->deleteIndustries($id, $table, $data['industry_id'], $data['industry_name']);
                    if ($delete) {
                        $insert = $this->insertIndustries($id, $table, $data);
                    }

                } else {

                    $insert = $this->insertIndustries($id, $table, $data);

                }

            }
        }

        public function add_companies_sub_industries($id, $items) {

            $table = 'rising-method-281311.dealroom_api.sub_industries';

            foreach ($items as $key => $value) {

                $check = $this->checkSubIndustries($id, $table, $value);

                if ($check) {
                    $delete = $this->deleteSubIndustries($id, $table, $value);
                    if ($delete) {
                        $insert = $this->insertSubIndustries($id, $table, $value);
                    }
                } else {
                    $insert = $this->insertSubIndustries($id, $table, $value);
                }
            }
        }

        public function preProcess($data) {

            foreach ($data as $key => $value) {

                if (in_array($key, array('industries')) && $value) {
                    $this->add_companies_industries($data['id'], $data[$key]);
                    unset($data[$key]);
                }

                if (in_array($key, array('sub_industries')) && $value) {
                    $this->add_companies_sub_industries($data['id'], $data[$key]);
                    unset($data[$key]);
                }

                if (in_array($key, array('hq_locations')) && $value) {
                    $this->add_companies_hq_locations($data['id'], $data[$key]);
                    unset($data[$key]);
                }

                if (in_array($key, array('employees_chart')) && $value) {
                    $this->add_companies_employees_chart($data['id'], $data[$key]);
                    unset($data[$key]);
                }

                if (in_array($key, array("last_updated"))) {
                    $date = date('Y-m-d H:i:s', strtotime($data['last_updated']));
                    $data['last_updated'] = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d H:i:s');
                }

                if (in_array($key, array('investors')) && $value) {
                    $this->add_companies_investors($data['id'], $data[$key]);
                    unset($data[$key]);
                }

                /*if (in_array($key, array('images', 'sub_industries', 'corporate_industries', 'service_industries', 'technologies', 'income_streams', 'hq_locations', 'tg_locations', 'client_focus', 'revenues', 'payments', 'achievements', 'facebook_likes_chart', 'alexa_rank_chart', 'twitter_tweets_chart', 'twitter_followers_chart', 'twitter_favorites_chart', 'kpi_summary', 'team', 'investors', 'fundings', 'traffic', 'similarweb_chart', 'tags'))) {
                    unset($data[$key]);
                }*/

                if (in_array($key, array("last_updated"))) {
                    $date = date('Y-m-d H:i:s', strtotime($data['last_updated']));
                    $data['last_updated'] = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d H:i:s');
                }

                if (in_array($key, array("similarweb_3_months_growth_unique","similarweb_3_months_growth_relative","similarweb_3_months_growth_delta","similarweb_6_months_growth_unique","similarweb_6_months_growth_relative","similarweb_6_months_growth_delta","similarweb_12_months_growth_unique","similarweb_12_months_growth_relative","similarweb_12_months_growth_delta","app_3_months_growth_unique","app_3_months_growth_relative","app_6_months_growth_unique","app_6_months_growth_relative","app_12_months_growth_unique","app_12_months_growth_relative","employee_3_months_growth_unique","employee_3_months_growth_relative","employee_3_months_growth_delta","employee_6_months_growth_unique","employee_6_months_growth_relative","employee_6_months_growth_delta","employee_12_months_growth_unique","employee_12_months_growth_relative","employee_12_months_growth_delta"))) {
                    $value = (int) $value;
                    $data[$key] = $value;
                }

                if (in_array($key, array("date","similarweb_3_months_growth_percentile","similarweb_6_months_growth_percentile","similarweb_12_months_growth_percentile","app_3_months_growth_percentile","app_6_months_growth_percentile","app_12_months_growth_percentile","employee_3_months_growth_percentile","employee_6_months_growth_percentile","employee_12_months_growth_percentile"))) {
                    $value = (string) $value;
                    $data[$key] = $value;
                }

                if (in_array($key, array('images', 'industries', 'sub_industries', 'corporate_industries', 'service_industries', 'technologies', 'income_streams', 'hq_locations', 'tg_locations', 'client_focus', 'revenues', 'payments', 'achievements', 'facebook_likes_chart', 'alexa_rank_chart', 'twitter_tweets_chart', 'twitter_followers_chart', 'twitter_favorites_chart', 'employees_chart', 'kpi_summary', 'team', 'investors', 'fundings', 'traffic', 'similarweb_chart'))) {
                    unset($data[$key]);
                }

            }

            return $data;
        }

        public function deleteCompany($id, $table) {

            $deleteRow = <<<ENDSQL
                delete FROM $table WHERE id = $id;
            ENDSQL;

            $queryJobConfig = $this->_bigQuery->query($deleteRow);
            $queryResults = $this->_bigQuery->runQuery($queryJobConfig);

        }

        public function insertCompany($id, $data) {

            $table = $this->_dataset->table('companies');
            $insertResponse = $table->insertRows([
                ['data' => $data]
            ]);

            if ($insertResponse->isSuccessful()) {
                print('Data streamed into BigQuery successfully' . PHP_EOL);
            } else {
                foreach ($insertResponse->failedRows() as $row) {
                    foreach ($row['errors'] as $error) {
                        printf('%s: %s' . PHP_EOL, $error['reason'], $error['message']);
                    }
                }
            }
        }

        public function checkCompany($id, $table) {

            $check = false;

            $query = <<<ENDSQL
                SELECT id
                FROM $table
                WHERE id = $id
                LIMIT 1;
                ENDSQL;

            $queryJobConfig = $this->_bigQuery->query($query);
            $queryResults = $this->_bigQuery->runQuery($queryJobConfig);

            if ($queryResults->isComplete()) {

                $rows = $queryResults->rows();
                foreach ($rows as $row) {
                    $check = true;
                }

            } else {
                throw new Exception('The query failed to complete');
            }

            return $check;

        }

        public function checkEmployeeChart($id, $table, $date, $value) {

            $check = false;

            $query = <<<ENDSQL
                SELECT id
                FROM $table
                WHERE entity_id = $id
                and date = '$date'
                and value = '$value'
                LIMIT 1;
                ENDSQL;

            $queryJobConfig = $this->_bigQuery->query($query);
            $queryResults = $this->_bigQuery->runQuery($queryJobConfig);

            if ($queryResults->isComplete()) {

                $rows = $queryResults->rows();
                foreach ($rows as $row) {
                    $check = true;
                }

            } else {
                throw new Exception('The query failed to complete');
            }

            return $check;

        }

        public function deleteEmployeeChart($id, $table, $date, $value) {

            $deleteRow = <<<ENDSQL
                delete FROM $table WHERE entity_id = $id and date = '$date' and value = '$value';
            ENDSQL;

            $queryJobConfig = $this->_bigQuery->query($deleteRow);
            $queryResults = $this->_bigQuery->runQuery($queryJobConfig);

        }

        public function insertEmployeeChart($id, $data) {

            $table = $this->_dataset->table('employees_chart');
            $insertResponse = $table->insertRows([
                ['data' => $data]
            ]);

            if ($insertResponse->isSuccessful()) {
                print('Data streamed into BigQuery successfully' . PHP_EOL);
            } else {
                foreach ($insertResponse->failedRows() as $row) {
                    foreach ($row['errors'] as $error) {
                        printf('%s: %s' . PHP_EOL, $error['reason'], $error['message']);
                    }
                }
            }
        }

        public function checkIndustries($id, $table, $industry_id, $industry_name) {

            $check = false;

            $query = <<<ENDSQL
                SELECT entity_id
                FROM $table
                WHERE entity_id = $id
                and industry_id = $industry_id
                and industry_name = '$industry_name'
                LIMIT 1;
                ENDSQL;

            $queryJobConfig = $this->_bigQuery->query($query);
            $queryResults = $this->_bigQuery->runQuery($queryJobConfig);

            if ($queryResults->isComplete()) {

                $rows = $queryResults->rows();
                foreach ($rows as $row) {
                    $check = true;
                }

            } else {
                throw new Exception('The query failed to complete');
            }

            return $check;

        }

        public function deleteIndustries($id, $table, $industry_id, $industry_name) {

            try {

                $deleteRow = <<<ENDSQL
                delete FROM $table WHERE entity_id = $id and industry_id = $industry_id and industry_name = '$industry_name';
                ENDSQL;

                $queryJobConfig = $this->_bigQuery->query($deleteRow);
                $queryResults = $this->_bigQuery->runQuery($queryJobConfig);

            } catch (Google\Cloud\Core\Exception\BadRequestException $e) {
                printf('Error: ' . $e->getMessage());
            }

        }

        public function insertIndustries($id, $table, $data) {

            $table = $this->_dataset->table('industries');
            $insertResponse = $table->insertRows([
                ['data' => $data]
            ]);

            if ($insertResponse->isSuccessful()) {
                print('Data streamed into BigQuery successfully' . PHP_EOL);
            } else {
                foreach ($insertResponse->failedRows() as $row) {
                    foreach ($row['errors'] as $error) {
                        printf('%s: %s' . PHP_EOL, $error['reason'], $error['message']);
                    }
                }
            }
        }

        public function checkSubIndustries($id, $table, $sub_industry_name) {

            $check = false;

            $query = <<<ENDSQL
                SELECT entity_id
                FROM $table
                WHERE entity_id = $id
                and sub_industry_name = '$sub_industry_name'
                LIMIT 1;
                ENDSQL;

            $queryJobConfig = $this->_bigQuery->query($query);
            $queryResults = $this->_bigQuery->runQuery($queryJobConfig);

            if ($queryResults->isComplete()) {

                $rows = $queryResults->rows();
                foreach ($rows as $row) {
                    $check = true;
                }

            } else {
                throw new Exception('The query failed to complete');
            }

            return $check;

        }

        public function deleteSubIndustries($id, $table, $sub_industry_name) {

            try {

                $deleteRow = <<<ENDSQL
                    delete FROM $table WHERE entity_id = $id and sub_industry_name = '$sub_industry_name';
                ENDSQL;

                $queryJobConfig = $this->_bigQuery->query($deleteRow);
                $queryResults = $this->_bigQuery->runQuery($queryJobConfig);

            } catch (Google\Cloud\Core\Exception\BadRequestException $e) {

                printf('Error: ' . $e->getMessage());

            }

        }

        public function insertSubIndustries($id, $table, $data) {

            $table = $this->_dataset->table('sub_industries');
            $insertResponse = $table->insertRows([
                ['data' => $data]
            ]);

            if ($insertResponse->isSuccessful()) {
                print('Data streamed into BigQuery successfully' . PHP_EOL);
            } else {
                foreach ($insertResponse->failedRows() as $row) {
                    foreach ($row['errors'] as $error) {
                        printf('%s: %s' . PHP_EOL, $error['reason'], $error['message']);
                    }
                }
            }
        }

        public function checkTechnologies($id, $table, $technology_name) {

            $check = false;

            $query = <<<ENDSQL
                SELECT entity_id
                FROM $table
                WHERE entity_id = $id
                and technology_name = '$technology_name'
                LIMIT 1;
                ENDSQL;

            $queryJobConfig = $this->_bigQuery->query($query);
            $queryResults = $this->_bigQuery->runQuery($queryJobConfig);

            if ($queryResults->isComplete()) {

                $rows = $queryResults->rows();
                foreach ($rows as $row) {
                    $check = true;
                }

            } else {
                throw new Exception('The query failed to complete');
            }

            return $check;

        }

        public function deleteTechnologies($id, $table, $technology_name) {

            $deleteRow = <<<ENDSQL
                delete FROM $table WHERE entity_id = $id and technology_name = '$technology_name';
            ENDSQL;

            $queryJobConfig = $this->_bigQuery->query($deleteRow);
            $queryResults = $this->_bigQuery->runQuery($queryJobConfig);

        }

        public function insertTechnologies($id, $table, $data) {

            $table = $this->_dataset->table('technologies');
            $insertResponse = $table->insertRows([
                ['data' => $data]
            ]);

            if ($insertResponse->isSuccessful()) {
                print('Data streamed into BigQuery successfully' . PHP_EOL);
            } else {
                foreach ($insertResponse->failedRows() as $row) {
                    foreach ($row['errors'] as $error) {
                        printf('%s: %s' . PHP_EOL, $error['reason'], $error['message']);
                    }
                }
            }
        }

        public function checkHqLocation($id, $table, $hq_location) {

            $entity_id = $id;
            $id = $hq_location['id'];
            $continent = $hq_location['continent'];
            $lat = $hq_location['lat'];
            $country = $hq_location['country'];
            $zip = $hq_location['zip'];
            $street = $hq_location['street'];
            $city = $hq_location['city'];
            $street_number = $hq_location['street_number'];
            $address = $hq_location['address'];
            $lon = $hq_location['lon'];

            $check = false;

            $query = <<<ENDSQL
                SELECT id
                FROM $table
                WHERE entity_id = $entity_id
                and id = $id
                LIMIT 1;
                ENDSQL;

            $queryJobConfig = $this->_bigQuery->query($query);
            $queryResults = $this->_bigQuery->runQuery($queryJobConfig);

            if ($queryResults->isComplete()) {

                $rows = $queryResults->rows();
                foreach ($rows as $row) {
                    $check = true;
                }

            } else {
                throw new Exception('The query failed to complete');
            }

            return $check;

        }

        public function deleteHqLocation($id, $table, $hq_location) {

            $continent = $hq_location['continent'];
            $lat = $hq_location['lat'];
            $country = $hq_location['country'];
            $zip = $hq_location['zip'];
            $street = $hq_location['street'];
            $city = $hq_location['city'];
            $street_number = $hq_location['street_number'];
            $address = $hq_location['address'];
            $lon = $hq_location['lon'];
            $id = $hq_location['id'];

            $deleteRow = <<<ENDSQL
                delete FROM $table WHERE entity_id = $id and id = $id
            ENDSQL;

            $queryJobConfig = $this->_bigQuery->query($deleteRow);
            $queryResults = $this->_bigQuery->runQuery($queryJobConfig);

        }

        public function insertHqLocation($id, $table, $hq_location) {

            $hq_location['entity_id'] = $id;

            $table = $this->_dataset->table('hq_locations');
            $insertResponse = $table->insertRows([
                ['data' => $hq_location]
            ]);

            if ($insertResponse->isSuccessful()) {
                print('Data streamed into BigQuery successfully' . PHP_EOL);
            } else {
                foreach ($insertResponse->failedRows() as $row) {
                    foreach ($row['errors'] as $error) {
                        printf('%s: %s' . PHP_EOL, $error['reason'], $error['message']);
                    }
                }
            }
        }

        public function checkInvestor($id, $table, $investor) {

            $investor_id = $investor['id'];
            $name = $investor['name'];
            $path = $investor['path'];
            $type = $investor['type'];
            $url = $investor['url'];
            $exited = $investor['exited'];

            $check = false;

            $query = <<<ENDSQL
                SELECT id
                FROM $table
                WHERE entity_id = $id
                and id = $investor_id
                and name = '$name'
                and path = '$path'
                and url = '$url'
                and type = '$type'
                LIMIT 1;
                ENDSQL;

/*            $query = <<<ENDSQL
                SELECT id
                FROM $table
                WHERE entity_id = 1422467
                and id = 28287
                LIMIT 1;
                ENDSQL;*/

            $queryJobConfig = $this->_bigQuery->query($query);
            $queryResults = $this->_bigQuery->runQuery($queryJobConfig);

            if ($queryResults->isComplete()) {

                $rows = $queryResults->rows();
                $check = isset($rows) ? true : false;

            } else {
                throw new Exception('The query failed to complete');
            }

            return $check;

        }

        public function deleteInvestor($id, $table, $investor) {

            $investor_id = $investor['id'];
            $name = $investor['name'];
            $path = $investor['path'];
            $type = $investor['type'];
            $url = $investor['url'];
            $exited = $investor['exited'];

            $deleteRow = <<<ENDSQL
                delete FROM $table WHERE entity_id = $id and id = $investor_id and name = '$name' and path = '$path' and url = '$url' and type = '$type'
            ENDSQL;

            $queryJobConfig = $this->_bigQuery->query($deleteRow);
            $queryResults = $this->_bigQuery->runQuery($queryJobConfig);

        }

        public function insertInvestor($id, $table, $investor) {

            $investor['entity_id'] = $id;

            $table = $this->_dataset->table('companies_investors');
            $insertResponse = $table->insertRows([
                ['data' => $investor]
            ]);

            if ($insertResponse->isSuccessful()) {
                print('Data streamed into BigQuery successfully' . PHP_EOL);
            } else {
                foreach ($insertResponse->failedRows() as $row) {
                    foreach ($row['errors'] as $error) {
                        printf('%s: %s' . PHP_EOL, $error['reason'], $error['message']);
                    }
                }
            }
        }

    }
