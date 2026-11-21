<?php
/**
 * Language helpers. Polylang handles the /es/ URLs, translation links and hreflang;
 * these helpers give templates the right interface text and links for the current language.
 */

defined( 'ABSPATH' ) || exit;

function meridian_lang() {
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language( 'slug' );
		if ( $lang ) {
			return $lang;
		}
	}
	return 'en';
}

function meridian_strings() {
	return array(
		'en' => array(
			'nav_label'        => 'Main navigation',
			'menu'             => 'Menu',
			'practice_areas'   => 'Practice Areas',
			'locations'        => 'Locations',
			'attorneys'        => 'Attorneys',
			'articles'         => 'Articles',
			'about'            => 'About',
			'contact'          => 'Contact',
			'call'             => 'Call 24/7',
			'free_consult'     => 'Free Consultation',
			'lang_label'       => 'Language',
			'learn_more'       => 'Learn more',
			'read_article'     => 'Read article',
			'view_profile'     => 'View profile',
			'offices_in'       => 'Offices in this state',
			'pa_title'         => 'Practice Areas',
			'pa_intro'         => 'Focused experience in the cases that change lives. We only get paid if we win.',
			'att_title'        => 'Our Attorneys',
			'att_intro'        => 'Trial-tested lawyers who answer your calls and know your name.',
			'loc_title'        => 'Locations',
			'loc_intro'        => 'Local offices across Texas and Louisiana, with home and hospital visits available.',
			'art_title'        => 'Articles & Guides',
			'art_intro'        => 'Plain-language answers to the questions our clients ask most.',
			'not_found'        => 'Page not found',
			'not_found_text'   => 'The page you are looking for has moved or no longer exists.',
			'back_home'        => 'Back to the homepage',
			'footer_tagline'   => 'Injury lawyers for Texas and Louisiana families. No fee unless we win.',
			'footer_explore'   => 'Explore',
			'footer_contact'   => 'Contact',
			'disclaimer'       => 'Demo website for a fictional law firm, built as a portfolio project. Nothing on this site is legal advice.',
			'rights'           => 'All rights reserved.',
			'skip'             => 'Skip to content',
			// Form.
			'form_title'       => 'Request a free case review',
			'form_intro'       => 'Tell us what happened. A member of our team will contact you within one business day.',
			'f_name'           => 'Full name',
			'f_phone'          => 'Phone number',
			'f_email'          => 'Email address',
			'f_case'           => 'Type of case',
			'f_case_choose'    => 'Choose one',
			'f_message'        => 'What happened?',
			'f_message_hint'   => 'A few sentences is enough. Please do not include medical record numbers.',
			'f_consent'        => 'I understand that contacting the firm does not create an attorney-client relationship.',
			'f_submit'         => 'Send my case review request',
			'f_required'       => 'Required',
			'f_errors'         => 'Please fix the following before sending:',
			'f_err_name'       => 'Enter your full name.',
			'f_err_contact'    => 'Enter a phone number or a valid email address.',
			'f_err_message'    => 'Tell us briefly what happened.',
			'f_err_consent'    => 'Please confirm you have read the notice.',
			'f_err_security'   => 'Your session expired. Please send the form again.',
			'f_success'        => 'Thank you. Your request was received and our team will contact you within one business day.',
			'case_other'       => 'Something else',
		),
		'es' => array(
			'nav_label'        => 'Navegación principal',
			'menu'             => 'Menú',
			'practice_areas'   => 'Áreas de práctica',
			'locations'        => 'Oficinas',
			'attorneys'        => 'Abogados',
			'articles'         => 'Artículos',
			'about'            => 'Nosotros',
			'contact'          => 'Contacto',
			'call'             => 'Llame 24/7',
			'free_consult'     => 'Consulta gratis',
			'lang_label'       => 'Idioma',
			'learn_more'       => 'Más información',
			'read_article'     => 'Leer artículo',
			'view_profile'     => 'Ver perfil',
			'offices_in'       => 'Oficinas en este estado',
			'pa_title'         => 'Áreas de práctica',
			'pa_intro'         => 'Experiencia enfocada en los casos que cambian vidas. Solo cobramos si ganamos.',
			'att_title'        => 'Nuestros abogados',
			'att_intro'        => 'Abogados con experiencia en juicios que contestan sus llamadas y conocen su nombre.',
			'loc_title'        => 'Oficinas',
			'loc_intro'        => 'Oficinas locales en Texas y Luisiana, con visitas a domicilio y al hospital.',
			'art_title'        => 'Artículos y guías',
			'art_intro'        => 'Respuestas claras a las preguntas que más nos hacen nuestros clientes.',
			'not_found'        => 'Página no encontrada',
			'not_found_text'   => 'La página que busca se movió o ya no existe.',
			'back_home'        => 'Volver al inicio',
			'footer_tagline'   => 'Abogados de lesiones para familias de Texas y Luisiana. Sin honorarios a menos que ganemos.',
			'footer_explore'   => 'Explorar',
			'footer_contact'   => 'Contacto',
			'disclaimer'       => 'Sitio de demostración de un bufete ficticio, creado como proyecto de portafolio. Nada en este sitio es asesoría legal.',
			'rights'           => 'Todos los derechos reservados.',
			'skip'             => 'Saltar al contenido',
			'form_title'       => 'Solicite una revisión gratuita de su caso',
			'form_intro'       => 'Cuéntenos qué pasó. Un miembro de nuestro equipo le contactará en un día hábil.',
			'f_name'           => 'Nombre completo',
			'f_phone'          => 'Número de teléfono',
			'f_email'          => 'Correo electrónico',
			'f_case'           => 'Tipo de caso',
			'f_case_choose'    => 'Elija una opción',
			'f_message'        => '¿Qué pasó?',
			'f_message_hint'   => 'Unas pocas oraciones son suficientes. No incluya números de expedientes médicos.',
			'f_consent'        => 'Entiendo que contactar al bufete no crea una relación abogado-cliente.',
			'f_submit'         => 'Enviar mi solicitud',
			'f_required'       => 'Obligatorio',
			'f_errors'         => 'Corrija lo siguiente antes de enviar:',
			'f_err_name'       => 'Escriba su nombre completo.',
			'f_err_contact'    => 'Escriba un teléfono o un correo electrónico válido.',
			'f_err_message'    => 'Cuéntenos brevemente qué pasó.',
			'f_err_consent'    => 'Confirme que leyó el aviso.',
			'f_err_security'   => 'Su sesión expiró. Envíe el formulario de nuevo.',
			'f_success'        => 'Gracias. Recibimos su solicitud y nuestro equipo le contactará en un día hábil.',
			'case_other'       => 'Otro',
		),
	);
}

