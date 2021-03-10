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

	private $base_url;
	private $refresh_token;

	public function config($config) {
		$this->base_url = $config['base_url'];
		$this->refresh_token = $config['refresh_token'];
	}

	private function get_access_token() {

	}

	private function make_call($request, $data = false, $method = 'GET') {
		$request = $this->base_url . $request;

		switch ($method) {
			case "POST":
				curl_setopt($curl, CURLOPT_POST, 1);
				if ($data)
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;
			case "PUT":
				curl_setopt($curl, CURLOPT_PUT, 1);
				break;
			default:
				if ($data)
					$request = sprintf("%s?%s", $request, http_build_query($data));
		}
	
		if ($this->refresh_token) {
			$access_token = get_access_token($this->refresh_token);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $access_token));
		}

		curl_setopt($curl, CURLOPT_URL, $request);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	
		$result = curl_exec($curl);
		$http_headers = curl_getinfo($curl);
		$http_error = curl_errno($curl);

		curl_close($curl);
	
		if ($http_error) {
			$result['response'] = curl_error($ch);
			$result['request'] = array("url" => $url, "code" => $http_headers['http_code'], "total_time" => $http_headers['total_time']);
			return $result;
		} else {
			$result['response'] = json_decode($result, true);
			$result['request'] = array("url" => $url, "code" => $http_headers['http_code'], "total_time" => $http_headers['total_time']);
			return $result;
		}	
	}

	public function get_items($collection, $data) {

		return $this->make_call('/items/' . $collection, $data, 'GET');

	}


}