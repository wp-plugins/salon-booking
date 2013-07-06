<?php 

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Position_Edit extends Salon_Page {
	
	private $table_data = null;

	
	public function __construct() {
		parent::__construct(false);
	}

	
	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}
	

	public function set_position_cd($position_cd) {
		 $this->table_data['position_cd'] = $position_cd;
	}

	
	public function check_request() {
		if (defined ( 'SALON_DEMO' ) && SALON_DEMO  ) {
			throw new Exception(Salon_Component::getMsg('I001',null) ,1);
		}
		if	( ($_POST['type'] != 'inserted' ) && empty($_POST['position_cd']) ) {
			throw new Exception(Salon_Component::getMsg('E901',null) );
		}
		if	(  ($_POST['type'] != 'inserted' )  && !empty($_POST['position_cd']) && $_POST['position_cd'] == Salon_Position::MAINTENANCE ) {
			throw new Exception(Salon_Component::getMsg('E901',null) );
		}
		$msg = null;
		if ($_POST['type'] != 'deleted' ) {
			if (Salon_Page::serverCheck(array('position_name','wp_role','remark'),$msg) == false) {
				throw new Exception($msg );
			}
		}
	}

	public function show_page() {
		$res = array();
		$res['no'] = __($_POST['type'],SL_DOMAIN);
		$res['check'] = '';
		
		$res['position_cd'] = $this->table_data['position_cd'];
		if ( $_POST['type'] != 'deleted' ) {
			$res['name'] = htmlspecialchars($this->table_data['name'],ENT_QUOTES);
			$res['wp_role'] = htmlspecialchars($this->table_data['wp_role'],ENT_QUOTES);
			$res['role'] = htmlspecialchars($this->table_data['role'],ENT_QUOTES);
			$res['remark'] = htmlspecialchars($this->table_data['remark'],ENT_QUOTES);
		}
		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($res).' }';
	}


}