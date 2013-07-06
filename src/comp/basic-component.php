<?php

class Basic_Component {
	
	private $version = '1.0';
	
	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}
	
	
	public function editTableData () {

		if ( $_POST['type'] == 'updated' ) {
			$set_data['open_time'] = Salon_Component::replaceTimeToDb($_POST['open_time']);
			$set_data['close_time'] = Salon_Component::replaceTimeToDb($_POST['close_time']);
			$set_data['time_step'] = intval($_POST['time_step']);
			$set_data['closed'] = $_POST['salon_closed'];
			$set_data['branch_cd'] = intval($_POST['target_branch_cd']);
			$set_data['duplicate_cnt'] = intval($_POST['duplicate_cnt']);
		}
		else {
			$set_data['branch_cd'] = intval($_POST['target_branch_cd']);
			$target_date = str_replace('/','',$_POST['target_date']);
			$datas = $this->datas->getBranchData($set_data['branch_cd'],'sp_dates');
			$sp_dates = unserialize($datas['sp_dates']);
			if ($_POST['type']	== 'inserted' ) {
				$sp_dates[substr($target_date,0,4)][$target_date] = intval($_POST['status']);
			}
			elseif ($_POST['type']	== 'deleted' ) {
				unset($sp_dates[substr($target_date,0,4)][$target_date]);
			}
			$set_data['sp_dates'] = $sp_dates;
		}
		return $set_data;
		
	}
	
	
	
}