<?php

/*
 * Directus-PHP-SDK (https://github.com/alantiller/directus-php-sdk)
 * Copyright (c) Alan Tiller (https://www.alantiller.co.uk/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace AlanTiller\DirectusSdk;

/**
 * Abstract base class for components implementing directus api calls
 * @internal
 */

abstract class ApiManager {

    public $base_url;
    public $auth_token = false;
    protected $storage_prefix;

    // Construct Function
    protected function __construct($base_url, $storage_prefix)
    {
        $this->base_url = rtrim($base_url, '/');
        $this->storage_prefix = $storage_prefix . '_';
    }

    // Value Storage
    private function set_value($key, $value)
    {
        $_SESSION[$this->storage_prefix . $key] = $value;
    }

    protected function get_value($key)
    {
        return $_SESSION[$this->storage_prefix . $key] ?? null;
    }

    protected function unset_value($key)
    {
        unset($_SESSION[$this->storage_prefix . $key]);
    }

    // Core Functions
    private function get_access_token()
    {
        if (($this->auth_storage === '_SESSION' || $this->auth_storage === '_COOKIE') && $this->get_value('refresh') != NULL):

            if ($this->get_value('access_expires') < time()):
                $refresh = curl_init($this->base_url . '/auth/refresh');
                curl_setopt($refresh, CURLOPT_POST, 1);
                curl_setopt($refresh, CURLOPT_POSTFIELDS, json_encode(array("refresh_token" => $this->get_value('refresh'))));
                curl_setopt($refresh, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($refresh, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $response = curl_exec($refresh);
                $httpcode = curl_getinfo($refresh, CURLINFO_HTTP_CODE);
                curl_close($refresh);
                if ($httpcode == 200):
                    $response = json_decode($response, true);
                    $this->set_value('refresh', $response['data']['refresh_token']);
                    $this->set_value('access', $response['data']['access_token']);
                    $expires = $response['data']['expires'] / 1000;
                    $expires = time() + $expires;
                    $this->set_value('access_expires', $expires);
                    return $response['data']['access_token'];
                else:
                    $this->auth_logout();
                    return false;
                endif;
            endif;

            return $this->get_value('access');
        elseif ($this->auth_token):
            return $this->auth_token;
        else:
            return false;
        endif;
    }


    protected function make_call($request, $data = false, $method = 'GET')
    {
        $request = $this->base_url . $request; // add the base url to the requested uri
        $auth_token = $this->auth_token;

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
                if ($data["folder"] != null) {
                    $fields["folder"] = $data["folder"];
                }

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
                $post_data .= "--" . $delimiter . "--" . $eol;

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

        if ($auth_token != false || $this->get_value('refresh')) {
            array_push($headers, "Authorization: Bearer " . $this->get_access_token());
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($curl, CURLOPT_URL, $request);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl); // execute the curl

        $http_error = curl_errno($curl);

        curl_close($curl);

        if ($http_error) {
            $result['errors'] = $http_error;
        } else {
            $result = json_decode($result, true);
        }

        return $result;
    }
}