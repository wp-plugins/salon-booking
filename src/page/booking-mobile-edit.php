<?php 

	require_once(SL_PLUGIN_SRC_DIR . 'page/booking-edit.php');

	
class Booking_Mobile_Edit extends Booking_Edit {
	
	
	private $branch_cd = '';
	private $branch_datas = null;

	private $reservation_datas = null;
	private $target_day = '';
	private $user_login = '';


	private $msg = '';
	private $checkOk = false;

	private $config_datas = null;
	private $insert_max_day = '';

	public function __construct($is_multi_branch) {
		parent::__construct($is_multi_branch);
		$this->branch_cd = $_POST['branch_cd'];
	}

	

	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
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

	public function set_user_login($user_login) {
		$this->user_login = $user_login;
	}
	
	public function set_config_datas($config_datas) {
		$this->config_datas = $config_datas;
		$this->insert_max_day = Salon_Component::computeDate($config_datas['SALON_CONFIG_AFTER_DAY']);

	}
	
	
	public function check_request() {
		$this->_parse_data();

		if ($this->isSalonAdmin() ) $check_item = array('customer_name','staff_cd','branch_cd','item_cds','time_from','time_to');
		else  $check_item = array('customer_name','staff_cd','branch_cd','mobile_tel','mail','item_cds','time_from','time_to');

		$this->checkOk = parent::serverCheck($check_item,$this->msg);
		//ここからスマートフォンのみのチェック
		//from toの大小(不正かバグしかない）
		$from = strtotime($_POST['start_date']);
		$to = strtotime($_POST['end_date']);
		if ($from >= $to) {
		  $this->checkOk = false;
		  $this->msg  .=  (empty($this->msg) ? '' : "\n"). 'EM003 '.__('Check reserved time ',SL_DOMAIN);
		}		
		//fromは今より後
		$dt = new DateTime();
		$current_time = $dt->format('Y-m-d H:i');
		if (strtotime($current_time) > $from) {
		  $this->checkOk = false;
		  $this->msg .=  (empty($this->msg) ? '' : "\n"). 'EM001 '.__('The past times can not reserve',SL_DOMAIN);
		}
		//未来も制限がある
		if (strtotime($this->insert_max_day) < $from) {
		  $this->checkOk = false;
		  $this->msg .=  (empty($this->msg) ? '' : "\n").  'EM002 '.sprintf(__('The future times can not reserved. please less than %s days ',SL_DOMAIN),$this->config_datas['SALON_CONFIG_AFTER_DAY']);
		}
		
		return $this->checkOk;		
		
	}
	
	private function _parse_data() {
		$_POST['status'] = '';
		//YYYY-MM-DD HH:MM 最後に読み直すために
		$split = explode(' ',$_POST['start_date']);
		$this->target_day = str_replace('-','',$split[0]); 
	}

	public function show_page() {
		if ($this->checkOk ) {	
			$first_hour = substr($this->branch_datas['open_time'],0,2);
	
			$res = parent::echoMobileData($this->reservation_datas,$this->target_day ,$first_hour,$this->user_login);
			if (is_user_logged_in()	) {
				$msg = __('reservation is compledted',SL_DOMAIN);
			}
			else {
				$msg = __('reservation is not compledted.Please confirm your reservation by [confirm form] in E-mail ',SL_DOMAIN);
			}
			echo '{	"status":"Ok","message":"'.$msg.'",
			"set_data":'.'{"'.$this->target_day.'":'.$res[$this->target_day].'} }';
		}
		else {
			$msg['status'] = 'Error';
			if (empty($errno) ) $msg['message'] = $this->msg;
			else $msg['message'] = $errstr.$detail_msg.'('.$errno.')';
			echo json_encode($msg);
		}
	}


}