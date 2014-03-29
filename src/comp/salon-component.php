<?php
class Response_Type {
	const JASON = 1;
	const HTML =2;
	const XML = 3;
	const JASON_406_RETURN = 4;
}

class Salon_Status {
	const OPEN = 0;
	const CLOSE = 1;
}

class Salon_Reservation_Status {
	const COMPLETE = 1;
	const TEMPORARY = 2;
	const DELETED =  3;
	const INIT =  0;
	const DUMMY_RESERVED = 4;	//実績登録の場合のみ
	const SALES_REGISTERD =  10;
	const BEFORE_DELETED =  5;  //現状未使用
}

class Salon_Edit {
	const OK = 1;
	const NG = 0;
}

class Salon_Regist_Customer {
	const OK = 1;
	const NG = 0;
}

class Salon_Config {
	const ONLY_BRANCH = 1;
	const MULTI_BRANCH = 2;
	const USER_LOGIN_OK = 1;
	const USER_LOGIN_NG = 0;
	const SET_STAFF_NORMAL = 1;
	const SET_STAFF_REVERSE = 2;
	const NO_PREFERNCE_OK = 1;
	const NO_PREFERNCE_NG = 0;
	const DEFALUT_BEFORE_DAY = 3;
	const DEFALUT_AFTER_DAY = 30;
	const DEFALUT_TIMELINE_Y_CNT = 5;   //	timelineのY軸に何人入れるか
	const DETAIL_MSG_OK = 1;
	const DETAIL_MSG_NG = 2;
	const NAME_ORDER_JAPAN = 1;
	const NAME_ORDER_OTHER = 2;
	const LOG_NEED =1;
	const LOG_NO_NEED =2;
	const DELETE_RECORD_YES = 1;
	const DELETE_RECORD_NO = 2;
	const DELETE_RECORD_PERIOD = 6;
	const MAINTENANCE_INCLUDE_STAFF = 0;
	const MAINTENANCE_NOT_INCLUDE_STAFF = 1;
	
}


class Salon_Working {
	const USUALLY = 1;
	const DAY_OFF = 2;
	const IN = 3;
	const OUT = 4;
	const LATE_IN = 5;
	const EARLY_OUT = 6;
	const HOLIDAY_WORK = 7;
	const ABSENCE = 8;
	
	
}

class Salon_Position {
	const MAINTENANCE = 7;
}

class Salon_Color {
	const HOLIDAY = "#FFCCFF";
	const USUALLY = "#6699FF";
}

class Salon_Default {
	const NO_PREFERENCE = -1;
	const BRANCH_CD = 1;
}

class Salon_Component {
	
	private $version = '1.0';
	

	public function __construct() {

	}


