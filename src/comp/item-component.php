<?php

class Item_Component {
	
	private $version = '1.0';
	
	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}
	
	
	
	public function editTableData () {
		
		if ( $_POST['type'] == 'deleted' ) {
			$set_data['item_cd'] = intval($_POST['item_cd']);
		}
		else {
//[2013/11/10]Ver 1.3.1 
			if ($_POST['type'] == 'updated' ) 	{
				$set_data['item_cd'] = intval($_POST['item_cd']);
				$set_data['display_sequence'] = intval($_POST['display_sequence']);
			}
			else {
				$set_data['display_sequence'] = $this->datas->getMaxDisplaySequence('salon_item')+1;
			}

			$set_data['name'] = stripslashes($_POST['name']);
			$set_data['short_name'] = $_POST['short_name'];
			$set_data['branch_cd'] = intval($_POST['branch_cd']);
			$set_data['minute'] = intval($_POST['minute']);
			$set_data['price'] = intval($_POST['price']);
			$set_data['remark'] = stripslashes($_POST['remark']);
			$set_data['memo'] = '';
			$set_data['notes'] = '';

			$tmp = stripslashes($_POST['photo']);
			if ( strpos($tmp, 'class=\'lightbox\'') === false)	{
				$set_data['photo'] = preg_replace('/^<a(.*?)>(.*)$/','<a ${1} class=\'lightbox\' >${2}',$tmp);
			}
			else {
				$set_data['photo'] = $tmp;
			}
			
		}
		return $set_data;
		
	}
	

	public function editColumnData() {
		$column = array();
		$column[2]="name = %s ";
		$column[3]="branch_cd = %d ";
		$column[5]="price = %d ";
		$column[6]="remark = %s ";
		
		
		$set_data['column_name'] = $column[intval($_POST['column'])];
		$set_data['value'] = stripslashes($_POST['value']);
		$set_data['item_cd'] = intval($_POST['item_cd']);
		return $set_data;
	}
	
	public function editSeqData() {
		$keys = explode(',',$_POST['item_cd']);
		$values = explode(',',$_POST['value']);
		$set_data = array($keys[0] => $values[1],$keys[1] => $values[0]);
		return $set_data;
	}
	
}