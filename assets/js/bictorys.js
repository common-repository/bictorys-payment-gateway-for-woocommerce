jQuery( function( $ ) {

	let bictorys_submit = false;

	$( '#wc-bictorys-form' ).hide();

	wcBictorysFormHandler();

	jQuery( '#bictorys-payment-button' ).click( function() {
		return wcBictorysFormHandler();
	} );

	jQuery( '#bictorys_form form#order_review' ).submit( function() {
		return wcBictorysFormHandler();
	} );

	function wcBictorysCustomFields() {

		let custom_fields = [
			{
				"display_name": "Plugin",
				"variable_name": "plugin",
				"value": "bictorys-payment-gateway-for-woocommerce"
			}
		];

		if ( wc_bictorys_params.meta_order_id ) {

			custom_fields.push( {
				display_name: "Order ID",
				variable_name: "order_id",
				value: wc_bictorys_params.meta_order_id
			} );

		}

		if ( wc_bictorys_params.meta_name ) {

			custom_fields.push( {
				display_name: "Customer Name",
				variable_name: "customer_name",
				value: wc_bictorys_params.meta_name
			} );
		}

		if ( wc_bictorys_params.meta_email ) {

			custom_fields.push( {
				display_name: "Customer Email",
				variable_name: "customer_email",
				value: wc_bictorys_params.meta_email
			} );
		}

		if ( wc_bictorys_params.meta_phone ) {

			custom_fields.push( {
				display_name: "Customer Phone",
				variable_name: "customer_phone",
				value: wc_bictorys_params.meta_phone
			} );
		}

		if ( wc_bictorys_params.meta_billing_address ) {

			custom_fields.push( {
				display_name: "Billing Address",
				variable_name: "billing_address",
				value: wc_bictorys_params.meta_billing_address
			} );
		}

		if ( wc_bictorys_params.meta_shipping_address ) {

			custom_fields.push( {
				display_name: "Shipping Address",
				variable_name: "shipping_address",
				value: wc_bictorys_params.meta_shipping_address
			} );
		}

		if ( wc_bictorys_params.meta_products ) {

			custom_fields.push( {
				display_name: "Products",
				variable_name: "products",
				value: wc_bictorys_params.meta_products
			} );
		}

		return custom_fields;
	}

	function wcBictorysCustomFilters() {

		let custom_filters = {};

		if ( wc_bictorys_params.card_channel ) {

			if ( wc_bictorys_params.banks_allowed ) {

				custom_filters[ 'banks' ] = wc_bictorys_params.banks_allowed;

			}

			if ( wc_bictorys_params.cards_allowed ) {

				custom_filters[ 'card_brands' ] = wc_bictorys_params.cards_allowed;
			}

		}

		return custom_filters;
	}

	function wcPaymentChannels() {

		let payment_channels = [];

		if ( wc_bictorys_params.bank_channel ) {
			payment_channels.push( 'bank' );
		}

		if ( wc_bictorys_params.card_channel ) {
			payment_channels.push( 'card' );
		}

		if ( wc_bictorys_params.ussd_channel ) {
			payment_channels.push( 'ussd' );
		}

		if ( wc_bictorys_params.qr_channel ) {
			payment_channels.push( 'qr' );
		}

		if ( wc_bictorys_params.bank_transfer_channel ) {
			payment_channels.push( 'bank_transfer' );
		}

		return payment_channels;
	}

	function wcBictorysFormHandler() {

		$( '#wc-bictorys-form' ).hide();

		if ( bictorys_submit ) {
			bictorys_submit = false;
			return true;
		}

		let $form = $( 'form#payment-form, form#order_review' ),
			bictorys_txnref = $form.find( 'input.bictorys_txnref' ),
			subaccount_code = '',
			charges_account = '',
			transaction_charges = '';

		bictorys_txnref.val( '' );

		if ( wc_bictorys_params.subaccount_code ) {
			subaccount_code = wc_bictorys_params.subaccount_code;
		}

		if ( wc_bictorys_params.charges_account ) {
			charges_account = wc_bictorys_params.charges_account;
		}

		if ( wc_bictorys_params.transaction_charges ) {
			transaction_charges = Number( wc_bictorys_params.transaction_charges );
		}

		let amount = Number( wc_bictorys_params.amount );

		let bictorys_callback = function( transaction ) {
			$form.append( '<input type="hidden" class="bictorys_txnref" name="bictorys_txnref" value="' + transaction.reference + '"/>' );
			bictorys_submit = true;

			$form.submit();

			$( 'body' ).block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				},
				css: {
					cursor: "wait"
				}
			} );
		};

		let paymentData = {
			key: wc_bictorys_params.key,
			email: wc_bictorys_params.email,
			amount: amount,
			ref: wc_bictorys_params.txnref,
			currency: wc_bictorys_params.currency,
			subaccount: subaccount_code,
			bearer: charges_account,
			transaction_charge: transaction_charges,
			metadata: {
				custom_fields: wcBictorysCustomFields(),
			},
			onSuccess: bictorys_callback,
			onCancel: () => {
				$( '#wc-bictorys-form' ).show();
				$( this.el ).unblock();
			}
		};

		if ( Array.isArray( wcPaymentChannels() ) && wcPaymentChannels().length ) {
			paymentData[ 'channels' ] = wcPaymentChannels();
			if ( !$.isEmptyObject( wcBictorysCustomFilters() ) ) {
				paymentData[ 'metadata' ][ 'custom_filters' ] = wcBictorysCustomFilters();
			}
		}

		const bictorys = new BictorysPop();
		bictorys.newTransaction( paymentData );
	}

} );