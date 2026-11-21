<?php
/**
 * Plugin Name: Meridian Core
 * Description: Content types, bilingual helpers, accessible consultation form and demo content for the Meridian Injury Law demo site.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: Allan Koaik
 * License: GPL-2.0-or-later
 * Text Domain: meridian-core
 */

defined( 'ABSPATH' ) || exit;

define( 'MERIDIAN_CORE_DIR', __DIR__ );

require_once __DIR__ . '/inc/post-types.php';
require_once __DIR__ . '/inc/i18n.php';
require_once __DIR__ . '/inc/shortcodes.php';
require_once __DIR__ . '/inc/form.php';
require_once __DIR__ . '/inc/seo.php';
require_once __DIR__ . '/inc/seed.php';

register_activation_hook( __FILE__, function () {
	meridian_register_post_types();
	flush_rewrite_rules();
} );
