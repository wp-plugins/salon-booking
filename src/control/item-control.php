<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/item-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/item-component.php');

class Item_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;
	
	private $action_class = '';
	private $permits = null;
	
	

	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Item_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Item_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Item_Component($this->datas);
		$this->permits = array('Item_Page','Item_Init','Item_Edit','Item_Col_Edit');
	}
	
	
	
	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch);
		if ($this->action_class == 'Item_Page' ) {
			$this->pages->set_branch_datas($this->datas->getAllBranchData());
		}
		elseif ($this->action_class == 'Item_Init' ) {
			$this->pages->set_init_datas($this->datas->getInitDatas());
		}
		elseif ($this->action_class == 'Item_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editTableData();
			$this->pages->set_table_data($res);
			if ($_POST['type'] == 'inserted' ) {
				$this->pages->set_item_cd($this->datas->insertTable( $res ));
				$this->pages->set_branch_name($this->pages->get_branch_cd());
			}
			if ($_POST['type'] == 'updated' ) {
				$this->datas->updateTable( $res );
				$this->pages->set_branch_name($this->pages->get_branch_cd());
			}
			if ($_POST['type'] == 'deleted' ) {
				$this->datas->deleteTable( $res);
			}
		}
		elseif ($this->action_class == 'Item_Col_Edit' ) {
			$this->set_response_type($this->pages->getResponseType());
			$this->pages->check_request();
			$res = $this->comp->editColumnData();
			$this->pages->set_table_data($res);
			$this->datas->updateColumn($res);
		}

		$this->pages->show_page();
		if ($this->action_class != 'Item_Page' ) die();
	}
}		//class


$staffs = new Item_Control();
$staffs->exec();