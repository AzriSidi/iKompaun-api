<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ApiModel extends CI_Model{

	public function getPay($input){
		$clmn_bil = 'NO_AKAUN';		
		$table_bil = 'HASIL.BIL';
		
		$this->db
			 ->select($clmn_bil." from ".$table_bil,false)
			 ->where('tkh_bayar is null',null,false)
			 ->where('AMAUN_BAYAR is null',null,false)
			 ->where("(KP='".$input['IC_NO']."'",null,false)
			 ->or_where("perkara5='".$input['BRN_NO']."')",null,false);
			
		$query = $this->db->get();
		if ($query->num_rows() > 0){
			$sql = $query->result();
			foreach($sql as $key => $row){
				$no_akaun = $row->NO_AKAUN;
				$result['data'][] = $this->checkPay(isset($no_akaun) ? $no_akaun : '');
			}			
		}else{
			$result['message'] = "No Data";
		}
		$this->db->close();	
		return $result;	
	}

	function checkPay($no_akaun){
		$clmn_bil = 'NO_AKAUN, KP, perkara5 as BRN_NO ,TKH_MASUK, AMAUN';
		$clmn_bilPaid = 'KP, perkara5 as BRN_NO';		
		$table_bil = 'HASIL.BIL';		

		$kutip = "SELECT 'x' FROM kutipan.kutipan WHERE NO_AKAUN = '".$no_akaun."' 
				  AND status <> 'B'";
		$bil2 = "select 'x' from hasil.bil2 where  NO_AKAUN = '".$no_akaun."' 
				 and status is null";
		$ebayar = "select 'x' from hasil.ebayar_trxid where no_kompaun = '".$no_akaun."' 
				   and flag = 'SUCCESSFUL' and status_kutipan is null";
		$sql = $kutip." union ".$bil2." union ".$ebayar;
		
		$query = $this->db->query($sql);
		$check = $query->row();
		if($check==null){
			$this->db
			 ->select($clmn_bil." from ".$table_bil,false)
			 ->where("NO_AKAUN = '".$no_akaun."'",null,false);
			$query = $this->db->get();
			$getResult = $query->row();
			return $getResult;
		}/* else{
			$this->db
			 ->select($clmn_bilPaid." from ".$table_bil,false)
			 ->where("NO_AKAUN = '".$no_akaun."'",null,false);
			$query = $this->db->get();
			$row = $query->row();
			$getResult = array("KP"=>$row->KP,"BRN_NO"=>$row->BRN_NO,"message"=>"Paid");
		} */
	}

	public function api_users($input,$token){
		$query = $this->db->query("SELECT *  FROM hasil.api_users WHERE
						  user_name ="."'".$input["user_name"]."'"."AND 
						  company_name = "."'".$input["company_name"]."'".
						  "AND auth = '1'");
		$count_row = $query->num_rows();
		if ($count_row > 0) {		
			 return false;
		} else {
			$this->db
			 ->set('user_name', "'".$input['user_name']."'", FALSE)
			 ->set('company_name', "'".$input['company_name']."'", FALSE)
			 ->set('token', "'".$token."'", FALSE)
			 ->set('auth', '1', FALSE)
			 ->insert('hasil.api_users', null, FALSE);
			return true;
		}
	}
	
	public function check_token($input){
		$this->db->select("*")
        		 ->from('HASIL.API_USERS')
				 ->where("USER_NAME",$input['user_name'])
				 ->where("COMPANY_NAME",$input['company_name']);
		$query = $this->db->get();
		if ($query->num_rows() > 0){
			$row = $query->row();
			if($row->AUTH=='1'){
				$row->AUTH = 'Authorized!';
			}else{
				$row->AUTH = 'Unauthorized!';
			}
			$tokenArr = array('token'=>$row->TOKEN,'auth'=>$row->AUTH);
			return $tokenArr;
		}else{
			return array('mgs'=>'No Token');
		}
	}

	public function auth_token($input){
		$this->db->select("*")
        		 ->from('HASIL.API_USERS')
				 ->where("USER_NAME",$input->user_name)
				 ->where("COMPANY_NAME",$input->company_name)
				 ->where("AUTH",'1');
		$query = $this->db->get();
		if ($query->num_rows() > 0){
			return true;
		}else{
			return false;
		}
	}

	public function saveLogApi($log){
		if($log['request'] != ''){
			$_REQUEST = $log['request'];
		}else{
			$_REQUEST = '';
		}
		$this->db->set('NO_AKAUN', $log['no_akaun'])
				 ->set('URL_API', $log['url_api'])
				 ->set('USER_NAME', $log['decodeToken']->user_name)
				 ->set('COMPANY_NAME', $log['decodeToken']->company_name)
				 ->set('SYSTEM', $log['system'])
				 ->set('REQUEST', $_REQUEST)
				 ->set('RESPONSE', $log['response'])
				 ->insert("KOMPAUN.API_LOG");
	}

	public function inputData($input){
		$mgs = false;

		$this->db->select("*")
        		 ->from('HASIL.BIL')
				 ->where("NO_AKAUN",$input['NO_AKAUN'])
				 ->where("NAMA",$input['NAMA'])
				 ->where("PERKARA1",$input['PERKARA1'])
				 ->where('TKH_MASUK', $input['TKH_MASUK'])
				 ->where("JENIS",$input['JENIS']);
		$query = $this->db->get();

		if ($query->num_rows() > 0){
			$this->db->set('NO_AKAUN', $input['NO_AKAUN'])
				 ->set('NAMA', $input['NAMA'])
				 ->set('ALAMAT1', $input['ALAMAT1'])
				 ->set('ALAMAT2', $input['ALAMAT2'])
				 ->set('ALAMAT3', $input['ALAMAT3'])
				 ->set('PERKARA1', $input['PERKARA1'])
				 ->set('PERKARA2', $input['PERKARA2'])
				 ->set('NO_RUJUKAN', $input['NO_RUJUKAN'])
				 ->set('AMAUN', $input['AMAUN'])
				 ->set('TKH_BAYAR', $input['TKH_BAYAR'])
				 ->set('TKH_MASUK', $input['TKH_MASUK'])
				 ->set('NO_RESIT', $input['NO_RESIT'])
				 ->set('NO_RUJUKAN2', $input['NO_RUJUKAN2'])
				 ->set('JENIS', $input['JENIS'])
				 ->set('PERKARA', $input['PERKARA'])
				 ->set('MASA', $input['MASA'])
				 ->set('NO_PEKERJA', $input['NO_PEKERJA'])
				 ->set('TRED', $input['TRED'])
				 ->set('PERKARA4', $input['PERKARA4'])
				 ->set('PERKARA5', $input['PERKARA5'])
				 ->set('KP', $input['KP'])
				 ->set('TKH_LAHIR', $input['TKH_LAHIR'])
				 ->set('PARLIMEN', $input['PARLIMEN'])
				 ->set('DAERAH', $input['DAERAH'])
				 ->set('DUN', $input['DUN'])
				 ->set('KOD_TERNAKAN', $input['KOD_TERNAKAN'])
				 ->insert("HASIL.BIL");
			$mgs = true;
		}
		return $mgs;
	}
}
