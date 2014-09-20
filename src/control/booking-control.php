<?php

	require_once(SL_PLUGIN_SRC_DIR . 'control/salon-control.php');
	require_once(SL_PLUGIN_SRC_DIR . 'data/booking-data.php');
	require_once(SL_PLUGIN_SRC_DIR . 'comp/booking-component.php');

class Booking_Control extends Salon_Control  {

	private $pages = null;
	private $datas = null;
	private $comp = null;
	
	private $action_class = '';
	
	private $branch_cd = '';
	private $permits = null;
	
	

	function __construct($branch_cd) {
		parent::__construct();
		$this->branch_cd = $branch_cd;
		if (empty($_REQUEST['menu_func']) ) {
			$this->action_class = 'BookingFront_Page';
			$this->set_response_type(Response_Type::HTML);
		}
		else {
			$this->action_class = $_REQUEST['menu_func'];

			if ( $this->action_class != 'Booking_Mobile_Edit' && $this->action_class != 'Booking_Get_Mobile') 
				$this->set_response_type(Response_Type::XML);
		}
		$this->datas = new Booking_Data();
		$this->set_config($this->datas->getConfigData());
		$this->comp = new Booking_Component($this->datas);
		$this->permits = array('BookingFront_Page','Booking_Get_Event','Booking_Edit','Booking_Get_Mobile','Booking_Mobile_Edit');
	}
	
	
	
	public function do_action() {
		$this->do_require($this->action_class ,'page',$this->permits);
		$this->pages = new $this->action_class($this->is_multi_branch);

		$user_login = $this->datas->getUserLogin();
		$role = array();
		$this->pages->set_isSalonAdmin($this->datas->isSalonAdmin($user_login,$role));

		if ($this->action_class == 'BookingFront_Page' ) {
			
			$this->pages->set_branch_datas($this->datas->getBranchData($this->branch_cd));
			$this->pages->set_item_datas($this->datas->getTargetItemData($this->branch_cd,true,true));

			$this->pages->set_staff_datas($this->comp->getTargetStaffData($this->branch_cd));
			$this->pages->set_config_datas($this->datas->getConfigData());
			$this->pages->set_working_datas($this->comp->editWorkingData($this->branch_cd));
			$this->pages->set_role($role);
			
			$this->pages->set_promotion_datas($this->comp->editPromotionData($this->branch_cd));
			
			if (Salon_Component::isMobile() ) {
				$this->pages->set_reservation_datas($this->datas->getAllEventData(date_i18n('Ymd'),$this->branch_cd,true));
			}
			if (!empty($user_login) )$this->pages->set_user_inf($this->datas->getUserInfDataByUserlogin($user_login));

		}
		elseif ($this->action_class == 'Booking_Get_Event' ) {
			$this->branch_cd = $this->pages->get_branch_cd();
			$this->pages->set_reservation_datas($this->datas->getAllEventData($this->pages->get_target_day($this->datas->getConfigData('SALON_CONFIG_BEFORE_DAY')),$this->branch_cd));
			$this->pages->set_item_datas($this->datas->getTargetItemData($this->branch_cd,true,true));
			$this->pages->set_user_login($user_login);
			
		}
		elseif ($this->action_class == 'Booking_Get_Mobile' ) {
			$this->branch_cd = $this->pages->get_branch_cd();
			$this->pages->set_config_datas($this->datas->getConfigData());
			if ($this->pages->check_request() ) {
				$this->pages->set_reservation_datas($this->datas->getAllEventData($this->pages->get_target_day(),$this->branch_cd,true));
				$this->pages->set_item_datas($this->datas->getTargetItemData($this->branch_cd,true,true));
				$this->pages->set_user_login($user_login);
			}
			
		}
		elseif ($this->action_class == 'Booking_Edit') { 
			$this->pages->check_request();
			$result = $this->comp->editTableData();
			$this->comp->serverCheck($result);
			$this->pages->set_table_data($result);
			if ($_POST['type'] == 'inserted' ) {
				$this->pages->set_reservation_cd($this->datas->insertTable($result));
			}
			elseif ($_POST['type'] == 'updated' ) {
				$this->datas->updateTable($result);
			}
			elseif ($_POST['type'] == 'deleted' ) {
				$this->datas->deleteTable($result);
			}
			$this->comp->sendMailForConfirm($this->pages->get_table_data());
		}
		elseif ($this->action_class == 'Booking_Mobile_Edit') { 
			$this->pages->set_config_datas($this->datas->getConfigData());
			if ($this->pages->check_request() ) {
				$result = $this->comp->editTableData();
				//休みやスタッフの2重予約チェックはPC版と同じでよい。
				$this->comp->serverCheck($result);
				$this->pages->set_table_data($result);
				if ($_POST['type'] == 'inserted' ) {
					$this->pages->set_reservation_cd($this->datas->insertTable($result));
				}
				elseif ($_POST['type'] == 'updated' ) {
					$this->datas->updateTable($result);
				}
				elseif ($_POST['type'] == 'deleted' ) {
					$this->datas->deleteTable($result);
				}
				$this->comp->sendMailForConfirm($this->pages->get_table_data());
				$this->pages->set_user_login($user_login);
				$this->branch_cd = $this->pages->get_branch_cd();
				$this->pages->set_branch_datas($this->datas->getBranchData($this->branch_cd));
				$this->pages->set_reservation_datas($this->datas->getAllEventData($this->pages->get_target_day(),$this->branch_cd,true));
			}
		}

		$this->pages->show_page();
		if ($this->action_class != 'BookingFront_Page') die();

	}
}		//class

$staffs = new Booking_Control(@$branch_cd);
$staffs->exec();