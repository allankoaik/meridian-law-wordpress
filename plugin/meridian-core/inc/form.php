<?php
/**
 * Accessible consultation form: [meridian_consult_form]
 * - Real <label>s, autocomplete hints, required fields announced
 * - Server-side validation with an error summary that receives focus
 * - Post/Redirect/Get on success so refreshing never re-sends
 * - Nonce + honeypot against spam; submissions saved as private "Inquiry" posts
 */

defined( 'ABSPATH' ) || exit;

$GLOBALS['meridian_form_state'] = array( 'errors' => array(), 'values' => array() );

add_action( 'template_redirect', function () {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['meridian_form'] ) ) {
		return;
	}

	$values = array(
		'name'    => sanitize_text_field( wp_unslash( $_POST['mx_name'] ?? '' ) ),
		'phone'   => sanitize_text_field( wp_unslash( $_POST['mx_phone'] ?? '' ) ),
		'email'   => sanitize_email( wp_unslash( $_POST['mx_email'] ?? '' ) ),
		'case'    => sanitize_text_field( wp_unslash( $_POST['mx_case'] ?? '' ) ),
		'message' => sanitize_textarea_field( wp_unslash( $_POST['mx_message'] ?? '' ) ),
		'consent' => ! empty( $_POST['mx_consent'] ),
	);
	$errors = array();

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_mx_nonce'] ?? '' ) ), 'meridian_consult' ) ) {
		$errors['mx_name'] = meridian_t( 'f_err_security' );
	}
	// Honeypot: real people never fill this hidden field.
	if ( ! empty( $_POST['mx_company'] ) ) {
		wp_safe_redirect( add_query_arg( 'sent', '1', wp_get_referer() ?: home_url( '/' ) ) . '#consult' );
		exit;
	}
	if ( strlen( $values['name'] ) < 2 ) {
		$errors['mx_name'] = meridian_t( 'f_err_name' );
	}
	if ( '' === $values['phone'] && ! is_email( $values['email'] ) ) {
		$errors['mx_phone'] = meridian_t( 'f_err_contact' );
	}
	if ( strlen( $values['message'] ) < 10 ) {
		$errors['mx_message'] = meridian_t( 'f_err_message' );
	}
	if ( ! $values['consent'] ) {
		$errors['mx_consent'] = meridian_t( 'f_err_consent' );
	}

	if ( $errors ) {
		$GLOBALS['meridian_form_state'] = compact( 'errors', 'values' );
		return; // Re-render the page with errors and the visitor's answers kept.
	}

	wp_insert_post( array(
		'post_type'    => 'inquiry',
		'post_status'  => 'private',
		'post_title'   => sprintf( '%s — %s', $values['name'], $values['case'] ?: 'General' ),
		'post_content' => sprintf(
			"Phone: %s\nEmail: %s\nCase type: %s\nLanguage: %s\n\n%s",
			$values['phone'], $values['email'], $values['case'], meridian_lang(), $values['message']
		),
	) );

	$to = get_option( 'admin_email' );
	if ( $to ) {
		wp_mail( $to, 'New case review request: ' . $values['name'], $values['message'] );
	}

	wp_safe_redirect( add_query_arg( 'sent', '1', wp_get_referer() ?: home_url( '/' ) ) . '#consult' );
	exit;
} );

