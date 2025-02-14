jQuery( function( $ ) {
    'use strict';

    /**
	 * Object to handle Squad admin functions.
	 */
	var wc_squad_admin = {
        /**
		 * Initialize.
		 */
		init: function() {

            // Toggle api key settings.
			$( document.body ).on( 'change', '#woocommerce_squad_testmode', function() {
                var test_secret_key = $( '#woocommerce_squad_test_secret_key' ).parents( 'tr' ).eq( 0 ),
					test_public_key = $( '#woocommerce_squad_test_public_key' ).parents( 'tr' ).eq( 0 ),
					live_secret_key = $( '#woocommerce_squad_live_secret_key' ).parents( 'tr' ).eq( 0 ),
					live_public_key = $( '#woocommerce_squad_live_public_key' ).parents( 'tr' ).eq( 0 );

                if ( $( this ).is( ':checked' ) ) {
                    test_secret_key.show();
                    test_public_key.show();
                    live_secret_key.hide();
                    live_public_key.hide();
                } else {
                    test_secret_key.hide();
                    test_public_key.hide();
                    live_secret_key.show();
                    live_public_key.show();
                }
            } );

            $( '#woocommerce_squad_testmode' ).change();

            // Toggle Custom Metadata settings.
			$( '.wc-squad-metadata' ).change( function() {
                if ( $( this ).is( ':checked' ) ) {
                    $( '.wc-squad-meta-order-id, .wc-squad-meta-name, .wc-squad-meta-email, .wc-squad-meta-phone, .wc-squad-meta-billing-address, .wc-squad-meta-shipping-address, .wc-squad-meta-products' ).closest( 'tr' ).show();
                }else{
                    $( '.wc-squad-meta-order-id, .wc-squad-meta-name, .wc-squad-meta-email, .wc-squad-meta-phone, .wc-squad-meta-billing-address, .wc-squad-meta-shipping-address, .wc-squad-meta-products' ).closest( 'tr' ).hide();
                }
            } ).change();

            $( '#woocommerce_squad_test_secret_key, #woocommerce_squad_live_secret_key' ).after(
				'<button class="wc-squad-toggle-secret" style="height: 30px; margin-left: 2px; cursor: pointer"><span class="dashicons dashicons-visibility"></span></button>'
			);

            $( '.wc-squad-toggle-secret' ).on( 'click', function( event ) {
                event.preventDefault();

                let $dashicon = $( this ).closest( 'button' ).find( '.dashicons' );
				let $input = $( this ).closest( 'tr' ).find( '.input-text' );
				let inputType = $input.attr( 'type' );

				if ( 'text' == inputType ) {
					$input.attr( 'type', 'password' );
					$dashicon.removeClass( 'dashicons-hidden' );
					$dashicon.addClass( 'dashicons-visibility' );
				} else {
					$input.attr( 'type', 'text' );
					$dashicon.removeClass( 'dashicons-visibility' );
					$dashicon.addClass( 'dashicons-hidden' );
				}
            } );

        }
    };

    wc_squad_admin.init();

} );