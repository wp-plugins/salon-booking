<?php
/*
Plugin Name: Salon booking 
Plugin URI: http://salon.mallory.jp/en
Description: Salon Booking enables the reservation to one-on-one business between a client and a staff. 
Version: 1.3.1
Author: kuu
Author URI: http://salon.mallory.jp/en
*/

define( 'SL_DOMAIN', 'salon' );
define( 'SL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SL_PLUGIN_NAME', trim( dirname( SL_PLUGIN_BASENAME ), '/' ) );
define( 'SL_PLUGIN_DIR', plugin_dir_path(__FILE__)  );
define( 'SL_PLUGIN_SRC_DIR', SL_PLUGIN_DIR . 'src/' );
define( 'SL_PLUGIN_URL',  plugin_dir_url(__FILE__) );
define( 'SL_PLUGIN_SRC_URL', SL_PLUGIN_URL . '/' . 'src' );
define( 'SL_LOG_DIR', '../../' );

define('SALON_JS_DIR', '/booking/');
define('SALON_CSS_DIR', '/booking/');
define('SALON_PHP_DIR', '/booking/');

define( 'SALON_DEMO', false);


define( 'SALON_MAX_FILE_SIZE', 10 );	//１０メガまでUPLOAD
define( 'SALON_UPLOAD_DIR_NAME','uploads'.DIRECTORY_SEPARATOR);
define( 'SALON_UPLOAD_DIR', SL_PLUGIN_DIR . SALON_UPLOAD_DIR_NAME);
define( 'SALON_UPLOAD_URL', SL_PLUGIN_URL .DIRECTORY_SEPARATOR. SALON_UPLOAD_DIR_NAME);
define( 'SALON_COLORBOX_SIZE', '80%');


$salon_booking = new Salon_Booking();

class Salon_Booking {
	
	private $config_branch;
	
	
	private $maintenance = '';
	private $management = '';
	
	private $user_role = '';
	