add_shortcode( 'meridian_consult_form', function () {
	$state  = $GLOBALS['meridian_form_state'];
	$errors = $state['errors'];
	$v      = wp_parse_args( $state['values'], array( 'name' => '', 'phone' => '', 'email' => '', 'case' => '', 'message' => '', 'consent' => false ) );

	$areas_args = array( 'post_type' => 'practice_area', 'posts_per_page' => 20, 'orderby' => 'menu_order', 'order' => 'ASC', 'no_found_rows' => true );
	if ( function_exists( 'pll_current_language' ) ) {
		$areas_args['lang'] = meridian_lang();
	}
	$areas = get_posts( $areas_args );

	$field_error = function ( $id ) use ( $errors ) {
		return isset( $errors[ $id ] ) ? '<p class="mx-field__error" id="' . esc_attr( $id ) . '-error">' . esc_html( $errors[ $id ] ) . '</p>' : '';
	};
	$described = function ( $id, $extra = '' ) use ( $errors ) {
		$ids = trim( $extra . ( isset( $errors[ $id ] ) ? " $id-error" : '' ) );
		return $ids ? ' aria-describedby="' . esc_attr( $ids ) . '"' : '';
	};
	$invalid = function ( $id ) use ( $errors ) {
		return isset( $errors[ $id ] ) ? ' aria-invalid="true"' : '';
	};
	$req = '<span class="mx-req">(' . esc_html( meridian_t( 'f_required' ) ) . ')</span>';

	ob_start();
	?>
	<div class="mx-form-wrap" id="consult">
		<h2 class="mx-form__title"><?php echo esc_html( meridian_t( 'form_title' ) ); ?></h2>
		<p><?php echo esc_html( meridian_t( 'form_intro' ) ); ?></p>

		<?php if ( isset( $_GET['sent'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="mx-alert mx-alert--success" role="status" tabindex="-1" data-mx-focus><?php echo esc_html( meridian_t( 'f_success' ) ); ?></div>
		<?php endif; ?>

		<?php if ( $errors ) : ?>
			<div class="mx-alert mx-alert--error" role="alert" tabindex="-1" data-mx-focus>
				<p><strong><?php echo esc_html( meridian_t( 'f_errors' ) ); ?></strong></p>
				<ul>
					<?php foreach ( $errors as $id => $message ) : ?>
						<li><a href="#<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $message ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<form class="mx-form" method="post" action="#consult" novalidate>
			<?php wp_nonce_field( 'meridian_consult', '_mx_nonce' ); ?>
			<input type="hidden" name="meridian_form" value="1">
			<div class="mx-hp" aria-hidden="true">
				<label for="mx_company">Company</label>
				<input type="text" id="mx_company" name="mx_company" tabindex="-1" autocomplete="off">
			</div>

			<div class="mx-field">
				<label for="mx_name"><?php echo esc_html( meridian_t( 'f_name' ) ); ?> <?php echo $req; // phpcs:ignore ?></label>
				<input type="text" id="mx_name" name="mx_name" autocomplete="name" required value="<?php echo esc_attr( $v['name'] ); ?>"<?php echo $described( 'mx_name' ) . $invalid( 'mx_name' ); // phpcs:ignore ?>>
				<?php echo $field_error( 'mx_name' ); // phpcs:ignore ?>
			</div>

			<div class="mx-form__row">
				<div class="mx-field">
					<label for="mx_phone"><?php echo esc_html( meridian_t( 'f_phone' ) ); ?></label>
					<input type="tel" id="mx_phone" name="mx_phone" autocomplete="tel" inputmode="tel" value="<?php echo esc_attr( $v['phone'] ); ?>"<?php echo $described( 'mx_phone' ) . $invalid( 'mx_phone' ); // phpcs:ignore ?>>
					<?php echo $field_error( 'mx_phone' ); // phpcs:ignore ?>
				</div>
				<div class="mx-field">
					<label for="mx_email"><?php echo esc_html( meridian_t( 'f_email' ) ); ?></label>
					<input type="email" id="mx_email" name="mx_email" autocomplete="email" value="<?php echo esc_attr( $v['email'] ); ?>">
				</div>
			</div>

			<div class="mx-field">
				<label for="mx_case"><?php echo esc_html( meridian_t( 'f_case' ) ); ?></label>
				<select id="mx_case" name="mx_case">
					<option value=""><?php echo esc_html( meridian_t( 'f_case_choose' ) ); ?></option>
					<?php foreach ( $areas as $area ) : ?>
						<option<?php selected( $v['case'], $area->post_title ); ?>><?php echo esc_html( $area->post_title ); ?></option>
					<?php endforeach; ?>
					<option<?php selected( $v['case'], meridian_t( 'case_other' ) ); ?>><?php echo esc_html( meridian_t( 'case_other' ) ); ?></option>
				</select>
			</div>

			<div class="mx-field">
				<label for="mx_message"><?php echo esc_html( meridian_t( 'f_message' ) ); ?> <?php echo $req; // phpcs:ignore ?></label>
				<p class="mx-field__hint" id="mx_message-hint"><?php echo esc_html( meridian_t( 'f_message_hint' ) ); ?></p>
				<textarea id="mx_message" name="mx_message" rows="5" required<?php echo $described( 'mx_message', 'mx_message-hint' ) . $invalid( 'mx_message' ); // phpcs:ignore ?>><?php echo esc_textarea( $v['message'] ); ?></textarea>
				<?php echo $field_error( 'mx_message' ); // phpcs:ignore ?>
			</div>

			<div class="mx-field mx-field--check">
				<input type="checkbox" id="mx_consent" name="mx_consent" value="1" required<?php checked( $v['consent'] ); ?><?php echo $described( 'mx_consent' ) . $invalid( 'mx_consent' ); // phpcs:ignore ?>>
				<label for="mx_consent"><?php echo esc_html( meridian_t( 'f_consent' ) ); ?></label>
				<?php echo $field_error( 'mx_consent' ); // phpcs:ignore ?>
			</div>

			<button type="submit" class="mx-btn mx-btn--gold mx-btn--lg"><?php echo esc_html( meridian_t( 'f_submit' ) ); ?></button>
		</form>
	</div>
	<?php
	return meridian_compact( ob_get_clean() );
} );
