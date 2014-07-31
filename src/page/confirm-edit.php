<?php 

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Confirm_Edit extends Salon_Page {
	
	private $table_data = null;
	
	private $reservation_cd = '';
	private $activation_key = '';

	private $datas = null;
	
	private $error_msg = '';
	



	public function __construct() {
		parent::__construct(false);
		$this->reservation_cd = intval($_POST['target']);
		$this->activation_key = $_POST['P2'];
	}

	
	public function get_reservation_cd () {
		return $this->reservation_cd;
	}
	public function set_reservation_datas ( $datas ) {
		$this->datas = $datas;
	}



	
	public function check_request() {
		if (wp_verify_nonce($_POST['nonce'],session_id()) === false) {
			throw new Exception(Salon_Component::getMsg('E005',__LINE__ ) );
		}
		if ( empty($_POST['target']) || ( $_POST['type'] !== 'exec' && $_POST['type'] !== 'cancel' ) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) );
		}
		if ( count($this->datas) == 0  ||  $this->datas['non_regist_activate_key'] !== $this->activation_key ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) );
		}
		$now =  date_i18n("YmdHi");
		if ($this->datas['check_day'] < $now )  {
			throw new Exception(Salon_Component::getMsg('E011',$this->datas['target_day'].' '.$this->datas['time_from']));
		}
		
	}



	public function show_page() {
		$status = array();
		if ($_POST['type'] == 'exec' ) $result = array('status_name'=>__('reservation completed',SL_DOMAIN));
		else $result = array('status_name'=>__('reservation deleted',SL_DOMAIN));
		
		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($result).' }';
	}


}