<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Download_Data extends Salon_Data {
	
	
	public function __construct() {
		parent::__construct();
	}
	
	public function getDownloadData($sql) {
		global $wpdb;
		$result = $wpdb->get_results($sql,ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $result;
		
	}
	
	public function writeCsvFile($file_name,$filedatas ) {
		//単純なカンマ区切りだとExcelで開けない
		//リトルエンディアン(0xFF=255 0xFE=254)をあらわすBom
   		$bom = chr(255) . chr(254);
		$encoded  = $bom . mb_convert_encoding($filedatas, 'UTF-16LE', 'UTF-8');
		$out = @fopen($file_name,'w');
		if ($out === false ) {
			$msg = error_get_last();
			throw new Exception(Salon_Component::getMsg('E904',$msg['message']) );
		}
		if (fputs( $out, $encoded ) === false) {
			throw new Exception(Salon_Component::getMsg('E905'));
		};
		@fclose($out);
		
	}
	
	
	
}