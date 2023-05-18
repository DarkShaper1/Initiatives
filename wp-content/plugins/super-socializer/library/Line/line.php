<?php
	/*
	 *    Copyright 2019 Makornthawat Eugene Emery
	 *
	 *    Licensed under the Apache License, Version 2.0 (the "License");
	 *    you may not use this file except in compliance with the License.
	 *    You may obtain a copy of the License at
	 *
	 *        http://www.apache.org/licenses/LICENSE-2.0
	 *
	 *    Unless required by applicable law or agreed to in writing, software
	 *    distributed under the License is distributed on an "AS IS" BASIS,
	 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	 *    See the License for the specific language governing permissions and
	 *    limitations under the License.
	 */
if(!class_exists('linelogin')){
	class linelogin {

		private	$clientId =  '';
		private $clientSecret = '';
		private $redirectUrl = '';

		public function __construct($config = array())
	    {
	        if (isset($config['client_id'])) {
             $this->clientId = $config['client_id'];
	        }
	        if (isset($config['client_secret'])) {
	            $this->clientSecret = $config['client_secret'];
	        }
	        if (isset($config['redirect_uri'])) {
	            $this->redirectUrl = $config['redirect_uri'];
	        }
	    }



	    // CHANGEME: Default value for CURLOPT_SSL_VERIFYHOST
	    private const VERIFYHOST = false;
	    // CHANGEME: Default value for CURLOPT_SSL_VERIFYPEER
	    private const VERIFYPEER = false;
	    // API DEFAULTS
	    private const AUTH_URL = 'https://access.line.me/oauth2/v2.1/authorize';
	    private const PROFILE_URL = 'https://api.line.me/v2/profile';
	    private const REVOKE_URL = 'https://api.line.me/oauth2/v2.1/revoke';
	    private const TOKEN_URL = 'https://api.line.me/oauth2/v2.1/token';
	    private const VERIFYTOKEN_URL = 'https://api.line.me/oauth2/v2.1/verify';
	    /*
	     *   function getLink
	     *
	     *   Args:
	     *      $scope (int) - Scope integer should equal the sum of the corresponding
	     *                     value for each of the scopes preset:
	     *                     
	     *                     open_id = 1
	     *                     profile = 2
	     *                     email   = 4
	     * 
	     *                     (Example): If your application needs access to open_id,
	     *                                profile and email the value would be "7"
	     * 
	     *   Returns:
	     *      $link - Returns generated link for Line Login/Register.
	     */
	    function getLink($scope) {
	        if (session_status() == PHP_SESSION_NONE) {
	            session_start();
	        }

	        $_SESSION['state'] = hash('sha256', microtime(TRUE).rand().$_SERVER['REMOTE_ADDR']);
	        $link = self::AUTH_URL . '?response_type=code&$clientId=' . $this->clientId . '&redirect_uri=' . $this->redirectUrl . '&scope=' . $this->scope($scope) .'&state=' . $_SESSION['state'];
	        return $link;
	    }
	    /*
	     *   function refresh
	     *   
	     *   Args:
	     *      $token - User access token.
	     * 
	     *   Returns:
	     *      $response (array) - Returns response array in json format.
	     */
	    function refresh($token) {
	        $header = ['Content-Type: application/x-www-form-urlencoded'];
	        $data = [
	            "grant_type" => "refresh_token",
	            "refresh_token" => $token,
	            "$clientId" => self::$clientId,
	            "$clientSecret" => self::$clientSecret
	        ];
	        $response = $this->sendCURL(self::TOKEN_URL, $header, 'POST', $data);
	        return $response;
	    }
	    /*
	     *   function token
	     *   
	     *   Args:
	     *      $code  (GET) - User authorization code.
	     *      $state (GET) - Randomized hash
	     * 
	     *   Returns:
	     *      $response (array) - Returns response array in json format.
	     */
	    function token($code, $state) {
	        if ($_SESSION['state'] != $state) {
	            return false;
	        }
	        $header = ['Content-Type: application/x-www-form-urlencoded'];
	        $data = [
	            "grant_type" => "authorization_code",
	            "code" => $code,
	            "redirect_uri" => $this->redirectUrl,
	            "client_id" => $this->clientId,
	            "client_secret" => $this->clientSecret
	        ];
	        $response = $this->sendCURL(self::TOKEN_URL, $header, 'POST', $data);
	        return json_decode($response);
	    }
	    /*
	     *   function profile
	     *   
	     *   Args:
	     *      $token - User access token.
	     * 
	     *   Returns:
	     *      $response (array) - Returns response array in json format.
	     */
	    function profile($token) {
	        $header = ['Authorization: Bearer ' . $token];
	        $response = $this->sendCURL(self::PROFILE_URL, $header, 'GET');
	        return json_decode($response);
	    }
	    /*
	     *   function verify
	     *   
	     *   Args:
	     *      $token - User access token.
	     * 
	     *   Returns:
	     *      $response (array) - Returns response array in json format.
	     */
	    function verify($token) {
	        $url = self::VERIFYTOKEN_URL . '?access_token=' . $token;
	        $response = $this->sendCURL($url, NULL, 'GET');
	        return $response;
	    }
	    private function scope($scope) {
	        $list = ['openid', 'profile', 'email'];
	        $scope = decbin($scope);
	        while (strlen($scope) < 3) {
	            $scope = '0' . $scope;
	        }
	        $scope = strrev($scope);
	        foreach ($list as $key => $value) {
	            if ($scope[$key] == 1) {
	                if (isset($ret)) {
	                    $ret = $ret . '%20' . $value;
	                } else {
	                    $ret = $value;
	                }
	            }
	        }
	        return $ret;

	    }
	    /*
	     *   private function sendCURL
	     *   
	     *   Args:
	     *      $url      (const) - Request URL.
	     *      $header   (array) - Headers used for this request.
	     *      $type     (char)  - Request type {POST|GET}.
	     *      $data     (array) - Request data (Can be NULL if sending a GET request).
	     * 
	     *   Returns:
	     *      $response (array) - Returns response array in json format.
	     */
	    private function sendCURL($url, $header, $type, $data=NULL) {
	        $request = curl_init();
	        if ($header != NULL) {
	            curl_setopt($request, CURLOPT_HTTPHEADER, $header);
	        }
	        curl_setopt($request, CURLOPT_URL, $url);
	        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, self::VERIFYHOST);
	        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, self::VERIFYPEER);
	        if (strtoupper($type) === 'POST') {
	            curl_setopt($request, CURLOPT_POST, TRUE);
	            curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query($data));
	        }
	        curl_setopt($request, CURLOPT_FOLLOWLOCATION, 1);
	        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
	        $response = curl_exec($request);
	        return $response;
	    }
	}
}
?>
