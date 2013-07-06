<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Booking_Get_Event extends Salon_Page {
	
	private $target_day = '';
	private $reservation_datas = null;
	private $item_datas =  null;
	private $branch_cd = '';
	
	private $user_login = '';
	
	public function __construct($is_multi_branch) {
		parent::__construct($is_multi_branch);
		$this->target_day = Salon_Component::computeDate(-500);	//[debug]
		$this->branch_cd = $_GET['branch_cd'];

	}
	
	public function get_target_day() {
		return $this->target_day;
	}
	
	public function get_branch_cd() {
		return $this->branch_cd;
	}
	
	
	public function set_reservation_datas($reservation_datas) {
		$this->reservation_datas = $reservation_datas;
		
	}

	public function set_item_datas($item_datas) {
		$this->item_datas = $item_datas;
		
	}
	
	public function set_user_login($user_login) {
		$this->user_login = $user_login;
	}


	public function show_page() {
		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8" ?>';
		$OK = Salon_Edit::OK;
		$NG = Salon_Edit::NG;
		$randam_num = mt_rand(1000000,9999999);
		echo '<data>';
		
		foreach ($this->reservation_datas as $k1 => $d1 ) {
			if (( ! empty($this->user_login) &&  $this->user_login === $d1['user_login'] ) || 	$this->isSalonAdmin() ) {
				$name = htmlspecialchars($d1['name'],ENT_QUOTES);
				$edit_name = sprintf(__("%s reserved",SL_DOMAIN),$name);
				$edit_remark = htmlspecialchars($d1['remark'],ENT_QUOTES);
				echo <<<EOT2
					<event id ="{$d1['reservation_cd']}" 
				 	staff_cd =  "{$d1['staff_cd']}"
				 	start_date= "{$d1['time_from']}"
				 	end_date  = "{$d1['time_to']}"
					edit_flg  = "{$OK}"
					name = "{$name}"
					mail = "{$d1['email']}"
					tel = "{$d1['tel']}"
					text = "{$edit_name}"
					remark = "{$edit_remark}"
					item_cds = "{$d1['item_cds']}"
					p2 = "{$d1['non_regist_activate_key']}"
					user_login = "{$d1['user_login']}"
EOT2;
			}
			else {
				if ($d1['status'] == Salon_Reservation_Status::COMPLETE ) 	$edit_name = __('reserved',SL_DOMAIN);
				else $edit_name = __('tempolary reserved',SL_DOMAIN);
				$temp_num = $d1['reservation_cd']+$randam_num;
				echo <<<EOT3
					<event id ="{$temp_num}" 
				 	staff_cd =  "{$d1['staff_cd']}"
				 	start_date= "{$d1['time_from']}"
				 	end_date  = "{$d1['time_to']}"
					edit_flg      = "{$NG}"
					name = "{$edit_name}"
					mail = ""
					tel = ""
					text = "{$edit_name}"
					remark = ""
					item_cds = ""
					p2 = ""
					user_login = ""
EOT3;
			}
			echo ' status = "'.$d1['status'].'" '.'/>';
		}

		echo '</data>';
//
//		echo '<coll_options for="item_cd">';
//		if ($this->item_datas) {
//			foreach ($this->item_datas  as $k1 => $d1) {
//				echo '<item value="'.$d1['item_cd'].'"  label="'.htmlspecialchar($d1['name'],ENT_QUOTES).'('.number_format($d1['price']).')'.'" ></item>';
//			}
//		}
//		echo '</coll_options></data>';
///*	jsonでは下記になるが、dataprocessorはxmlのみなのでxmlのままとする。		
//		$jdata = array();
//		
//		foreach ($this->reservation_datas as $k1 => $d1) {
//			$data = array();
//			$data['event_id'] = $d1['reservation_cd'];
//			$data['start_date'] = $d1['time_from'];
//			$data['end_date'] = $d1['time_to'];
//			if (( ! empty($this->user_login) &&  $this->user_login === $d1['user_login'] ) || 	$this->isSalonAdmin() ) {
//				$data['edit_flg'] = Salon_Edit::OK;
//				$data['name'] = sprintf(__("%s reserved",SL_DOMAIN),htmlspecialchars($d1['name'],ENT_QUOTES));
//				$data['mail'] = $d1['email'];
//				$data['tel'] =  $d1['tel'];
//				$data['text'] = $data['name'];
//				$data['remark'] = htmlspecialchars($d1['remark'],ENT_QUOTES);
//				$data['item_cds'] = $d1['item_cds'];
//			}
//			else {
//				$data['edit_flg'] = Salon_Edit::NG;
//				if ($d1['status'] == Salon_Reservation_Status::COMPLETE ) $edit_name = __('reserved',SL_DOMAIN);
//				else $edit_name = __('tempolary reserved',SL_DOMAIN);
//				$data['name'] = $edit_name;
//				$data['mail'] = "";
//				$data['tel'] =  "";
//				$data['text'] = $edit_name;
//				$data['remark'] = "";
//				$data['item_cds'] = "";
//			}
//			$jdata[] = $data;
//		}
//
//		echo json_encode($jdata);
//*/		
		

	}
}