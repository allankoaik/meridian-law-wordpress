<?php
/**
 * Reusable building blocks used inside the block templates and page content:
 *   [meridian_nav]       header navigation + language switcher + call button
 *   [meridian_footer]    footer columns, localized
 *   [meridian_heading]   localized archive heading + intro
 *   [meridian_cards]     card grid for practice areas, attorneys, locations or articles
 *   [meridian_children]  city offices under a state page
 *   [meridian_attorney_meta] role line for an attorney bio
 */

defined( 'ABSPATH' ) || exit;

const MERIDIAN_PHONE = '(713) 555-0142';

function meridian_icon( $name ) {
	$paths = array(
		'car'    => '<path d="M5 16h14M6.5 16l1.4-5.2A2 2 0 0 1 9.8 9.3h4.4a2 2 0 0 1 1.9 1.5L17.5 16"/><rect x="4" y="16" width="16" height="3.5" rx="1"/><circle cx="7.5" cy="19.5" r="1.2"/><circle cx="16.5" cy="19.5" r="1.2"/>',
		'truck'  => '<rect x="2.5" y="7" width="11" height="9" rx="1"/><path d="M13.5 10h4l3 3.5V16h-7z"/><circle cx="7" cy="17.5" r="1.6"/><circle cx="17" cy="17.5" r="1.6"/>',
		'helmet' => '<path d="M4 16a8 8 0 0 1 16 0"/><path d="M3 16h18v2H3z"/><path d="M10 8V5.5h4V8"/>',
		'heart'  => '<path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/>',
		'pin'    => '<path d="M12 21s-6-5.6-6-11a6 6 0 0 1 12 0c0 5.4-6 11-6 11z"/><circle cx="12" cy="10" r="2.2"/>',
		'phone'  => '<path d="M6.6 3.5h2.6l1.3 4-1.9 1.3a11 11 0 0 0 5.6 5.6l1.3-1.9 4 1.3v2.6a2 2 0 0 1-2.2 2A16 16 0 0 1 4.6 5.7a2 2 0 0 1 2-2.2z"/>',
		'doc'    => '<path d="M7 3h7l4 4v14H7z"/><path d="M14 3v4h4M9.5 12h6M9.5 15.5h6"/>',
	);
	$path = $paths[ $name ] ?? $paths['doc'];
	return '<svg class="mx-icon" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $path . '</svg>';
}

function meridian_avatar( $post_id ) {
	$initials = get_post_meta( $post_id, '_meridian_initials', true );
	if ( ! $initials ) {
		$words    = preg_split( '/\s+/', get_the_title( $post_id ) );
		$initials = strtoupper( substr( $words[0], 0, 1 ) . substr( end( $words ), 0, 1 ) );
	}
	return '<svg class="mx-avatar" viewBox="0 0 120 120" width="120" height="120" aria-hidden="true" focusable="false"><rect width="120" height="120" rx="60" fill="#13294B"/><circle cx="60" cy="60" r="52" fill="none" stroke="#C8A24A" stroke-width="2"/><text x="60" y="71" text-anchor="middle" font-family="Georgia, serif" font-size="34" fill="#F7F4EE">' . esc_html( $initials ) . '</text></svg>';
}

/* ---------------------------------------------------------------- Header nav */

add_shortcode( 'meridian_nav', function () {
	$current = trailingslashit( strtok( home_url( add_query_arg( array() ) ), '?' ) );
	$items   = array(
		meridian_archive_url( 'practice_area' ) => meridian_t( 'practice_areas' ),
		meridian_archive_url( 'location' )      => meridian_t( 'locations' ),
		meridian_archive_url( 'attorney' )      => meridian_t( 'attorneys' ),
		meridian_page_url( 'articles' )         => meridian_t( 'articles' ),
		meridian_page_url( 'about' )            => meridian_t( 'about' ),
	);

	ob_start();
	?>
	<nav class="mx-nav" aria-label="<?php echo esc_attr( meridian_t( 'nav_label' ) ); ?>">
		<button class="mx-nav__toggle" type="button" aria-expanded="false" aria-controls="mx-nav-panel">
			<span class="mx-nav__bars" aria-hidden="true"></span><?php echo esc_html( meridian_t( 'menu' ) ); ?>
		</button>
		<div class="mx-nav__panel" id="mx-nav-panel">
			<ul class="mx-nav__list">
				<?php foreach ( $items as $url => $label ) :
					$is_current = 0 === strpos( $current, trailingslashit( $url ) ); ?>
					<li><a href="<?php echo esc_url( $url ); ?>"<?php echo $is_current ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<?php echo meridian_language_switcher(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<a class="mx-btn mx-btn--gold mx-nav__cta" href="<?php echo esc_url( meridian_page_url( 'contact' ) ); ?>"><?php echo esc_html( meridian_t( 'free_consult' ) ); ?></a>
		</div>
	</nav>
	<?php
	return meridian_compact( ob_get_clean() );
} );

function meridian_language_switcher() {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return '';
	}
	$languages = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0 ) );
	if ( empty( $languages ) ) {
		return '';
	}
	$out = '<ul class="mx-lang" aria-label="' . esc_attr( meridian_t( 'lang_label' ) ) . '">';
	foreach ( $languages as $language ) {
		$label = 'es' === $language['slug'] ? 'Español' : 'English';
		$short = strtoupper( $language['slug'] );
		if ( ! empty( $language['current_lang'] ) ) {
			$out .= '<li><span class="mx-lang__current" aria-current="true"><abbr title="' . esc_attr( $label ) . '">' . esc_html( $short ) . '</abbr></span></li>';
		} else {
			$out .= '<li><a href="' . esc_url( $language['url'] ) . '" hreflang="' . esc_attr( $language['slug'] ) . '" lang="' . esc_attr( $language['slug'] ) . '"><abbr title="' . esc_attr( $label ) . '">' . esc_html( $short ) . '</abbr><span class="screen-reader-text"> ' . esc_html( $label ) . '</span></a></li>';
		}
	}
	return $out . '</ul>';
}

