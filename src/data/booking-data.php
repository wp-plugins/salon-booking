<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Booking_Data extends Salon_Data {
	
	const TABLE_NAME = 'salon_reservation';	
	
	public function __construct() {
		parent::__construct();
	}
	
	public function getWorkingDataByBranchCd($target_branch_cd ,$day_from = null,$day_to = null,$over24 = false){
		global $wpdb;
		if (empty($day_from) ) $day_from = Salon_Component::computeDate(-1);
		if (empty($day_to) ) $day_to = Salon_Component::computeMonth(1);
		$before_day = '';
		if ($over24) $before_day = ',DATE_FORMAT(date_add(in_time, interval -1 day),"%%Y%%m%%d") as before_day ';
		//from toは日がまたがっている前提
		$sql =	$wpdb->prepare(' SELECT  '.
				' wk.staff_cd,DATE_FORMAT(in_time,"%%Y%%m%%d") as day ,'.
				' DATE_FORMAT(in_time,"%%Y%%m%%d%%H%%i") as in_time,'.
				' DATE_FORMAT(out_time,"%%Y%%m%%d%%H%%i") as out_time,working_cds '.
				$before_day.
				' FROM '.$wpdb->prefix.'salon_working wk ,'.
				'      '.$wpdb->prefix.'salon_staff st '.
				'   WHERE in_time >= %s '.
				'     AND in_time <= %s '.
				'     AND st.branch_cd = %d '.
				'     AND st.staff_cd = wk.staff_cd '.
				' ORDER BY wk.staff_cd,in_time ',$day_from,$day_to,$target_branch_cd);
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		return $result;
	}

	public function getItemDataByReservationCd($reservation_cd){
		global $wpdb;
		$result = $wpdb->get_var(
					$wpdb->prepare(
						' SELECT item_cds FROM '.$wpdb->prefix.'salon_reservation'.
						' WHERE  reservation_cd = %d',$reservation_cd));
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		$items = explode( ',',$result);
		$edit_result = array();
		foreach ($items as $k1 => $d1 ) {
			$edit_result[] = $d1;
		}
		return $edit_result;
	}
	
	
	public function getAllEventData($target_day ,$target_branch_cd = null,$isOnly_target_day =false,$branch_datas = null){
		global $wpdb;
		if (empty($target_branch_cd) ) $target_branch_cd = Salon_Default::BRANCH_CD;

		$to_date = '2099-12-31 12:00:00';
		//モバイルの場合はYYYYMMDDなのでここで変換
		if ($isOnly_target_day) {
			//24時間対応で当日だけではなく翌日も
			$yyyymmdd = substr($target_day,0,4).'-'.substr($target_day,4,2).'-'.substr($target_day,6,2).' '.substr($branch_datas['open_time'],0,2).':'.substr($branch_datas['open_time'],2,2);
			$tareget_day_wk = new DateTime($yyyymmdd);
			$to_date_wk = new DateTime(substr($target_day,0,4).'-'.substr($target_day,4,2).'-'.substr($target_day,6,2));
			$plushh = +substr($branch_datas['close_time'],0,2);
			$plusmm = +substr($branch_datas['close_time'],2,2);
			$to_date_wk->modify('+'.$plushh.' hour');		
			$to_date_wk->modify('+'.$plusmm.' minute');		

			$to_date = $to_date_wk->format('Y-m-d H:i:s');
			$target_day = $tareget_day_wk->format('Y-m-d H:i:s');
		}
		else {
			$now = date_i18n('Ymd');
			$to_date = salon_component::computeDate($this->getConfigData('SALON_CONFIG_AFTER_DAY'),substr($now,0,4),substr($now,4,2),substr($now,6,2));
		}
		
		$sql = 	$wpdb->prepare(
						' SELECT '.
						' reservation_cd,branch_cd,staff_cd,'.
						' user_login,non_regist_name as name,non_regist_email as email,'.
						' non_regist_tel as tel, '.
						' non_regist_activate_key,time_from,time_to,item_cds,status,'.
						' coupon, '.
						' remark,memo,notes,delete_flg,insert_time,update_time '.
						' FROM '.$wpdb->prefix.'salon_reservation '.
						'   WHERE time_from >= %s '.
						'     AND time_from <= %s '.
						'     AND (status = %d OR status = %d) '.
						'     AND delete_flg <> '.Salon_Reservation_Status::DELETED.
						'     AND branch_cd = %d '.
						' ORDER BY time_from ',
						$target_day,$to_date,Salon_Reservation_Status::COMPLETE,Salon_Reservation_Status::TEMPORARY,$target_branch_cd
				);
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}

		return $result;
	}
	
	public function getBranchData($branch_cd,$target_column = '*') {
		$result = parent::getBranchData($branch_cd);
		if ($result === false ) {
			throw new Exception(Salon_Component::getMsg('E009',$branch_cd) );
		}
		return $result;
	}
	
	
	

	public function insertTable ($table_data){

		$reservation_cd = $this->insertSql(self::TABLE_NAME,$table_data,'%d,%s,%s,%s,%s,%s,%s,%s,%d,%s,%s,%s,%s,%s,%s');
		if ($reservation_cd === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $reservation_cd;
	}

	public function updateTable ($table_data){

			$set_string = 	' user_login = %s , '.
							' staff_cd = %d , '.
							' branch_cd = %d , '.
							' non_regist_name = %s , '.
							' non_regist_email = %s , '.
							' non_regist_tel = %s , '.
							' non_regist_activate_key = %s , '.
							' time_from = %s , '.
							' time_to = %s , '.
							' status = %s ,  '.
							' remark = %s , '.
							' coupon = %s , '.
							' update_time = %s '.
							(empty($table_data['item_cds']) ? ' ' : ' , item_cds = %s  ' );
													
			$set_data_temp = array(
							$table_data['user_login'],
							$table_data['staff_cd'],
							$table_data['branch_cd'],
							$table_data['non_regist_name'],
							$table_data['non_regist_email'],
							$table_data['non_regist_tel'],
							$table_data['non_regist_activate_key'],
							$table_data['time_from'],
							$table_data['time_to'],
							$table_data['status'],
							$table_data['remark'],
							$table_data['coupon'],
							date_i18n('Y-m-d H:i:s')	);
			if (!empty($table_data['item_cds']) )	$set_data_temp[] = $table_data['item_cds'];
			$set_data_temp[] = $table_data['reservation_cd'];
		$where_string = ' reservation_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	

	public function deleteTable ($table_data){
		//bookingの削除では削除扱いはしない
		$set_string = 	' status = %d  '.
						' ,update_time = %s ';
												
		if ( is_user_logged_in() )	{
			$name = $this->getUserName();
			$set_string .= 	' ,remark = concat(remark,"'.sprintf(__("\nCanceled by %s. ",SL_DOMAIN),$name).'") ';
		}

		$set_data_temp = array(
						$table_data['status']
						,date_i18n('Y-m-d H:i:s')
						,$table_data['reservation_cd']);
		$where_string = ' reservation_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	
	public function getInitDatas() {
	}
}