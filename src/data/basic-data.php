<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Basic_Data extends Salon_Data {
	
	const TABLE_NAME = 'salon_branch';	
	
	function __construct() {
		parent::__construct();
	}

	public function getAllSpDateData($target_year = null,$target_branch_cd = null){
		$datas = $this->getBranchData($target_branch_cd,'sp_dates');
		$result = array();
		if ($datas) {
			$sp_dates = unserialize($datas['sp_dates']);
			if ($sp_dates && !empty($sp_dates[$target_year])) {
				foreach ($sp_dates[$target_year] as $k1 => $d1) {
					$title = __('special holiday',SL_DOMAIN);
					if ($d1==Salon_Status::OPEN) $title = __('on business',SL_DOMAIN);
					$target_date = __('%%m/%%d/%%Y',SL_DOMAIN);
					$target_date = str_replace('%%Y',substr($k1,0,4),$target_date);
					$target_date = str_replace('%%m',substr($k1,4,2),$target_date);
					$target_date = str_replace('%%d',substr($k1,6,2),$target_date);
					$result[] = array("target_date"=>$target_date,"status_title"=>$title,"status"=>$d1);
				}
			}
		}
		return ($result);
	}
	

	public function updateTable ($table_data){
		$set_string = 	' open_time = %s , '.
						' close_time = %s , '.
						' time_step = %d , '.
						' closed = %s , '.
						' duplicate_cnt = %d , '.
						' memo = %s , '.
						' update_time = %s ';
												
		$set_data_temp = array(
						$table_data['open_time'],
						$table_data['close_time'],
						$table_data['time_step'],
						$table_data['closed'],
						$table_data['duplicate_cnt'],
						$table_data['memo'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['branch_cd']);
		$where_string = ' branch_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}

	
	public function updateSpDate ($table_data){
		$set_string = 	' sp_dates = %s , '.
						' update_time = %s ';
												
		$set_data_temp = array(
						serialize($table_data['sp_dates']),
						date_i18n('Y-m-d H:i:s'),
						$table_data['branch_cd']);
		$where_string = ' branch_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	

	

	
	
}