<?php

class Staff_Component {
	
	private $version = '1.0';
	
	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}
	
	private function _is_TargetUser($role,$target_role) {
		if ( array_key_exists('subscriber',$role) ) return false;
		switch($target_role) {
			case 'administrator':
				break;
			case 'editor':
				if (array_key_exists('administrator',$role) ) return false;
				break;
			case 'author':
				if (array_key_exists('administrator',$role) ) return false;
				if (array_key_exists('editor',$role) ) return false;
				break;
			case 'contributor':
				if (array_key_exists('administrator',$role) ) return false;
				if (array_key_exists('editor',$role) ) return false;
				if (array_key_exists('author',$role) ) return false;
				break;
			default:
				return false;
		}
		return true;
	}
	
	public function editInitData($result) {
		
		$edit = array();
		$index = 0;
		$save_key = $result[0]['ID'];
		//ログインID単位の配列に変換する。
		for ($i =0; $i < count($result) ; $i++ ) {
			if ($save_key <> $result[$i]['ID'] ) {
				$index++;
			}
			$edit[$index][$result[$i]['meta_key']] = $result[$i]['meta_value'];
			//[TODO]ちと冗長？
			$edit[$index]['ID'] = $result[$i]['ID'];
			$edit[$index]['staff_cd'] = $result[$i]['staff_cd'];
			$edit[$index]['user_login'] = $result[$i]['user_login'];
			$edit[$index]['mail'] = $result[$i]['user_email'];
			$edit[$index]['branch_cd'] = $result[$i]['branch_cd'];
			$edit[$index]['position_cd'] = $result[$i]['position_cd'];
			$edit[$index]['remark'] = $result[$i]['remark'];
			$edit[$index]['memo'] = $result[$i]['memo'];
			$edit[$index]['notes'] = $result[$i]['notes'];
			$edit[$index]['photo'] = $result[$i]['photo'];
			$edit[$index]['duplicate_cnt'] = $result[$i]['duplicate_cnt'];
			$edit[$index]['employed_day'] = $result[$i]['employed_day'];
			$edit[$index]['leaved_day'] = $result[$i]['leaved_day'];
			$save_key = $result[$i]['ID'];
		}
		//不要な項目が多いので編集する
		$result_after = array();
		$index = 0;
		$current_user = wp_get_current_user();
		global $wpdb;
		foreach ( $edit as $k1 => $d1 ) {
			$role = unserialize($d1[$wpdb->prefix.'capabilities']) ;
			//顧客は「購読者」のみで、スタッフは「購読者」以外
//			if ( ! array_key_exists('subscriber',$role) ){
			if ( $this->_is_TargetUser($role,$current_user->roles[0]) ){
				$result_after[$index]['ID'] = $d1['ID'];
				$result_after[$index]['staff_cd'] = $d1['staff_cd'];
				$result_after[$index]['user_login'] = $d1['user_login'];
				$result_after[$index]['mail'] = $d1['mail'];
				$result_after[$index]['branch_cd'] = $d1['branch_cd'];
				$result_after[$index]['remark'] = $d1['remark'];
				$result_after[$index]['memo'] = $d1['memo'];
				$result_after[$index]['notes'] = $d1['notes'];
				$result_after[$index]['first_name'] = $d1['first_name'];
				$result_after[$index]['last_name'] = $d1['last_name'];
				$result_after[$index]['zip'] = $d1['zip'];
				$result_after[$index]['address'] = $d1['address'];
				$result_after[$index]['tel'] = $d1['tel'];
				$result_after[$index]['mobile'] = $d1['mobile'];
				$result_after[$index]['position_cd'] = $d1['position_cd'];
				$result_after[$index]['photo'] = $d1['photo'];
				$result_after[$index]['duplicate_cnt'] = $d1['duplicate_cnt'];
				$result_after[$index]['employed_day'] = $d1['employed_day'];
				$result_after[$index]['leaved_day'] = $d1['leaved_day'];
				$index++;
			}
		}
		//地位
		$position_datas = $this->datas->getAllPositionData();
		$position_datas_after = array();
		foreach ($position_datas as $k1 => $d1 ) {
			$position_datas_after[$d1['position_cd']]= $d1['name'];
		}
		//氏名を編集したり、もろもろ
		foreach ( $result_after as $k1 => $d1) {
			if (empty($d1['first_name'] ) ) $result_after[$k1]['first_name'] = __('first name',SL_DOMAIN).__('not registered',SL_DOMAIN);
			if (empty($d1['last_name'] ) ) $result_after[$k1]['last_name'] = __('last name',SL_DOMAIN).__('not registered',SL_DOMAIN);
			if (empty($d1['zip'] ) ) $result_after[$k1]['zip'] = __('zip',SL_DOMAIN).__('not registered',SL_DOMAIN);
			if (empty($d1['address'] ) ) $result_after[$k1]['address'] = __('address',SL_DOMAIN).__('not registered',SL_DOMAIN);
//			if (empty($d1['tel'] ) ) $result_after[$k1]['tel'] = __('tel',SL_DOMAIN).__('not registered',SL_DOMAIN);
//			if (empty($d1['mobile'] ) ) $result_after[$k1]['mobile'] = __('mobile',SL_DOMAIN).__('not registered',SL_DOMAIN);
			if (empty($d1['branch_cd'] ) ) {
				$result_after[$k1]['branch_name'] = __('not registered',SL_DOMAIN);
			}
			else {
				$datas = $this->datas->getBranchData($d1['branch_cd']);
				$result_after[$k1]['branch_name'] = $datas['name'];
			}
			$tmp = str_replace("\'","'",$d1['photo']);
			if (!empty($_SERVER['HTTPS']) ) {
				$url = site_url();
				$url = substr($url,strpos($url,':')+1);
				$tmp = preg_replace("$([hH][tT][tT][pP]:".$url.")$","https:".$url,$tmp);
			}
			$result_after[$k1]['photo'] = $tmp;			
			$result_after[$k1]['position_name'] = '';
			if ($d1['position_cd']) $result_after[$k1]['position_name'] = $position_datas_after[ $d1['position_cd']];
		}
		return $result_after;
		
	}
	
	
	public function editUserData() {
		$set_data['ID'] = $_POST['ID'];
		$set_data['user_login'] = $_POST['user_login'];
		$set_data['mail'] = $_POST['mail'];
		$set_data['zip'] = $_POST['zip'];
		$set_data['address'] = stripslashes($_POST['address']);
		$set_data['tel'] = $_POST['tel'];
		$set_data['mobile'] = $_POST['mobile'];
		$set_data['first_name'] = stripslashes($_POST['first_name']);
		$set_data['last_name'] = stripslashes($_POST['last_name']);
		$set_data['position_cd'] = $_POST['position_cd'];
		return $set_data;
	}

	public function editTableData () {
		
		if ( $_POST['type'] == 'deleted' ) {
			$set_data['staff_cd'] = intval($_POST['staff_cd']);
		}
		else {
			if ($_POST['type'] == 'updated' ) 	$set_data['staff_cd'] = intval($_POST['staff_cd']);
			$set_data['branch_cd'] = intval($_POST['branch_cd']);
			$set_data['position_cd'] = intval($_POST['position_cd']);
			$set_data['remark'] = stripslashes($_POST['remark']);
			$set_data['memo'] = '';
			$set_data['notes'] = '';
			$set_data['duplicate_cnt'] = intval($_POST['duplicate_cnt']);
			//
			$tmp = stripslashes($_POST['photo']);
			if ( strpos($tmp, 'class=\'lightbox\'') === false)	{
				$set_data['photo'] = preg_replace('/^<a(.*?)>(.*)$/','<a ${1} class=\'lightbox\' >${2}',$tmp);
			}
			else {
				$set_data['photo'] = $tmp;
			}
			$set_data['user_login'] = $_POST['user_login'];
			$set_data['employed_day'] = '';
			if (!empty($_POST['employed_day'])) $set_data['employed_day'] = Salon_Component::editRequestYmdForDb($_POST['employed_day']);
			$set_data['leaved_day'] = '';
			if (!empty($_POST['leaved_day'])) $set_data['leaved_day'] = Salon_Component::editRequestYmdForDb($_POST['leaved_day']);
		}
		return $set_data;
		
	}
	
	public function editColunDataForWpUser() {
		
		switch (intval($_POST['column'])) {
			case 2:
				if ($this->datas->getConfigData('SALON_CONFIG_NAME_ORDER') == Salon_Config::NAME_ORDER_JAPAN )	$meta = 'last_name';
				else $meta = 'first_name';
				break;
			case 3:
				if ($this->datas->getConfigData('SALON_CONFIG_NAME_ORDER') == Salon_Config::NAME_ORDER_JAPAN )	$meta = 'first_name';
				else $meta = 'last_name';
				break;
		}
		return array('ID'=>intval($_POST['ID']),'meta'=>$meta,'value'=>stripslashes($_POST['value']));
		
	}

	public function editColumnData() {
		$column = array();
		$column[4]="branch_cd = %d ";
		$column[5]="position_cd = %d ";
		$column[6]="remark = %s ";
		
		
		$set_data['column_name'] = $column[intval($_POST['column'])];
		$set_data['value'] = stripslashes($_POST['value']);
		$set_data['staff_cd'] = intval($_POST['staff_cd']);
		$set_data['is_need_update_role'] = false;
		if ($_POST['column'] == 5 ) $set_data['is_need_update_role']=true;
		$set_data['ID'] = intval($_POST['ID']);		
		return $set_data;
	}
	
	
}