<?php
/**
 * Basic SEO: meta descriptions from excerpts and LegalService structured data.
 * (Polylang already prints hreflang alternates for translated pages.)
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
	if ( is_singular() ) {
		$post = get_post();
		$desc = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 28, '…' );
	} else {
		$desc = meridian_t( 'footer_tagline' );
	}
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}

	if ( is_front_page() ) {
		$data = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'LegalService',
			'name'       => 'Meridian Injury Law',
			'url'        => home_url( '/' ),
			'telephone'  => '+1-713-555-0142',
			'areaServed' => array( 'Texas', 'Louisiana' ),
			'knowsLanguage' => array( 'en', 'es' ),
		);
		echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
	}
}, 5 );
