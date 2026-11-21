<?php
/**
 * Custom post types: practice areas, attorneys, locations (state > city) and form inquiries.
 * Kept in a plugin (not the theme) so content survives a theme change.
 */

defined( 'ABSPATH' ) || exit;

function meridian_register_post_types() {
	$common = array(
		'public'       => true,
		'show_in_rest' => true,
		'menu_position' => 20,
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
	);

	register_post_type( 'practice_area', $common + array(
		'labels'      => meridian_labels( 'Practice Area', 'Practice Areas' ),
		'menu_icon'   => 'dashicons-portfolio',
		'has_archive' => 'practice-areas',
		'rewrite'     => array( 'slug' => 'practice-areas', 'with_front' => false ),
	) );

	register_post_type( 'attorney', $common + array(
		'labels'      => meridian_labels( 'Attorney', 'Attorneys' ),
		'menu_icon'   => 'dashicons-businessperson',
		'has_archive' => 'attorneys',
		'rewrite'     => array( 'slug' => 'attorneys', 'with_front' => false ),
	) );

	// Hierarchical: a state is a parent, its cities are children -> /locations/texas/houston/.
	register_post_type( 'location', $common + array(
		'labels'       => meridian_labels( 'Location', 'Locations' ),
		'menu_icon'    => 'dashicons-location',
		'hierarchical' => true,
		'has_archive'  => 'locations',
		'rewrite'      => array( 'slug' => 'locations', 'with_front' => false ),
	) );

	// Private store for consultation requests so the team can read them in wp-admin.
	register_post_type( 'inquiry', array(
		'labels'          => meridian_labels( 'Inquiry', 'Inquiries' ),
		'public'          => false,
		'show_ui'         => true,
		'menu_icon'       => 'dashicons-email-alt',
		'supports'        => array( 'title', 'editor' ),
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
	) );
}
add_action( 'init', 'meridian_register_post_types' );

function meridian_labels( $singular, $plural ) {
	return array(
		'name'          => $plural,
		'singular_name' => $singular,
		'add_new_item'  => "Add New $singular",
		'edit_item'     => "Edit $singular",
		'view_item'     => "View $singular",
		'all_items'     => "All $plural",
		'search_items'  => "Search $plural",
		'menu_name'     => $plural,
	);
}

/** One-time URL rule rebuild after the demo content installer runs. */
add_action( 'init', function () {
	if ( get_option( 'meridian_flush_rewrite' ) ) {
		delete_option( 'meridian_flush_rewrite' );
		flush_rewrite_rules();
	}
}, 999 );

/** Make the custom types translatable in Polylang. */
add_filter( 'pll_get_post_types', function ( $types, $is_settings ) {
	foreach ( array( 'practice_area', 'attorney', 'location' ) as $type ) {
		$types[ $type ] = $type;
	}
	return $types;
}, 10, 2 );

/** Order archives by menu order so the team controls the order without code. */
add_action( 'pre_get_posts', function ( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_post_type_archive( array( 'practice_area', 'attorney', 'location' ) ) ) {
		$query->set( 'orderby', 'menu_order title' );
		$query->set( 'order', 'ASC' );
		$query->set( 'posts_per_page', 50 );
		if ( $query->is_post_type_archive( 'location' ) ) {
			$query->set( 'post_parent', 0 );
		}
	}
} );
