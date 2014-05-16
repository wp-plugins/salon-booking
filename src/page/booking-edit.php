<?php 

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Booking_Edit extends Salon_Page {
	
	
	protected $table_data = null;
	private $reservation_cd = '';

	public function __construct($is_multi_branch) {
		parent::__construct($is_multi_branch);
	}

	
	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}
	
	public function get_table_data() {
		return $this->table_data;
	}
	

	public function set_reservation_cd($reservation_cd) {
		$this->reservation_cd = $reservation_cd;
		$this->table_data['reservation_cd'] = $reservation_cd;
	}

	
	public function check_request() {
		$this->_parse_data();

		$msg = null;
		if ($this->isSalonAdmin() ) $check_item = array('customer_name','branch_cd','time_from','time_to');
		else  $check_item = array('customer_name','branch_cd','tel','item_cds','mail','time_from','time_to');
		if (parent::serverCheck($check_item,$msg) == false) {
			throw new Exception($msg );
		}
	}
	
	private function _parse_data() {
		$_POST['type'] = $_POST['!nativeeditor_status'];
		$_POST['reservation_cd'] = intval($_POST['id']);
		$this->reservation_cd = intval($_POST['id']);
//		if (empty($_POST['item_cd']) ) $_POST['item_cds'] = '';
//		else $_POST['item_cds'] = $_POST['item_cd'];
		
	}

	public function show_page() {
		//ログインしていないときは、更新はできないようにする
		$edit_flg = Salon_Edit::NG;
		$type = htmlspecialchars($_POST['type']);
		$ID = floatval($_POST['id']);
		$status = '';
		if (is_user_logged_in()	) {
			$edit_flg = Salon_Edit::OK;
			$tid = $this->reservation_cd;
			$msg = __('reservation is compledted',SL_DOMAIN);
			$edit_name = '';
			$p2  = '';
			if ($_POST['type'] != 'deleted' ) {
				$p2 = $this->table_data['non_regist_activate_key'];
				$edit_name = htmlspecialchars($this->table_data['non_regist_name'],ENT_QUOTES);
				$status = $this->table_data['status'];
			}
		}
		else {
			$edit_flg = Salon_Edit::NG;
			$p2 = '';
			$tid = $ID;
//			$edit_name = __('tempolary reserved',SL_DOMAIN).'('.htmlspecialchars($this->table_data['non_regist_name'],ENT_QUOTES).')';
			$edit_name = htmlspecialchars($this->table_data['non_regist_name'],ENT_QUOTES);
			$msg = __('reservation is not compledted.Please confirm your reservation by [confirm form] in E-mail ',SL_DOMAIN);
			$status = Salon_Reservation_Status::TEMPORARY;
		}
		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="UTF-8" ?>';
		echo <<<EOT
		<data>
			<action type="{$type}" 
					sid="{$ID}" 
					tid="{$tid}" 
					name="{$edit_name}" 
					status="{$status}" 
					p2 = "{$p2}"
					edit_flg="{$edit_flg}" 
					alert_msg = "{$msg}" >
			</action>
		</data>
EOT;
	}


}