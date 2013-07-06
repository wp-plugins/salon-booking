<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/basic-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/basic-component.php');

class Basic_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;
	
	private $action_class = '';
	private $permits = null;
	
	

	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Basic_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Basic_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Basic_Component($this->datas);
		$this->permits = array('Basic_Page','Basic_Init','Basic_Edit');
	}
	
	
	
	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch);


		if ($this->action_class == 'Basic_Page' ) {

			$user_login = $this->datas->getUserLogin();
			$branch_cd = $this->datas->getBracnCdbyCurrentUser($user_login);
			$this->pages->set_current_user_branch_cd($branch_cd);
			$this->pages->set_branch_datas($this->datas->getBranchData($branch_cd));

		}
		elseif ($this->action_class == 'Basic_Init' ) {
			$branch_cd = $this->pages->get_target_branch_cd();
			$target_year = $this->pages->get_target_year();
			$this->pages->set_init_datas($this->datas->getAllSpDateData($target_year,$branch_cd));
		}
		elseif ($this->action_class == 'Basic_Edit' ) {
			$this->pages->check_request();
			$res = $this->comp->editTableData();
			$this->pages->set_table_data($res);

			if ( ($_POST['type'] == 'inserted' ) || ($_POST['type'] == 'deleted' ) ) {
				$this->datas->updateSpDate( $res );
			}
			elseif ($_POST['type'] == 'updated' ) {
				$this->datas->updateTable( $res );
			}
		}

		$this->pages->show_page();
		if ($this->action_class != 'Basic_Page') die();
	}
}		//class


$staffs = new Basic_Control();
$staffs->exec();