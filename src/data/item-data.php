<?php

	require_once(SL_PLUGIN_SRC_DIR . 'data/salon-data.php');

	
class Item_Data extends Salon_Data {
	
	const TABLE_NAME = 'salon_item';
	
	function __construct() {
		parent::__construct();
	}
	

	public function insertTable ($table_data){
		$item_cd = $this->insertSql(self::TABLE_NAME,$table_data,'%s,%s,%d,%d,%d,%s,%s,%s,%s,%d');
		if ($item_cd === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		return $item_cd;
	}

	public function updateTable ($table_data){

		$set_string = 	' name = %s , '.
						' short_name = %s , '.
						' branch_cd = %d , '.
						' minute = %d , '.
						' price = %d , '.
						' remark =  %s , '.
						' memo =  %s , '.
						' photo =  %s , '.
						' display_sequence = %d , '.
						' update_time = %s ';
												
		$set_data_temp = array($table_data['name'],
						$table_data['short_name'],
						$table_data['branch_cd'],
						$table_data['minute'],
						$table_data['price'],
						$table_data['remark'],
						$table_data['memo'],
						$table_data['photo'],
						$table_data['display_sequence'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['item_cd']);
		$where_string = ' item_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	
	public function updateColumn($table_data){
		
		$set_string = 	$table_data['column_name'].' , '.
								' update_time = %s ';
														
		$set_data_temp = array($table_data['value'],
						date_i18n('Y-m-d H:i:s'),
						$table_data['item_cd']);
		$where_string = ' item_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false ) {
			$this->_dbAccessAbnormalEnd();
		}
		
		
	}

	public function deleteTable ($table_data){
		$set_string = 	' delete_flg = %d, update_time = %s  ';
		$set_data_temp = array(Salon_Reservation_Status::DELETED,
						date_i18n('Y-m-d H:i:s'),
						$table_data['item_cd']);
		$where_string = ' item_cd = %d ';
		if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false) {
			$this->_dbAccessAbnormalEnd();
		}
		return true;
	}
	

	public function getInitDatas() {
		return $this->getAllItemData();
	}
	
	public function updateSeq($table_data) {
		foreach ($table_data as $k1 => $d1) {
			$set_string = 	'display_sequence = %d , '.
							' update_time = %s ';
															
			$set_data_temp = array($d1,
							date_i18n('Y-m-d H:i:s'),
							$k1);
			$where_string = ' item_cd = %d ';
			if ( $this->updateSql(self::TABLE_NAME,$set_string,$where_string,$set_data_temp) === false ) {
				$this->_dbAccessAbnormalEnd();
			}
		}
	}

	
	
}