<?php 

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Basic_Edit extends Salon_Page {
	
	private $table_data = null;
	
	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}

	
	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}
	
	
	public function get_reservation_cd () {
		return $this->table_data['reservation_cd'];
	}
	public function get_branch_cd () {
		return $this->table_data['branch_cd'];
	}


	
	public function check_request() {
		if ( empty($_POST['target_branch_cd'] ) ){
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) );
		}
		$checks = array();
		if ($_POST['type'] == 'updated' ) {
			$checks = array('open_time','close_time','time_step','closed_day_check');
		}
		
		if (Salon_Page::serverCheck($checks,$msg) == false) {
			throw new Exception($msg );
		}
	
	}

	public function show_page() {

		$this->table_data['no'] = __($_POST['type'],SL_DOMAIN);
		$this->table_data['check'] = '';
		if ($_POST['type'] != 'updated' ) {
			$this->table_data['target_date'] = htmlspecialchars($_POST['target_date'],ENT_QUOTES);
			if  ($_POST['type']	== 'inserted' ) {
				$title = __('special holiday',SL_DOMAIN);
				if ($_POST['status']==Salon_Status::OPEN) $title = __('on business',SL_DOMAIN);
				$this->table_data['status_title'] = $title;
				$this->table_data['status'] = htmlspecialchars($_POST['status'],ENT_QUOTES);
			}
		}
		
		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($this->table_data).' }';
	}


}