function meridian_t( $key ) {
	$strings = meridian_strings();
	$lang    = isset( $strings[ meridian_lang() ] ) ? meridian_lang() : 'en';
	return $strings[ $lang ][ $key ] ?? ( $strings['en'][ $key ] ?? $key );
}

/** Link to a page saved during setup (about, contact, articles), in the current language. */
function meridian_page_url( $key ) {
	$ids = get_option( 'meridian_pages', array() );
	if ( empty( $ids[ $key ] ) ) {
		return home_url( '/' );
	}
	$id = (int) $ids[ $key ];
	if ( function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $id, meridian_lang() );
		if ( $translated ) {
			$id = $translated;
		}
	}
	return get_permalink( $id );
}

function meridian_archive_url( $post_type ) {
	$url = get_post_type_archive_link( $post_type );
	// Polylang adds the /es/ prefix to archive links for the current language.
	if ( function_exists( 'PLL' ) && 'en' !== meridian_lang() && PLL()->links_model ) {
		$language = PLL()->model->get_language( meridian_lang() );
		if ( $language ) {
			$url = PLL()->links_model->switch_language_in_link( $url, $language );
		}
	}
	return $url;
}

function meridian_home_url() {
	return function_exists( 'pll_home_url' ) ? pll_home_url( meridian_lang() ) : home_url( '/' );
}

/**
 * Remove whitespace between tags in shortcode output. The Shortcode block runs wpautop(),
 * which would otherwise turn line breaks in our markup into stray <p> and <br> tags.
 */
function meridian_compact( $html ) {
	return trim( preg_replace( '/>\s+</', '><', $html ) );
}
