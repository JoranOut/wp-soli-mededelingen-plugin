<?php

namespace Soli\Mededelingen;

/*
  Plugin Name: Soli Mededelingen Plugin
  Version: 0.1.0
  Author: Joran Out
  Description: Members-only announcements (mededelingen) post type for Soli sites, usable in the Query Loop block
  Requires PHP: 8.2
  Text Domain: soli-mededelingen
  Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SOLI_MEDEDELINGEN__PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'SOLI_MEDEDELINGEN__PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SOLI_MEDEDELINGEN__PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'SOLI_MEDEDELINGEN__PLUGIN_VERSION', '0.1.0' );

require_once SOLI_MEDEDELINGEN__PLUGIN_DIR_PATH . 'includes/class-soli-mededelingen-post-type.php';
require_once SOLI_MEDEDELINGEN__PLUGIN_DIR_PATH . 'includes/class-soli-mededelingen-visibility.php';

add_action( 'init', function () {
	load_plugin_textdomain( 'soli-mededelingen', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	include_once 'updater.php';

	if ( ! defined( 'WP_GITHUB_FORCE_UPDATE' ) ) {
		define( 'WP_GITHUB_FORCE_UPDATE', true );
	}

	if ( is_admin() ) {
		$config = array(
			'slug'               => plugin_basename( __FILE__ ),
			'proper_folder_name' => dirname( plugin_basename( __FILE__ ) ),
			'api_url'            => 'https://api.github.com/repos/JoranOut/wp-soli-mededelingen-plugin',
			'raw_url'            => 'https://raw.githubusercontent.com/JoranOut/wp-soli-mededelingen-plugin/main',
			'github_url'         => 'https://github.com/JoranOut/wp-soli-mededelingen-plugin',
			// Fallback only. The updater resolves the real download from the GitHub
			// releases API and overrides this with the release's zip asset.
			'zip_url'            => 'https://github.com/JoranOut/wp-soli-mededelingen-plugin/releases/latest/download/wp-soli-mededelingen-plugin.zip',
			'sslverify'          => true,
			// Both ends of the supported range are rewritten at packaging time by
			// the nightly and release workflows, from the versions the e2e matrix
			// actually ran against: 'requires' from package.json's
			// wordpress.requiresAtLeast, 'tested' from wordpress.org's current
			// release. Do not reformat.
			'requires'           => '6.9',
			'tested'             => '7.0.4',
			'readme'             => 'readme.md',
		);

		new WP_GitHub_Updater( $config );
	}
} );

// Register the mededelingen post type and its Query Loop variation script
$soli_mededelingen_post_type = new Post_Type();
$soli_mededelingen_post_type->init();

// Keep mededelingen invisible to not-logged-in visitors
$soli_mededelingen_visibility = new Visibility();
$soli_mededelingen_visibility->init();
