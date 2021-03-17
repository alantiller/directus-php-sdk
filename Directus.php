<?php

/*
 * Directus Class
 *
 * The main class of the unoffical Directus PHP SDK.
 * Designed to make talking to Directus in PHP easier, quicker
 * and much, much simpler.
 *
 * @copyright Copyright (c) 2021 Alan Tiller & Slations <alan@slations.co.uk>
 * @license GNU
 *
 */

class DirectusSDK {

    public $base_url;
    public $auth_token = false;

    private $auth_domain = '/';
    private $auth_storage = '_SESSION';
    private $strip_headers;

    // Config Functions
	
    public function config($config) {
        $this->base_url = rtrim($config['base_url'], '/'); // Added to remove trailing "/" if one exists
        $this->auth_storage = $config['auth_storage'];
        $this->strip_headers = $config['strip_headers'];
        $this->auth_domain = $config['auth_domain'];
    }

    public function auth_token($token) {
        $this->auth_token = $token;
    }

    // Value Storage
	
    private function set_value($key, $value) {
        if($this->auth_storage === '_SESSION'):
            $_SESSION[$key] = $value;
        elseif($this->auth_storage === '_COOKIE'):
            setcookie($key, $value, time() + 604800, "/", $this->auth_domain);
        endif;
    }
	
    public function get_value($key) {
        if($this->auth_storage === '_SESSION'):
            return $_SESSION[$key];
        elseif($this->auth_storage === '_COOKIE'):
            return $_COOKIE[$key];
        endif;
    }

    private function unset_value($key) {
        if($this->auth_storage === '_SESSION'):
            unset($_SESSION[$key]);
        elseif($this->auth_storage === '_COOKIE'):
            setcookie($key, '', time() - 1, "/", $this->auth_domain);
        endif;
    }
	
    // Core Functions

    private function get_access_token() {
        if(($this->auth_storage === '_SESSION' || $this->auth_storage === '_COOKIE') && $this->get_value('directus_refresh') != NULL):
            if ($this->get_value('directus_access_expires') < time()):
                $refresh = curl_init($this->base_url . '/auth/refresh');
                curl_setopt($refresh, CURLOPT_POST, 1);
                curl_setopt($refresh, CURLOPT_POSTFIELDS, json_encode(array("refresh_token" => $this->get_value('directus_refresh'))));
                curl_setopt($refresh, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($refresh, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $response = curl_exec($refresh);
                $httpcode = curl_getinfo($refresh, CURLINFO_HTTP_CODE);
                curl_close($refresh);
                if ($httpcode == 200):
                    $response = json_decode($response, true);
                    $this->set_value('directus_refresh', $response['data']['refresh_token']);
                    $this->set_value('directus_access', $response['data']['access_token']);
                    $expires = $response['data']['expires'] / 1000;
                    $expires = time() + $expires;
                    $this->set_value('directus_access_expires', $expires);
                    return $response['data']['access_token'];
                else:
                    $this->auth_logout();
                    return false;
                endif;
            endif;
            return $this->get_value('directus_access');
        elseif ($this->auth_token):
            return $this->auth_token;
        else:
            return false;
        endif;
    }
	
    private function strip_headers($response) {
        if($this->strip_headers === false):
            return $response;
        else:
            unset($response['headers']);
            return $response;
        endif;
    }

    private function make_call($request, $data = false, $method = 'GET', $bypass = false) {
        $request = $this->base_url . $request; // add the base url to the requested uri

        $curl = curl_init(); // creates the curl

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case "PATCH":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));			 					
                break;
            default:
                if ($data)
                    $request = sprintf("%s?%s", $request, http_build_query($data));
        }

        $headers = array('Content-Type: application/json');
        if(($auth_token != false || $this->get_value('directus_refresh')) && $bypass == false)
            array_push($headers, "Authorization: Bearer " . $this->get_access_token());
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($curl, CURLOPT_URL, $request);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl); // execute the curl

        $http_headers = curl_getinfo($curl);
        $http_error = curl_errno($curl);
        
        curl_close($curl);
	
        if ($http_error) {
            $result['errors'] = $http_error;
            $result['headers'] = $http_headers;
            return $result;
        } else {
            $result = json_decode($result, true);
            $result['headers'] = $http_headers;
            return $result;
        }	
    }

    // Items

    public function get_items($collection, $data = false) {
        if(is_array($data)):
            return $this->strip_headers($this->make_call('/items/' . $collection, $data, 'GET'));
        elseif(is_integer($data) || is_string($data)):
            return $this->strip_headers($this->make_call('/items/' . $collection . '/' . $data, false, 'GET'));
        else:
            return $this->strip_headers($this->make_call('/items/' . $collection, false, 'GET'));
        endif;
    }

    public function create_items($collection, $fields) {
        return $this->strip_headers($this->make_call('/items/' . $collection, $fields, 'POST'));
    }

    public function update_items($collection, $fields, $id = null) {
        if ($id != NULL):
            return $this->strip_headers($this->make_call('/items/' . $collection . '/' . $id, $fields, 'PATCH'));
        else:
            return $this->strip_headers($this->make_call('/items/' . $collection, $fields, 'PATCH'));
        endif;
    }

    public function delete_items($collection, $id) {
        if(is_array($id)):
            return $this->strip_headers($this->make_call('/items/' . $collection, $id, 'DELETE'));    
        else:
            return $this->strip_headers($this->make_call('/items/' . $collection . '/' . $id, false, 'DELETE'));
        endif;
    }

    // Auth

    public function auth_user($email, $password, $otp = false) {
        $data = array('email' => $email, 'password' => $password);
        
        if($otp != false)
            $data['otp'] = $otp;

        $response = $this->make_call('/auth/login', $data, 'POST');

        if($response['headers']['http_code'] === 200):
            $this->set_value('directus_refresh', $response['data']['refresh_token']);
            $this->set_value('directus_access', $response['data']['access_token']);
            
            $expires = $response['data']['expires'] / 1000;
            $expires = time() + $expires;

            $this->set_value('directus_access_expires', $expires);

            return true;
        else:
            return $this->strip_headers($response);
        endif;
    }

    public function auth_logout() {
        $data = array("refresh_token" => $this->get_value('directus_refresh'));
        $response = $this->make_call('/auth/logout', $data, 'POST', true);
        if($response['headers']['http_code'] === 200):
            $this->unset_value('directus_refresh');
            $this->unset_value('directus_access');
            $this->unset_value('directus_access_expires');
            header("Refresh:0");
            return true;
        else:
            return $this->strip_headers($response);
        endif;   
    }

    public function auth_password_request($email, $reset_url = false) {
        $data = array('email' => $email);
        if($reset_url != false)
            $data['reset_url'] = $reset_url;
        $response = $this->make_call('/auth/password/request', $data, 'POST');
        if($response['headers']['http_code'] === 200):
            return true;
        else:
            return $this->strip_headers($response);
        endif;
    }

    public function auth_password_reset($token, $password) {
        $data = array('token' => $token, 'password' => $password);
        $response = $this->make_call('/auth/password/reset', $data, 'POST');
        if($response['headers']['http_code'] === 200):
            return true;
        else:
            return $this->strip_headers($response);
        endif;
    }
} ?>