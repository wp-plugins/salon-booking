<?php

class Branch_Component {
	
	private $version = '1.0';
	
	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}
	
	
	
	public function editTableData () {
		
		if ( $_POST['type'] == 'deleted' ) {
			$set_data['branch_cd'] = intval($_POST['branch_cd']);
		}
		else {
			if ($_POST['type'] == 'updated' ) 	$set_data['branch_cd'] = intval($_POST['branch_cd']);

			$set_data['name'] = stripslashes($_POST['name']);
//			$set_data['zip'] = str_replace('-','',$_POST['zip']);
//			$set_data['zip'] = substr($set_data['zip'],0,3).'-'.substr($set_data['zip'],3);
			$set_data['zip'] = $_POST['zip'];
			$set_data['address'] = stripslashes($_POST['address']);
			$set_data['tel'] = $_POST['tel'];
			$set_data['mail'] = $_POST['mail'];
			$set_data['remark'] = stripslashes($_POST['remark']);
			$set_data['duplicate_cnt'] = intval($_POST['duplicate_cnt']);
			$set_data['memo'] = '';
			$set_data['notes'] = '';
			$set_data['open_time'] = Salon_Component::replaceTimeToDb($_POST['open_time']);
			$set_data['close_time'] = Salon_Component::replaceTimeToDb($_POST['close_time']);
			$set_data['time_step'] = $_POST['time_step'];
			$set_data['closed'] = $_POST['closed'];
	
		}
		return $set_data;
		
	}
	

	public function editColumnData() {
		$column = array();
		$column[2]="name = %s ";
		$column[3]="remark = %s ";
		
		
		$set_data['column_name'] = $column[intval($_POST['column'])];
		$set_data['value'] = stripslashes($_POST['value']);
		$set_data['branch_cd'] = intval($_POST['branch_cd']);
		return $set_data;
	}
	
	public function editInitDatas() {
		$result = $this->datas->getInitdatas();
		foreach ($result as $k1 => $d1 ) {
			$result[$k1]['shortcode'] = '[salon-booking branch_cd='.$d1['branch_cd'].']';
			$result[$k1]['open_time'] = Salon_Component::formatTime($d1['open_time']);
			$result[$k1]['close_time'] = Salon_Component::formatTime($d1['close_time']);
		}
		return $result;
	}
	
	
}