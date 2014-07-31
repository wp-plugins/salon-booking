<?php

class Mail_Component {
	
	private $version = '1.0';
	
	private $datas = null;

	public function __construct(&$datas) {
		$this->datas = $datas;
	}
	


	
	public function editTableData () {
		$set_data['SALON_CONFIG_SEND_MAIL_TEXT'] = stripslashes($_POST['config_mail_text']);
		$set_data['SALON_CONFIG_SEND_MAIL_TEXT_USER'] = stripslashes($_POST['config_mail_text_user']);
		$set_data['SALON_CONFIG_SEND_MAIL_FROM'] = stripslashes($_POST['config_mail_from']);
		$set_data['SALON_CONFIG_SEND_MAIL_RETURN_PATH'] = stripslashes($_POST['config_mail_returnPath']);

		return $set_data;
		
	}
	
	
}