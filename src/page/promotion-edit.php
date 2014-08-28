<?php 

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Promotion_Edit extends Salon_Page {
	
	private $table_data = null;

	
	public function __construct($is_multi_branch) {
		parent::__construct($is_multi_branch);
	}

	
	public function set_table_data($table_data) {
		$this->table_data = $table_data;
		
	}
	
	
	public function get_branch_cd () {
		return $this->table_data['branch_cd'];
	}


	
	public function check_request() {
		if ( ($_POST['type'] != 'inserted' ) && empty($_POST['promotion_cd']) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) );
		}
		$msg = null;
		if ($_POST['type'] != 'deleted' ) {
			if (Salon_Page::serverCheck(array('description','set_code','valid_from','valid_to','remark','usable_patern','rank_patern','discount_patern','discount'),$msg) == false) {
				throw new Exception($msg );
			}
		}
	}

	public function show_page() {

		$res = array();

		$res['no'] = __($_POST['type'],SL_DOMAIN);
		$res['check'] = '';

	
		if ( $_POST['type'] != 'deleted' ) {
			$res['promotion_cd'] = $this->table_data['promotion_cd'];
			$res['branch_cd'] = $this->table_data['branch_cd'];
			$res['set_code'] = htmlspecialchars($this->table_data['set_code'],ENT_QUOTES);
			$res['description'] = htmlspecialchars($this->table_data['description'],ENT_QUOTES);
			$res['valid_from'] = $this->table_data['valid_from'];
			$res['valid_to'] = $this->table_data['valid_to'];
			$res['usable_patern_cd'] = $this->table_data['usable_patern_cd'];
			$res['usable_data'] = $this->table_data['usable_data'];
			$res['times'] = $this->table_data['times'];
			
			$res['discount_patern_cd'] = $this->table_data['discount_patern_cd'];
			$res['discount'] = $this->table_data['discount'];
			$res['remark'] = htmlspecialchars($this->table_data['remark'],ENT_QUOTES);

//			$res['usable_data'] = htmlspecialchars($this->table_data['name'],ENT_QUOTES);

		}
		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($res).' }';
	}


}