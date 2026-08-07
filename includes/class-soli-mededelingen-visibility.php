<?php

namespace Soli\Mededelingen;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hides mededelingen from not-logged-in visitors on every surface:
 *
 * - Query Loop blocks and other secondary WP_Query instances return nothing
 * - The single page and the post type archive respond with HTTP 403
 * - The REST endpoints (collection and single) respond with HTTP 403
 *
 * Site search is handled in Post_Type via a per-request exclude_from_search.
 * Logged-in users (any role) see everything; draft/private post statuses keep
 * their normal WordPress rules.
 */
class Visibility {

	public function init() {
		add_action( 'pre_get_posts', array( $this, 'hide_from_public_queries' ) );
		add_action( 'template_redirect', array( $this, 'guard_front_end' ) );
		add_filter( 'rest_request_before_callbacks', array( $this, 'guard_rest' ), 10, 3 );
	}

	/**
	 * Empty every logged-out query that targets mededelingen, or strip the
	 * post type when the query mixes multiple types (e.g. REST search).
	 *
	 * The main query is left alone entirely: single and archive get a 403
	 * from guard_front_end() (emptying them here would turn that into a
	 * silent 404), and site search is covered by the per-request
	 * exclude_from_search flag in Post_Type.
	 */
	public function hide_from_public_queries( $query ) {
		if ( is_admin() || is_user_logged_in() ) {
			return;
		}

		if ( $query->is_main_query() ) {
			return;
		}

		$post_type = $query->get( 'post_type' );
		if ( empty( $post_type ) ) {
			return;
		}

		if ( is_array( $post_type ) ) {
			if ( ! in_array( Post_Type::POST_TYPE, $post_type, true ) ) {
				return;
			}
			$remaining = array_values( array_diff( $post_type, array( Post_Type::POST_TYPE ) ) );
			if ( empty( $remaining ) ) {
				$query->set( 'post__in', array( 0 ) );
			} else {
				$query->set( 'post_type', $remaining );
			}
		} elseif ( Post_Type::POST_TYPE === $post_type ) {
			$query->set( 'post__in', array( 0 ) );
		}
	}

	/**
	 * 403 for logged-out visits to a single mededeling or the archive,
	 * following the guard pattern in wp-soli-event-plugin.
	 */
	public function guard_front_end() {
		if ( is_admin() || is_user_logged_in() ) {
			return;
		}

		if ( ! is_singular( Post_Type::POST_TYPE ) && ! is_post_type_archive( Post_Type::POST_TYPE ) ) {
			return;
		}

		wp_die(
			esc_html__( 'Mededelingen are only visible to members. Please log in to view them.', 'soli-mededelingen' ),
			esc_html__( 'Forbidden', 'soli-mededelingen' ),
			array( 'response' => 403 )
		);
	}

	/**
	 * 403 for logged-out REST requests to the mededelingen routes. Logged-in
	 * requests (cookie or application password) pass through untouched so the
	 * editor and the Query Loop preview keep working.
	 *
	 * @param mixed            $response Current response, null when nothing intervened yet.
	 * @param array|callable   $handler  Route handler (unused).
	 * @param \WP_REST_Request $request  The request.
	 * @return mixed
	 */
	public function guard_rest( $response, $handler, $request ) {
		if ( null !== $response || is_user_logged_in() ) {
			return $response;
		}

		if ( 0 !== strpos( $request->get_route(), '/wp/v2/' . Post_Type::REST_BASE ) ) {
			return $response;
		}

		return new \WP_Error(
			'rest_forbidden',
			__( 'Mededelingen are only visible to members.', 'soli-mededelingen' ),
			array( 'status' => 403 )
		);
	}
}
