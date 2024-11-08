jQuery( function( $ ) {
	'use strict';

	/**
	 * Object to handle Bictorys admin functions.
	 */
	var wc_bictorys_admin = {
		/**
		 * Initialize.
		 */
		init: function() {

			// Toggle api key settings.
			$( document.body ).on( 'change', '#woocommerce_bictorys_testmode', function() {
				var test_secret_key = $( '#woocommerce_bictorys_test_secret_key' ).parents( 'tr' ).eq( 0 ),
					test_public_key = $( '#woocommerce_bictorys_test_public_key' ).parents( 'tr' ).eq( 0 ),
					live_secret_key = $( '#woocommerce_bictorys_live_secret_key' ).parents( 'tr' ).eq( 0 ),
					live_public_key = $( '#woocommerce_bictorys_live_public_key' ).parents( 'tr' ).eq( 0 );

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

			$( '#woocommerce_bictorys_testmode' ).change();

			$( document.body ).on( 'change', '.woocommerce_bictorys_split_payment', function() {
				var subaccount_code = $( '.woocommerce_bictorys_subaccount_code' ).parents( 'tr' ).eq( 0 ),
					subaccount_charge = $( '.woocommerce_bictorys_split_payment_charge_account' ).parents( 'tr' ).eq( 0 ),
					transaction_charge = $( '.woocommerce_bictorys_split_payment_transaction_charge' ).parents( 'tr' ).eq( 0 );

				if ( $( this ).is( ':checked' ) ) {
					subaccount_code.show();
					subaccount_charge.show();
					transaction_charge.show();
				} else {
					subaccount_code.hide();
					subaccount_charge.hide();
					transaction_charge.hide();
				}
			} );

			$( '#woocommerce_bictorys_split_payment' ).change();

			// Toggle Custom Metadata settings.
			$( '.wc-bictorys-metadata' ).change( function() {
				if ( $( this ).is( ':checked' ) ) {
					$( '.wc-bictorys-meta-order-id, .wc-bictorys-meta-name, .wc-bictorys-meta-email, .wc-bictorys-meta-phone, .wc-bictorys-meta-billing-address, .wc-bictorys-meta-shipping-address, .wc-bictorys-meta-products' ).closest( 'tr' ).show();
				} else {
					$( '.wc-bictorys-meta-order-id, .wc-bictorys-meta-name, .wc-bictorys-meta-email, .wc-bictorys-meta-phone, .wc-bictorys-meta-billing-address, .wc-bictorys-meta-shipping-address, .wc-bictorys-meta-products' ).closest( 'tr' ).hide();
				}
			} ).change();

			// Toggle Bank filters settings.
			$( '.wc-bictorys-payment-channels' ).on( 'change', function() {

				var channels = $( ".wc-bictorys-payment-channels" ).val();

				if ( $.inArray( 'card', channels ) != '-1' ) {
					$( '.wc-bictorys-cards-allowed' ).closest( 'tr' ).show();
					$( '.wc-bictorys-banks-allowed' ).closest( 'tr' ).show();
				}
				else {
					$( '.wc-bictorys-cards-allowed' ).closest( 'tr' ).hide();
					$( '.wc-bictorys-banks-allowed' ).closest( 'tr' ).hide();
				}

			} ).change();

			$( ".wc-bictorys-payment-icons" ).select2( {
				templateResult: formatBictorysPaymentIcons,
				templateSelection: formatBictorysPaymentIconDisplay
			} );

			$( '#woocommerce_bictorys_test_secret_key, #woocommerce_bictorys_live_secret_key' ).after(
				'<button class="wc-bictorys-toggle-secret" style="height: 30px; margin-left: 2px; cursor: pointer"><span class="dashicons dashicons-visibility"></span></button>'
			);

			$( '.wc-bictorys-toggle-secret' ).on( 'click', function( event ) {
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

	function formatBictorysPaymentIcons( payment_method ) {
		if ( !payment_method.id ) {
			return payment_method.text;
		}

		var $payment_method = $(
			'<span><img src=" ' + wc_bictorys_admin_params.plugin_url + '/assets/images/' + payment_method.element.value.toLowerCase() + '.png" class="img-flag" style="height: 15px; weight:18px;" /> ' + payment_method.text + '</span>'
		);

		return $payment_method;
	};

	function formatBictorysPaymentIconDisplay( payment_method ) {
		return payment_method.text;
	};

	wc_bictorys_admin.init();

} );
