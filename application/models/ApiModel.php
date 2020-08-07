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
		}/* else{
			$this->db
			 ->select($clmn_bilPaid." from ".$table_bil,false)
			 ->where("NO_AKAUN = '".$no_akaun."'",null,false);
			$query = $this->db->get();
			$row = $query->row();
			$getResult = array("KP"=>$row->KP,"BRN_NO"=>$row->BRN_NO,"message"=>"Paid");
		} */
		return $getResult;
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
}