	public function __construct() {

		add_action('init', array( &$this, 'init_session_start'));
		
		$this->maintenance = SL_DOMAIN.'-maintenace';
		$this->management = SL_DOMAIN.'-management';

		require_once(SL_PLUGIN_SRC_DIR.'comp/salon-component.php');
		$this->config_branch = get_option( 'SALON_CONFIG_BRANCH', Salon_Config::ONLY_BRANCH );
//		require_once(SL_PLUGIN_DIR.'/salon-installer.php');
		register_activation_hook(__FILE__, array( &$this, 'salon_install'));
		load_plugin_textdomain( SL_DOMAIN, SL_PLUGIN_DIR.'/lang', SL_PLUGIN_NAME.'/lang' );

		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_javascript' ) );			
		add_filter( 'get_pages', array( &$this, 'get_pages' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'front_javascript' ) );			
		add_shortcode('salon-booking', array( &$this, 'salon_booking_shortcode'));
		add_shortcode('salon-confirm', array( &$this,'salon_booking_confirm'));


		add_action('salon_daily_event', array( &$this, 'daily_action'));
		register_deactivation_hook(__FILE__, array( &$this, 'salon_deactivation'));

		add_filter('user_contactmethods',array( &$this,'update_profile_fields'),10,1);
		
		add_action('wp_ajax_staff', array( &$this,'edit_staff')); 
		add_action('wp_ajax_basic', array( &$this,'edit_base')); 
		add_action('wp_ajax_branch', array( &$this,'edit_branch')); 
		add_action('wp_ajax_booking', array( &$this,'edit_booking')); 
		add_action('wp_ajax_config', array( &$this,'edit_config')); 
		add_action('wp_ajax_confirm', array( &$this,'edit_confirm')); 
		add_action('wp_ajax_customer', array( &$this,'edit_customer')); 
		add_action('wp_ajax_download', array( &$this,'edit_download')); 
		add_action('wp_ajax_item', array( &$this,'edit_item')); 
		add_action('wp_ajax_position', array( &$this,'edit_position')); 
		add_action('wp_ajax_reservation', array( &$this,'edit_reservation')); 
		add_action('wp_ajax_sales', array( &$this,'edit_sales')); 
		add_action('wp_ajax_search', array( &$this,'edit_search')); 
		add_action('wp_ajax_working', array( &$this,'edit_working')); 
		add_action('wp_ajax_log', array( &$this,'edit_log')); 
		add_action('wp_ajax_photo', array( &$this,'edit_photo')); 

		add_action('wp_ajax_nopriv_booking', array( &$this,'edit_booking')); 
		add_action('wp_ajax_nopriv_confirm', array( &$this,'edit_confirm')); 
		add_action('wp_ajax_nopriv_search', array( &$this,'edit_search')); 


		if (SALON_DEMO ) {		
			add_action( 'admin_bar_menu',  array( &$this,'remove_admin_bar_menu'), 201 );
			add_action('admin_head',  array( &$this,'my_admin_head'));
			add_action('wp_before_admin_bar_render',  array( &$this,'add_new_item_in_admin_bar'));
			add_action('wp_dashboard_setup', array( &$this,'example_remove_dashboard_widgets'));
		}


	}
	
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/ デモ	
// 管理バーの項目を非表示
public function remove_admin_bar_menu( $wp_admin_bar ) {
	if (SALON_DEMO && strtolower($this->user_role) != 'administrator') {		
		$wp_admin_bar->remove_menu('wp-logo');			// ロゴ
		$wp_admin_bar->remove_menu('site-name');		// サイト名
		$wp_admin_bar->remove_menu('view-site');		// サイト名 -> サイトを表示
		$wp_admin_bar->remove_menu('comments');			// コメント
		$wp_admin_bar->remove_menu('new-content');		// 新規
		$wp_admin_bar->remove_menu('new-post');			// 新規 -> 投稿
		$wp_admin_bar->remove_menu('new-media');		// 新規 -> メディア
		$wp_admin_bar->remove_menu('new-link');			// 新規 -> リンク
		$wp_admin_bar->remove_menu('new-page');			// 新規 -> 固定ページ
		$wp_admin_bar->remove_menu('new-user');			// 新規 -> ユーザー
		$wp_admin_bar->remove_menu('updates');			// 更新
		$wp_admin_bar->remove_menu('my-account');		// マイアカウント
		$wp_admin_bar->remove_menu('user-info');		// マイアカウント -> プロフィール
		$wp_admin_bar->remove_menu('edit-profile');		// マイアカウント -> プロフィール編集
		$wp_admin_bar->remove_menu('logout');			// マイアカウント -> ログアウト
	}
}
// 管理バーのヘルプメニューを非表示にする
public function my_admin_head(){
	if (SALON_DEMO && strtolower($this->user_role) != 'administrator')
		 echo '<style type="text/css">#contextual-help-link-wrap{display:none;}</style>';
}

public function add_new_item_in_admin_bar() {
	if (SALON_DEMO && strtolower($this->user_role) != 'administrator') {		
		global $wp_admin_bar;
		$wp_admin_bar->add_menu(array(
		'id' => 'new_item_in_admin_bar',
		'title' => __('Log Out'),
		'href' => wp_logout_url()
		));
	}
}
public function example_remove_dashboard_widgets() {
	if (SALON_DEMO && strtolower($this->user_role) != 'administrator') {		
		global $wp_meta_boxes;
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);		// 現在の状況
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);	// 最近のコメント
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);	// 被リンク
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);			// プラグイン
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);		// クイック投稿
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);		// 最近の下書き
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);			// WordPressブログ
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);			// WordPressフォーラム
	}
}
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/ デモ	




	public function daily_action() {
		error_log('daily_action start  '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
		$this->_sl_daily__delete_temporary_sql();
		error_log('daily_action temporary_data_delete  '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
		$this->_sl_daily_action_sql();
	}

	private function _sl_daily__delete_temporary_sql () {
		global $wpdb;
		$target = Salon_Component::computeDate(-1);
		//photodata
		$this->_delete_temp_photo_data($target);
		$current_time = date_i18n('Y-m-d H:i:s');
		$sql = 'SELECT * FROM '.$wpdb->prefix.'salon_reservation WHERE update_time < %s AND status = %d AND delete_flg <> %d';
		$edit_sql = $wpdb->prepare($sql,$target,Salon_Reservation_Status::TEMPORARY,Salon_Reservation_Status::DELETED);
		$result = $wpdb->get_results($edit_sql,ARRAY_A);
		if ($result === false ) {
			error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
			return;
		}
		if (count($result) == 0 ) {
			error_log('temporary no delete data '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
			return;
		}
		else {
			error_log('_/_/_/ delete data _/_/_/'.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
			$tmp = array();
			foreach ($result as $k1 => $d1 ) {
				$tmp[] = $d1['reservation_cd'];
			}
			error_log('data -> '.implode(',',$tmp)."\n", 3, ABSPATH.'/'.date('Y').'.txt');
		}
		$sql = 'UPDATE '.$wpdb->prefix.'salon_reservation SET delete_flg = %d, update_time = %s WHERE update_time < %s AND status = %d AND delete_flg <> %d';
		$edit_sql = $wpdb->prepare($sql,Salon_Reservation_Status::DELETED,$current_time,$target,Salon_Reservation_Status::TEMPORARY,Salon_Reservation_Status::DELETED);
		if ($wpdb->query($edit_sql) === false ) {
			error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
			return;
		}
		$current_time = date_i18n('Y-m-d H:i:s');
		$sql = 'INSERT INTO '.$wpdb->prefix.'salon_log  (`sql`,remark,insert_time ) VALUES  (%s,%s,%s) ';
		$result = $wpdb->query($wpdb->prepare($sql,$edit_sql,__FILE__.' '.__FUNCTION__,$current_time));
	}

	private function _delete_temp_photo_data($target) {
		global $wpdb;
		$sql = 'SELECT  photo_path,photo_resize_path,photo_id FROM '.$wpdb->prefix.'salon_photo WHERE update_time < %s AND delete_flg = %d';
		$edit_sql = $wpdb->prepare($sql,$target,Salon_Reservation_Status::TEMPORARY);
		$result = $wpdb->get_results($edit_sql,ARRAY_A);
		if ($result === false ) {
			error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
			return;
		}
		//これは削除もれがあった場合のみ有効なコード。確定前にF5とかか
		error_log('_/_/_/ photo delete data start _/_/_/'.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
		$sql = 'DELETE FROM '.$wpdb->prefix.'salon_photo  WHERE update_time < %s AND delete_flg = %d';
		$edit_sql = $wpdb->prepare($sql,$target,Salon_Reservation_Status::DELETED);
		if ($wpdb->query($edit_sql) === false ) {
			error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
			return;
		}
		foreach ($result as $k1 => $d1 ) {
			
			error_log($d1['photo_id'].' '.$d1['photo_path'].' '.$d1['photo_resize_path'].date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
			if ( ! unlink(SALON_UPLOAD_DIR.basename($d1['photo_path'])) ) {
				error_log('delete error:'.SALON_UPLOAD_DIR.basename($d1['photo_path']).' '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
			}
			if ( ! unlink(SALON_UPLOAD_DIR.basename($d1['photo_resize_path'])) ) {
				error_log('delete error:'.SALON_UPLOAD_DIR.basename($d1['photo_resize_path']).' '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
			}
			$sql = 'UPDATE '.$wpdb->prefix.'salon_photo SET delete_flg = %d WHERE photo_id = %d';
			$edit_sql = $wpdb->prepare($sql,Salon_Reservation_Status::DELETED,$d1['photo_id']);
			if ($wpdb->query($edit_sql) === false ) {
				error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
				return;
			}
		}
		
	}
	
	private function _sl_daily_action_sql () {
		$result =  unserialize(get_option( 'SALON_CONFIG'));
		if (empty($result['SALON_CONFIG_DELETE_RECORD']) ) $result['SALON_CONFIG_DELETE_RECORD'] =  Salon_Config::DELETE_RECORD_NO;
		if ($result['SALON_CONFIG_DELETE_RECORD'] ==   Salon_Config::DELETE_RECORD_NO ) return;
		if (empty($result['SALON_CONFIG_DELETE_RECORD_PERIOD']) ) $result['SALON_CONFIG_DELETE_RECORD_PERIOD'] =  Salon_Config::DELETE_RECORD_PERIOD;
		$from = Salon_Component::computeMonth(-1*$result['SALON_CONFIG_DELETE_RECORD_PERIOD']);
		global $wpdb;
		$sql = 'SELECT * FROM '.$wpdb->prefix.'salon_reservation WHERE update_time < %s ';
		$edit_sql = $wpdb->prepare($sql,$from);
		$result = $wpdb->get_results($edit_sql,ARRAY_A);
		if ($result === false ) {
			error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
			return;
		}
		if (count($result) == 0 ) {
			error_log($table_name.' no delete data '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
			return;
		}
		else {
			error_log('_/_/_/ '.$table_name.' update mask data _/_/_/'.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
			$tmp = array();
			foreach ($result as $k1 => $d1 ) {
				$tmp[] = $d1['reservation_cd'];
			}
			error_log('data -> '.implode(',',$tmp)."\n", 3, ABSPATH.'/'.date('Y').'.txt');
		}
		
		$sql = 'UPDATE '.$wpdb->prefix.'salon_reservation SET non_regist_name = "" , non_regist_email= "" , non_regist_tel= "" WHERE update_time < %s ';
		$edit_sql = $wpdb->prepare($sql,$from);
		if ($wpdb->query($edit_sql) === false ) {
			error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
			return;
		}
		$sql = 'DELETE FROM '.$wpdb->prefix.'salon_log  WHERE insert_time < %s ';
		$edit_sql = $wpdb->prepare($sql,$from);
		if ($wpdb->query($edit_sql) === false ) {
			error_log('sql error:'.$wpdb->last_error.$wpdb->last_query.' '.date_i18n('Y-m-d H:i:s')."\n", 3, ABSPATH.'/'.date('Y').'.txt');
			return;
		}
		$current_time = date_i18n('Y-m-d H:i:s');
		$sql = 'INSERT INTO '.$wpdb->prefix.'salon_log  (`sql`,remark,insert_time ) VALUES  (%s,%s,%s) ';
		$result = $wpdb->query($wpdb->prepare($sql,$edit_sql,__FILE__.' '.__FUNCTION__,$current_time));
		
	}


	public function init_session_start(){
		session_start();
	}

	public function update_profile_fields( $contactmethods ) {
//		unset($contactmethods['aim']);
//		unset($contactmethods['jabber']);
//		unset($contactmethods['yim']);
		
		if(!array_key_exists('zip', $contactmethods)) $contactmethods['zip']= __('zip',SL_DOMAIN);
		if(!array_key_exists('address', $contactmethods))$contactmethods['address']= __('address',SL_DOMAIN);
		if(!array_key_exists('tel', $contactmethods))$contactmethods['tel']= __('tel',SL_DOMAIN);
		if(!array_key_exists('mobile', $contactmethods))$contactmethods['mobile']= __('mobile',SL_DOMAIN);

		return $contactmethods;
	}


	public function is_multi_branch (){
		return $this->config_branch == Salon_Config::MULTI_BRANCH;
	}

	public function get_default_brandh_cd (){
		return SALON_CONFIG_DEFAULT_BRANCH_CD;
	}
	
	
	function get_pages( $pages ) {
		$confirm_page_id =  get_option('salon_confirm_page_id');
		for ( $i = 0; $i < count($pages); $i++ ) {
			if ( !empty($pages[$i]->ID) && $pages[$i]->ID == $confirm_page_id  )
				unset( $pages[$i] );
		}
		
		return $pages;
	}

	public function admin_init() {
		
		global $plugin_page;
		if ( ! isset( $plugin_page ) || ( substr($plugin_page,0,5)  !=	'salon' )) return;
		remove_action( 'admin_notices', 'update_nag', 3 );

		
	}
	

	private function _get_userdata (&$user_role) {
		$edit_menu = array();
		global $current_user;
		get_currentuserinfo();
		$user_roles = $current_user->roles;
		$user_role = array_shift($user_roles);
		if ($user_role == 'subscriber' ) return;
		global $wpdb;
		$sql =  'SELECT role FROM '.$wpdb->prefix.'salon_position po ,'.
								$wpdb->prefix.'salon_staff st '.
						' WHERE st.user_login = %s '.
						'   AND st.position_cd = po.position_cd ';
		$result = $wpdb->get_results(
					$wpdb->prepare($sql,$current_user->user_login),ARRAY_A);
		$show_menu =  explode(",",$result[0]['role']);
		if (in_array('edit_customer',$show_menu) ) $edit_menu[$this->maintenance][] = 'edit_customer';
		if (in_array('edit_item',$show_menu) ) $edit_menu[$this->maintenance][] = 'edit_item';
		if (in_array('edit_staff',$show_menu) ) $edit_menu[$this->maintenance][] = 'edit_staff';
		if (in_array('edit_branch',$show_menu) ) $edit_menu[$this->maintenance][] = 'edit_branch';
		if (in_array('edit_config',$show_menu) ) $edit_menu[$this->maintenance][] = 'edit_config';
		if (in_array('edit_position',$show_menu) )  $edit_menu[$this->maintenance][] = 'edit_position';
		if (in_array('edit_log',$show_menu) )  $edit_menu[$this->maintenance][] = 'edit_log';
		if (in_array('edit_reservation',$show_menu) ) $edit_menu[$this->management][] = 'edit_reservation';
		if (in_array('edit_sales',$show_menu) ) $edit_menu[$this->management][] = 'edit_sales';
		if (in_array('edit_working',$show_menu) ) $edit_menu[$this->management][] = 'edit_working';
		if (in_array('edit_working_all',$show_menu) ) $edit_menu[$this->management][] = 'edit_working';
		if (in_array('edit_base',$show_menu) ) $edit_menu[$this->management][] = 'edit_base';
		return $edit_menu;
	}
	

	
	public function admin_menu() {
//		$user_role = '';
		$show_menu = $this->_get_userdata($this->user_role);
		if ($show_menu[$this->maintenance] && count($show_menu[$this->maintenance]) > 0 ) {
			add_menu_page( __('Salon Maintenance',SL_DOMAIN), __('Salon Maintenance',SL_DOMAIN), 'level_1', $this->maintenance, array( &$this,$show_menu[$this->maintenance][0]),WP_PLUGIN_URL.'/salon-booking/images/menu-icon.png' );
			if (in_array('edit_customer',$show_menu[$this->maintenance]) ) {
				$file = $show_menu[$this->maintenance][0] == 'edit_customer' ? $this->maintenance : 'salon_customer';
				add_submenu_page(  $this->maintenance, __('Customer Info',SL_DOMAIN), __('Customer Info',SL_DOMAIN), 'level_1', $file, array( &$this, 'edit_customer' ) );
			}
			if (in_array('edit_item',$show_menu[$this->maintenance]) ) {
				$file = $show_menu[$this->maintenance][0] == 'edit_item' ? $this->maintenance : 'salon_item';
				add_submenu_page(  $this->maintenance, __('Menu Information',SL_DOMAIN), __('Menu Information',SL_DOMAIN), 'level_1', $file, array( &$this, 'edit_item' ) );
			}
			if (in_array('edit_staff',$show_menu[$this->maintenance]) ) {
				$file = $show_menu[$this->maintenance][0] == 'edit_staff' ? $this->maintenance : 'salon_staff';
				add_submenu_page(  $this->maintenance, __('Staff Information',SL_DOMAIN), __('Staff Information',SL_DOMAIN), 'level_1', $file, array( &$this, 'edit_staff' ) );
			}
			if (in_array('edit_branch',$show_menu[$this->maintenance]) &&  $this->is_multi_branch() )  {
				$file = $show_menu[$this->maintenance][0] == 'edit_branch' ? $this->maintenance : 'salon_branch';
					add_submenu_page( $this->maintenance, __('Shop Information',SL_DOMAIN), __('Shop Information',SL_DOMAIN), 'level_1', $file, array( &$this, 'edit_branch' ) );
			}
			if (in_array('edit_config',$show_menu[$this->maintenance]) ) {
				$file = $show_menu[$this->maintenance][0] == 'edit_config' ? $this->maintenance : 'salon_config';
				add_submenu_page(  $this->maintenance, __('Environment Setting',SL_DOMAIN), __('Environment Setting',SL_DOMAIN), 'level_1', $file, array( &$this, 'edit_config' ) );
			}
			if (in_array('edit_position',$show_menu[$this->maintenance]) )  {
				$file = $show_menu[$this->maintenance][0] == 'edit_position' ? $this->maintenance : 'salon_position';
				add_submenu_page(  $this->maintenance, __('Position Information',SL_DOMAIN), __('Position Information',SL_DOMAIN), 'level_1', $file, array( &$this, 'edit_position' ) );
			}
			if (in_array('edit_log',$show_menu[$this->maintenance]) )  {
				$file = $show_menu[$this->maintenance][0] == 'edit_log' ? $this->maintenance : 'salon_log';
				add_submenu_page(  $this->maintenance, __('View Log',SL_DOMAIN), __('View Log',SL_DOMAIN), 'level_1', $file, array( &$this, 'edit_log' ) );
			}


		}
		if ($show_menu[$this->management] && count($show_menu[$this->management]) > 0 ) {
			add_menu_page( __('Salon Management',SL_DOMAIN), __('Salon Management',SL_DOMAIN), 'level_1', $this->management, array( &$this,$show_menu[$this->management][0] ),WP_PLUGIN_URL.'/salon-booking/images/menu-icon.png');
			if (in_array('edit_reservation',$show_menu[$this->management]) ) {
				$file = $show_menu[$this->management][0] == 'edit_reservation' ? $this->management : 'salon_reservation';
				add_submenu_page( $this->management, __('Reservation Regist',SL_DOMAIN), __('Reservation Regist',SL_DOMAIN), 'level_1', $file, array( &$this, 'edit_reservation' ) );
			}
			if (in_array('edit_sales',$show_menu[$this->management]) ) {
				$file = $show_menu[$this->management][0] == 'edit_sales' ? $this->management : 'salon_sales';
				add_submenu_page( $this->management, __('Performance Regist',SL_DOMAIN), __('Performance Regist',SL_DOMAIN), 'level_1', $file, array( &$this, 'edit_sales' ) );
			}
			if (in_array('edit_working',$show_menu[$this->management]) ) {
				$file = $show_menu[$this->management][0] == 'edit_working' ? $this->management : 'salon_working';
				add_submenu_page( $this->management, __('Time Card',SL_DOMAIN), __('Time Card',SL_DOMAIN), 'level_1', $file, array( &$this, 'edit_working' ) );
			}
			if (in_array('edit_base',$show_menu[$this->management]) ) {
				$file = $show_menu[$this->management][0] == 'edit_base' ? $this->management : 'salon_basic';
				add_submenu_page( $this->management, __('Basic Information',SL_DOMAIN), __('Basic Information',SL_DOMAIN), 'level_1', $file, array( &$this, 'edit_base' ) );
			}
		}

		if (SALON_DEMO && strtolower($this->user_role) != 'administrator') {		
			global $menu;
			unset($menu[2]);//ダッシュボード
			unset($menu[4]);//メニューの線1
			unset($menu[5]);//ｐｏｓｔ
			unset($menu[10]);//メディア
			unset($menu[15]);//リンク
			unset($menu[20]);//ページ
			unset($menu[25]);//コメント
			unset($menu[59]);//メニューの線2
			unset($menu[60]);//テーマ
			unset($menu[65]);//プラグイン
			unset($menu[70]);//プロファイル
			unset($menu[75]);//ツール
			unset($menu[80]);//設定
			unset($menu[90]);//メニューの線3		
		}


	}
	

	public function edit_config() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/config-control.php' );
	}
	public function edit_branch() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/branch-control.php' );
	}
	public function edit_staff() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/staff-control.php' );
	}
	public function edit_position() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/position-control.php' );
	}
	public function edit_item() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/item-control.php' );
	}
	public function edit_customer() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/customer-control.php' );
	}
	public function edit_sales() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/sales-control.php' );
	}
	public function edit_reservation() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/reservation-control.php' );
	}
	public function edit_working() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/working-control.php' );
	}
	public function edit_base() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/basic-control.php' );
	}
	public function edit_booking() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/booking-control.php' );
	}
	public function edit_log() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/log-control.php' );
	}
	public function edit_confirm() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/confirm-control.php' );
	}
	public function edit_download() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/download-control.php' );
	}
	public function edit_search() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/search-control.php' );
	}
	public function edit_photo() {
		require_once( SL_PLUGIN_SRC_DIR.'/control/photo-control.php' );
	}

	public function admin_javascript($hook_suffix) {
		global $plugin_page;
		if ( ! isset( $plugin_page ) || ( substr($plugin_page,0,5)  !=	'salon' )) return;
		wp_enqueue_script( 'jquery');		
		wp_enqueue_script('thickbox');
		wp_enqueue_script( 'jquery-ui-datepicker');
		wp_enqueue_script( 'flexigrid', SL_PLUGIN_URL.'js/flexigrid.js',array( 'jquery' ) );
		wp_enqueue_script( 'edit', SL_PLUGIN_URL.'js/jquery.jeditable.js',array( 'jquery' ) );
		wp_enqueue_script( 'dataTables', SL_PLUGIN_URL.'js/jquery.dataTables.js',array( 'jquery' ) );
		wp_enqueue_script( 'dataTables_plugin1', SL_PLUGIN_URL.'js/fnReloadAjax.js',array( 'dataTables' ) );
		wp_enqueue_script( 'jsonparse', SL_PLUGIN_URL.'js/jquery.json-2.4.min.js',array( 'jquery' ) );
		wp_enqueue_script( 'dateformat', SL_PLUGIN_URL.'js/jquery.dateFormat.js',array( 'jquery' ) );
		
		
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style('flexigrid', SL_PLUGIN_URL.'css/flexigrid.css');
		wp_enqueue_style('dataTables', SL_PLUGIN_URL.'css/dataTables.css');
		wp_enqueue_style('salon', SL_PLUGIN_URL.'css/salon.css');
		
		if ($plugin_page == 'salon_working' ) {
			wp_enqueue_script( 'dhtmlxscheduler', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler.js',array( 'jquery' ) );
			if (defined ( 'WPLANG' ) && file_exists(SL_PLUGIN_DIR.SALON_JS_DIR.'locale_'.WPLANG.'.js') ) 
				wp_enqueue_script( 'dhtmlxscheduler_locale', SL_PLUGIN_URL.SALON_JS_DIR.'locale_'.WPLANG.'.js',array( 'dhtmlxscheduler' ) );
			wp_enqueue_script( 'dhtmlxscheduler_limit', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler_limit.js',array( 'dhtmlxscheduler' ) );
			wp_enqueue_script( 'dhtmlxscheduler_collision', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler_collision.js',array( 'dhtmlxscheduler' ) );
			wp_enqueue_script( 'dhtmlxscheduler_key_nav', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler_key_nav.js',array( 'dhtmlxscheduler' ) );
		}
		if ($plugin_page == 'salon_staff' ) {
			wp_enqueue_script( 'jquery-ui-sortable');
			wp_enqueue_script( 'colorbox', SL_PLUGIN_URL.'js/jquery.colorbox-min.js',array( 'jquery' ) );
			wp_enqueue_style('colorbox', SL_PLUGIN_URL.'css/colorbox.css');
			wp_enqueue_script( 'dropzone', SL_PLUGIN_URL.'js/dropzone.min.js',array( 'jquery' ) );
			wp_enqueue_style('dropzone', SL_PLUGIN_URL.'css/dropzone.css');
		}
	}
	
	public function front_javascript() {
		wp_enqueue_script( 'jquery');		
		wp_enqueue_script( 'colorbox', SL_PLUGIN_URL.'js/jquery.colorbox-min.js',array( 'jquery' ) );
		wp_enqueue_style('colorbox', SL_PLUGIN_URL.'css/colorbox.css');
		wp_enqueue_style('salon', SL_PLUGIN_URL.'css/salon.css');
		wp_enqueue_script( 'dhtmlxscheduler', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler.js',array( 'jquery' ) );

		if (defined ( 'WPLANG' ) && file_exists(SL_PLUGIN_DIR.SALON_JS_DIR.'locale_'.WPLANG.'.js') ) 
			wp_enqueue_script( 'dhtmlxscheduler_locale', SL_PLUGIN_URL.SALON_JS_DIR.'locale_'.WPLANG.'.js',array( 'dhtmlxscheduler' ) );
		wp_enqueue_script( 'dhtmlxscheduler_limit', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler_limit.js',array( 'dhtmlxscheduler' ) );
		wp_enqueue_script( 'dhtmlxscheduler_timeline', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler_timeline.js',array( 'dhtmlxscheduler' ) );
		wp_enqueue_script( 'dhtmlxscheduler_collision', SL_PLUGIN_URL.SALON_JS_DIR.'dhtmlxscheduler_collision.js',array( 'dhtmlxscheduler' ) );
	}

	public function salon_booking_shortcode($atts) {
		extract(shortcode_atts(array('branch_cd' => '1'), $atts));
		require_once(SL_PLUGIN_SRC_DIR.'/control/booking-control.php');
	
	}


	public function salon_booking_confirm($atts) {
		require_once(SL_PLUGIN_SRC_DIR.'/control/confirm-control.php');
	
	}
	
	function _isExixtColumn($table_name ,$column_name){
		global $wpdb;
		$sql = "show columns from ".$wpdb->prefix.$table_name;
		$columns = $wpdb->get_results($sql,ARRAY_A);
		foreach ($columns as $k1 => $d1 ) {
			if ($d1['Field'] == $column_name ) return true;
		}
		return false;
	}
	
	function salon_install(){
		
		if (!get_option('salon_confirm_page_id') ) {
			$post = array(
				'ID' => '' 	//[ <投稿 ID> ] // 既存の投稿を更新する場合。
				,'menu_order' => 999 //[ <順序値> ] // 追加する投稿が固定ページの場合、ページの並び順を番号で指定できます。
				,'comment_status' => 'closed'	//[ 'closed' | 'open' ] // 'closed' はコメントを閉じます。
				,'ping_status' => 'closed' //[ 'closed' | 'open' ] // 'closed' はピンバック／トラックバックをオフにします。
				,'pinged' => '' //[ ? ] // ピンバック済。
				,'post_author' => '' //[ <user ID> ] // 作成者のユーザー ID。
				,'post_content' => '[salon-confirm]' //[ <投稿の本文> ] // 投稿の全文。
				,'post_date' => date_i18n('Y-m-d H:i:s') //[ Y-m-d H:i:s ] // 投稿の作成日時。
				,'post_date_gmt' => gmdate('Y-m-d H:i:s') //[ Y-m-d H:i:s ] // 投稿の作成日時（GMT）。
				,'post_excerpt' => '' //[ <抜粋> ] // 投稿の抜粋。
				,'post_name' => ''	//[ <スラッグ名> ] // 投稿スラッグ。
				,'post_parent' => 0	//[ <投稿 ID> ] // 親投稿の ID。
				,'post_password' => '' //[ <投稿パスワード> ] // 投稿の閲覧時にパスワードが必要になります。
				,'post_status' => 'publish' //[ 'draft' | 'publish' | 'pending'| 'future' ] // 公開ステータス。 
				,'post_title' => __('Reservation Confirm',SL_DOMAIN)	//[ <タイトル> ] // 投稿のタイトル。
				,'post_type' => 'page' //[ 'post' | 'page' ] // 投稿タイプ名。
				,'tags_input' => '' //[ '<タグ>, <タグ>, <...>' ] // 投稿タグ。
				,'to_ping' => ''	//[ ? ] //?
			); 
			
			$id = wp_insert_post( $post );
			update_option('salon_confirm_page_id', $id);
		}
		
		global $wpdb;
		$current = date_i18n('Y-m-d H:i:s');

		//ver 1.2.1 From
		$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_photo (
			`photo_id`		INT NOT NULL AUTO_INCREMENT,
			`photo_name`		varchar(255) default NULL,
			`photo_path`		varchar(255) default NULL,
			`photo_resize_path`		varchar(255) default NULL,
			`width`			INT NOT NULL default '0',
			`height`			INT NOT NULL default '0',
			`delete_flg` tinyint NOT NULL default '0',
			`insert_time` DATETIME,
			`update_time` DATETIME,
			UNIQUE  (`photo_id`)
		 ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");


		//ver 1.2.1 To


		if (get_option('salon_installed') ) {
			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."salon_position SET role = 'edit_customer,edit_item,edit_staff,edit_branch,edit_config,edit_position,edit_reservation,edit_sales,edit_working,edit_base,edit_admin,edit_booking,edit_working_all,edit_log',update_time = %s WHERE position_cd = %d",$current,Salon_Position::MAINTENANCE));
			
			
			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix."salon_staff SET photo = null,update_time = %s WHERE photo LIKE %s ",$current,'%:%'));
			
			//ver 1.3.1 
			if (! $this->_isExixtColumn("salon_item","display_sequence") ) {
				$wpdb->query("ALTER TABLE ".$wpdb->prefix."salon_item ADD `display_sequence` INT NOT NULL DEFAULT '0' AFTER `notes` ");
				//IDと同じ値を設定しとく
				$wpdb->query("UPDATE ".$wpdb->prefix."salon_item SET  display_sequence = item_cd ");
				
			}
			
		}
		else {
			//status 会員の場合は、Icomplete
			//       会員でない場合は、INIT→メールでactivate→complete
			//[TODO]nameとemailは最後は落とすか？。会員登録しない人のために残すか？
			//[TODO]済　item_cdは複数項目入っているので項目名を変更する。
			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_reservation (
								`reservation_cd`	INT not null  AUTO_INCREMENT,
								`branch_cd`		INT,
								`staff_cd`		INT,
								`user_login`		VARCHAR(60) default null,
								`non_regist_name`			VARCHAR(40),
								`non_regist_email`			VARCHAR(100),
								`non_regist_tel`		char(20) default null,
								`non_regist_activate_key`	VARCHAR(8),
								`time_from`		DATETIME,
								`time_to`		DATETIME,
								`item_cds`		VARCHAR(50),
								`status`		INT,
								`remark`		TEXT,
								`memo`			TEXT,
								`notes`			TEXT,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`reservation_cd`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	
			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_sales (
								`reservation_cd`	INT not null ,
								`branch_cd`		INT,
								`staff_cd`		INT,
								`customer_cd`	INT,
								`time_from`		DATETIME,
								`time_to`		DATETIME,
								`item_cds`		VARCHAR(50),
								`status`		INT,
								`remark`		TEXT,
								`memo`			TEXT,
								`notes`			TEXT,
								`price`		INT,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`reservation_cd`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	
			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_customer (
								`customer_cd`	INT not null AUTO_INCREMENT,
								`ID`		BIGINT,
								`user_login`		 	varchar(60) default null,
								`branch_cd`		INT,
								`remark`		TEXT,
								`memo`			TEXT,
								`notes`			TEXT,
								`photo`			TEXT default null,
								`is_send_mail`	INT default 1,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`customer_cd`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	
	
			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_branch (
								`branch_cd`		INT not null AUTO_INCREMENT,
								`name`			VARCHAR(40),
								`zip`			char(20),
								`address`			TEXT,
								`tel`			char(20),
								`mail`			char(50),
								`remark`		TEXT,
								`memo`			TEXT,
								`notes`			TEXT,
								`open_time`			char(4),							
								`close_time`			char(4),							
	/* 0-> Sun 1-> Mon ～ 6-> Sat c.f. 2,3  -> 火・水定休日　Javascriptのgetdayの値と合わせている */
								`closed`				char(15),								
								`sp_dates`			TEXT,
								`time_step`	INT default 15,
								`duplicate_cnt`	INT default 1,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`branch_cd`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	
			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_staff (
								`staff_cd`		INT not null AUTO_INCREMENT,
								`user_login`		 	varchar(60) default null,
								`branch_cd`		INT,
								`position_cd`		INT,
								`day_off`		char(15),								
								`remark`		TEXT,
								`memo`			TEXT,
								`notes`			TEXT,
								`employed_day`	DATETIME default null,
								`leaved_day`	DATETIME default null,
								`photo`			TEXT default null,
								`duplicate_cnt`	INT default 0,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`staff_cd`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	
	
			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_working (
								`staff_cd`		INT not null,
								`in_time`	DATETIME default null,
								`out_time`	DATETIME default null,
								`working_cds`	VARCHAR(50),
								`remark`		TEXT,
								`memo`			TEXT,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`staff_cd`,`in_time`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	
	
			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_position (
								`position_cd`		INT not null AUTO_INCREMENT,
								`name`			VARCHAR(40),
								`wp_role`			VARCHAR(40),
								`role`			VARCHAR(300),
								`remark`		TEXT,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
							  PRIMARY KEY  (`position_cd`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	
			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_item (
								`item_cd`		INT not null AUTO_INCREMENT,
								`name`			TEXT,
								`branch_cd`		INT,
								`short_name`	TEXT,
								`minute`		INT,
								`price`			INT,
								`photo`			TEXT default null,
								`remark`		TEXT,
								`memo`			TEXT,
								`notes`			TEXT,
								`display_sequence`		INT,
								`delete_flg`	INT default 0,
								`insert_time`	DATETIME,
								`update_time`	DATETIME,
								PRIMARY KEY (`item_cd`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	
			$wpdb->query("	CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."salon_log (
								`no`		INT not null AUTO_INCREMENT,
								`sql`			TEXT,
								`remark`		TEXT,
								`insert_time`	DATETIME,
							  PRIMARY KEY  (`no`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	

			
			$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_branch VALUES (".Salon_Default::BRANCH_CD.",'".__('SAMPLE SHOP NAME',SL_DOMAIN)."','100-0001','".__('SAMPLE SHOOP ADDRESS',SL_DOMAIN)."','223456789','mail@1.com','REMARK','MEMO','NOTES','1000','1900','2','',30,1,0,%s,%s);",$current,$current));
			$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_item VALUES (1,'".__('SAMPLE MENU CUT',SL_DOMAIN)."',".Salon_Default::BRANCH_CD.",'".__('SAMPLE MENU CUT',SL_DOMAIN)."',".__('30,50',SL_DOMAIN).",null,null,null,null,1,0,%s,%s);",$current,$current));
			$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_item VALUES (2,'".__('SAMPLE MENU PERM',SL_DOMAIN)."',".Salon_Default::BRANCH_CD.",'".__('SAMPLE MENU PERM',SL_DOMAIN)."',".__('90,100',SL_DOMAIN).",null,null,null,null,2,0,%s,%s);",$current,$current));
			//インストールしたユーザを割り当てる
			$current_user = wp_get_current_user();
			
			$zip = get_user_option('zip',$current_user->ID);
			if (empty($zip)) update_user_meta( $current_user->ID, 'zip',__('zip',SL_DOMAIN));
			$address = get_user_option('address',$current_user->ID); 
			if (empty($address)) update_user_meta( $current_user->ID, 'address',__('address',SL_DOMAIN));
			$tel = get_user_option('tel',$current_user->ID);
			if (empty($tel)) update_user_meta( $current_user->ID, 'tel',__('999-999-999',SL_DOMAIN));
			$mobile = get_user_option('mobile',$current_user->ID);
			if (empty($mobile)) update_user_meta( $current_user->ID, 'mobile',__('999-999-999',SL_DOMAIN));
	
			
			$staff_cd = $wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_staff (user_login,branch_cd,position_cd,remark,memo,notes,insert_time,update_time) VALUES ('".$current_user->user_login."',".Salon_Default::BRANCH_CD.",7,'remark','memo','notes',%s,%s);",$current,$current));  
					
			update_option('salon_initial_user', $staff_cd);
			if (defined ( 'SALON_DEMO' ) && SALON_DEMO   ) {
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (1,'".__('PRESIDENT',SL_DOMAIN)."','contributor','edit_customer,edit_item,edit_staff,edit_branch,edit_config,edit_position,edit_reservation,edit_sales,edit_working,edit_base,edit_admin,edit_booking,edit_working_all','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (2,'".__('DIRECTER',SL_DOMAIN)."','contributor','edit_customer,edit_item,edit_staff,edit_branch,edit_config,edit_position,edit_reservation,edit_sales,edit_working,edit_base,edit_admin,edit_booking,edit_working_all','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (3,'".__('SHOP MANAGER',SL_DOMAIN)."','contributor','edit_customer,edit_item,edit_staff,edit_reservation,edit_sales,edit_working,edit_base,edit_booking,edit_working_all','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (4,'".__('CHIEF',SL_DOMAIN)."','contributor','edit_customer,edit_reservation,edit_sales,edit_working,edit_booking','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (5,'".__('STAFF',SL_DOMAIN)."','contributor','edit_reservation,edit_sales,edit_working,edit_booking','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (6,'".__('TEMPORARY',SL_DOMAIN)."','contributor','edit_reservation,edit_sales','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (".Salon_Position::MAINTENANCE.",'".__('MAINTENANCE',SL_DOMAIN)."','administrator','edit_customer,edit_item,edit_staff,edit_branch,edit_config,edit_position,edit_reservation,edit_sales,edit_working,edit_base,edit_admin,edit_booking,edit_working_all','".__('this data can not delete or update',SL_DOMAIN)."',0,%s,%s);",$current,$current));
			}
			else {
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (1,'".__('PRESIDENT',SL_DOMAIN)."','administrator','edit_customer,edit_item,edit_staff,edit_branch,edit_config,edit_position,edit_reservation,edit_sales,edit_working,edit_base,edit_admin,edit_booking,edit_working_all','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (2,'".__('DIRECTER',SL_DOMAIN)."','administrator','edit_customer,edit_item,edit_staff,edit_branch,edit_config,edit_position,edit_reservation,edit_sales,edit_working,edit_base,edit_admin,edit_booking,edit_working_all','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (3,'".__('SHOP MANAGER',SL_DOMAIN)."','editor','edit_customer,edit_item,edit_staff,edit_reservation,edit_sales,edit_working,edit_base,edit_booking,edit_working_all','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (4,'".__('CHIEF',SL_DOMAIN)."','editor','edit_customer,edit_reservation,edit_sales,edit_working,edit_booking','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (5,'".__('STAFF',SL_DOMAIN)."','author','edit_reservation,edit_sales,edit_working,edit_booking','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (6,'".__('TEMPORARY',SL_DOMAIN)."','contributor','edit_reservation,edit_sales','',0,%s,%s);",$current,$current));
				$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."salon_position VALUES (".Salon_Position::MAINTENANCE.",'".__('MAINTENANCE',SL_DOMAIN)."','administrator','edit_customer,edit_item,edit_staff,edit_branch,edit_config,edit_position,edit_reservation,edit_sales,edit_working,edit_base,edit_admin,edit_booking,edit_working_all,edit_log','".__('this data can not delete or update',SL_DOMAIN)."',0,%s,%s);",$current,$current));
			}
					
			
			$holiday = '';
			if (defined ( 'WPLANG' ) && file_exists(SL_PLUGIN_DIR.'/lang/holiday-'.WPLANG.'.php') )require_once(SL_PLUGIN_DIR.'/lang/holiday-'.WPLANG.'.php');
			else require_once(SL_PLUGIN_DIR.'/lang/holiday.php');
			update_option('salon_holiday', serialize($holiday));
			
			update_option('salon_installed', 1);
		}
		wp_schedule_event( ceil( time() / 86400 ) * 86400 + ( 1 - get_option( 'gmt_offset' ) ) * 3600, 'daily', 'salon_daily_event' );
		
		
	}


	public function salon_deactivation() {
		wp_clear_scheduled_hook('salon_daily_event');
	}
	
	
	
}


?>