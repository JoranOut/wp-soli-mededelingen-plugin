<?php

namespace Soli\Mededelingen;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the soli_mededeling post type and the editor script that adds
 * the "Mededelingen" Query Loop variation.
 */
class Post_Type {

	const POST_TYPE = 'soli_mededeling';
	const REST_BASE = 'mededelingen';

	public function init() {
		add_action( 'init', array( $this, 'register_post_type' ), 0 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	public function register_post_type() {
		$labels = array(
			'name'               => __( 'Mededelingen', 'soli-mededelingen' ),
			'singular_name'      => __( 'Mededeling', 'soli-mededelingen' ),
			'add_new'            => __( 'Add Mededeling', 'soli-mededelingen' ),
			'add_new_item'       => __( 'Add New Mededeling', 'soli-mededelingen' ),
			'view_item'          => __( 'View Mededeling', 'soli-mededelingen' ),
			'edit_item'          => __( 'Edit Mededeling', 'soli-mededelingen' ),
			'insert_into_item'   => __( 'Insert into Mededeling', 'soli-mededelingen' ),
			'search_items'       => __( 'Search Mededelingen', 'soli-mededelingen' ),
			'not_found'          => __( 'No Mededelingen Found', 'soli-mededelingen' ),
		);

		$supports = array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' );

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Members-only announcements', 'soli-mededelingen' ),
			'supports'            => $supports,
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-megaphone',
			'can_export'          => true,
			'has_archive'         => true,
			// Not-logged-in visitors may never find mededelingen through site
			// search; the flag is evaluated per request so members still can.
			'exclude_from_search' => ! is_user_logged_in(),
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'rewrite'             => array( 'slug' => 'mededelingen' ),
			'show_in_rest'        => true,
			'rest_base'           => self::REST_BASE,
		);

		register_post_type( self::POST_TYPE, $args );
	}

	public function enqueue_editor_assets() {
		$asset_file = SOLI_MEDEDELINGEN__PLUGIN_DIR_PATH . 'build/index.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}
		$asset = require $asset_file;

		wp_enqueue_script(
			'soli-mededelingen-editor',
			SOLI_MEDEDELINGEN__PLUGIN_DIR_URL . 'build/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_set_script_translations(
			'soli-mededelingen-editor',
			'soli-mededelingen',
			SOLI_MEDEDELINGEN__PLUGIN_DIR_PATH . 'languages'
		);
	}
}
