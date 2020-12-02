<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class JwtModel extends CI_Model {
	public static $response = false;

	public function __construct(){
        parent::__construct();
		// Your own constructor code
		$this->load->model('ApiModel');
    }

	public function encodeToken($input){
		$token = AUTHORIZATION::generateToken($input);
		$insertUsers = $this->ApiModel->api_users($input,$token);
		if($insertUsers){
			self::$response = $token;
		}				
		return self::$response;
	}

	public function decodeToken($token){
		try {
			$data = AUTHORIZATION::validateToken($token);						
			if ($data) {
				$authToken = $this->ApiModel->auth_token($data);
				return $authToken;
			}
		} catch (Exception $e) {
			return self::$response;
		}
	}

	public function getDecodeToken($token){
		try {
			$data = AUTHORIZATION::validateToken($token);						
			return $data;
		} catch (Exception $e) {
			return self::$response;
		}
	}
}
