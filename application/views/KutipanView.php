<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$myObj = new \stdClass();
$jsonMain = new \stdClass();
if(empty($records)){
	$myObj->record = "No";
	$jsonMain->kutipan = $myObj;
}else{
	foreach ($records as $row){
	$myArr[] = array('mesin' => $row->MESIN, 'stesyen' => $row->STESYEN, 
		'kategori' => $row->KATEGORI, 'no_akaun' => $row->NO_AKAUN,
		'kod_akaun' => $row->KOD_AKAUN, 'amaun_bil' => $row->AMAUN_BIL,
		'no_rujukan' => $row->NO_RUJUKAN, 'tarikh' => $row->TARIKH,
		'no_pekerja' => $row->NO_PEKERJA, 'multi_payment' => $row->MULTI_PAYMENT,
		'multi_vot' => $row->MULTI_VOT, 'amaun_diterima' => $row->AMAUN_DITERIMA,
		'amaun_lebihan' => $row->AMAUN_LEBIHAN, 'no_bil' => $row->NO_BIL, 
		'tahun_bil' => $row->TAHUN_BIL,	'no_resit' => $row->NO_RESIT, 
		'jenis_bayar' => $row->JENIS_BAYAR, 'kod_bank' => $row->KOD_BANK, 
		'ruj_bayaran' => $row->RUJ_BAYARAN, 'lebih_bayar' => $row->LEBIH_BAYAR,
		'status' => $row->STATUS, 'cetak' => $row->CETAK, 'post' => $row->POST,
		'id_bil' => $row->ID_BIL, 'bank_slip' => $row->BANK_SLIP, 'id_bil_lama' 
		=> $row->ID_BIL_LAMA);
	}
	$myObj->record = "Yes";
	$myObj->detail = $myArr;
	$jsonMain->kutipan = $myObj;
}
header('Content-Type: application/json');
$output = json_encode($jsonMain,JSON_PRETTY_PRINT);
echo $output;