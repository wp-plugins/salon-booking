<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/staff-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/staff-component.php');

class Staff_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;
	
	private $action_class = '';
	private $permits = null;
	
	

	function __construct() {
		parent::__construct();
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'Staff_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];
		}
		$this->datas = new Staff_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Staff_Component($this->datas);
		$this->permits = array('Staff_Page','Staff_Init','Staff_Edit','Staff_Col_Edit');
	}
	
	
	
	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch);
		if ($this->action_class == 'Staff_Page' ) {
			$this->pages->set_branch_datas($this->datas->getAllBranchData());
			$this->pages->set_position_datas($this->datas->getAllPositionData(true));
			$this->pages->set_config_datas($this->datas->getConfigData());
		}
		elseif ($this->action_class == 'Staff_Init' ) {
			$this->pages->set_init_datas($this->comp->editInitData($this->datas->getInitDatas()));
		}
		elseif ($this->action_class == 'Staff_Edit' ) {
			$this->pages->check_request();
			if ( $_POST['type'] != 'deleted' ) {
				$user_datas = $this->comp->editUserData();
				$user_datas['ID'] = $this->datas->setUserId($user_datas);
				$this->pages->set_table_data($user_datas);
			}	
			$res = $this->comp->editTableData();
			$this->pages->set_table_data($res);
		
			if ($_POST['type'] == 'inserted' ) {
				$this->datas->fixedPhoto($_POST['type'],$res['photo']);
				$res['staff_cd'] = $this->datas->insertTable( $res);
//				$this->pages->set_staff_cd($this->datas->insertTable( $res));
//				$branch_name = $this->datas->getBranchData($this->pages->get_branch_cd(),'name');
//				$this->pages->set_branch_name($branch_name['name']);
//				$this->pages->set_position_name( $this->datas->getPositionData($res['position_cd']));
			}
			elseif ($_POST['type'] == 'updated' ) {
				$this->datas->updateStaffPhotoData($res['staff_cd'],$res['photo']);
				$this->datas->updateTable( $res);
//				$branch_name = $this->datas->getBranchData($this->pages->get_branch_cd(),'name');
//				$this->pages->set_branch_name($branch_name['name']);
//				$this->pages->set_position_name( $this->datas->getPositionData($res['position_cd']));
			}
			elseif ($_POST['type'] == 'deleted' ) {
				$this->datas->deleteStaffPhotoData($res['staff_cd']);
				$this->datas->deleteTable( $res);
			}
			$reRead = $this->comp->editInitData($this->datas->getStaffDataByStaffcd($res['staff_cd']));
			$this->pages->set_table_data($reRead[0]);
		}
		elseif ($this->action_class == 'Staff_Col_Edit' ) {
			$this->set_response_type($this->pages->getResponseType());
			$this->pages->set_config_datas($this->datas->getConfigData());
			if ($this->pages->check_request() ) {
				if ($this->pages->isWpuserdata() ) {
					$res = $this->comp->editColunDataForWpUser();
					$this->datas->updateWpUser($res);
				}
				else {
					$res = $this->comp->editColumnData();
					$this->datas->updateColumn($res);
				}
				$this->pages->set_table_data($res);
			}
		}

		$this->pages->show_page();
		if ($this->action_class != 'Staff_Page' ) die();
	}
}		//class


$staffs = new Staff_Control();
$staffs->exec();
