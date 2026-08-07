<?php
/**
 * Seeds fixture content for the e2e tests. Run via `wp eval-file`.
 * Idempotent: existing fixture posts are reused. Prints JSON with the
 * created ids and urls.
 */

$mededeling_title = 'Geheime mededeling voor leden';

$existing = get_posts(
	array(
		'post_type'   => 'soli_mededeling',
		'post_status' => 'publish',
		'title'       => $mededeling_title,
		'numberposts' => 1,
	)
);

if ( $existing ) {
	$mededeling_id = $existing[0]->ID;
} else {
	$mededeling_id = wp_insert_post(
		array(
			'post_type'    => 'soli_mededeling',
			'post_status'  => 'publish',
			'post_title'   => $mededeling_title,
			'post_content' => 'Alleen voor ingelogde leden bedoeld.',
		)
	);
}

$page_title = 'Mededelingen overzicht';

$existing_page = get_posts(
	array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'title'       => $page_title,
		'numberposts' => 1,
	)
);

if ( $existing_page ) {
	$page_id = $existing_page[0]->ID;
} else {
	$page_id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $page_title,
			'post_content' => file_get_contents( __DIR__ . '/query-loop-page.html' ),
		)
	);
}

echo wp_json_encode(
	array(
		'mededelingId'  => $mededeling_id,
		'mededelingUrl' => get_permalink( $mededeling_id ),
		'pageUrl'       => get_permalink( $page_id ),
	)
);
