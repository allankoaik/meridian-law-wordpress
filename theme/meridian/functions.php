<?php
/**
 * Meridian theme setup. Design lives in theme.json (global styles) and style.css;
 * content types and bilingual helpers live in the Meridian Core plugin.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', function () {
	$version = wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'meridian', get_stylesheet_uri(), array(), $version );
	wp_enqueue_script( 'meridian-nav', get_theme_file_uri( 'assets/js/site.js' ), array(), $version, array( 'in_footer' => true, 'strategy' => 'defer' ) );
} );

// Load the same styles in the editor so pages look the same while editing.
add_action( 'after_setup_theme', function () {
	add_editor_style( 'style.css' );
} );

add_action( 'init', function () {
	register_block_style( 'core/button', array( 'name' => 'gold', 'label' => 'Gold' ) );
	register_block_style( 'core/button', array( 'name' => 'navy', 'label' => 'Navy' ) );
	register_block_style( 'core/button', array( 'name' => 'outline-light', 'label' => 'Outline (light)' ) );
	register_block_style( 'core/list', array( 'name' => 'checks', 'label' => 'Checkmarks' ) );
} );

// Tell editors when the companion plugin is missing instead of showing raw shortcodes.
add_action( 'admin_notices', function () {
	if ( ! function_exists( 'meridian_lang' ) && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-warning"><p>The Meridian theme needs the <strong>Meridian Core</strong> plugin for practice areas, attorneys, locations and the contact form.</p></div>';
	}
} );
