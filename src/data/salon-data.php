<?php

abstract class Salon_Data {
	
	const SALON_NAME_DELIMITER = ' ';
	const SALON_DUMMY_DOMAIN = '@dummy.com';
	//salon上でのAdminはPosition_cdが1～4まで
	const SALON_ADMINISTRATOR = 4; 
	const SALON_MAINTENANCE = 7;
	

	private $version = '1.0';
	private $config = null;
	
	private $isAdmin = null;


	public function __construct() {
		$result =  unserialize(get_option( 'SALON_CONFIG'));
		if (empty($result['SALON_CONFIG_BRANCH']) ) $result['SALON_CONFIG_BRANCH'] =  Salon_Config::MULTI_BRANCH;
		if (!isset($result['SALON_CONFIG_USER_LOGIN']) && empty($result['SALON_CONFIG_USER_LOGIN']) ) $result['SALON_CONFIG_USER_LOGIN'] = Salon_Config::USER_LOGIN_OK;
//		if (empty($result['SALON_CONFIG_SEND_MAIL_TEXT']) ) $result['SALON_CONFIG_SEND_MAIL_TEXT'] = __('Mr/Ms {X-TO_NAME} Please Fixed this reservation.Click the following URL<br>{X-SHOP}',SL_DOMAIN);
//		if (empty($result['SALON_CONFIG_SEND_MAIL_TEXT_USER']) ) $result['SALON_CONFIG_SEND_MAIL_TEXT_USER'] = __('Mr/Ms {X-TO_NAME} Thank you for registration .your User_id is %s,your initial password is %s',SL_DOMAIN);
		if (empty($result['SALON_CONFIG_STAFF_HOLIDAY_SET']) ) $result['SALON_CONFIG_STAFF_HOLIDAY_SET'] =  Salon_Config::SET_STAFF_NORMAL;
		if (empty($result['SALON_CONFIG_BEFORE_DAY']) ) $result['SALON_CONFIG_BEFORE_DAY'] =  Salon_Config::DEFALUT_BEFORE_DAY;
		if (empty($result['SALON_CONFIG_AFTER_DAY']) ) $result['SALON_CONFIG_AFTER_DAY'] =  Salon_Config::DEFALUT_AFTER_DAY;
		if (empty($result['SALON_CONFIG_TIMELINE_Y_CNT']) ) $result['SALON_CONFIG_TIMELINE_Y_CNT'] =  Salon_Config::DEFALUT_TIMELINE_Y_CNT;
		if (empty($result['SALON_CONFIG_SHOW_DETAIL_MSG']) ) $result['SALON_CONFIG_SHOW_DETAIL_MSG'] =  Salon_Config::DETAIL_MSG_NG;
		if (empty($result['SALON_CONFIG_NAME_ORDER']) ) $result['SALON_CONFIG_NAME_ORDER'] =  Salon_Config::NAME_ORDER_JAPAN;
		if (!isset($result['SALON_CONFIG_NO_PREFERENCE']) && empty($result['SALON_CONFIG_NO_PREFERENCE']) ) $result['SALON_CONFIG_NO_PREFERENCE']  = Salon_Config::NO_PREFERNCE_OK;
		if (empty($result['SALON_CONFIG_LOG']) ) $result['SALON_CONFIG_LOG']  = Salon_Config::LOG_NEED;
		if (empty($result['SALON_CONFIG_DELETE_RECORD']) ) $result['SALON_CONFIG_DELETE_RECORD'] =  Salon_Config::DELETE_RECORD_NO;
		if (empty($result['SALON_CONFIG_DELETE_RECORD_PERIOD']) ) $result['SALON_CONFIG_DELETE_RECORD_PERIOD'] =  Salon_Config::DELETE_RECORD_PERIOD;
		if (empty($result['SALON_CONFIG_MAINTENANCE_INCLUDE_STAFF']) ) $result['SALON_CONFIG_MAINTENANCE_INCLUDE_STAFF'] =  Salon_Config::MAINTENANCE_INCLUDE_STAFF;
		if (empty($result['SALON_CONFIG_SEND_MAIL_FROM']) ) $result['SALON_CONFIG_SEND_MAIL_FROM'] =  "";
		if (empty($result['SALON_CONFIG_SEND_MAIL_RETURN_PATH']) ) $result['SALON_CONFIG_SEND_MAIL_RETURN_PATH'] =  "";
		
		if (empty($result['SALON_CONFIG_MOBILE_USE']) ) $result['SALON_CONFIG_MOBILE_USE']  = Salon_Config::MOBILE_USE_YES;
		if (empty($result['SALON_CONFIG_MOBILE_NO_PHOTO']) ) $result['SALON_CONFIG_MOBILE_NO_PHOTO']  = "";
		if (empty($result['SALON_CONFIG_LOAD_TAB']) ) $result['SALON_CONFIG_LOAD_TAB']  = Salon_Config::LOAD_STAFF;
		
		//[2014/08/01]Ver1.4.6
		if (empty($result['SALON_CONFIG_SEND_MAIL_TEXT']) ) $result['SALON_CONFIG_SEND_MAIL_TEXT'] = __("Mr/Ms {X-TO_NAME} \nPlease confirm this reservation.Click the following URL\n{X-URL}\n\n{X-SHOP_NAME}\n{X-SHOP_ADDRESS}\n{X-SHOP_TEL}\n{X-SHOP_MAIL}",SL_DOMAIN);		
		if (empty($result['SALON_CONFIG_SEND_MAIL_TEXT_USER']) ) $result['SALON_CONFIG_SEND_MAIL_TEXT_USER'] = __("Mr/Ms {X-TO_NAME} \nThank you for the registration.\nyour User_id is {X-USER_ID},\nyour initial password is {X-PASSWORD}\n\n{X-SHOP_NAME}\n{X-SHOP_ADDRESS}\n{X-SHOP_TEL}\n{X-SHOP_MAIL}",SL_DOMAIN);
		
		
		if (empty($result['SALON_CONFIG_SEND_MAIL_SUBJECT']) ) $result['SALON_CONFIG_SEND_MAIL_SUBJECT'] = __("Confirm Reservation",SL_DOMAIN);
		if (empty($result['SALON_CONFIG_SEND_MAIL_SUBJECT_USER']) ) $result['SALON_CONFIG_SEND_MAIL_SUBJECT_USER'] = __("Your registration is completed",SL_DOMAIN);
		
		if (empty($result['SALON_CONFIG_RESERVE_DEADLINE']) ) $result['SALON_CONFIG_RESERVE_DEADLINE'] =  Salon_Config::DEFALUT_RESERVE_DEADLINE;
		
		
		
				
		$this->config = $result;
	}
	

