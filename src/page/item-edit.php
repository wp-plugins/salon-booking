<?php 

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Item_Edit extends Salon_Page {
	
	private $branch_datas = null;
	private $table_data = null;

	private $branch_name = '';
	
	public function __construct($is_multi_branch) {
		parent::__construct($is_multi_branch);
	}

	
	public function set_table_data($table_data) {
		$this->table_data = $table_data;
	}
	
	public function set_item_cd($item_cd) {
		 $this->table_data['item_cd'] = $item_cd;
	}

	public function get_branch_cd() {
		return $this->table_data['branch_cd'];
	}

	public function set_branch_name($branch_name) {
		$this->table_data['branch_name'] = $branch_name;
	}
	
	public function check_request() {
		if	( ($_POST['type'] != 'inserted' ) && empty($_POST['item_cd']) ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__) );
		}
		$msg = null;
		if ($_POST['type'] != 'deleted' ) {
			if (Salon_Page::serverCheck(array('item_name','short_name','branch_cd','minute','price','remark'),$msg) == false) {
				throw new Exception($msg );
			}
		}
		if (defined ( 'SALON_DEMO' ) && SALON_DEMO && $_POST['type'] == 'deleted' ) {
			throw new Exception(Salon_Component::getMsg('I003',null) ,1);
		}
	}

	public function show_page() {
		$res = array();

		$res['no'] = __(parent::replaceResult($_POST['type']),SL_DOMAIN);
		$res['check'] = '';

		$res['item_cd'] = $this->table_data['item_cd'];
		
		if ( $_POST['type'] != 'deleted' ) {
			
	
			$res['name'] = htmlspecialchars($this->table_data['name'],ENT_QUOTES);
			$res['short_name'] = htmlspecialchars($this->table_data['short_name'],ENT_QUOTES);
			$res['branch_cd'] = $this->table_data['branch_cd'];
			$res['branch_name'] = htmlspecialchars($this->table_data['branch_name'],ENT_QUOTES);
			$res['minute'] = $this->table_data['minute'];
			$res['price'] = $this->table_data['price'];
			$res['remark'] = htmlspecialchars($this->table_data['remark'],ENT_QUOTES);
			$res['photo'] = $this->table_data['photo'];
			$res['display_sequence'] = $this->table_data['display_sequence'];
		}
		
		
		echo '{	"status":"Ok","message":"'.Salon_Component::getMsg('N001').'",
				"set_data":'.json_encode($res).' }';
	}


}