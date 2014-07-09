<?php

class Booking_Component {
	
	private $version = '1.0';
	
	private $datas = null;
	private $is_need_sendmail = false;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}
	
	public function getTargetStaffData($branch_cd) {
		$result = $this->datas->getTargetStaffData($branch_cd);
		foreach ($result as $k1 => $d1 ) {
			//[PHOTO]
			$photo_result = $this->datas->getPhotoData($d1['photo']);
			$tmp = array();
			for($i = 0 ;$i<count($photo_result);$i++) {
				$tmp[] = $photo_result[$i];
			}
			$result[$k1]['photo_result'] = $tmp;
			//[PHOTO]
		}
		return $result;
	}

	public function editWorkingData($branch_cd) {
		$day_from = Salon_Component::computeDate(-1 * $this->datas->getConfigData('SALON_CONFIG_BEFORE_DAY'));
		$day_to = Salon_Component::computeDate( $this->datas->getConfigData('SALON_CONFIG_AFTER_DAY'));
		$result = $this->datas->getWorkingDataByBranchCd($branch_cd ,$day_from,$day_to);
		$result_after = array();
		$is_normal_patern = true;
		if ($this->datas->getConfigData('SALON_CONFIG_STAFF_HOLIDAY_SET') == Salon_Config::SET_STAFF_REVERSE ) {
			$is_normal_patern = false;
		}
		foreach ($result as $k1 => $d1 ){
			$working_cds = explode( ',',$d1['working_cds']);
			if ($is_normal_patern ) {
				if (in_array(Salon_Working::DAY_OFF,$working_cds) ){
					$result_after[$d1['day']][$d1['staff_cd']] = $d1;
				}
			}
			else {
				if ( in_array(Salon_Working::USUALLY,$working_cds) ||
					in_array(Salon_Working::HOLIDAY_WORK,$working_cds)){
					//１日複数回出退勤を繰り返す登録をした場合、staffcdのみだと最後のみが有効になる
					$result_after[$d1['day']][] = $d1;
				}
			}
		}
		return $result_after;
	}
	public function editTableData () {
		if  ($_POST['type'] == 'deleted' ) {
			$set_data['reservation_cd'] = intval($_POST['id']);
		}
		else {
			$set_data['staff_cd'] = intval($_POST['staff_cd']);	
			$set_data['non_regist_name'] = stripslashes($_POST['name']);			
			$set_data['non_regist_email'] = $_POST['mail'];	
			$set_data['time_from'] = $_POST['start_date'];	
			$set_data['time_to'] = $_POST['end_date'];		
			$set_data['status'] = $_POST['status'];		
			$set_data['remark'] = stripslashes($_POST['remark']);		
			$set_data['branch_cd'] = intval($_POST['branch_cd']);
			$set_data['user_login'] = '';	
			$set_data['memo'] = '';
			$set_data['notes'] = '';
			$set_data['non_regist_activate_key'] = substr(md5(uniqid(mt_rand(),1)),0,8);
			$set_data['item_cds'] = $_POST['item_cds'];	
			$set_data['non_regist_tel'] = $_POST['tel'];	
			
			$user_login = $this->datas->getUserLogin();	
			if (  empty($user_login) ) {
				$set_data['status'] = Salon_Reservation_Status::TEMPORARY;
				$this->is_need_sendmail = true;
			}
			else {
				$set_data['status'] = Salon_Reservation_Status::COMPLETE;
				if ( $this->datas->isSalonAdmin($user_login) ) {
					if (empty($_POST['user_login']) ) {
						if (empty($_POST['regist_customer'] ) ) $regist_customer = false;
						else $regist_customer = true;
						$set_data['user_login'] = $this->datas->registCustomer($set_data['branch_cd'],$set_data['non_regist_email'], $set_data['non_regist_tel'] ,$set_data['non_regist_name'],__('registerd by reservation process(booking)',SL_DOMAIN),'','','',$regist_customer,false);
					}
					else {
						$set_data['user_login'] = $_POST['user_login'];
					}
					
				}
				else {
					$set_data['user_login'] = $user_login;
				}
			}

			if ($_POST['type'] == 'updated' ) {
				$set_data['reservation_cd'] = intval($_POST['id']);
			}
			
		}
		return $set_data;
	}
	
	public function sendMailForConfirm($set_data) {
		if 	($this->is_need_sendmail ) {
			$branch_data = $this->datas->getBranchData($set_data['branch_cd'],'name');
			$to = $set_data['non_regist_email'];
			$subject = sprintf(__("Confirm Reservation[%d]",SL_DOMAIN),$set_data['reservation_cd']);
			$message = $this->_create_body($set_data['reservation_cd'],$set_data['non_regist_name'],$set_data['non_regist_activate_key'],$branch_data['name']);

			$header = $this->datas->getConfigData('SALON_CONFIG_SEND_MAIL_FROM');	
			if (!empty($header))	$header = "from:".$header."\n";

			
			if (function_exists( 'mb_internal_encoding' )) {
				$header .= 'Content-Type:text/html; charset="'.mb_internal_encoding().'"';
			}
			else {
				$header .= 'Content-Type:text/html;';
			}

			add_action( 'phpmailer_init', array( &$this,'setReturnPath') );
			
			if (wp_mail( $to,$subject, $message,$header ) === false ) {
				$msg = error_get_last();
				throw new Exception(Salon_Component::getMsg('E907',$msg['message']));
			}

		}
		
	}
	
	public function setReturnPath( $phpmailer ) {
		$path = $this->datas->getConfigData('SALON_CONFIG_SEND_MAIL_RETURN_PATH');
		if (empty($path)) return;
		$phpmailer->Sender = $path;
	}

	private function _create_body($reservation_cd,$name ,$activate_key,$branch_name) {
		$url = get_bloginfo( 'url' );
		$page = get_option('salon_confirm_page_id');
		$send_mail_text = $this->datas->getConfigData('SALON_CONFIG_SEND_MAIL_TEXT');
		$body = sprintf(Salon_Component::writeMailHeader().
			'<body>'.$send_mail_text.'<br>
				<a href="%s/?page_id=%d&P1=%d&P2=%s" >'.__('to confirmed reservation form',SL_DOMAIN).'</a>
			</body>',$url,intval($page),intval($reservation_cd),$activate_key);
		$body = str_replace('{X-TO_NAME}',htmlspecialchars($name,ENT_QUOTES),$body);
		$body = str_replace('{X-SHOP}',htmlspecialchars($branch_name,ENT_QUOTES),$body);
		return $body;

	}


	public function serverCheck($set_data) {
		Salon_Component::serverReservationCheck($set_data,$this->datas);
		
//		global $wpdb;
//		$reservation_data = '';
//		if ( $_POST['type'] == 'inserted'    ) {
//			if ( ! empty($set_data['reservation_cd']) )
//				throw new Exception(Salon_Component::getMsg('E901',__LINE__),1);
//		}
//		else {
//			$reservation_data = $this->datas->getTargetSalesData($set_data['reservation_cd']);
//			if ($_POST['p2'] != $reservation_data[0]['non_regist_activate_key'] ) {
//				throw new Exception(Salon_Component::getMsg('E901',__LINE__),1);
//			}
//		}
//		
//		if ( ($_POST['type'] != 'deleted') && ($set_data['staff_cd'] !=  Salon_Default::NO_PREFERENCE ) ){
//			$result = $wpdb->get_results(
//						$wpdb->prepare(
//							' SELECT  '.
//							' duplicate_cnt '.
//							' FROM '.$wpdb->prefix.'salon_staff '.
//							'   WHERE staff_cd = %d  ',
//							$set_data['staff_cd']
//						),ARRAY_A
//					);
//			if ($result === false ) {
//				$this->datas->_dbAccessAbnormalEnd();
//			}
//			$reservation_cd = '';
//			if ( $_POST['type'] == 'updated'    ) $reservation_cd = $set_data['reservation_cd'];
//			$cnt = $this->datas->countReservation($set_data['staff_cd'],$set_data['time_from'],$set_data['time_to'],$reservation_cd);
//			if ($cnt > $result[0]['duplicate_cnt'] ) {
//				throw new Exception(Salon_Component::getMsg('W002'),1);
//			}
//		}
//		return true;

	}

	
}
