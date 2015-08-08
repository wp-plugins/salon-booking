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

	private $insert_max_day = '';
	private $role = null;

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->branch_cd = $_POST['branch_cd'];
	}

	public function set_role($role) {
		$this->role = $role;
	}
	private function _is_editBooking() {
			if (in_array('edit_booking',$this->role) || $this->isSalonAdmin() ) return true;
	}
	
	public function is_editBooking() {
		return $this->_is_editBooking();
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

		if ($this->_is_editBooking() ) $check_item = array('customer_name','staff_cd','branch_cd','item_cds','time_from','time_to');
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
		//fromは指定分以降より後
		$limit_time = new DateTime(date_i18n('Y-m-d H:i'));
//		$limit_time->add(new DateInterval("PT".$this->config_datas['SALON_CONFIG_RESERVE_DEADLINE']."M"));
		$limit_time->modify("+".$this->config_datas['SALON_CONFIG_RESERVE_DEADLINE']." min");
//		if ($limit_time->getTimestamp() > $from) {
		if (+$limit_time->format('U') > $from) {
		  $this->checkOk = false;
		  $this->msg .=  (empty($this->msg) ? '' : "\n"). 'EM004 '.sprintf(__('Your reservation is possible from %s.',SL_DOMAIN),$limit_time->format(__('m/d/Y',SL_DOMAIN).' H:i'));
		}


		//fromは今より後
//		$dt = new DateTime();
//		$current_time = $dt->format('Y-m-d H:i');
//		if (strtotime($current_time) > $from) {
//		  $this->checkOk = false;
//		  $this->msg .=  (empty($this->msg) ? '' : "\n"). 'EM001 '.__('The past times can not reserve',SL_DOMAIN);
//		}

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
		if ($_POST['type'] != 'inserted' ) {
			$this->reservation_cd = intval($_POST['id']);
		} 
	}

	public function show_page() {
		if ($this->checkOk ) {	
			$first_hour = substr($this->branch_datas['open_time'],0,2);
			$last_hour = substr($this->branch_datas['close_time'],0,2);
	
			$res = parent::echoMobileData($this->reservation_datas,$this->target_day ,$first_hour,$last_hour,$this->user_login);
			if (is_user_logged_in()	) {
				$msg = __('The reservation is compledted',SL_DOMAIN);
			}
			else {
				if ($this->config_datas['SALON_CONFIG_CONFIRM_STYLE'] == Salon_Config::CONFIRM_BY_MAIL ) {
					$msg = __('The reservation is not completed.Please confirm your reservation by [confirm form] in E-mail ',SL_DOMAIN);
				}
				else if ($this->config_datas['SALON_CONFIG_CONFIRM_STYLE'] == Salon_Config::CONFIRM_BY_ADMIN ) {
					$msg = __('The reservation is not completed.After your reservation confirmed by administrator ',SL_DOMAIN);
				}
				else if ($this->config_datas['SALON_CONFIG_CONFIRM_STYLE'] == Salon_Config::CONFIRM_NO ) {
					$msg = __('The reservation is compledted',SL_DOMAIN);
				}
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