	static function editSalesData($item_datas, $staff_datas, &$result ) {
		//アイテム名の設定　アイテムは正規化せず、コードで吸収する
		$item_table = array();
		//連想配列に書き直す
		foreach ($item_datas as $k1 => $d1) {
			$item_table[$d1['item_cd']]  = array('name'=> $d1['name'],'price'=>$d1['price']);
		}
		//スタッフ名の設定　joinすると遅くなりそうなので、コードで吸収する
		$staff_table = array();
		//連想配列に書き直す
		foreach ($staff_datas as $k1 => $d1) {
			$staff_table[$d1['staff_cd']]  = array('name'=> $d1['name']);
		}
		$staff_table[Salon_Default::NO_PREFERENCE] = array('name' => __('Anyone',SL_DOMAIN));
		//
//		var_export($staff_table);
//		var_export($result);
		//個別データを編集する
		foreach ($result as $k1 => $d1 ) {
			$result[$k1]['staff_name_bef'] = @$staff_table[$result[$k1]['staff_cd_bef']]['name'];
	//		//予約実績としては、SALESに登録して完了とする。
	//		//で、実績として予約時の内容を設定しとく
			if ( empty($d1['time_from_aft']) ) {
				$result[$k1]['status'] = Salon_Reservation_Status::COMPLETE;
				$result[$k1]['status_name'] = __('result not registerd',SL_DOMAIN);
			
				$result[$k1]['time_from_aft'] = $result[$k1]['time_from_bef'];
				$result[$k1]['time_to_aft'] = $result[$k1]['time_to_bef'];
				$result[$k1]['staff_cd_aft'] = $result[$k1]['staff_cd_bef'];
				$result[$k1]['staff_name_aft'] = $result[$k1]['staff_name_bef'];
				$result[$k1]['item_cds_aft'] = $result[$k1]['item_cds_bef'];
				$result[$k1]['remark'] = $result[$k1]['remark_bef'];
			}
			else {
				$result[$k1]['staff_name_aft'] = "";
				if (!empty($result[$k1]['staff_cd_aft'])) 
					$result[$k1]['staff_name_aft'] = $staff_table[$result[$k1]['staff_cd_aft']]['name'];
				$result[$k1]['status'] = Salon_Reservation_Status::SALES_REGISTERD;
				$result[$k1]['status_name'] = __('result registerd',SL_DOMAIN);
			}
			$items = explode( ',',$d1['item_cds_bef']);
			$res = array();
			$result[$k1]['item_cd_array_bef'] = array();
			foreach ($items as $k2 => $d2 ) {
				if (!empty($item_table[$d2])) {
					$res[] = $item_table[$d2]['name'];
					$result[$k1]['item_cd_array_bef'][$d2] = @$item_table[$d2]['name'];
				}
			}
			$result[$k1]['item_name_bef'] = implode(',',$res);
			$items = explode( ',',$result[$k1]['item_cds_aft']);
			$price = 0;
			foreach ($items as $k2 => $d2 ) {
				if (!empty($item_table[$d2])) {
					$result[$k1]['item_cd_array_aft'][$d2] = @$item_table[$d2]['name'];
					$price += @$item_table[$d2]['price'];
				}
			}
			$result[$k1]['price'] = $price;
		}
		return true;
	
		
	}
	