	public function getAllBranchData ($add_where = '') {
		global $wpdb;
		if (empty($add_where) ) {
			$add_where = 'where delete_flg <> '.Salon_Reservation_Status::DELETED;
		}
		$result = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'salon_branch '.$add_where.' ORDER BY branch_cd ',ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $result;
	}

	private function _unset_roles(&$result,$target) {
		foreach ($result as $k1 => $d1 ) {
			foreach($target as $d2 ) {
				if ($d1['wp_role'] == $d2) unset($result[$k1]);
			}
		}
	}

	public function getAllPositionData($is_user = false){
		global $wpdb;
		$result = $wpdb->get_results(' SELECT * FROM '.$wpdb->prefix.'salon_position where delete_flg <> '.Salon_Reservation_Status::DELETED.' ORDER BY position_cd',ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		if (! $is_user  ) return $result;

		$current_user = wp_get_current_user();
		
		switch($current_user->roles[0]) {
			case 'administrator':
				break;
			case 'editor':
				$this->_unset_roles($result,array('administrator'));
				break;
			case 'author':
				$this->_unset_roles($result,array('administrator','editor'));
				break;
			case 'contributor':
				$this->_unset_roles($result,array('administrator','editor','author'));
				break;
			default:
				throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__.':'.__function__));
		}
		return $result;		
	}

	public function getPositionData($position_cd){
		global $wpdb;
		$result = $wpdb->get_results(' SELECT name FROM '.$wpdb->prefix.'salon_position where delete_flg <> '.Salon_Reservation_Status::DELETED.' AND position_cd = '.$position_cd,ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}

		return $result[0]['name'];
	}
	
	public function getAllItemData($add_where = ''){
		global $wpdb;
	
		if (empty($add_where) ) {
			$add_where = 'where it.delete_flg <> '.Salon_Reservation_Status::DELETED;
		}
		
		$add_where .= ' AND it.branch_cd = br.branch_cd ';
		$sql = ' SELECT it.*'.
			//[2014/06/22]Ver1.4.1
					',DATE_FORMAT(exp_from,"'.__("%m/%d/%Y",SL_DOMAIN).'") as exp_from '.
					',DATE_FORMAT(exp_to,"'.__("%m/%d/%Y",SL_DOMAIN).'") as exp_to '.
					',br.name as branch_name FROM '.$wpdb->prefix.'salon_item it , '.$wpdb->prefix.'salon_branch br '.$add_where.' ORDER BY branch_cd,display_sequence,item_cd ';
		$result = $wpdb->get_results($sql,ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		foreach ($result as $k1 => $d1 ) {
			//写真はシングルこーてしょんが変化されて格納されているので直す
			$tmp = str_replace("\'","'",$d1['photo']);
			if (!empty($_SERVER['HTTPS']) ) {
				$url = str_replace('/','\/',site_url());
				$url = substr($url,strpos($url,':')+1);
				$tmp = preg_replace("/([hH][tT][tT][pP]:".$url.")/","https:".$url,$tmp);
			}
			$result[$k1]['photo'] = $tmp;			
	//		var_export(str_replace("\'","'",$d1['photo']));
		}
		return $result;
	}

	public function getAllItemDataForSet(){
		global $wpdb;
		//過去データは不要
		$sql = $wpdb->prepare(' SELECT branch_cd, item_cd , name,short_name,all_flg '.
			   ' FROM '.$wpdb->prefix.'salon_item  '.
			   ' WHERE delete_flg <> '.Salon_Reservation_Status::DELETED.
			   ' AND exp_to > %s '.
			   ' ORDER BY branch_cd,display_sequence,item_cd ',date_i18n('Ymd'));
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		//支店単位にまとめる
		$edit_result = array();
		if (count($result) > 0 ) {
			$save_branch_cd = $result[0]['branch_cd'];
			$temp_array = array();
			foreach($result as $k1 => $d1) {
				if ($save_branch_cd <> $d1['branch_cd']) {
					$edit_result[$save_branch_cd] = $temp_array;
					$temp_array = array();
				}
				$temp_array[] = $d1;
				$save_branch_cd = $d1['branch_cd'];
			}
			$edit_result[$save_branch_cd] = $temp_array;
		}
		return $edit_result;
	}

	public function getItemCdByBranch($branch_cd) {
		global $wpdb;
		
		//過去データは不要
		$sql = $wpdb->prepare(' SELECT item_cd '.
			   ' FROM '.$wpdb->prefix.'salon_item  '.
			   ' WHERE delete_flg <> '.Salon_Reservation_Status::DELETED.
			   ' AND exp_to > %s '.
			   ' AND branch_cd = %d '.
			   ' ORDER BY branch_cd,display_sequence,item_cd ',date_i18n('Ymd'),$branch_cd);
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		//一つの項目にまとめる
		$edit_result = array();
		if (count($result) > 0 ) {
			foreach($result as $k1 => $d1) {
				$edit_result[] = $d1['item_cd'];
			}
		}
		return implode(',',$edit_result);
	}
	
	public function getTargetItemData($branch_cd,$except_delete = true){
		global $wpdb;
		$delete_str = ' delete_flg <> '.Salon_Reservation_Status::DELETED;
		if (! $except_delete ) $delete_str = '1=1';
		$sql = ' SELECT item_cd,name,short_name,minute,price ,DATE_FORMAT(exp_from,"%%Y%%m%%d") as exp_from,DATE_FORMAT(exp_to,"%%Y%%m%%d") as exp_to'.
			   ' FROM '.$wpdb->prefix.'salon_item '.
			   ' WHERE '.$delete_str.
			   ' AND exp_to > %s '.
			   ' AND branch_cd = %d  '.
			   ' ORDER BY branch_cd,display_sequence,item_cd ';
		$result = $wpdb->get_results($wpdb->prepare($sql,date_i18n('Ymd'),$branch_cd),ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $result;
	}
	
	//[2014/08/06]
	public function getMenuItemCalcEndTime($startTime,$menus) {
		global $wpdb;
		$setIn = explode(',',$menus);
		$sql = $wpdb->prepare(' SELECT sum(minute) as min'.
			   ' FROM '.$wpdb->prefix.'salon_item '.
			   ' WHERE item_cd IN(' . substr(str_repeat(',%d', count($setIn)), 1) . ')',$setIn);
			   
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		$from = new DateTime($startTime);
		$from->add(new DateInterval("PT".$result[0]['min']."M"));
		return $from->format('Y-m-d H:i');
	}

	public function getTargetStaffData($branch_cd = null,$except_delete = true){
		global $wpdb;
		$where = 'WHERE st.delete_flg <> '.Salon_Reservation_Status::DELETED ;
		if (! $except_delete ) $where = 'WHERE 1 = 1 ';
		if (!empty($branch_cd) ) {
			$where .= $wpdb->prepare(" AND st.branch_cd = %s ",$branch_cd);
		}
		if ($this->config['SALON_CONFIG_NAME_ORDER'] == Salon_Config::NAME_ORDER_JAPAN ) {
			$name_order = 'um2.meta_value," " ,um1.meta_value';
		}
		else {
			$name_order = 'um1.meta_value," " ,um2.meta_value';
		}
		
		$sql = 	' SELECT staff_cd,concat('.$name_order.') as name , photo , remark , duplicate_cnt,position_cd,display_sequence'.
				' ,in_items ,memo'.
				' FROM '.$wpdb->prefix.'salon_staff st  '.
				' INNER JOIN '.$wpdb->prefix.'users us  '.
				'       ON    us.user_login = st.user_login '.
				' INNER JOIN '.$wpdb->prefix.'usermeta um1  '.
				'       ON    us.ID = um1.user_id AND um1.meta_key ="first_name" '.
				' INNER JOIN '.$wpdb->prefix.'usermeta um2  '.
				'       ON    us.ID = um2.user_id AND um2.meta_key ="last_name" '.
				$where.
				' ORDER BY st.branch_cd,display_sequence,position_cd ';
	
		$result = $wpdb->get_results($sql,ARRAY_A);
		//
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $result;
	}
	public function getTargetStaffDataByUserlogin($user_login){
		global $wpdb;
		if ($this->config['SALON_CONFIG_NAME_ORDER'] == Salon_Config::NAME_ORDER_JAPAN ) {
			$name_order = 'um2.meta_value," " ,um1.meta_value';
		}
		else {
			$name_order = 'um1.meta_value," " ,um2.meta_value';
		}
		
		$sql = 	' SELECT staff_cd,concat('.$name_order.') as name , photo , remark , duplicate_cnt,position_cd'.
				' FROM '.$wpdb->prefix.'salon_staff st  '.
				' INNER JOIN '.$wpdb->prefix.'users us  '.
				'       ON    us.user_login = st.user_login '.
				'       AND   st.user_login = %s '.
				' INNER JOIN '.$wpdb->prefix.'usermeta um1  '.
				'       ON    us.ID = um1.user_id AND um1.meta_key ="first_name" '.
				' INNER JOIN '.$wpdb->prefix.'usermeta um2  '.
				'       ON    us.ID = um2.user_id AND um2.meta_key ="last_name" '.
				'WHERE st.delete_flg <> '.Salon_Reservation_Status::DELETED.
				' ORDER BY staff_cd ';
	
		$result = $wpdb->get_results($wpdb->prepare($sql,$user_login),ARRAY_A);
		//
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $result;
	}
	
	public function getUserInfDataByUserlogin($user_login) {
		global $wpdb;
		if ($this->config['SALON_CONFIG_NAME_ORDER'] == Salon_Config::NAME_ORDER_JAPAN ) {
			$name_order = 'um2.meta_value," " ,um1.meta_value';
		}
		else {
			$name_order = 'um1.meta_value," " ,um2.meta_value';
		}
		
		$sql = 	' SELECT us.user_login as user_login,concat('.$name_order.') as user_name , us.user_email , um3.meta_value as tel , um4.meta_value as mobile'.
				' FROM '.$wpdb->prefix.'users us  '.
				' INNER JOIN '.$wpdb->prefix.'usermeta um1  '.
				'       ON    us.ID = um1.user_id AND um1.meta_key ="first_name" '.
				' INNER JOIN '.$wpdb->prefix.'usermeta um2  '.
				'       ON    us.ID = um2.user_id AND um2.meta_key ="last_name" '.
				' LEFT JOIN '.$wpdb->prefix.'usermeta um3  '.
				'       ON    us.ID = um3.user_id AND um3.meta_key ="tel" '.
				' LEFT JOIN '.$wpdb->prefix.'usermeta um4  '.
				'       ON    us.ID = um4.user_id AND um4.meta_key ="mobile" '.
				'WHERE us.user_login = %s  ';
		$result = $wpdb->get_results($wpdb->prepare($sql,$user_login),ARRAY_A);
		//
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		
		if (empty($result[0]['tel']) && !empty($result[0]['mobile']) ) {
			$result[0]['tel'] = $result[0]['mobile'];
		}
		
		return $result[0];

	}



	
	public function getBranchData($branch_cd ,$target_column = '*'){
		global $wpdb;
	
		$result = $wpdb->get_results(
						$wpdb->prepare(' SELECT '.$target_column.' FROM '.$wpdb->prefix.'salon_branch WHERE branch_cd = %d AND delete_flg <> %d ',$branch_cd,Salon_Reservation_Status::DELETED),
						ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		if ($result) {
			return $result[0];
		}
		else {
			return false;
		}
	}

	static function getAllSalesData($target_day_from = null,$target_day_to = null,$target_branch_cd = null){
		global $wpdb;
		$where = '';
		if (empty($target_day_from) ) $target_day_from = Salon_Component::computeDate(-1);
		if (empty($target_day_to) ) $target_day_to = Salon_Component::computeDate(1);
		
		$sql = 			'SELECT '.
						' rs.reservation_cd ,'.
						' DATE_FORMAT(rs.time_from,"'.__('%%m/%%d/%%Y',SL_DOMAIN).'") as target_day,'.
						' rs.user_login,'.
						' rs.non_regist_name as name,'.
						' rs.non_regist_email as email,'.
						' rs.non_regist_tel as tel, '.
						' rs.non_regist_activate_key , '.
						' DATE_FORMAT(rs.time_from, "%%H:%%i")  as time_from_bef,'.
						' DATE_FORMAT(rs.time_to, "%%H:%%i")   as time_to_bef,'.
						' CONCAT (DATE_FORMAT(rs.time_from,"'.__('%%m/%%d/%%Y',SL_DOMAIN).'")," ",DATE_FORMAT(rs.time_from, "%%H:%%i"),"-",DATE_FORMAT(rs.time_to, "%%H:%%i")) as reserved_time,'.
						' rs.branch_cd,'.
						' rs.staff_cd as staff_cd_bef,'.
	/*
						' st1.name as staff_name_bef,'.
	*/
						' rs.item_cds as item_cds_bef,'.
						' rs.remark as remark_bef,'.
						' DATE_FORMAT(sa.time_from, "%%H:%%i") as time_from_aft,'.
						' DATE_FORMAT(sa.time_to, "%%H:%%i")   as time_to_aft,'.
						' sa.staff_cd as staff_cd_aft,'.
	/*
						' st2.name as staff_name_aft,'.
	*/
						' sa.item_cds as item_cds_aft,'.
						' sa.remark as remark,'.
						' sa.price, '.
						' rs.status as rstatus_cd'.
						' FROM (SELECT * FROM '.$wpdb->prefix.'salon_reservation WHERE delete_flg <> %d ) rs '.
	/*
						' INNER JOIN '.$wpdb->prefix.'salon_staff st1'.
						' ON rs.staff_cd = st1.staff_cd'.
	*/
						' LEFT  JOIN '.$wpdb->prefix.'salon_sales sa'.
						' ON rs.reservation_cd = sa.reservation_cd'.
	/*
						' LEFT  JOIN '.$wpdb->prefix.'salon_staff st2'.
						' ON sa.staff_cd = st2.staff_cd '.
	*/
						' WHERE rs.time_from >= %s '.
						' AND rs.time_to <= %s '.
						' AND rs.branch_cd = %d '.
						' ORDER BY target_day,rs.time_from';
		$edit_sql = $wpdb->prepare($sql,Salon_Reservation_Status::DELETED,$target_day_from,$target_day_to,$target_branch_cd);
		$result = $wpdb->get_results($edit_sql,ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $result;
	}

	static function getTargetSalesData($reservation_cd){	
		global $wpdb;
		$where = '';
		if (empty($target_day) ) $target_day = Salon_Component::computeDate(-1);
		
		$sql = 			'SELECT '.
						' rs.reservation_cd ,'.
						' DATE_FORMAT(rs.time_from,"'.__('%%m/%%d/%%Y',SL_DOMAIN).'") as target_day,'.
						' rs.user_login,'.
						' rs.non_regist_name as name,'.
						' rs.non_regist_email as email,'.
						' rs.non_regist_tel as tel, '.
						' rs.non_regist_activate_key, '.
						' DATE_FORMAT(rs.time_from, "%%H:%%i")  as time_from_bef,'.
						' DATE_FORMAT(rs.time_to, "%%H:%%i")   as time_to_bef,'.
						' CONCAT (DATE_FORMAT(rs.time_from,"'.__('%%m/%%d/%%Y',SL_DOMAIN).'")," ",DATE_FORMAT(rs.time_from, "%%H:%%i"),"-",DATE_FORMAT(rs.time_to, "%%H:%%i")) as reserved_time,'.
						' rs.branch_cd,'.
						' rs.staff_cd as staff_cd_bef,'.
	/*
						' st1.name as staff_name_bef,'.
	*/
						' rs.item_cds as item_cds_bef,'.
						' rs.remark as remark_bef,'.
						' DATE_FORMAT(sa.time_from, "%%H:%%i") as time_from_aft,'.
						' DATE_FORMAT(sa.time_to, "%%H:%%i")   as time_to_aft,'.
						' sa.staff_cd as staff_cd_aft,'.
	/*
						' st2.name as staff_name_aft,'.
	*/
						' sa.item_cds as item_cds_aft,'.
						' sa.remark as remark,'.
						' sa.price,'.
						' rs.status as rstatus_cd'.
						' FROM '.$wpdb->prefix.'salon_reservation rs '.
	/*
						' INNER JOIN '.$wpdb->prefix.'salon_staff st1'.
						' ON rs.staff_cd = st1.staff_cd'.
	*/
						' LEFT  JOIN '.$wpdb->prefix.'salon_sales sa'.
						' ON rs.reservation_cd = sa.reservation_cd'.
	/*
						' LEFT  JOIN '.$wpdb->prefix.'salon_staff st2'.
						' ON sa.staff_cd = st2.staff_cd '.
	*/
						' WHERE rs.reservation_cd = %d ';
		$result = $wpdb->get_results($wpdb->prepare($sql,$reservation_cd),ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		
		return $result;
	
	
	}
	

	//テーブルにはひとつのキー項目がautoincrementで定義されている前提
	//$set_dataにはキー項目は設定していない
	//$set_attrにはそれぞれの項目の属性が%s,%d,%fで入っている
	public function insertSql($table_name,$set_data,$set_attr) {
		global $wpdb;
		$sql = ' INSERT INTO '.$wpdb->prefix.$table_name.' ( ';
		//最後の２カラムはinsertとupdate
		$val = ' VALUES ('.$set_attr.',%s,%s)';
		
		foreach ( $set_data as $k1 => $d1 ) {
			$sql .= $k1.',';
		}
		//最後の２カラムはinsertとupdate
		$sql .= 'insert_time,update_time)';
		$current_time = date_i18n('Y-m-d H:i:s');
		$set_data['insert_time'] = $current_time;
		$set_data['update_time'] = $current_time;
		
		$sql = $sql.$val;
		$exec_sql = $wpdb->prepare($sql,$set_data);
		$result = $wpdb->query($exec_sql);

		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		$save_id = mysql_insert_id();
		if ((defined ( 'SALON_DEMO' ) && SALON_DEMO   ) || ($this->config['SALON_CONFIG_LOG'] == Salon_Config::LOG_NEED )) {
			$this->_writeLog($exec_sql);
		}
		//intの前提
		return $save_id;
	}
	
	private function _writeLog($setdata) {
		
		global $wpdb;
		$current_time = date_i18n('Y-m-d H:i:s');
		$sql = 'INSERT INTO '.$wpdb->prefix.'salon_log'.
				' (`sql`,remark,insert_time ) '.
				' VALUES  (%s,%s,%s) ';
		$result = $wpdb->query($wpdb->prepare($sql,$setdata,$_SERVER['REMOTE_ADDR'].':'.$_SERVER['HTTP_REFERER'].':'.$this->getUserLogin( ).':'.$_SERVER['HTTP_USER_AGENT'] ,$current_time));
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		
	}
	
	public function updateSql($table_name,$set_string,$where_string,$set_data) {
		global $wpdb;
		
		$sql = 	' UPDATE '.$wpdb->prefix.$table_name.
				' SET '.$set_string.
				' WHERE '.$where_string ;

		$exec_sql = $wpdb->prepare($sql,$set_data);
		$result = $wpdb->query($exec_sql);
		
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		if ((defined ( 'SALON_DEMO' ) && SALON_DEMO   ) || ($this->config['SALON_CONFIG_LOG'] == Salon_Config::LOG_NEED )) {
			$this->_writeLog($exec_sql);
		}
//		if (!$result) {
//			throw new Exception(Salon_Component::getMsg('E901',$wpdb->last_query));
//		}
		return true;
	}

	//

	public function deleteSql($table_name,$where_string,$set_data) {
		global $wpdb;
		
		$sql = 	' DELETE FROM '.$wpdb->prefix.$table_name.
				' WHERE '.$where_string ;
				
		$exec_sql = $wpdb->prepare($sql,$set_data);
		$result = $wpdb->query($exec_sql);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		if ((defined ( 'SALON_DEMO' ) && SALON_DEMO   ) || ($this->config['SALON_CONFIG_LOG'] == Salon_Config::LOG_NEED )) {
			$this->_writeLog($exec_sql);
		}
//		if (!$result) {
//			throw new Exception(Salon_Component::getMsg('E901',$wpdb->last_query));
//		}
		return true;
	}

	public function getUserName($user_login){
		if (empty($user_login) ) {
			$user_login = $this->getUserLogin();
		}
		if ($this->config['SALON_CONFIG_NAME_ORDER'] == Salon_Config::NAME_ORDER_JAPAN ) {
			$name_order = 'um2.meta_value," " ,um1.meta_value';
		}
		else {
			$name_order = 'um1.meta_value," " ,um2.meta_value';
		}
		global $wpdb;
		$sql = 	' SELECT concat('.$name_order.') as name '.
				' FROM '.$wpdb->prefix.'users us  '.
				' INNER JOIN '.$wpdb->prefix.'usermeta um1  '.
				'       ON    us.ID = um1.user_id AND um1.meta_key ="first_name" '.
				' INNER JOIN '.$wpdb->prefix.'usermeta um2  '.
				'       ON    us.ID = um2.user_id AND um2.meta_key ="last_name" '.
				' WHERE us.user_login = %s ';
		$result = $wpdb->get_results($wpdb->prepare($sql,$user_login),ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $result[0]['name'];
	}

	public function getUserLogin( ) {
		if (is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			return $current_user->user_login;
		}
		else {
			return '';
		}
	}
	
	public function getUserPass($user_login) {
		if (empty($user_login )) return '';
		global $wpdb;
		$sql = ' SELECT notes FROM '.$wpdb->prefix.'salon_customer '.
				' WHERE user_login = %s ';
		$result = $wpdb->get_results($wpdb->prepare($sql,$user_login),ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		if (count($result) == 0 ) return '';
		return $result[0]['notes'];
	}

	public function getBracnCdbyCurrentUser($user_login = null){
		if (empty($user_login) ) $user_login = $this->getUserLogin();
		if (empty($user_login) ){
			return '';
		}
		global $wpdb;
		$sql = ' SELECT branch_cd FROM '.$wpdb->prefix.'salon_staff '.
				' WHERE user_login = %s ';
		$result = $wpdb->get_results($wpdb->prepare($sql,$user_login),ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $result[0]['branch_cd'];
	}

	

	public function isSalonAdmin($user_login,&$role = false){	
		if (!is_null($this->isAdmin)) return  $this->isAdmin;

		$this->isAdmin = false;

		if (empty($user_login) ) $user_login = $this->getUserLogin();
		if (empty($user_login) ){
			return false;
		}
		
		global $wpdb;
		$sql = $wpdb->prepare(' SELECT st.position_cd as position_cd,role FROM '.
				$wpdb->prefix.'salon_staff st '.
				' INNER JOIN '.$wpdb->prefix.'salon_position po '.
				' ON st.position_cd = po.position_cd '.
				' WHERE user_login = %s '.
				'   AND st.delete_flg  <> %d ',$user_login,Salon_Reservation_Status::DELETED);
		if ($wpdb->query($sql) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		if ($result) {
			$show_menu =  explode(",",$result[0]['role']);
			if (! $role ) $role = $show_menu;
			if (in_array('edit_admin',$show_menu) || $result[0]['position_cd'] == self::SALON_MAINTENANCE) 	$this->isAdmin = true;
		}
		return $this->isAdmin;
	}
	
	

	public function setUserId ($user_datas) {
		global $wpdb;
		
		if (empty($user_datas['ID']) ) {

			$user_login  = $user_datas['user_login'];
			
			$sql = $wpdb->prepare('SELECT count(*) as cnt'.
					' FROM '.$wpdb->prefix.'users '.
					' WHERE user_login = %s ',$user_login);

			if ($wpdb->query($sql) === false ) {
				$this->_dbAccessAbnormalEnd();
			}
			else {
				$result = $wpdb->get_results($sql,ARRAY_A);
			}
			if ($result[0]['cnt'] <> 0 ) {
				throw new Exception(Salon_Component::getMsg('W003',""),1);
			}
			if (empty($user_datas['mail']) ){
				if (!empty($user_datas['tel']) ) {
					$user_datas['mail'] = str_replace('-','',$user_datas['tel']).self::SALON_DUMMY_DOMAIN;
				}
				else {
					if (!empty($user_datas['mobile']) ) {
						$user_datas['mail'] = str_replace('-','',$user_datas['mobile']).self::SALON_DUMMY_DOMAIN;
					}
					else {
						throw new Exception((Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__)));				
					}
				}
			}
			
			$user_id = wp_create_user( $user_datas['user_login'], $user_datas['user_login'], $user_datas['mail'] );
			if ( ! empty($user_id->errors) ) {
				throw new Exception(Salon_Component::getMsg('W004',""),1);
			}
		}
		else {
			if (empty($user_datas['mail']) || empty($user_datas['user_login']) ) {
				throw new Exception((Salon_Component::getMsg('E901',$edit_msg)));				
			}
			$user_id = intval($user_datas['ID']);
			$set_data_user['ID'] = $user_id;
			$set_data_user['user_email'] =  $user_datas['mail'];
			wp_update_user($set_data_user);
		}
		//update_user_metaはなければ追加する仕様
		update_user_meta( $user_id, 'zip',$user_datas['zip']);
		update_user_meta( $user_id, 'address',$user_datas['address']);
		update_user_meta( $user_id, 'tel',$user_datas['tel']);
		update_user_meta( $user_id, 'mobile',$user_datas['mobile']);
		update_user_meta( $user_id, 'first_name',$user_datas['first_name']);
		update_user_meta( $user_id, 'last_name',$user_datas['last_name']);
		//positionコードにより値を設定する
		$role = $this->_getRoleByPosition(intval($user_datas['position_cd']));
		global $wpdb;
		update_user_meta( $user_id, $wpdb->prefix.'capabilities',array($role=>"1") );
		return $user_id;

	}
	
	public function updateWpUser($set_value) {
		if ( update_user_meta( $set_value['ID'], $set_value['meta'],$set_value['value']) === false ) {
			$add_char = sprintf("update_user_meta: ID[%d] meta[%s] val[%s]",$set_value['ID'], $set_value['meta'],$set_value['value']);
			throw new Exception(Salon_Component::getMsg('E901',$add_char));				
		}
	}

	protected function _getRoleByPosition($position_cd){	
	
		global $wpdb;
		$where = ' WHERE delete_flg <> '.Salon_Reservation_Status::DELETED.
				 ' AND   position_cd = %d ';
		$sql = ' SELECT wp_role FROM '.$wpdb->prefix.'salon_position '.$where;
		$result = $wpdb->get_results($wpdb->prepare($sql,$position_cd),ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		if ($result) {
			return $result[0]['wp_role'];
		}
		else {
			return 'subscriber';
		}
	}


	public function registCustomer($branch_cd, $mail ,	$tel,$name,$remark = '',$zip = '',$address='',$mobile='',$is_regist = false,$is_rand = true) {
		if (empty($remark) ) $remark = __('not registered',SL_DOMAIN);
		if (empty($zip) ) $zip = __('not registered',SL_DOMAIN);
		if (empty($address) ) $address = __('not registered',SL_DOMAIN);
		if (empty($mobile) ) $mobile = __('not registered',SL_DOMAIN);

		//mailかtelはOR条件で必須
		global $wpdb;
		
		$edit_name = explode(self::SALON_NAME_DELIMITER, Salon_Component::zenSp2han($name));
		if ($this->config['SALON_CONFIG_NAME_ORDER'] == Salon_Config::NAME_ORDER_JAPAN ) {
			$last_name = $edit_name[0];
			$first_name = $edit_name[1];
		}
		else {
			$last_name = $edit_name[1];
			$first_name = $edit_name[0];
		}

		$user_login = '';
		$result = $this->checkUserlogin($mail,$tel,$name,$user_login);


		if (count($result) > 0 ) {
			foreach ($result as $k1 => $d1 ) {
				$edit[$d1['meta_key']] = $d1['meta_value'];
			}
			$err_item = '';
			if ($mail !== $result[0]['user_email']) {
				$err_item = sprintf(__('mail',SL_DOMAIN).__(' old[%s] new[%s]',SL_DOMAIN),$result[0]['user_email'],$mail);
			}
			if ($tel !== $edit['tel'] ){
				$err_item = sprintf(__('tel',SL_DOMAIN).__(' old[%s] new[%s]',SL_DOMAIN),$edit['tel'],$tel);
			}
			
			if ($this->config['SALON_CONFIG_NAME_ORDER'] == Salon_Config::NAME_ORDER_JAPAN ) {
				$wk_name = trim($edit['last_name'].' '.$edit['first_name']);
			}
			else {
				$wk_name = trim($edit['first_name'].' '.$edit['last_name']);
			}
			if ($name != $wk_name ){
				$err_item = sprintf(__('name',SL_DOMAIN).__(' old[%s] new[%s]',SL_DOMAIN),$wk_name,$name);
			}
			if ( $err_item != '') {
				throw new Exception(Salon_Component::getMsg('E006',$err_item));
			}
			return $user_login;
		}
	
		if (! $is_regist  )	return $branch_cd.':'.$mail.':'.$tel;
		$pass = $user_login;
		if ($is_rand ) 	$pass = substr(md5(uniqid(mt_rand(),1)),0,10);
		$user_id = wp_create_user( $user_login, $pass, $mail );
		if ( ! empty($user_id->errors) ) {
			$edit_msg = '';
			foreach ($user_id->errors as $k1 => $d1 ) {
				$edit_msg = $d1[0].'('.$k1.')';
			}
			throw new Exception(Salon_Component::getMsg('E903',$edit_msg));
		}
		update_user_meta( $user_id, 'tel',$tel);
		update_user_meta( $user_id, 'zip',$zip);
		update_user_meta( $user_id, 'address',$address);
		update_user_meta( $user_id, 'mobile',$mobile);
		update_user_meta( $user_id, 'first_name',$first_name);
		update_user_meta( $user_id, 'last_name',$last_name);
		update_user_meta( $user_id, $wpdb->prefix.'capabilities',array('subscriber'=>"1"));
		
		$set_data['ID'] = $user_id;
		$set_data['user_login'] = $user_login;
		$set_data['branch_cd'] = $branch_cd;
		$set_data['remark'] = $remark;
		$set_data['memo'] = '';
		$set_data['notes'] = $pass;
		$set_data['photo'] = '';
		
		$customer_cd = $this->insertSql('salon_customer ',$set_data,'%d,%s,%d,%s,%s,%s,%s');
		if ($customer_cd === false ) {
			throw new Exception(Salon_Component::getMsg('E902',array($wpdb->last_error,$wpdb->last_query)));
		}
		
		$this->sendMailRegist($first_name,$last_name,$mail,$user_login,$pass,$branch_cd);		
		return $user_login;	
		
	}


	public function sendMailRegist($first_name,$last_name,$mail,$user_login,$pass,$branch_cd) {
		if (strpos($mail,self::SALON_DUMMY_DOMAIN) !== false) return;
		
		$branch_datas = $this->getBranchData($branch_cd);
		$to = $mail;
		$subject = $this->getConfigData('SALON_CONFIG_SEND_MAIL_SUBJECT_USER');
		$message = $this->_create_body($first_name,$last_name,$user_login,$pass,$branch_datas);

		$header = $this->getConfigData('SALON_CONFIG_SEND_MAIL_FROM');	
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

	public function setReturnPath( $phpmailer ) {
		$path = $this->getConfigData('SALON_CONFIG_SEND_MAIL_RETURN_PATH');
		if (empty($path)) return;
		$phpmailer->Sender = $path;
	}

	private function _create_body($first_name,$last_name,$user_login,$pass,$branch_datas) {
		if ($this->config['SALON_CONFIG_NAME_ORDER'] == Salon_Config::NAME_ORDER_JAPAN ) {
			$name = $last_name.' '.$first_name;
		}
		else {
			$name = $first_name.' '.$last_name;
		}
		$send_mail_text = $this->getConfigData('SALON_CONFIG_SEND_MAIL_TEXT_USER');
		


		$body = '<body>'.$send_mail_text.'</body>';


		$body = str_replace('{X-TO_NAME}',htmlspecialchars($name,ENT_QUOTES),$body);
		$body = str_replace('{X-USER_ID}',$user_login,$body);
		$body = str_replace('{X-PASSWORD}',$pass,$body);
		
		
		$body = str_replace('{X-SHOP_NAME}',htmlspecialchars($branch_datas['name'],ENT_QUOTES),$body);
		$body = str_replace('{X-SHOP_ADDRESS}',htmlspecialchars($branch_datas['address'],ENT_QUOTES),$body);
		$body = str_replace('{X-SHOP_TEL}',htmlspecialchars($branch_datas['tel'],ENT_QUOTES),$body);
		$body = str_replace('{X-SHOP_MAIL}',htmlspecialchars($branch_datas['mail'],ENT_QUOTES),$body);
		
		$body = Salon_Component::writeMailHeader().nl2br($body);

		return $body;
	}




	public function checkUserlogin(&$mail,$tel,$name,&$user_login){	
		//mailかtelはOR条件で必須
		//該当のuser_loginの存在を確認する。
		global $wpdb;
	
		if (empty($mail) ) 	{
			$user_login = str_replace('-','',$tel);
			$mail = $user_login.self::SALON_DUMMY_DOMAIN;
		}
		else {
			$user_login = $mail;
		}
	
		$sql = 'SELECT us.user_login,us.user_email,um.* '.
				' FROM '.$wpdb->prefix.'users us  '.
				' INNER JOIN '.$wpdb->prefix.'usermeta um  '.
				'       ON    us.ID = um.user_id '.
				' WHERE us.user_login = %s ';
		$result = $wpdb->get_results($wpdb->prepare($sql,$user_login),ARRAY_A);
	
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		
		return $result;
		
	}

	public function getUserAllInf(){
		global $wpdb;
		$sql = 'SELECT ID,user_login,user_email,meta_key,meta_value '.
				' FROM '.$wpdb->prefix.'users us  '.
				' INNER JOIN '.$wpdb->prefix.'usermeta um  '.
				'       ON    us.ID = um.user_id '.
				' WHERE '.
				'      (meta_key = "first_name" OR '.
				'       meta_key = "last_name"  OR '.
				'       meta_key = "address"  OR '.
				'       meta_key = "tel"        OR '.
				'       meta_key = "mobile" OR '.
				'       meta_key = "'.$wpdb->prefix.'capabilities" ) '.
				' ORDER BY ID';
		$result = $wpdb->get_results($sql,ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		$edit = array();
		//ログインID単位の配列に変換する。
		for ($i =0; $i < count($result) ; $i++ ) {
			$edit[$result[$i]['user_login']][$result[$i]['meta_key']] = $result[$i]['meta_value'];
			//[TODO]冗長？
			$edit[$result[$i]['user_login']]['ID'] = $result[$i]['ID'];
			$edit[$result[$i]['user_login']]['mail'] = $result[$i]['user_email'];
		}
		
		return $edit;
	}

	public function countReservation($staff_cd ,$in_time,$out_time,$reservation_cd = '' ) {
		global $wpdb;
		$where = '';
		if (!empty($staff_cd) ) $where = $wpdb->prepare('AND staff_cd = %d ',$staff_cd);
		if (empty($reservation_cd)) {
			$exec_sql =	$wpdb->prepare(
						' SELECT  '.
						' count(*) as cnt '.
						' FROM '.$wpdb->prefix.'salon_reservation '.
						'   WHERE ((time_from < %s AND %s <= time_to )'.
						'		OR (time_from <= %s AND %s < time_to ) )'.
						$where.
						'     AND delete_flg <> %d ',
						$out_time,$out_time,$in_time,$in_time,Salon_Reservation_Status::DELETED);
		}
		else {
			$exec_sql =	$wpdb->prepare(
						' SELECT  '.
						' count(*) as cnt '.
						' FROM '.$wpdb->prefix.'salon_reservation '.
						'   WHERE ((time_from < %s AND %s <= time_to )'.
						'		OR (time_from <= %s AND %s < time_to ) )'.
						$where.
						'     AND delete_flg <> %d '.
						'     AND reservation_cd <> %d ',
						$out_time,$out_time,$in_time,$in_time,Salon_Reservation_Status::DELETED,$reservation_cd);
		}
		$result = $wpdb->get_results($exec_sql,ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $result[0]['cnt'];
	}
	//photo from
	//$idsはphoto_idをカンマ区切りで設定する。
	public function getPhotoData($ids) {
		$result = array();
		if (! empty($ids) ) {
			global $wpdb;
			$result = $wpdb->get_results('SELECT photo_id,photo_name,photo_path,photo_resize_path FROM '.$wpdb->prefix.'salon_photo '.
										' WHERE photo_id in ('.$ids.')  AND delete_flg <> '.Salon_Reservation_Status::DELETED,ARRAY_A);
			if ($result === false ) {
				$this->_dbAccessAbnormalEnd();
			}
		}
		$photo_result = array();
		if (count($result) > 0) {
			$edit_result = array();
			foreach ($result as $k1 => $d1) {
				$edit_result[$d1['photo_id']] = $d1;
			}
			$seq = explode(",",$ids);
			for($i = 0;$i<count($seq);$i++) {
				if (array_key_exists($seq[$i],$edit_result) )
					$photo_result[] = $edit_result[$seq[$i]];
				//テーブルのデータを直接削除する以外ないはず
				else 
					$photo_result[] = array('photo_id' => $seq[$i] ,'photo_name' => 'NO IMAGE', 'photo_path' => '','photo_resize_path' => '');				
			}
		}
		return $photo_result;
	}

	public function availablePhotoData($ids) {
		global $wpdb;
		$exec_sql =	$wpdb->prepare(
					' UPDATE  '.
					$wpdb->prefix.'salon_photo '.
					'  SET delete_flg = '.Salon_Reservation_Status::INIT.
					'   WHERE photo_id in (%s) ',$ids);
		$result = $wpdb->query($exec_sql);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		if ((defined ( 'SALON_DEMO' ) && SALON_DEMO   ) || ($this->config['SALON_CONFIG_LOG'] == Salon_Config::LOG_NEED )) {
			$this->_writeLog($exec_sql);
		}
	}

	public function getPhotoDataForDelete($photo_id){
		global $wpdb;
		$result = $wpdb->get_results(' SELECT photo_path,photo_resize_path FROM '.$wpdb->prefix.'salon_photo where delete_flg <> '.Salon_Reservation_Status::DELETED.' AND photo_id = '.$photo_id,ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}

		return $result;
	}

	public function deletePhotoData($photo_id) {
		$res = $this->getPhotoDataForDelete($photo_id);
		if (count($res) == 0 ) {
			throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),__('NO PHOTO DATA',SL_DOMAIN));
		}
		$files = array($res[0]['photo_path'],$res[0]['photo_resize_path']);
		foreach ($files as $d1) {
			if ( ! unlink(SALON_UPLOAD_DIR.basename($d1)) ) {
				throw new Exception(Salon_Component::getMsg('E901',basename(__FILE__).':'.__LINE__),__('PHOTO DATA CAN\'T DELETE',SL_DOMAIN));
			}
		}
	}

	public function insertPhotoData ($photo_id,$target_file_name,$target_width=100,$target_height=100){
		global $wpdb;
		//項目の増減がありので、とりあえずINSERTして必要なファイル名のみupdateする
		$sql = ' INSERT INTO '.$wpdb->prefix.'salon_photo '
				.' (photo_name,photo_path,photo_resize_path,width,height,delete_flg,insert_time,update_time )'
				.' SELECT photo_name,photo_path,photo_resize_path,width,height,delete_flg,insert_time,update_time FROM '.$wpdb->prefix.'salon_photo '
				.'  WHERE photo_id = %d ';

		$exec_sql = $wpdb->prepare($sql,$photo_id);
		$result = $wpdb->query($exec_sql);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		$new_photo_id = mysql_insert_id();
		if ((defined ( 'SALON_DEMO' ) && SALON_DEMO   ) || ($this->config['SALON_CONFIG_LOG'] == Salon_Config::LOG_NEED )) {
			$this->_writeLog($exec_sql);
		}

		$set_string = 	' photo_path = %s , '.
						' photo_resize_path = %s , '.
						' insert_time = %s , '.
						' update_time = %s ';
												
		$set_data_temp = array(
						SALON_UPLOAD_URL.$target_file_name,
						SALON_UPLOAD_URL.$target_width."_".$target_height."_".$target_file_name,
						date_i18n('Y-m-d H:i:s'),
						date_i18n('Y-m-d H:i:s'),
						$new_photo_id);
		$where_string = ' photo_id = %d ';
		if ( $this->updateSql('salon_photo',$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return $new_photo_id;
	}


	public function deletePhotoDatas ($photo_ids){
		if (empty($photo_ids) )  return;
		global $wpdb;
		$set_string = 	' delete_flg = %s , '.
						' update_time = %s ';
												
		$set_data_temp = array(
						Salon_Reservation_Status::DELETED,
						date_i18n('Y-m-d H:i:s'));
		$where_string = ' photo_id IN ('.$photo_ids.') ';
		if ( $this->updateSql('salon_photo',$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		$sql = ' SELECT photo_path,photo_resize_path FROM '.$wpdb->prefix.'salon_photo '
				.'  WHERE photo_id IN ('.$photo_ids.') ';

		$result = $wpdb->get_results($sql,ARRAY_A);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		foreach ($result as  $d1) {
			$files = array($d1['photo_path'],$d1['photo_resize_path']);
			foreach ($files as $d2) {
				if ( ! @unlink(SALON_UPLOAD_DIR.basename($d2)) ) {
					//エラーを返しても消えたのがどうなるわけでもないのでログに書き留めておく
					//throw new Exception(Salon_Component::getMsg('E901',__('PHOTO DATA CAN\'T DELETE',SL_DOMAIN).' -> '.SALON_UPLOAD_DIR.basename($d2).' '.basename(__FILE__).':'.__LINE__));
					error_log(Salon_Component::getMsg('E901',__('PHOTO DATA CAN\'T DELETE',SL_DOMAIN).' -> '.SALON_UPLOAD_DIR.basename($d2).' '.basename(__FILE__).':'.__LINE__).' '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');

					
				}
			}
		}
	}
	
	public function fixedPhoto($type,$new_photo_ids,$old_photo_ids = "") {
		if (empty($new_photo_ids) )  return;
		//仮登録と仮削除を確定する→ＮＧ
		//仮登録を確定する
		global $wpdb;
		$sql = ' UPDATE '.$wpdb->prefix.'salon_photo '
				.' SET delete_flg = %d '
				.'  WHERE photo_id in ( '.$new_photo_ids.' ) AND delete_flg = %d ';
		
		$exec_sql = $wpdb->prepare($sql,Salon_Reservation_Status::INIT,Salon_Reservation_Status::TEMPORARY);
		$result = $wpdb->query($exec_sql);
		if ($result === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		if ((defined ( 'SALON_DEMO' ) && SALON_DEMO   ) || ($this->config['SALON_CONFIG_LOG'] == Salon_Config::LOG_NEED )) {
			$this->_writeLog($exec_sql);
		}
		if ( $type == "updated" &&  !empty($old_photo_ids)) {
			//更新前にあって、更新後にないＩＤを消す
			$old_array = explode(',',$old_photo_ids);
			$new_array = explode(',',$new_photo_ids);
			$del_array = array();
			foreach($old_array as $d1) {
				if (!in_array($d1,$new_array) ) $del_array[] = $d1;
			}
			if (count($del_array) > 0 ) {
				$this->deletePhotoDatas(implode(',',$del_array));
			}
		}
	}
	

	//[photo to]

//[2013/11/10]Ver 1.3.1 
	public function getMaxDisplaySequence ($table_name) {
		$cnt = 0;
		global $wpdb;
		$sql = 'SELECT max(display_sequence) as max_seq FROM '.$wpdb->prefix.$table_name.' where delete_flg <> '.Salon_Reservation_Status::DELETED;
		if ($wpdb->query($sql) === false  ) {
			$this->_dbAccessAbnormalEnd();
		}
		else {
			$result = $wpdb->get_results($sql,ARRAY_A);
		}
		if ($result) {
			$cnt = $result[0]['max_seq'];
		}
		return $cnt;
	}
	
	public function updateSeq($table_data,$tale_name,$key_name ) {
		foreach ($table_data as $k1 => $d1) {
			$set_string = 	'display_sequence = %d , '.
							' update_time = %s ';
															
			$set_data_temp = array($d1,
							date_i18n('Y-m-d H:i:s'),
							$k1);
			$where_string = $key_name.' = %d ';
			if ( $this->updateSql("salon_".$tale_name,$set_string,$where_string,$set_data_temp) === false ) {
				$this->_dbAccessAbnormalEnd();
			}
		}
	}


	public function getConfigData ($target = null) {
		if (empty($target) ) return $this->config;
		return @$this->config[$target];
	}

	
	public function setConfigData ($table_data) {
		foreach ($table_data as $k1 => $d1 ) {
			$this->config[$k1] = $d1;
		}
		update_option('SALON_CONFIG',serialize($this->config));
		update_option('SALON_CONFIG_BRANCH',$this->config['SALON_CONFIG_BRANCH']);
		
		$this->_writeLog(serialize($this->config));

	}


	

	public function _dbAccessAbnormalEnd () {
		global $wpdb;
		throw new Exception(Salon_Component::getMsg('E902',array($wpdb->last_error,$wpdb->last_query)) );
	}
	
}