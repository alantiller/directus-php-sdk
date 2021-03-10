<?php

/*
 * Directus Class
 *
 * This is the unoffical Directus PHP SDK.
 * Designed to make talking to Directus in PHP easier, quicker
 * and much, much simpler.
 *
 * @copyright Copyright (c) 2021 Alan Tiller & Slations <alan@slations.co.uk>
 * @license GNU
 *
 */

class Directus {

    public $base_url;
    private $auth_storage;
    private $api_auth_token;

    public function config($config) {
        $this ->base_url = $config['base_url'];
        $this->auth_storage = $config['auth_storage'];
    }

    public function auth_token($token) {
        $this->api_auth_token = $token;
    }

    private function get_access_token() {

    }

    private function make_call($request, $data = false, $method = 'GET') {
        $request = $this->base_url . $request;
        $curl = curl_init();

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            case "PATCH":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                if ($data)
                    $request = sprintf("%s?%s", $request, http_build_query($data));
        }

        if ($this->api_auth_token)
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $this->api_auth_token));
        
        curl_setopt($curl, CURLOPT_URL, $request);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        $http_error = curl_errno($curl);
        
        curl_close($curl);
	
        if ($http_error) {
            return curl_error($ch);
        } else {
            return json_decode($result, true);
        }	
    }

    // Items

    public function get_items($collection, $data) {
        return $this->make_call('/items/' . $collection, $data, 'GET');
    }

    public function create_items($collection, $fields) {
        return $this->make_call('/items/' . $collection, $fields, 'POST');
    }

    public function update_items($collection, $id, $fields) {
        return $this->make_call('/items/' . $collection . '/' . $id, $fields, 'PATCH');
    }

    public function delete_items($collection, $id) {
        if(is_array($id)):
            return $this->make_call('/items/' . $collection, $id, 'DELETE');    
        else:
            return $this->make_call('/items/' . $collection . '/' . $id, false, 'DELETE');
        endif;
    }

}