	static function serverReservationCheck($set_data ,&$datas) {

		global $wpdb;
		$reservation_data = '';
		if ( $_POST['type'] == 'inserted'    ) {
			if ( ! empty($set_data['reservation_cd']) )
				throw new Exception(self::getMsg('E901',__LINE__),1);
		}
		else {
			$reservation_data = $datas->getTargetSalesData($set_data['reservation_cd']);
			if ($_POST['p2'] != $reservation_data[0]['non_regist_activate_key'] ) {
				throw new Exception(self::getMsg('E901',__LINE__),1);
			}
		}
		
		$reservation_cd = '';
		if ( $_POST['type'] == 'updated'    ) $reservation_cd = $set_data['reservation_cd'];
		if ( ($_POST['type'] != 'deleted') ) {
			$result_branch = $wpdb->get_results(
						$wpdb->prepare(
							' SELECT  '.
							' duplicate_cnt,closed,sp_dates '.
							' FROM '.$wpdb->prefix.'salon_branch '.
							'   WHERE branch_cd = %d  ',
							$set_data['branch_cd']
						),ARRAY_A
					);
			if ($result_branch === false ) {
				$datas->_dbAccessAbnormalEnd();
			}
			//休業日のチェックと特別な営業日のチェック
			//
			$sp_dates = unserialize($result_branch[0]['sp_dates']);
			$year = substr($set_data['time_from'],0,4);
			//yyyy-mm-dd
			$ymd = str_replace('-','',substr($set_data['time_from'],0,10));

			if(isset($sp_dates[$year][$ymd]) && $sp_dates[$year][$ymd] == Salon_Status::OPEN ) {
			}
			else {
				$holidays = explode(',',$result_branch[0]['closed']);
				if (in_array(salon_component::getDayOfWeek($set_data['time_from']),$holidays) ) {
					throw new Exception(self::getMsg('E901',__LINE__),1);
				}
			}
			
			if  ($set_data['staff_cd'] !=  Salon_Default::NO_PREFERENCE ) {
				$result = $wpdb->get_results(
							$wpdb->prepare(
								' SELECT  '.
								' duplicate_cnt '.
								' FROM '.$wpdb->prefix.'salon_staff '.
								'   WHERE staff_cd = %d  ',
								$set_data['staff_cd']
							),ARRAY_A
						);
				if ($result === false ) {
					$datas->_dbAccessAbnormalEnd();
				}
				$cnt = $datas->countReservation($set_data['staff_cd'],$set_data['time_from'],$set_data['time_to'],$reservation_cd);
				if ($cnt > $result[0]['duplicate_cnt'] ) {
					throw new Exception(self::getMsg('W002',array(__('staff',SL_DOMAIN), $result[0]['duplicate_cnt']+1)),1);
				}
	
			}
//			$result_branch = $wpdb->get_results(
//						$wpdb->prepare(
//							' SELECT  '.
//							' duplicate_cnt '.
//							' FROM '.$wpdb->prefix.'salon_branch '.
//							'   WHERE branch_cd = %d  ',
//							$set_data['branch_cd']
//						),ARRAY_A
//					);
//			if ($result_branch === false ) {
//				$datas->_dbAccessAbnormalEnd();
//			}
			$possible_cnt = $result_branch[0]['duplicate_cnt'];
			$edit_sql = $wpdb->prepare(
							' SELECT  '.
							' count(*) as staff_cnt, '.
							' sum(duplicate_cnt) as duplicate_cnt  '.
							' FROM '.$wpdb->prefix.'salon_staff '.
							'   WHERE branch_cd = %d  '.
							'   AND   delete_flg <> %d ',
							$set_data['branch_cd'],Salon_Reservation_Status::DELETED
						);
			$result = $wpdb->get_results($edit_sql,ARRAY_A);
			if ($result === false ) {
				$datas->_dbAccessAbnormalEnd();
			}
			$possible_cnt += $result[0]['staff_cnt'] + $result[0]['duplicate_cnt'];
			$cnt = $datas->countReservation('',$set_data['time_from'],$set_data['time_to'],$reservation_cd);
			if ($cnt >= $possible_cnt ) {
				throw new Exception(self::getMsg('W002',array(__('branch',SL_DOMAIN), $possible_cnt)),1);
			}

		}

			
		return true;

	}
	static function writeMailHeader() {
		$charset = '';
		if (function_exists( 'mb_internal_encoding' )) {
			$charset = 'charset="'.mb_internal_encoding().'"';
		}
		return '<!DOCTYPE HTML PUBLIC
			 "-//W3C//DTD HTML 4.01 Transitional//EN">
			<html lang="ja">
			<head>
			  <meta http-equiv="Content-Language"
				content="ja">
			  <meta http-equiv="Content-Type"
				content="text/html; '.$charset.'>
			  <title></title>
			  <meta http-equiv="Content-Style-Type"
				content="text/css">
			  <style type="text/css"><!--
				body{margin:0;padding:0;}
			  --></style>
			</head>	';
	}

