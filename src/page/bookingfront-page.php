<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class BookingFront_Page extends Salon_Page {
	
	const Y_PIX = 550;

	private $branch_datas = null;
	private $item_datas = null;
	private $staff_datas = null;
	private $working_datas = null;
	
	private $first_hour = '';
	private $last_hour = '';
	private $insert_max_day = '';

	private $reseration_cd = '';
	
	private $config_datas = null;
	private $target_year = '';
	
		
	private $role = null;

	private $url = '';

	private $reservation_datas = null;

	private $user_inf = null;	
	
	private $promotion_datas = null; 
	
	private $current_time = '';
	private $close_24 = '';

	public function __construct($is_multi_branch) {
		parent::__construct($is_multi_branch,session_id());
		$this->target_year = date_i18n("Y");
		$url = get_bloginfo('wpurl');
		if (is_ssl() && strpos(strtolower ( $url),'https') === false ) {
			$url = preg_replace("/[hH][tT][tT][pP]:/","https:",$url);
		}
		$this->url = $url;
		$this->current_time = date_i18n("Hi");
	}
	
	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
		$this->first_hour = substr($this->branch_datas['open_time'],0,2);
		$this->last_hour = substr($this->branch_datas['close_time'],0,2);
		if (intval(substr($this->branch_datas['close_time'],2,2)) > 0 ) $this->last_hour++;
		$this->close_24 = $this->branch_datas['close_time'];
		if ($this->last_hour > 23 ) {
			
			$this->close_24 = sprintf("%02d",+$this->last_hour-24).substr($this->branch_datas['close_time'],2,2);
			
		}
	}


	public function get_targetDate_for_mobile () {
		$init_target_day = date_i18n('Ymd');
		if ( $this->last_hour > 23  && $this->branch_datas["open_time"] > $this->current_time && $this->close_24 >= $this->current_time)  {
			$init_target_day = date('Ymd',strtotime(date_i18n('Y-m-d')." -1 day"));
		}
		return $init_target_day;
	}

	public function set_item_datas ($item_datas) {
		$this->item_datas = $item_datas;
	}

	public function set_staff_datas ($staff_datas) {
		$this->staff_datas = $staff_datas;
		if (count($this->staff_datas) === 0 ) {
			throw new Exception(Salon_Component::getMsg('E010',__function__.':'.__LINE__ ) );
		}
	}

	
	public function set_working_datas ($working_datas) {
		$this->working_datas = $working_datas;
	}


	public function set_promotion_datas ($promotion_datas) {
		$this->promotion_datas = $promotion_datas;

	}

	
	public function set_role($role) {
		$this->role = $role;
	}
	
	public function set_reservation_datas($reservation_datas) {
		$this->reservation_datas = $reservation_datas;
		
	}

	public function set_user_inf($user_inf) {
		$this->user_inf = $user_inf;
	}

	private function _is_userlogin() {
		return $this->config_datas['SALON_CONFIG_USER_LOGIN'] == Salon_Config::USER_LOGIN_OK ;
	}
	
	private function _is_noPreference() {
		return $this->config_datas['SALON_CONFIG_NO_PREFERENCE'] == Salon_Config::NO_PREFERNCE_OK;
	}

	private function _is_staffSetNormal() {
		return $this->config_datas['SALON_CONFIG_STAFF_HOLIDAY_SET'] == Salon_Config::SET_STAFF_NORMAL;
	}
	
	private function _is_editBooking() {
			if (in_array('edit_booking',$this->role) || $this->isSalonAdmin() ) return true;
	}
	
	
	
	public function set_config_datas($config_datas) {
		$this->config_datas = $config_datas;
		$edit = Salon_Component::computeDate($config_datas['SALON_CONFIG_AFTER_DAY']);
		$this->insert_max_day = substr($edit,0,4).','.(intval(substr($edit,5,2))-1).','.(intval(substr($edit,8,2))+1);

	}


	public function show_page() {
		if ( Salon_Component::isMobile() ) {
			require_once(SL_PLUGIN_SRC_DIR . '/page/booking_mobile-page.php');
		}

		else {
			require_once(SL_PLUGIN_SRC_DIR . '/page/booking_pc-page.php');
		}
	}
	private function _editDate($yyyymmdd) {
		return substr($yyyymmdd,0,4). substr($yyyymmdd,5,2).  substr($yyyymmdd,8,2);
	}
	private function _editTime($yyyymmdd) {
		return substr($yyyymmdd,11,2). substr($yyyymmdd,14,2);
	}
	
	private function _echoLoadTab() {
		if (empty($this->config_datas['SALON_CONFIG_LOAD_TAB'])) echo "timeline";
		else {
			$setData = array("timeline","timeline","month","week","day");
			echo $setData[$this->config_datas['SALON_CONFIG_LOAD_TAB']];
		}
	}
	
}		//class

