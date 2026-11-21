/* Meridian: mobile menu (disclosure pattern) and focus management for form messages. */
( function () {
	document.documentElement.classList.add( 'mx-js' );

	var toggle = document.querySelector( '.mx-nav__toggle' );
	var panel = document.getElementById( 'mx-nav-panel' );

	if ( toggle && panel ) {
		function setOpen( open ) {
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			panel.classList.toggle( 'is-open', open );
		}

		toggle.addEventListener( 'click', function () {
			setOpen( toggle.getAttribute( 'aria-expanded' ) !== 'true' );
		} );

		// Escape closes the menu and returns focus to the button.
		document.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' && toggle.getAttribute( 'aria-expanded' ) === 'true' ) {
				setOpen( false );
				toggle.focus();
			}
		} );

		// Close the mobile menu if the screen grows to desktop size.
		window.matchMedia( '(min-width: 72em)' ).addEventListener( 'change', function ( mq ) {
			if ( mq.matches ) {
				setOpen( false );
			}
		} );
	}

	// Move focus to the form's success or error message so screen reader users hear it.
	// Wait for the full page load: the browser's own jump to #consult would otherwise reset focus.
	var message = document.querySelector( '[data-mx-focus]' );
	if ( message ) {
		window.addEventListener( 'load', function () {
			message.focus();
		} );
	}
} )();