	static function getMsg($err_cd, $add_char = '') {
		$err_msg = '';
		switch ($err_cd) {
			case 'N001':
				$err_msg = sprintf(__("%s normal end",SL_DOMAIN),$add_char);
				break;	
			case 'E001':
				$err_msg = sprintf(__("%s error !!",SL_DOMAIN),$add_char);
				break;	
			case 'E002':
				$err_msg = sprintf(__("this user not registerd",SL_DOMAIN));
				break;	
			case 'E003':
				$err_msg = sprintf(__("this staff not registerd[%s] ",SL_DOMAIN),$add_char);
				break;	
			case 'E004':
				$err_msg = sprintf(__("sorry! under maintenance[%s]",SL_DOMAIN),$add_char);
				break;	
			case 'E005':
				$err_msg = sprintf(__("sorry! this page not displayed %s",SL_DOMAIN),$add_char);
				break;	
			case 'E006':
				$err_msg = sprintf(__("user data is differented %s",SL_DOMAIN),$add_char);
				break;
			case 'E007':
				$err_msg = sprintf(__("%s an unexpected error has occurred %s",SL_DOMAIN),$err_cd,$add_char);
				break;	
			case 'E008':
				$err_msg = sprintf(__("sorry! this page not displayed. checks cookies on ? %s ",SL_DOMAIN),$add_char);
				break;	
			case 'E009':
				$err_msg = sprintf(__("this branch_cd[%d] can't find.Please check set short code format. [salon-booking] or if multi shop [salon-booking branch_cd=XX]. ",SL_DOMAIN),$add_char);
				break;
			case 'E010':
				$err_msg = sprintf(__("this branch has no staff ",SL_DOMAIN),$add_char);
				break;	
			case 'E201':
				$err_msg = sprintf(__("%s required[%s]",SL_DOMAIN),$err_cd,$add_char);
				break;	
			case 'E202':
				$err_msg = sprintf(__("%s this is not time data[%s]",SL_DOMAIN),$err_cd,$add_char);
				break;	
			case 'E203':
				$err_msg = sprintf(__("%s numeric input please[%s]",SL_DOMAIN),$err_cd,$add_char);
				break;	
//			case 'E204':
//				$err_msg = sprintf(__("%s half width input please[%s]",SL_DOMAIN),$err_cd,$add_char);
//				break;	
			case 'E205':
				$err_msg = sprintf(__("%s zip code XXXXX-XXXX input please[%s]",SL_DOMAIN),$err_cd,$add_char);
				break;	
			case 'E206':
				$err_msg = sprintf(__("%s Telephone XXXX-XXX-XXXX input please[%s]",SL_DOMAIN),$err_cd,$add_char);
				break;	
			case 'E207':
				$err_msg = sprintf(__("%s XXX@XXX.XXX input please[%s]",SL_DOMAIN),$err_cd,$add_char);
				break;	
			case 'E208':
				$err_msg = sprintf(__("%s MM/DD/YYYY or MMDDYYYY input please[%s]",SL_DOMAIN),$err_cd,$add_char);
				break;	
			case 'E209':
				$err_msg = sprintf(__("%s this day not exist?[%s]",SL_DOMAIN),$err_cd,$add_char);
				break;	
			case 'E210':
				$err_msg = sprintf(__("%s space input between fires-name and last-name[%s]",SL_DOMAIN),$err_cd,$add_char);
				break;	
			case 'E211':
				$err_msg = sprintf(__("%s within %d charctors[%s]",SL_DOMAIN),$err_cd,$add_char[0],$add_char[1]);
				break;	
			case 'E401':
				$err_msg = __('an unexpected error has occurred',SL_DOMAIN);
				break;	
			case 'E901':
				$err_msg = sprintf(__("this data is unacceptble.Bug?[%s]",SL_DOMAIN),$add_char);
				break;	
			case 'E902':
				$err_msg = sprintf(__("database error [%s][%s]",SL_DOMAIN),$add_char[0],$add_char[1]);
				break;	
			case 'E903':
				$err_msg = sprintf(__("create userid error [%s]",SL_DOMAIN),$add_char);
				break;	
			case 'E904':
				$err_msg = sprintf(__("file open error[%s]",SL_DOMAIN),$add_char);
				break;	
			case 'E905':
				$err_msg = sprintf(__("file write error",SL_DOMAIN));
				break;	
			case 'E906':
				$err_msg = sprintf(__("target data not found",SL_DOMAIN));
				break;	
			case 'E907':
				$err_msg = sprintf(__("e-mail could not be sent %s",SL_DOMAIN),$add_char);	
				break;	
			case 'W001':
				$err_msg = sprintf(__("already reservation existed, so you can't day off",SL_DOMAIN),$add_char);	
				break;	
			case 'W002':
				$err_msg = sprintf(__("already reservation existed .this %s can reserve %s reservations at same time range,please update datas ",SL_DOMAIN),$add_char[0],$add_char[1]);	
				break;	
			case 'I001':
				$err_msg = sprintf(__("when demo site ,can't insert,update and delete.",SL_DOMAIN),$add_char);
				break;	
			case 'I002':
				$err_msg = sprintf(__("Customer is registerd.\nUser Login : %s\nPassword : %s",SL_DOMAIN),$add_char[0],$add_char[1]);
				break;	
			default:
				$err_msg = $err_cd.__("message not found",SL_DOMAIN).$add_char;
				
		}
		return $err_msg;
	}
	
