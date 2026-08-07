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

// The plugin stores no options and creates no tables. Published mededelingen
// are intentionally left in the database so nothing is lost on uninstall.
