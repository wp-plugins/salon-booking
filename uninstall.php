<?php

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
    exit();
	

function salon_delete_plugin() {
	global $wpdb;

	delete_option( 'salon_holiday' );

	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_reservation" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_sales" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_customer" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_branch" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_staff" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_working" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_position" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_item" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."salon_log" );

	$id = get_option('salon_confirm_page_id');
	if (! empty($id)  ){
		if (wp_delete_post( $id, true ) === false) var_export('delete post error ID:'.$id);
		
	}
	delete_option('salon_confirm_page_id');
	delete_option( 'salon_installed' );
	delete_option( 'SALON_CONFIG' );
	delete_option( 'SALON_CONFIG_BRANCH' );
	delete_option( 'salon_initial_user' );

}

salon_delete_plugin();
