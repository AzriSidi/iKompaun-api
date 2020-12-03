<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';
use api\libraries\REST_Controller;
use api\libraries\Format;

class ApiController extends REST_Controller {
	private static $sys = 'iKompaun';

    public function __construct(){
        parent::__construct();
		// Your own constructor code
		$this->load->model('ApiModel');
		$this->load->model('JwtModel');
    }
    
    function index_get(){
        $data = array("mgs"=>"this is controller");
        $this->response($data);
	}

	function generateToken_post(){
		$item = json_decode(json_encode($this->post()));
        $input['user_name'] = $item->user_name;
		$input['company_name'] = $item->company_name;
		$token = $this->JwtModel->encodeToken($input);
		if($token){
			$status = parent::HTTP_OK;
			$response = ['status' => $status, 'token' => $token];
		}else{
			$status = parent::HTTP_UNAUTHORIZED;
			$response = ['status' => $status, 'msg' => 'Unauthorized Access!'];
		}
        $this->response($response);
	}

	function checkToken_post(){
		$item = json_decode(json_encode($this->post()));
        $input['user_name'] = $item->user_name;
		$input['company_name'] = $item->company_name;
		$checkToken = $this->ApiModel->check_token($input);
        $this->response($checkToken);
	}
	
	function getPay_post(){
		// Get all the headers
		$token = false;
		$json = str_replace('[]','null',json_encode($this->post()));		
		$item = json_decode($json);
        $input['IC_NO'] = $item->IC_NO;
		$input['BRN_NO'] = $item->BRN_NO;

		$headers = $this->input->request_headers();
		if(isset($headers['Token'])){
			$token = $headers['Token'];
		}
		// Extract the token
		if($token){
			$authToken = $this->JwtModel->decodeToken($token);
			if($authToken){
				$response = $this->ApiModel->getPay($input);
			}else{
				$status = parent::HTTP_UNAUTHORIZED;
				$response = ['status' => $status, 'msg' => 'Unauthorized Access!'];
			}
		}else{
			$status = parent::HTTP_UNAUTHORIZED;
			$response = ['status' => $status, 'msg' => 'Unauthorized Access!'];
		}
		$this->logApi('',current_url(),$this->JwtModel->getDecodeToken($token),self::$sys,$input,$response);
		$this->response($response);
	}

	public function inputData_post(){
		$token = false;
		$json = str_replace('[]','null',json_encode($this->post()));		
		$item = json_decode($json);

        $input['NO_AKAUN'] = $item->NO_AKAUN;
		$input['NAMA'] = $item->NAMA;
		$input['ALAMAT1'] = $item->ALAMAT1;
		$input['ALAMAT2'] = $item->ALAMAT2;
		$input['ALAMAT3'] = $item->ALAMAT3;
		$input['PERKARA1'] = $item->PERKARA1;
		$input['PERKARA2'] = $item->PERKARA2;
		$input['NO_RUJUKAN'] = $item->NO_RUJUKAN;
		$input['AMAUN'] = $item->AMAUN;
		$input['TKH_BAYAR'] = $item->TKH_BAYAR;
		$input['TKH_MASUK'] = $item->TKH_MASUK;
		$input['NO_RESIT'] = $item->NO_RESIT;
		$input['NO_RUJUKAN2'] = $item->NO_RUJUKAN2;
		$input['JENIS'] = $item->JENIS;
		$input['PERKARA'] = $item->PERKARA;
		$input['MASA'] = $item->MASA;
		$input['NO_PEKERJA'] = $item->NO_PEKERJA;
		$input['TRED'] = $item->TRED;
		$input['PERKARA4'] = $item->PERKARA4;
		$input['PERKARA5'] = $item->PERKARA5;
		$input['KP'] = $item->KP;
		$input['TKH_LAHIR'] = $item->TKH_LAHIR;
		$input['PARLIMEN'] = $item->PARLIMEN;
		$input['DAERAH'] = $item->DAERAH;
		$input['DUN'] = $item->DUN;
		$input['KOD_TERNAKAN'] = $item->KOD_TERNAKAN;

		$headers = $this->input->request_headers();
		if(isset($headers['Token'])){
			$token = $headers['Token'];
		}
		// Extract the token
		if($token){
			$authToken = $this->JwtModel->decodeToken($token);
			if($authToken){
				$data = $this->ApiModel->inputData($input);
				if($data['bool']){					
					$status = parent::HTTP_OK;
					$response = array('status' => $status,'mgs'=>$data['text']);
				}else{
					$status = parent::HTTP_FORBIDDEN;
					$response = ['status' => $status, 'msg' => 'FORBIDDEN'];
				}
			}else{
				$status = parent::HTTP_UNAUTHORIZED;
				$response = ['status' => $status, 'msg' => 'Unauthorized Access!'];
			}
		}else{
			$status = parent::HTTP_UNAUTHORIZED;
			$response = ['status' => $status, 'msg' => 'Unauthorized Access!'];
		}
		$this->logApi('',current_url(),$this->JwtModel->getDecodeToken($token),self::$sys,$input,$response);
		$this->response($response);
	}

	public function logApi($no_akaun,$url_api,$decodeToken,$sys,$request,$response){
		$format = new Format();

		if($request != null){
			$log['request'] = $format->to_xml($request);
		}else{
			$log['request'] = '';
		}
		
		$log['no_akaun'] = $no_akaun;
		$log['url_api'] = $url_api;
		$log['decodeToken'] = $decodeToken;
		$log['system'] = $sys;
		$log['response'] = $format->to_xml($response);
		$this->ApiModel->saveLogApi($log);
	}
}