/* ---------------------------------------------------------------- Footer */

add_shortcode( 'meridian_footer', function () {
	ob_start();
	?>
	<div class="mx-footer">
		<div class="mx-footer__brand">
			<p class="mx-footer__logo">Meridian <span>Injury Law</span></p>
			<p><?php echo esc_html( meridian_t( 'footer_tagline' ) ); ?></p>
		</div>
		<div>
			<h2 class="mx-footer__heading"><?php echo esc_html( meridian_t( 'footer_explore' ) ); ?></h2>
			<ul>
				<li><a href="<?php echo esc_url( meridian_archive_url( 'practice_area' ) ); ?>"><?php echo esc_html( meridian_t( 'practice_areas' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( meridian_archive_url( 'location' ) ); ?>"><?php echo esc_html( meridian_t( 'locations' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( meridian_archive_url( 'attorney' ) ); ?>"><?php echo esc_html( meridian_t( 'attorneys' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( meridian_page_url( 'articles' ) ); ?>"><?php echo esc_html( meridian_t( 'articles' ) ); ?></a></li>
			</ul>
		</div>
		<div>
			<h2 class="mx-footer__heading"><?php echo esc_html( meridian_t( 'footer_contact' ) ); ?></h2>
			<ul>
				<li><a href="tel:+17135550142"><?php echo meridian_icon( 'phone' ); // phpcs:ignore ?> <?php echo esc_html( MERIDIAN_PHONE ); ?></a></li>
				<li><a href="<?php echo esc_url( meridian_page_url( 'contact' ) ); ?>"><?php echo esc_html( meridian_t( 'free_consult' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( meridian_page_url( 'about' ) ); ?>"><?php echo esc_html( meridian_t( 'about' ) ); ?></a></li>
			</ul>
		</div>
	</div>
	<p class="mx-footer__legal">© <?php echo esc_html( gmdate( 'Y' ) ); ?> Meridian Injury Law. <?php echo esc_html( meridian_t( 'rights' ) ); ?> <?php echo esc_html( meridian_t( 'disclaimer' ) ); ?></p>
	<?php
	return meridian_compact( ob_get_clean() );
} );

/* ---------------------------------------------------------------- Archive heading */

add_shortcode( 'meridian_heading', function ( $atts ) {
	$atts = shortcode_atts( array( 'key' => 'pa' ), $atts );
	$key  = sanitize_key( $atts['key'] );
	return '<header class="mx-page-head"><div class="mx-page-head__inner"><h1>' . esc_html( meridian_t( $key . '_title' ) ) . '</h1><p>' . esc_html( meridian_t( $key . '_intro' ) ) . '</p></div></header>';
} );

/* ---------------------------------------------------------------- Card grids */

add_shortcode( 'meridian_cards', function ( $atts ) {
	$atts = shortcode_atts( array( 'type' => 'practice_area', 'limit' => 12, 'parent' => '' ), $atts );
	$type = in_array( $atts['type'], array( 'practice_area', 'attorney', 'location', 'post' ), true ) ? $atts['type'] : 'practice_area';

	$args = array(
		'post_type'      => $type,
		'posts_per_page' => (int) $atts['limit'],
		'orderby'        => 'post' === $type ? 'date' : 'menu_order title',
		'order'          => 'post' === $type ? 'DESC' : 'ASC',
		'no_found_rows'  => true,
	);
	if ( function_exists( 'pll_current_language' ) ) {
		$args['lang'] = meridian_lang();
	}
	if ( 'location' === $type ) {
		$args['post_parent'] = '' === $atts['parent'] ? 0 : (int) $atts['parent'];
	}

	$query = new WP_Query( $args );
	if ( ! $query->have_posts() ) {
		return '';
	}

	$out = '<ul class="mx-cards mx-cards--' . esc_attr( $type ) . '">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$out .= meridian_card( get_post() );
	}
	wp_reset_postdata();
	return $out . '</ul>';
} );

function meridian_card( $post ) {
	$type  = $post->post_type;
	$title = get_the_title( $post );
	$url   = get_permalink( $post );
	$text  = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 22 );

	switch ( $type ) {
		case 'attorney':
			$media = meridian_avatar( $post->ID );
			$meta  = '<p class="mx-card__meta">' . esc_html( get_post_meta( $post->ID, '_meridian_role', true ) ) . '</p>';
			$cta   = meridian_t( 'view_profile' );
			break;
		case 'location':
			$media = meridian_icon( 'pin' );
			$meta  = '';
			$cta   = meridian_t( 'learn_more' );
			break;
		case 'post':
			$media = meridian_icon( 'doc' );
			$meta  = '<p class="mx-card__meta"><time datetime="' . esc_attr( get_the_date( 'c', $post ) ) . '">' . esc_html( get_the_date( '', $post ) ) . '</time></p>';
			$cta   = meridian_t( 'read_article' );
			break;
		default:
			$media = meridian_icon( get_post_meta( $post->ID, '_meridian_icon', true ) ?: 'doc' );
			$meta  = '';
			$cta   = meridian_t( 'learn_more' );
	}

	// The whole card is clickable through the heading link (stretched with CSS),
	// so screen readers hear one clear link per card instead of duplicates.
	return '<li class="mx-card"><div class="mx-card__media">' . $media . '</div>'
		. '<h3 class="mx-card__title"><a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a></h3>'
		. $meta
		. '<p class="mx-card__text">' . esc_html( $text ) . '</p>'
		. '<span class="mx-card__cta" aria-hidden="true">' . esc_html( $cta ) . ' →</span></li>';
}

/* ---------------------------------------------------------------- State -> city list */

add_shortcode( 'meridian_children', function () {
	$post = get_post();
	if ( ! $post || 'location' !== $post->post_type ) {
		return '';
	}
	$children = get_children( array(
		'post_parent' => $post->ID,
		'post_type'   => 'location',
		'post_status' => 'publish',
		'orderby'     => 'menu_order title',
		'order'       => 'ASC',
	) );
	if ( ! $children ) {
		return '';
	}
	$out = '<section class="mx-children" aria-labelledby="mx-children-title"><h2 id="mx-children-title">' . esc_html( meridian_t( 'offices_in' ) ) . '</h2><ul class="mx-cards mx-cards--location">';
	foreach ( $children as $child ) {
		$out .= meridian_card( $child );
	}
	return $out . '</ul></section>';
} );

add_shortcode( 'meridian_attorney_meta', function () {
	$post = get_post();
	if ( ! $post ) {
		return '';
	}
	return '<div class="mx-bio-head">' . meridian_avatar( $post->ID ) . '<p class="mx-bio-head__role">' . esc_html( get_post_meta( $post->ID, '_meridian_role', true ) ) . '</p></div>';
} );

add_shortcode( 'meridian_t', function ( $atts ) {
	$atts = shortcode_atts( array( 'key' => '' ), $atts );
	return esc_html( meridian_t( sanitize_key( $atts['key'] ) ) );
} );

add_shortcode( 'meridian_home_link', function () {
	return '<a class="mx-btn mx-btn--navy" href="' . esc_url( meridian_home_url() ) . '">' . esc_html( meridian_t( 'back_home' ) ) . '</a>';
} );

/**
 * The core Shortcode block runs wpautop() on its output, which wraps our markup in stray
 * <p> tags. Render our own shortcodes directly instead; editors still get the normal block.
 */
add_filter( 'render_block', function ( $content, $block ) {
	if ( 'core/shortcode' !== ( $block['blockName'] ?? '' ) ) {
		return $content;
	}
	$inner = trim( $block['innerHTML'] ?? '' );
	// In post content the shortcode is still unexpanded here.
	if ( false !== strpos( $inner, '[meridian_' ) ) {
		return do_shortcode( $inner );
	}
	// In templates WordPress expands shortcodes before blocks render, so we see our finished HTML.
	if ( false !== strpos( $inner, 'class="mx-' ) ) {
		return $inner;
	}
	return $content;
}, 10, 2 );