	static function computeDate($addDays = 1,$year = null, $month = null , $day =null) {
		if ( empty($year) ) $year = date_i18n("Y");
		if ( empty($month) ) $month = date_i18n("m");
		if ( empty($day) ) $day = date_i18n("d");
		$baseSec = mktime(0, 0, 0, $month, $day, $year);
		$addSec = $addDays * 86400;
		$targetSec = $baseSec + $addSec;
		return date("Y-m-d H:i:s", $targetSec);
	}
	
	static function getMonthEndDay($year, $month) {
		$dt = mktime(0, 0, 0, $month + 1, 0, $year);
		return date("d", $dt);
	}
	
	static function computeMonth($addMonths=1,$year = null, $month = null , $day =null) {
		if ( empty($year) ) $year = date_i18n("Y");
		if ( empty($month) ) $month = date_i18n("m");
		if ( empty($day) ) $day = date_i18n("d");
		$month += $addMonths;
		$endDay = self::getMonthEndDay($year, $month);
		if($day > $endDay) $day = $endDay;
		$dt = mktime(0, 0, 0, $month, $day, $year);
		return date("Y-m-d H:i:s", $dt);
	}
	
	static function computeYear($addYears=1,$year = null, $month = null , $day =null) {
		if ( empty($year) ) $year = date_i18n("Y");
		if ( empty($month) ) $month = date_i18n("m");
		if ( empty($day) ) $day = date_i18n("d");
		$year += $addYears;
		$dt = mktime(0, 0, 0, $month, $day, $year);
		return date("Y-m-d H:i:s", $dt);
	}
	
	static function zenSp2han($in) {
		if (function_exists( 'mb_convert_kana' )) {
			return  mb_convert_kana($in,"s");
		}
		else {
			return $in;
		}
	}
	
	static function formatTime($time_data) {
		return sprintf("%02s:%02s",+substr($time_data,0,2),substr($time_data,2,2));
	}
	
	static function replaceTimeToDb($time_data) {
		if (preg_match('/(?<hour>\d+):(?<minute>\d+)/', $time_data, $matches) == 0 ) {
			$matches['hour'] = substr($time_data,0,2);
			$matches['minute'] = substr($time_data,2,2);
		}
		return sprintf("%02d%02d",+$matches['hour'],+$matches['minute']);
	}
	
	
	static function editRequestYmdForDb($in) {
		if (empty($in) ) return;
		if (preg_match('/^'.__('(?<month>\d{1,2})[\/\.\-](?<day>\d{1,2})[\/\.\-](?<year>\d{4})',SL_DOMAIN).'$/',$in,$matches) == 0 )  
		   preg_match('/^'.__('(?<month>\d{2})(?<day>\d{2})(?<year>\d{4})',SL_DOMAIN).'$/',$in,$matches); 
		return sprintf("%4d-%02d-%02d",+$matches['year'],+$matches['month'],+$matches['day']);
	}
	
	static function getDayOfWeek($in) {
		return date("w", strtotime($in));
	}

	
}