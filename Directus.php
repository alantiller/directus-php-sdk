<?php

/*
 * Directus Class
 *
 * The main class of the unoffical Directus PHP SDK.
 * Designed to make talking to Directus in PHP easier, quicker
 * and much, much simpler.
 *
 * @copyright Copyright (c) 2021 Slations (Alan Tiller T/A) <alan@slations.co.uk>
 * @license GNU
 *
 */

class Directus {
    public $base_url;
    public $auth_token = false;
    private $auth_domain = '/';
    private $auth_storage = '_SESSION';
    private $strip_headers = true;

    // Construct Function
    public function __construct($base_url, $auth_storage = '_SESSION', $auth_domain = '/', $strip_headers = true) {
        $this->base_url = rtrim($base_url, '/');
        $this->auth_storage = $auth_storage;
        $this->strip_headers = $strip_headers;
        $this->auth_domain = $auth_domain;
    }

    // Value Storage
    private function set_value($key, $value) {
        $_SESSION[$key] = $value;
        if($this->auth_storage === '_COOKIE'):
            setcookie($key, $value, time() + 604800, "/", $this->auth_domain);
        endif;
    }
    public function get_value($key) {
        return $_SESSION[$key];
    }
    private function unset_value($key) {
        unset($_SESSION[$key]);
        if($this->auth_storage === '_COOKIE'):
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
		$headers = array();
		
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
				array_push($headers, "Content-Type: application/json");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
				array_push($headers, "Content-Type: application/json");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case "PATCH":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
				array_push($headers, "Content-Type: application/json");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));			 					
                break;
			case "POST_MULTIPART":
				$fields = array("storage" => $data["storage"], "download_filename" => $data["file"]["name"]);
				if ($data["folder"] != null) {$fields["folder"] = $data["folder"];}
				
				$boundary = uniqid();
				$delimiter = '-------------' . $boundary;
				$eol = "\r\n";
				$post_data = '';
				
				// Add fields
				foreach ($fields as $name => $content) {
					$post_data .= "--" . $delimiter . $eol . 'Content-Disposition: form-data; name="' 
					. $name . "\"" . $eol . $eol . $content . $eol;
				}
		
				// Include File
				$post_data .= "--" . $delimiter . $eol . 'Content-Disposition: form-data; name="' 
				. $data["file"]["name"] . '"; filename="' . $data["file"]["name"] . '"' . $eol 
				. 'Content-Type: ' . mime_content_type($_FILES["file"]['tmp_name']) . $eol 
				. 'Content-Transfer-Encoding: binary' . $eol;
				$post_data .= $eol;
				$post_data .= file_get_contents($data["file"]["tmp_name"]) . $eol;
				$post_data .= "--" . $delimiter . "--".$eol;			
				
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);			
				array_push($headers, "Content-Type: multipart/form-data; boundary=" . $delimiter);
				array_push($headers, "Content-Length: " . strlen($post_data));
				break;
            default:
                if ($data)
                    $request = sprintf("%s?%s", $request, http_build_query($data));
        }

        
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

    // Set Auth Token
    public function auth_token($token) {
        $this->auth_token = $token;
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

    // Users
    public function users_get($data = false) {
        if(is_array($data)):
            return $this->strip_headers($this->make_call('/users', $data, 'GET'));
        elseif(is_integer($data) || is_string($data)):
            return $this->strip_headers($this->make_call('/users/' . $data, false, 'GET'));
        else:
            return $this->strip_headers($this->make_call('/users', false, 'GET'));
        endif;
    }
    public function users_create($fields) {
        return $this->strip_headers($this->make_call('/users', $fields, 'POST'));
    }
    public function users_update($fields, $id = null) {
        return $this->strip_headers($this->make_call('/users/' . $id, $fields, 'PATCH'));
    }
    public function users_delete($id) {
        if(is_array($id)):
            return $this->strip_headers($this->make_call('/users', $id, 'DELETE'));    
        else:
            return $this->strip_headers($this->make_call('/users/' . $id, false, 'DELETE'));
        endif;
    }
    public function users_invite($email, $role, $invite_url = false) {
        $data = array('email' => $email, 'role' => $role);
        if($invite_url != false)
            $data['invite_url'] = $invite_url;
        $response = $this->make_call('/users/invite', $data, 'POST');
        if($response['headers']['http_code'] === 200):
            return true;
        else:
            return $this->strip_headers($response);
        endif;
    }
    public function users_accept_invite($password, $token) {
        $data = array('password' => $password, 'token' => $token);
        $response = $this->make_call('/users/invite/accept', $data, 'POST');
        if($response['headers']['http_code'] === 200):
            return true;
        else:
            return $this->strip_headers($response);
        endif;
    }
    public function users_me($filter = false) {
        return $this->strip_headers($this->make_call('/users/me', $filter, 'GET'));
    }

	
    // Files
    public function files_get($uri, $data = false) {
        return $this->strip_headers($this->make_call('/files', null, 'GET'));
    }
    public function files_create($file, $folder = null, $storage = 'local') {
        $data = array("file" => $file, "storage" => $storage, "folder" => $folder);
        return $this->strip_headers($this->make_call('/files', $data, 'POST_MULTIPART'));
    }
	
	
    // Custom Calls
    public function get($uri, $data = false) {
        return $this->strip_headers($this->make_call($uri, $data, 'GET'));
    }
    public function post($uri, $data = false) {
        return $this->strip_headers($this->make_call($uri, $data, 'POST'));
    }
    public function patch($uri, $data = false) {
        return $this->strip_headers($this->make_call($uri, $data, 'PATCH'));
    }
    public function delete($uri, $data = false) {
        return $this->strip_headers($this->make_call($uri, $data, 'DELETE'));
    }
} ?>
