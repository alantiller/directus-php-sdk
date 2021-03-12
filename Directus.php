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
    private $auth_storage = '$_SESSION';
    private $api_auth_token = false;

    public function config($config) {
        $this ->base_url = $config['base_url'];
        $this->auth_storage = $config['auth_storage'];
    }

    public function auth_token($token) {
        $this->api_auth_token = $token;
    }

    private function get_access_token() {
        if($this->auth_storage === '$_SESSION' && $_SESSION['directus_refresh'] != NULL):

            $access_token = get_access_token();
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token));

        elseif($this->auth_storage === '$_COOKIE' && $_COOKIE['directus_refresh'] != NULL):

            $access_token = get_access_token();
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token));

        elseif ($this->api_auth_token):

            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $this->api_auth_token));

        else:
            return false;
        endif;
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

        if($access_token = get_access_token())
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token));
        
        curl_setopt($curl, CURLOPT_URL, $request);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        $http_headers = curl_getinfo($curl);
        $http_error = curl_errno($curl);
        
        curl_close($curl);
	
        if ($http_error) {
            $result = curl_error($ch);
			$result['headers'] = array("url" => $url, "code" => $http_headers['http_code'], "total_time" => $http_headers['total_time']);
            return $result;
        } else {
            $result = json_decode($result, true);
			$result['headers'] = array("url" => $url, "code" => $http_headers['http_code'], "total_time" => $http_headers['total_time']);
			return $result;
        }	
    }

    // Items

    public function get_items($collection, $data = false) {
        if(is_array($id)):
            return $this->make_call('/items/' . $collection, $id, 'DELETE');
        elseif(is_integer($id)):

        else:
            return $this->make_call('/items/' . $collection . '/' . $id, false, 'DELETE');
        endif;



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

    // Auth

    public function auth_user($email, $password, $otp = false) {
        $fields = array();

        $fields['email'] = $email;
        $fields['password'] = $password;
        $fields['mode'] = "json";

        if($otp != false)
            $fields['otp'] = $otp;

        $response = $this->make_call('/auth/login', $fields, 'POST');

        if($response['headers']['code'] === 200):
            $this->auth_storage_set_value('directus_refresh', $response['data']['refresh_token']);
            $this->auth_storage_set_value('directus_access', $response['data']['access_token']);
            
            $expires = $response['data']['expires'] / 1000;
            $expires = time() + $expires;

            $this->auth_storage_set_value('directus_access_expires', $expires);

            return true;
        else:
            return false;
        endif;
    }

    public function auth_logout($collection, $fields) {
        return $this->make_call('/items/' . $collection, $fields, 'POST');
    }

    public function auth_password_request($collection, $fields) {
        return $this->make_call('/items/' . $collection, $fields, 'POST');
    }

    public function auth_password_reset($collection, $fields) {
        return $this->make_call('/items/' . $collection, $fields, 'POST');
    }

    // Users

}

?>