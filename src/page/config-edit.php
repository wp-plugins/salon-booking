<?php 

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Config_Edit extends Salon_Page {
	
	private $table_data = null;
	private $default_mail = '';
	
	public function __construct($is_multi_branch) {
		parent::__construct($is_multi_branch);
	}

	public function check_request() {
		$msg = null;
		Salon_Page::serverCheck(array(),$msg);
		
	}
	
	
	public function set_table_data($table_data) {
		return $this->table_data;
	}

	public function show_page() {
		$this->table_data['SALON_CONFIG_SEND_MAIL_TEXT'] = htmlspecialchars($this->table_data['SALON_CONFIG_SEND_MAIL_TEXT'],ENT_QUOTES);

		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($this->table_data).' }';
	}


}