<?php
/**
 * Uninstall script for Soli Mededelingen Plugin
 *
 * This file is executed when the plugin is deleted through the WordPress admin.
 *
 * @package Soli\Mededelingen
 */

// If uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// A second copy of this plugin may still be active - for instance when another
// version is installed alongside this one and this folder is the one being
// deleted. Removing shared data would break that active copy, so bail out.
$soli_mededelingen_active = (array) get_option( 'active_plugins', array() );
if ( is_multisite() ) {
	$soli_mededelingen_active = array_merge( $soli_mededelingen_active, array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) );
}
foreach ( $soli_mededelingen_active as $soli_mededelingen_active_file ) {
	if ( basename( $soli_mededelingen_active_file ) === 'wp-soli-mededelingen-plugin.php' && dirname( $soli_mededelingen_active_file ) !== basename( __DIR__ ) ) {
		return;
	}
}
unset( $soli_mededelingen_active, $soli_mededelingen_active_file );

// The plugin stores no options and creates no tables. Published mededelingen
// are intentionally left in the database so nothing is lost on uninstall.
