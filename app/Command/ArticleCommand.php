<?php

namespace App\Command;

use App\Repository\Article;

class ArticleCommand
{
    /**
     * handle command line article
     * 
     * @param array $args
     * @param array $options
     */
    public function handle($args, $options)
    {
        try {
            $apiDatas = $this->getDataFromApi();
            $dbDatas  = $this->getDataFromDb();

            $limit = 5;
            $result = [];
            foreach ($dbDatas as $key => $data) {
                // if position is wrong
                if ($data['position'] > $key + 1) {
                    $slice = array_splice($apiDatas, 0, $data['position'] - count($result) - 1);
                    array_push($result, ...$slice);
                }

                $result[] = $data;

                if (count($result) >= $limit) break;
            }

            if (count($result) < $limit) {
                array_push($result, ...array_splice($apiDatas, 0, $limit - count($result)));
            }

            echo json_encode(array_slice($result, 0, $limit));
        } catch (\Exception $e) {
            cli_error($e->getMessage());
        }
    }

    /**
     * get data from database
     */
    public function getDataFromDb()
    {
        $repo = new Article();
        return $repo->getDataToCombine();
    }


    /**
     * get data from api
     */
    public function getDataFromApi()
    {
        // initiate curl
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://cdnstatic.detik.com/internal/sample/demo.json",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => true,
            CURLOPT_ENCODING => 'utf-8',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                'Accept: application/json'
            ]
        ]);

        // execute
        $response = curl_exec($curl);
        $errno = curl_errno($curl);
        $errmsg = curl_error($curl);
        $info = curl_getinfo($curl);

        // close session
        curl_close($curl);

        // get response header and http status code
        $rawheaders = preg_split('/\r\n|\r|\n/', trim(substr($response, 0, $info['header_size'])));
        preg_match('/^(HTTP\/[\d\.]+) (\d{3}) (.+?)$/', array_shift($rawheaders), $httpstatus);

        // success request
        if ((int) $errno == 0 && (int) $httpstatus[2] <= 299 && (int) $httpstatus[2] >= 200) {
            $body = substr($response, $info['header_size']);
            return json_decode($body, true);
        } else {
            return [];
        }
    }
}
