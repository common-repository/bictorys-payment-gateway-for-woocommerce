<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bictorys_PG_WC_Gateway extends WC_Payment_Gateway_CC {

    const DEFAULT_LOCAL_CODE = 'fr-FR';
    const DEFAULT_CONTENT_TYPE = 'application/json';
    const DEFAULT_TIMEOUT_IN_SECONDE = 60;
    const SUCCESS_REDIRECT_URL = '';
    const ERROR_REDIRECT_URL = '';

    const DEFAULT_TEST_API_SCHEME = 'https';
	const DEFAULT_LIVE_API_SCHEME = 'https';
    const DEFAULT_TEST_BASE_URI = 'api.test.bictorys.com';
	const DEFAULT_LIVE_BASE_URI = 'api.bictorys.com';

    const API_PAY_CHARGE_ENDPOINT = 'pay/v1';
    const API_PAY_CHARGE_RESOURCE = 'charges';
    const API_PAY_CHARGE_PORT = '443';

    const API_CUSTOMER_MANAGEMENT_ENDPOINT = 'customer-management/v1';
    const API_CUSTOMER_MANAGEMENT_RESOURCE = 'customers';
    const API_CUSTOMER_MANAGEMENT_PORT = '443';

	/**
	 * Is test mode active?
	 *
	 * @var bool
	 */
	public $testmode;

	/**
	 * Should orders be marked as complete after payment?
	 * 
	 * @var bool
	 */
	public $autocomplete_order;

	/**
	 * Bictorys payment page type.
	 *
	 * @var string
	 */
	public $payment_page;

	/**
	 * Bictorys test public key.
	 *
	 * @var string
	 */
	public $test_public_key;

	/**
	 * Bictorys test secret key.
	 *
	 * @var string
	 */
	public $test_secret_key;

	/**
	 * Bictorys live public key.
	 *
	 * @var string
	 */
	public $live_public_key;

	/**
	 * Bictorys live secret key.
	 *
	 * @var string
	 */
	public $live_secret_key;

    /**
     * Bictorys request ID.
     *
     * @var string
     */
    public $request_id;

	/**
	 * Should we save customer cards?
	 *
	 * @var bool
	 */
	public $saved_cards;

	/**
	 * Should Bictorys split payment be enabled.
	 *
	 * @var bool
	 */
	public $split_payment;

	/**
	 * Should the cancel & remove order button be removed on the pay for order page.
	 *
	 * @var bool
	 */
	public $remove_cancel_order_button;

	/**
	 * Bictorys sub account code.
	 *
	 * @var string
	 */
	public $subaccount_code;

	/**
	 * Bictorys merchant reference.
	 *
	 * @var string
	 */
	public $merchant_reference;

	/**
	 * Who bears Bictorys charges?
	 *
	 * @var string
	 */
	public $charges_account;

	/**
	 * A flat fee to charge the sub account for each transaction.
	 *
	 * @var string
	 */
	public $transaction_charges;

	/**
	 * Should custom metadata be enabled?
	 *
	 * @var bool
	 */
	public $custom_metadata;

	/**
	 * Should the order id be sent as a custom metadata to Bictorys?
	 *
	 * @var bool
	 */
	public $meta_order_id;

	/**
	 * Should the customer name be sent as a custom metadata to Bictorys?
	 *
	 * @var bool
	 */
	public $meta_name;

	/**
	 * Should the billing email be sent as a custom metadata to Bictorys?
	 *
	 * @var bool
	 */
	public $meta_email;

	/**
	 * Should the billing phone be sent as a custom metadata to Bictorys?
	 *
	 * @var bool
	 */
	public $meta_phone;

	/**
	 * Should the billing address be sent as a custom metadata to Bictorys?
	 *
	 * @var bool
	 */
	public $meta_billing_address;

	/**
	 * Should the shipping address be sent as a custom metadata to Bictorys?
	 *
	 * @var bool
	 */
	public $meta_shipping_address;

	/**
	 * Should the order items be sent as a custom metadata to Bictorys?
	 *
	 * @var bool
	 */
	public $meta_products;

	/**
	 * API public key
	 *
	 * @var string
	 */
	public $public_key;

	/**
	 * API secret key
	 *
	 * @var string
	 */
	public $secret_key;

	/**
	 * API base URI
	 *
	 * @var string
	 */
	public $default_base_uri;

	/**
	 * API base scheme
	 *
	 * @var string
	 */
	public $default_base_api_scheme;

	/**
	 * Gateway disabled message
	 *
	 * @var string
	 */
	public $msg;

	/**
	 * Gateway success redirect URL.
	 *
	 * @var string
	 */
	public $success_redirect_url;

	/**
	 *  Gateway error redirection URL.
	 *
	 * @var string
	 */
	public $error_redirect_url;

	/**
	 *  Webhook secret key.
	 *
	 * @var string
	 */
	public $webhook_secret;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'bictorys';
		$this->method_title       = __( 'Bictorys', 'bictorys-payment-gateway-for-woocommerce' );
		/*
		 * translators:
		 * %1$s: Link to sign up for a Bictorys account
		 * %2$s: Link to get API keys
		 */
		$this->method_description = sprintf( __( 'Bictorys provide merchants with the tools and services needed to accept online payments from local and international customers using Mastercard, Visa, Verve Cards and Bank Accounts. <a href="%1$s" target="_blank">Sign up</a> for a Bictorys account, and <a href="%2$s" target="_blank">get your API keys</a>.', 'bictorys-payment-gateway-for-woocommerce' ), 'https://bictorys.com', 'https://dashboard.bictorys.com/#/settings/developer' );
		$this->has_fields         = true;

		$this->payment_page = $this->get_option( 'payment_page' );

		$this->supports = array(
			'products',
			'refunds',
			'tokenization',
			'subscriptions',
			'multiple_subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
			'subscription_payment_method_change_customer',
		);

		// Load the form fields
		$this->init_form_fields();

		// Load the settings
		$this->init_settings();

		// Get setting values

		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );
		$this->enabled            = $this->get_option( 'enabled' );
		$this->testmode           = $this->get_option( 'testmode' ) === 'yes' ? true : false;
		$this->autocomplete_order = $this->get_option( 'autocomplete_order' ) === 'yes' ? true : false;

		$this->test_public_key = $this->get_option( 'test_public_key' );
		$this->test_secret_key = $this->get_option( 'test_secret_key' );

		$this->live_public_key = $this->get_option( 'live_public_key' );
		$this->live_secret_key = $this->get_option( 'live_secret_key' );

		$this->saved_cards = $this->get_option( 'saved_cards' ) === 'yes' ? true : false;

		$this->split_payment              = $this->get_option( 'split_payment' ) === 'yes' ? true : false;
		$this->remove_cancel_order_button = $this->get_option( 'remove_cancel_order_button' ) === 'yes' ? true : false;
		$this->subaccount_code            = $this->get_option( 'subaccount_code' );
		$this->merchant_reference         = $this->get_option( 'merchant_reference' );
		$this->success_redirect_url       = $this->get_option( 'success_redirect_url' );
		$this->error_redirect_url         = $this->get_option( 'error_redirect_url' );
		$this->charges_account            = $this->get_option( 'split_payment_charge_account' );
		$this->transaction_charges        = $this->get_option( 'split_payment_transaction_charge' );
		$this->webhook_secret         	  = $this->get_option( 'webhook_secret' );

        $this->request_id = wp_generate_uuid4();

		$this->custom_metadata = $this->get_option( 'custom_metadata' ) === 'yes' ? true : false;

		$this->meta_order_id         = $this->get_option( 'meta_order_id' ) === 'yes' ? true : false;
		$this->meta_name             = $this->get_option( 'meta_name' ) === 'yes' ? true : false;
		$this->meta_email            = $this->get_option( 'meta_email' ) === 'yes' ? true : false;
		$this->meta_phone            = $this->get_option( 'meta_phone' ) === 'yes' ? true : false;
		$this->meta_billing_address  = $this->get_option( 'meta_billing_address' ) === 'yes' ? true : false;
		$this->meta_shipping_address = $this->get_option( 'meta_shipping_address' ) === 'yes' ? true : false;
		$this->meta_products         = $this->get_option( 'meta_products' ) === 'yes' ? true : false;

		$this->public_key = $this->testmode ? $this->test_public_key : $this->live_public_key;
		$this->secret_key = $this->testmode ? $this->test_secret_key : $this->live_secret_key;
		$this->default_base_uri = $this->testmode ? self::DEFAULT_TEST_BASE_URI : self::DEFAULT_LIVE_BASE_URI;
		$this->default_base_api_scheme = $this->testmode ? self::DEFAULT_TEST_API_SCHEME : self::DEFAULT_LIVE_API_SCHEME;

		// Hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);

		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

		// Payment listener/API hook.
		add_action( 'woocommerce_api_Bictorys_PG_WC_Gateway', array( $this, 'verify_bictorys_transaction' ) );

		// Webhook listener/API hook.
		add_action( 'woocommerce_api_bts_wc_bictorys_webhook', array( $this, 'process_webhooks' ) );

		// Check if the gateway can be used.
		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = false;
		}

	}

	/**
	 * Check if this gateway is enabled and available in the user's country.
	 */
	public function is_valid_for_use() {
		if ( ! in_array( get_woocommerce_currency(), apply_filters( 'bictorys_pg_wc_supported_currencies', array( 'NGN', 'USD', 'ZAR', 'GHS', 'KES', 'XOF', 'EGP' ) ) ) ) {

			/*
			 * translators:
			 * %s: Link to the WooCommerce general settings page
			 */
			$this->msg = sprintf( __( 'Bictorys does not support your store currency. Kindly set it to either NGN (&#8358), GHS (&#x20b5;), USD (&#36;), KES (KSh), ZAR (R), XOF (CFA), or EGP (E£) <a href="%s">here</a>', 'bictorys-payment-gateway-for-woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=general' ) );

			return false;

		}

		return true;

	}

	/**
	 * Display bictorys payment icon.
	 */
	public function get_icon() {
		if ( 'mobile_money' === $this->payment_page ) {
			$icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/wc_bictorys_mobile.png', WC_BICTORYS_MAIN_FILE ) ) . '" alt="Bictorys Payment Options" />';
		} elseif ( 'card' === $this->payment_page ) {
			$icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/wc_bictorys_card.png', WC_BICTORYS_MAIN_FILE ) ) . '" alt="Bictorys Payment Options" />';
		} elseif ( 'all' === $this->payment_page ) {
			$icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/wc_bictorys_all.png', WC_BICTORYS_MAIN_FILE ) ) . '" alt="Bictorys Payment Options" />';
		} else {
			$icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/wc_bictorys_all.png', WC_BICTORYS_MAIN_FILE ) ) . '" alt="Bictorys Payment Options" />';
		}

		return apply_filters( 'bictorys_pg_wc_gateway_icon', $icon, $this->id );

	}

	/**
	 * Check if Bictorys merchant details is filled.
	 */
	public function admin_notices() {
		if ( $this->enabled == 'no' ) {
			return;
		}

		// Check required fields.
		if ( ! ( $this->public_key && $this->secret_key ) ) {
			/*
			 * translators:
			 * %s: Link to the WooCommerce settings page for Bictorys
			 */
			echo '<div class="error"><p>' . sprintf( esc_html__( 'Please enter your Bictorys merchant details <a href="%s">here</a> to be able to use the Bictorys WooCommerce plugin.', 'bictorys-payment-gateway-for-woocommerce' ), esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bictorys' ) ) ) . '</p></div>';
			return;
		}
	}

	/**
	 * Check if Bictorys gateway is enabled.
	 *
	 * @return bool
	 */
	public function is_available() {
		if ( 'yes' == $this->enabled ) {

			if ( ! ( $this->public_key && $this->secret_key ) ) {

				return false;

			}

			return true;

		}

		return false;

	}

	/**
	 * Admin Panel Options.
	 */
	public function admin_options() {

		?>

		<h2><?php esc_html_e( 'Bictorys', 'bictorys-payment-gateway-for-woocommerce' ); ?></h2>
		<?php
		if ( function_exists( 'wc_back_link' ) ) {
			wc_back_link( __( 'Return to payments', 'bictorys-payment-gateway-for-woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=checkout' ) );
		}
		?>
		</h2>

		<h4>
			<?php
			/* translators:
			 * %1$s: Link to set webhook URL
			 * %2$s: Webhook URL
			 */
			printf(
				wp_kses_post(
					'Optional: To avoid situations where bad network makes it impossible to verify transactions, set your webhook URL <a href="%1$s" target="_blank" rel="noopener noreferrer">here</a> to the URL below<span style="color: red"><pre><code>%2$s</code></pre></span>'
				),
				esc_url('https://dashboard.bictorys.com/#/settings/developer'),
				esc_html(WC()->api_request_url('Bts_WC_Bictorys_Webhook'))
			);
			?>
		</h4>

		<?php

		if ( $this->is_valid_for_use() ) {

			echo '<table class="form-table">';
			$this->generate_settings_html();
			echo '</table>';

		} else {
			?>
			<div class="inline error"><p><strong><?php esc_html_e( 'Bictorys Payment Gateway Disabled', 'bictorys-payment-gateway-for-woocommerce' ); ?></strong>: <?php echo esc_html( $this->msg ); ?></p></div>

			<?php
		}

	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$form_fields = array(
			'enabled'                          => array(
				'title'       => __( 'Enable/Disable', 'bictorys-payment-gateway-for-woocommerce' ),
				'label'       => __( 'Enable Bictorys', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => __( 'Enable Bictorys as a payment option on the checkout page.', 'bictorys-payment-gateway-for-woocommerce' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'title'                            => array(
				'title'       => __( 'Title', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the payment method title which the user sees during checkout.', 'bictorys-payment-gateway-for-woocommerce' ),
				'default'     => __( 'Debit/Credit Cards', 'bictorys-payment-gateway-for-woocommerce' ),
				'desc_tip'    => true,
			),
			'description'                      => array(
				'title'       => __( 'Description', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the payment method description which the user sees during checkout.', 'bictorys-payment-gateway-for-woocommerce' ),
				'default'     => __( 'Make payment using your debit and credit cards', 'bictorys-payment-gateway-for-woocommerce' ),
				'desc_tip'    => true,
			),
			'testmode'                         => array(
				'title'       => __( 'Test mode', 'bictorys-payment-gateway-for-woocommerce' ),
				'label'       => __( 'Enable Test Mode', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => __( 'Test mode enables you to test payments before going live. <br />Once the LIVE MODE is enabled on your Bictorys account uncheck this.', 'bictorys-payment-gateway-for-woocommerce' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'payment_page'                     => array(
				'title'       => __( 'Payment Option', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Select wich payment methods are available to the customer.', 'bictorys-payment-gateway-for-woocommerce' ),
				'default'     => '',
				'desc_tip'    => false,
				'options'     => array(
					''          	=> __( 'Select One', 'bictorys-payment-gateway-for-woocommerce' ),
					'mobile_money'  => __( 'Mobile Money', 'bictorys-payment-gateway-for-woocommerce' ),
					'card'  		=> __( 'Credit Card', 'bictorys-payment-gateway-for-woocommerce' ),
					'all'  			=> __( 'Mobile Money + Credit Card', 'bictorys-payment-gateway-for-woocommerce' ),
				),
			),
			'test_secret_key'                  => array(
				'title'       => __( 'Test Secret Key', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'password',
				'description' => __( 'Enter your Test Secret Key here', 'bictorys-payment-gateway-for-woocommerce' ),
				'default'     => '',
			),
			'test_public_key'                  => array(
				'title'       => __( 'Test Public Key', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter your Test Public Key here.', 'bictorys-payment-gateway-for-woocommerce' ),
				'default'     => '',
			),
			'live_secret_key'                  => array(
				'title'       => __( 'Live Secret Key', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'password',
				'description' => __( 'Enter your Live Secret Key here.', 'bictorys-payment-gateway-for-woocommerce' ),
				'default'     => '',
			),
			'live_public_key'                  => array(
				'title'       => __( 'Live Public Key', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter your Live Public Key here.', 'bictorys-payment-gateway-for-woocommerce' ),
				'default'     => '',
			),
			'webhook_secret'	=> array(
				'title'       => __( 'Webhook Secret', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter the webhook secret key here.', 'bictorys-payment-gateway-for-woocommerce' ),
				'class'       => 'woocommerce_bictorys_webhook_secret',
				'default'     => '',
			),
			'autocomplete_order'               => array(
				'title'       => __( 'Autocomplete Order After Payment', 'bictorys-payment-gateway-for-woocommerce' ),
				'label'       => __( 'Autocomplete Order', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'checkbox',
				'class'       => 'wc-bictorys-autocomplete-order',
				'description' => __( 'If enabled, the order will be marked as complete after successful payment', 'bictorys-payment-gateway-for-woocommerce' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'remove_cancel_order_button'       => array(
				'title'       => __( 'Remove Cancel Order & Restore Cart Button', 'bictorys-payment-gateway-for-woocommerce' ),
				'label'       => __( 'Remove the cancel order & restore cart button on the pay for order page', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'merchant_reference'                  => array(
				'title'       => __( 'Merchant Reference', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter the merchant reference here.', 'bictorys-payment-gateway-for-woocommerce' ),
				'class'       => 'woocommerce_bictorys_merchant_reference',
				'default'     => '',
			),
			'success_redirect_url'	=> array(
				'title'       => __( 'Success Redirect URL', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter the payment success redirect URL here.', 'bictorys-payment-gateway-for-woocommerce' ),
				'class'       => 'woocommerce_bictorys_success_redirect_url',
				'default'     => '',
			),
			'error_redirect_url'	=> array(
				'title'       => __( 'Error Redirect URL', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Enter the payment error or cancel redirect URL here.', 'bictorys-payment-gateway-for-woocommerce' ),
				'class'       => 'woocommerce_bictorys_error_redirect_url',
				'default'     => '',
			),
			'custom_gateways'                  => array(
				'title'       => __( 'Additional Bictorys Gateways', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Create additional custom Bictorys based gateways. This allows you to create additional Bictorys gateways using custom filters. You can create a gateway that accepts only verve cards, a gateway that accepts only bank payment, a gateway that accepts a specific bank issued cards.', 'bictorys-payment-gateway-for-woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
				'options'     => array(
					''  => __( 'Select One', 'bictorys-payment-gateway-for-woocommerce' ),
					'1' => __( '1 gateway', 'bictorys-payment-gateway-for-woocommerce' ),
					'2' => __( '2 gateways', 'bictorys-payment-gateway-for-woocommerce' ),
					'3' => __( '3 gateways', 'bictorys-payment-gateway-for-woocommerce' ),
					'4' => __( '4 gateways', 'bictorys-payment-gateway-for-woocommerce' ),
					'5' => __( '5 gateways', 'bictorys-payment-gateway-for-woocommerce' ),
				),
			),
			'saved_cards'                      => array(
				'title'       => __( 'Saved Cards', 'bictorys-payment-gateway-for-woocommerce' ),
				'label'       => __( 'Enable Payment via Saved Cards', 'bictorys-payment-gateway-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => __( 'If enabled, users will be able to pay with a saved card during checkout. Card details are saved on Bictorys servers, not on your store.<br>Note that you need to have a valid SSL certificate installed.', 'bictorys-payment-gateway-for-woocommerce' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
		);

		if ( 'NGN' !== get_woocommerce_currency() ) {
			unset( $form_fields['custom_gateways'] );
		}

		$this->form_fields = $form_fields;

	}

	/**
	 * Payment form on checkout page
	 */
	public function payment_fields() {
		if ( $this->description ) {
			$description_output = wpautop( wptexturize( $this->description ) );
			echo esc_html( $description_output );
		}

		if ( ! is_ssl() ) {
			return;
		}

		if ( $this->supports( 'tokenization' ) && is_checkout() && $this->saved_cards && is_user_logged_in() ) {
			$this->tokenization_script();
			$this->saved_payment_methods();
			$this->save_payment_method_checkbox();
		}

	}

	/**
	 * Outputs scripts used for bictorys payment.
	 */
	public function payment_scripts() {
		if ( isset( $_GET['pay_for_order'] ) || ! is_checkout_pay_page() ) {
			// Check if the nonce is set and valid
			if (isset($_GET['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'pay_for_order_nonce')) {
				// Process your form data here
			} else {
				return;
			}
		}

		if ( $this->enabled === 'no' ) {
			return;
		}

		$order_key = urldecode(sanitize_text_field(wp_unslash($_GET['key'])));
		$order_id  = absint( get_query_var( 'order-pay' ) );

		$order = wc_get_order( $order_id );

		if ( $this->id !== $order->get_payment_method() ) {
			return;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'jquery' );

		wp_enqueue_script( 'bictorys', 'https://js.bictorys.com/v2/inline.js', array( 'jquery' ), WC_BICTORYS_VERSION, false );

		wp_enqueue_script( 'wc_bictorys', plugins_url( 'assets/js/bictorys' . $suffix . '.js', WC_BICTORYS_MAIN_FILE ), array( 'jquery', 'bictorys' ), WC_BICTORYS_VERSION, false );

		$bictorys_params = array(
			'key' => $this->public_key,
		);

		if ( is_checkout_pay_page() && get_query_var( 'order-pay' ) ) {

			$email         = $order->get_billing_email();
			$amount        = $order->get_total() * 100;
			$txnref        = $order_id . '_' . time();
			$the_order_id  = $order->get_id();
			$the_order_key = $order->get_order_key();
			$currency      = $order->get_currency();

			if ( $the_order_id == $order_id && $the_order_key == $order_key ) {

				$bictorys_params['email']    = $email;
				$bictorys_params['amount']   = $amount;
				$bictorys_params['txnref']   = $txnref;
				$bictorys_params['currency'] = $currency;

			}

			if ( $this->split_payment ) {

				$bictorys_params['subaccount_code'] = $this->subaccount_code;
				$bictorys_params['charges_account'] = $this->charges_account;

				if ( empty( $this->transaction_charges ) ) {
					$bictorys_params['transaction_charges'] = '';
				} else {
					$bictorys_params['transaction_charges'] = $this->transaction_charges * 100;
				}
			}

			if ( $this->custom_metadata ) {

				if ( $this->meta_order_id ) {

					$bictorys_params['meta_order_id'] = $order_id;

				}

				if ( $this->meta_name ) {

					$bictorys_params['meta_name'] = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

				}

				if ( $this->meta_email ) {

					$bictorys_params['meta_email'] = $email;

				}

				if ( $this->meta_phone ) {

					$bictorys_params['meta_phone'] = $order->get_billing_phone();

				}

				if ( $this->meta_products ) {

					$line_items = $order->get_items();

					$products = '';

					foreach ( $line_items as $item_id => $item ) {
						$name      = $item['name'];
						$quantity  = $item['qty'];
						$products .= $name . ' (Qty: ' . $quantity . ')';
						$products .= ' | ';
					}

					$products = rtrim( $products, ' | ' );

					$bictorys_params['meta_products'] = $products;

				}

				if ( $this->meta_billing_address ) {

					$billing_address = $order->get_formatted_billing_address();
					$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

					$bictorys_params['meta_billing_address'] = $billing_address;

				}

				if ( $this->meta_shipping_address ) {

					$shipping_address = $order->get_formatted_shipping_address();
					$shipping_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $shipping_address ) );

					if ( empty( $shipping_address ) ) {

						$billing_address = $order->get_formatted_billing_address();
						$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

						$shipping_address = $billing_address;

					}

					$bictorys_params['meta_shipping_address'] = $shipping_address;

				}
			}

			$order->update_meta_data( '_bictorys_txn_ref', $txnref );
			$order->save();
		}

		wp_localize_script( 'wc_bictorys', 'wc_bictorys_params', $bictorys_params );

	}

	/**
	 * Load admin scripts.
	 */
	public function admin_scripts() {
		if ( 'woocommerce_page_wc-settings' !== get_current_screen()->id ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$bictorys_admin_params = array(
			'plugin_url' => WC_BICTORYS_URL,
		);

		wp_enqueue_script( 'wc_bictorys_admin', plugins_url( 'assets/js/bictorys-admin' . $suffix . '.js', WC_BICTORYS_MAIN_FILE ), array(), WC_BICTORYS_VERSION, true );

		wp_localize_script( 'wc_bictorys_admin', 'wc_bictorys_admin_params', $bictorys_admin_params );

	}

	/**
	 * Process the payment.
	 *
	 * @param int $order_id
	 *
	 * @return array|void
	 */
	public function process_payment( $order_id ) {
		return $this->process_redirect_payment_option( $order_id, $this->payment_page );
	}

	/**
	 * Process a redirect payment option payment.
	 *
	 * @since 1.0.0
	 * @param int $order_id
	 * @return array|void
	 */
	public function process_redirect_payment_option( $order_id, $payment_page ) {
		$order        = wc_get_order( $order_id );
		$amount       = $order->get_total() * 100;
		$txnref       = $order_id . '_' . time();
		$callback_url = WC()->api_request_url( 'Bictorys_PG_WC_Gateway' );

		$payment_channels = $this->get_gateway_payment_channels( $order );

		switch ($payment_page) {
			case 'all':
				$payment_category = '';
				break;
			case 'card':
				$payment_category = '?payment_category=card';
				break;
			case 'mobile_money':
				$payment_category = '?payment_category=mobile_money';
				break;
			default:
				$payment_category = '';
		}

		$bictorys_params = array(
			'amount'       => $amount,
			'email'        => $order->get_billing_email(),
			'currency'     => $order->get_currency(),
			'reference'    => $txnref,
			'callback_url' => $callback_url,
		);

		if ( ! empty( $payment_channels ) ) {
			$bictorys_params['channels'] = $payment_channels;
		}

		if ( $this->split_payment ) {

			$bictorys_params['subaccount'] = $this->subaccount_code;
			$bictorys_params['bearer']     = $this->charges_account;

			if ( empty( $this->transaction_charges ) ) {
				$bictorys_params['transaction_charge'] = '';
			} else {
				$bictorys_params['transaction_charge'] = $this->transaction_charges * 100;
			}
		}

		$bictorys_params['metadata']['custom_fields'] = $this->get_custom_fields( $order_id );
		$bictorys_params['metadata']['cancel_action'] = wc_get_cart_url();

		$order->update_meta_data( '_bictorys_txn_ref', $txnref );
		$createCustomerResponse = $this->createCustomer($order);
        if ($createCustomerResponse['success'] === true) {
            if (($initiateTransactionResponse = $this->initiateTransaction($order, $createCustomerResponse['data'], $payment_category))) {
                $order->set_meta_data([
                    'merchantReference' => $createCustomerResponse['data']->merchantReference
                ]);

                return array(
                    'result'   => 'success',
                    'redirect' => $initiateTransactionResponse->link . $payment_category,
			    );
            }
        } else {
			if($this->testmode) {
				wc_add_notice( 'Error ' . $createCustomerResponse['statusCode'] . ' : ' . $createCustomerResponse['details'], 'error' );
			}
		}

        $order->save();
        wc_add_notice( __( 'Unable to process payment try again', 'bictorys-payment-gateway-for-woocommerce' ), 'error' );
        return;
	}

	/**
	 * Creates a customer using the provided order information.
	 *
	 * @param WC_Order $order The order object containing the customer information.
	 * @throws Exception_Class If there is an error creating the customer.
	 * @return array An array containing the success status and customer data.
	 */
	private function createCustomer($order)
    {
        /** @var WC_Order $order */
        $params = array(
            'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'phone' => $order->get_billing_phone(),
            'email' => $order->get_billing_email(),
            'city' => $order->get_billing_city(),
            'address' => $order->get_billing_address_1(),
            'postalCode' => $order->get_billing_postcode(),
            'locale' => self::DEFAULT_LOCAL_CODE,
            'country' => $order->get_billing_country(),
        );

        $url = $this->buildUrl(
            self::API_CUSTOMER_MANAGEMENT_ENDPOINT,
            self::API_CUSTOMER_MANAGEMENT_RESOURCE,
            self::API_CUSTOMER_MANAGEMENT_PORT,
                array(
                       'createOrUpdate' => true
                )
        );

        $headers = array(
            'Content-Type' => self::DEFAULT_CONTENT_TYPE,
            'Request-Id' => $this->request_id,
            'X-API-Key' => $this->secret_key
        );

        $args = array(
            'headers' => $headers,
            'timeout' => self::DEFAULT_TIMEOUT_IN_SECONDE,
            'body'    => wp_json_encode( $params ),
        );

        $request = wp_remote_post( $url, $args );
        if ( ! is_wp_error( $request ) && $this->isResponseOk( $request ) ) {
            return ['success' => true, 'data' => json_decode( wp_remote_retrieve_body( $request ) )];
        }

		$body = json_decode( wp_remote_retrieve_body( $request ) );
		$details = $body->details ?? 'No details';

		return [
			'success' => false,
			'data' => json_decode( wp_remote_retrieve_body( $request )),
			'details' => $details,
			'statusCode' => wp_remote_retrieve_response_code( $request )
		];
    }

    private function initiateTransaction($order, $createCustomerResponse, $payment_category)
    {
        /** @var WC_Order $order */
        $payUri = $this->buildUrl(
                self::API_PAY_CHARGE_ENDPOINT,
                self::API_PAY_CHARGE_RESOURCE,
                self::API_PAY_CHARGE_PORT,
        );

        $headers = array(
            'Content-Type' => self::DEFAULT_CONTENT_TYPE,
            'Request-Id' => $this->request_id,
            'X-API-Key' => $this->secret_key
        );

		// Get the store address settings
		$storeAddress = get_option('woocommerce_store_address');

		// Extract the country from the address (default to Sénégal if not found)
		$storeCountry = !empty($storeAddress['country']) ? $storeAddress['country'] : 'sn';

		$possible_params = [
			"merchantReference" => $createCustomerResponse->merchantReference,
			"successRedirectUrl" => $this->success_redirect_url,
			"errorRedirectUrl" => $this->error_redirect_url,
			"orderDetails" => $this->buildOrderDetails($order),
			"paymentReference" => $order->get_id(),
			"customerId" => $createCustomerResponse->id,
			"paymentCategory" => $payment_category,
			//"country" => $storeCountry
		];
		
		foreach ($possible_params as $key => $value) {
			if (!is_null($value)) {
				$params[$key] = $value;
			}
		}

		$params["amount"] = $order->get_total();
		$params["currency"] = $order->get_currency();
		$params["customerId"] = $createCustomerResponse->id;

        $args = array(
            'headers' => $headers,
            'timeout' => self::DEFAULT_TIMEOUT_IN_SECONDE,
            'body'    => wp_json_encode( $params ),
        );

        $request = wp_remote_post( $payUri, $args );

        if ( ! is_wp_error( $request ) && $this->isResponseOk( $request ) ) {
            return json_decode(wp_remote_retrieve_body($request));
        }

        return false;
    }

    private function isResponseOk($request)
    {
        $responseCode = (int) wp_remote_retrieve_response_code( $request );

        return $responseCode >= 200 && $responseCode < 300;
    }

    private function buildOrderDetails($order)
    {
        /** @var WC_Order $order */
        $itemData = [];

        $items = $order->get_items();

        foreach ($items as $item) {
            /** @var WC_Order_Item_Product $item */

            $itemData[] = [
                "name" => $item->get_name(),
                "price" =>  (float) $item->get_total() / ((float) $item->get_quantity()),
                "quantity" => $item->get_quantity(),
                "taxRate" =>  $item->get_total_tax()
            ];
        }

        return $itemData;
    }

    private function buildUrl($endpoint, $resource, $port = null, $queryData = array())
    {
        null === $port && $port = '80';

        $queryStrData = http_build_query($queryData);

        $url = sprintf(
            '%s://%s:%s/%s/%s',
            $this->default_base_api_scheme,
            $this->default_base_uri,
            $port,
            $endpoint,
            $resource
        );

        if ('' != $queryStrData) {
            $url .= '?' . $queryStrData;
        }

        return $url;
    }

	/**
	 * Process a token payment.
	 *
	 * @param $token
	 * @param $order_id
	 *
	 * @return bool
	 */
	public function process_token_payment( $token, $order_id ) {
		if ( $token && $order_id ) {

			$order = wc_get_order( $order_id );

			$order_amount = $order->get_total() * 100;
			$txnref       = $order_id . '_' . time();

			$order->update_meta_data( '_bictorys_txn_ref', $txnref );
			$order->save();

			$bictorys_url = 'https://api.bictorys.com/transaction/charge_authorization';

			$headers = array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->secret_key,
			);

			$metadata['custom_fields'] = $this->get_custom_fields( $order_id );

			if ( strpos( $token, '###' ) !== false ) {
				$payment_token  = explode( '###', $token );
				$auth_code      = $payment_token[0];
				$customer_email = $payment_token[1];
			} else {
				$auth_code      = $token;
				$customer_email = $order->get_billing_email();
			}

			$body = array(
				'email'              => $customer_email,
				'amount'             => $order_amount,
				'metadata'           => $metadata,
				'authorization_code' => $auth_code,
				'reference'          => $txnref,
				'currency'           => $order->get_currency(),
			);

			$args = array(
				'body'    => wp_json_encode( $body ),
				'headers' => $headers,
				'timeout' => 60,
			);

			$request = wp_remote_post( $bictorys_url, $args );

			$response_code = wp_remote_retrieve_response_code( $request );

			if ( ! is_wp_error( $request ) && in_array( $response_code, array( 200, 400 ), true ) ) {

				$bictorys_response = json_decode( wp_remote_retrieve_body( $request ) );

				if ( ( 200 === $response_code ) && ( 'success' === strtolower( $bictorys_response->data->status ) ) ) {

					$order = wc_get_order( $order_id );

					if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {

						wp_redirect( $this->get_return_url( $order ) );

						exit;

					}

					$order_total      = $order->get_total();
					$order_currency   = $order->get_currency();
					$currency_symbol  = get_woocommerce_currency_symbol( $order_currency );
					$amount_paid      = $bictorys_response->data->amount / 100;
					$bictorys_ref     = $bictorys_response->data->reference;
					$payment_currency = $bictorys_response->data->currency;
					$gateway_symbol   = get_woocommerce_currency_symbol( $payment_currency );

					// check if the amount paid is equal to the order amount.
					if ( $amount_paid < $order_total ) {

						$order->update_status( 'on-hold', '' );

						$order->add_meta_data( '_transaction_id', $bictorys_ref, true );
						
						/*
						 * translators:
						 * %1$s: Line break
						 * %2$s: Line break
						 * %3$s: Line break
						 */
						$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'bictorys-payment-gateway-for-woocommerce' ), '<br />', '<br />', '<br />' );
						$notice_type = 'notice';

						// Add Customer Order Note
						$order->add_order_note( $notice, 1 );

						// Add Admin Order Note
						/*
						 * translators:
						 * %1$s: Line break
						 * %2$s: Line break
						 * %3$s: Line break
						 * %4$s: Currency symbol for Amount Paid
						 * %5$s: Amount Paid
						 * %6$s: Currency symbol for Total Order Amount
						 * %7$s: Total Order Amount
						 * %8$s: Line break
						 * %9$s: Bictorys Transaction Reference
						 */
						$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Bictorys Transaction Reference:</strong> %9$s', 'bictorys-payment-gateway-for-woocommerce' ), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $bictorys_ref );
						$order->add_order_note( $admin_order_note );

						wc_add_notice( $notice, $notice_type );

					} else {

						if ( $payment_currency !== $order_currency ) {

							$order->update_status( 'on-hold', '' );

							$order->update_meta_data( '_transaction_id', $bictorys_ref );

							/*
							 * translators:
							 * %1$s: Line break
							 * %2$s: Line break
							 * %3$s: Line break
							 */
							$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'bictorys-payment-gateway-for-woocommerce' ), '<br />', '<br />', '<br />' );
							$notice_type = 'notice';

							// Add Customer Order Note
							$order->add_order_note( $notice, 1 );

							// Add Admin Order Note
							/*
							 * translators:
							 * %1$s: Line break
							 * %2$s: Line break
							 * %3$s: Line break
							 * %4$s: Order Currency
							 * %5$s: Currency Symbol for Order Currency
							 * %6$s: Payment Currency
							 * %7$s: Currency Symbol for Payment Currency
							 * %8$s: Line break
							 * %9$s: Bictorys Transaction Reference
							 */
							$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Bictorys Transaction Reference:</strong> %9$s', 'bictorys-payment-gateway-for-woocommerce' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $bictorys_ref );
							$order->add_order_note( $admin_order_note );

							function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

							wc_add_notice( $notice, $notice_type );

						} else {

							$order->payment_complete( $bictorys_ref );

							$order->add_order_note( sprintf( 'Payment via Bictorys successful (Transaction Reference: %s)', $bictorys_ref ) );

							if ( $this->is_autocomplete_order_enabled( $order ) ) {
								$order->update_status( 'completed' );
							}
						}
					}

					$order->save();

					$this->save_subscription_payment_token( $order_id, $bictorys_response );

					WC()->cart->empty_cart();

					return true;

				} else {

					$order_notice  = __( 'Payment was declined by Bictorys.', 'bictorys-payment-gateway-for-woocommerce' );
					$failed_notice = __( 'Payment failed using the saved card. Kindly use another payment option.', 'bictorys-payment-gateway-for-woocommerce' );

					if ( ! empty( $bictorys_response->message ) ) {

						/*
						 * translators:
						 * %s: Reason for payment decline.
						 */
						$order_notice  = sprintf( __( 'Payment was declined by Bictorys. Reason: %s.', 'bictorys-payment-gateway-for-woocommerce' ), $bictorys_response->message );
						/*
						 * translators:
						 * %s: Reason for payment failure.
						 */
						$failed_notice = sprintf( __( 'Payment failed using the saved card. Reason: %s. Kindly use another payment option.', 'bictorys-payment-gateway-for-woocommerce' ), $bictorys_response->message );

					}

					$order->update_status( 'failed', $order_notice );

					wc_add_notice( $failed_notice, 'error' );

					do_action( 'Bictorys_PG_WC_Gateway_process_payment_error', $failed_notice, $order );

					return false;
				}
			}
		} else {

			wc_add_notice( __( 'Payment Failed.', 'bictorys-payment-gateway-for-woocommerce' ), 'error' );

		}

	}

	/**
	 * Show new card can only be added when placing an order notice.
	 */
	public function add_payment_method() {
		wc_add_notice( __( 'You can only add a new card when placing an order.', 'bictorys-payment-gateway-for-woocommerce' ), 'error' );

		return;

	}

	/**
	 * Displays the payment page.
	 *
	 * @param $order_id
	 */
	public function receipt_page( $order_id ) {
		$order = wc_get_order( $order_id );

		echo '<div id="wc-bictorys-form">';

		echo '<p>' . esc_html__( 'Thank you for your order, please click the button below to pay with Bictorys.', 'bictorys-payment-gateway-for-woocommerce' ) . '</p>';

		echo '<div id="bictorys_form"><form id="order_review" method="post" action="' . esc_url( WC()->api_request_url( 'Bictorys_PG_WC_Gateway' ) ) . '"></form><button class="button" id="bictorys-payment-button">' . esc_html__( 'Pay Now', 'bictorys-payment-gateway-for-woocommerce' ) . '</button>';

		if ( ! $this->remove_cancel_order_button ) {
			echo '<a class="button cancel" id="bictorys-cancel-payment-button" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . esc_html__( 'Cancel order & restore cart', 'bictorys-payment-gateway-for-woocommerce' ) . '</a></div>';

		}

		echo '</div>';

	}

	/**
	 * Verify Bictorys payment.
	 */
	public function verify_bictorys_transaction() {
		if ( isset( $_REQUEST['bictorys_txnref'] ) || isset( $_REQUEST['reference'] ) ) {
			if (isset($_REQUEST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'bictorys_txnref_nonce')) {
				$bictorys_txn_ref = isset( $_REQUEST['bictorys_txnref'] ) ? sanitize_text_field( $_REQUEST['bictorys_txnref'] ) : sanitize_text_field( $_REQUEST['reference'] );
			} else {
				$bictorys_txn_ref = false;
			}
		} else {
			$bictorys_txn_ref = false;
		}

		@ob_clean();

		if ( $bictorys_txn_ref ) {

            $bictorys_response = $this->get_bictorys_transaction( $bictorys_txn_ref );

			if ( false !== $bictorys_response ) {

				if ( 'success' == $bictorys_response->data->status ) {

					$order_details = explode( '_', $bictorys_response->data->reference );
					$order_id      = (int) $order_details[0];
					$order         = wc_get_order( $order_id );

					if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {

						wp_redirect( $this->get_return_url( $order ) );

						exit;

					}

					$order_total      = $order->get_total();
					$order_currency   = $order->get_currency();
					$currency_symbol  = get_woocommerce_currency_symbol( $order_currency );
					$amount_paid      = $bictorys_response->data->amount / 100;
					$bictorys_ref     = $bictorys_response->data->reference;
					$payment_currency = strtoupper( $bictorys_response->data->currency );
					$gateway_symbol   = get_woocommerce_currency_symbol( $payment_currency );

					// check if the amount paid is equal to the order amount.
					if ( $amount_paid < $order_total ) {

						$order->update_status( 'on-hold', '' );

						$order->add_meta_data( '_transaction_id', $bictorys_ref, true );

						/*
						 * translators:
						 * %1$s: Line break
						 * %2$s: Line break
						 * %3$s: Line break
						 */
						$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'bictorys-payment-gateway-for-woocommerce' ), '<br />', '<br />', '<br />' );
						$notice_type = 'notice';

						// Add Customer Order Note
						$order->add_order_note( $notice, 1 );

						// Add Admin Order Note
						/*
						 * translators:
						 * %1$s: Line break
						 * %2$s: Line break
						 * %3$s: Line break
						 * %4$s: Currency symbol for Amount Paid
						 * %5$s: Amount Paid
						 * %6$s: Currency symbol for Total Order Amount
						 * %7$s: Total Order Amount
						 * %8$s: Line break
						 * %9$s: Bictorys Transaction Reference
						 */
						$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Bictorys Transaction Reference:</strong> %9$s', 'bictorys-payment-gateway-for-woocommerce' ), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $bictorys_ref );
						$order->add_order_note( $admin_order_note );

						function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

						wc_add_notice( $notice, $notice_type );

					} else {

						if ( $payment_currency !== $order_currency ) {

							$order->update_status( 'on-hold', '' );

							$order->update_meta_data( '_transaction_id', $bictorys_ref );
							/*
							 * translators:
							 * %1$s: Line break
							 * %2$s: Line break
							 * %3$s: Line break
							 */
							$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'bictorys-payment-gateway-for-woocommerce' ), '<br />', '<br />', '<br />' );
							$notice_type = 'notice';

							// Add Customer Order Note
							$order->add_order_note( $notice, 1 );

							// Add Admin Order Note
							/*
							 * translators:
							 * %1$s: Line break
							 * %2$s: Line break
							 * %3$s: Line break
							 * %4$s: Order Currency
							 * %5$s: Currency Symbol for Order Currency
							 * %6$s: Payment Currency
							 * %7$s: Currency Symbol for Payment Currency
							 * %8$s: Line break
							 * %9$s: Bictorys Transaction Reference
							 */
							$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Bictorys Transaction Reference:</strong> %9$s', 'bictorys-payment-gateway-for-woocommerce' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $bictorys_ref );
							$order->add_order_note( $admin_order_note );

							function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

							wc_add_notice( $notice, $notice_type );

						} else {

							$order->payment_complete( $bictorys_ref );
							/*
							 * translators: %s: Transaction Reference.
							 */
							$order->add_order_note( sprintf( __( 'Payment via Bictorys successful (Transaction Reference: %s)', 'bictorys-payment-gateway-for-woocommerce' ), $bictorys_ref ) );

							if ( $this->is_autocomplete_order_enabled( $order ) ) {
								$order->update_status( 'completed' );
							}
						}
					}

					$order->save();

					$this->save_card_details( $bictorys_response, $order->get_user_id(), $order_id );

					WC()->cart->empty_cart();

				} else {

					$order_details = explode('_', sanitize_text_field(wp_unslash($_REQUEST['bictorys_txnref'])));

					$order_id = (int) $order_details[0];

					$order = wc_get_order( $order_id );

					$order->update_status( 'failed', __( 'Payment was declined by Bictorys.', 'bictorys-payment-gateway-for-woocommerce' ) );

				}
			}

			wp_redirect( $this->get_return_url( $order ) );

			exit;
		}

		wp_redirect( wc_get_page_permalink( 'cart' ) );

		exit;

	}

	/**
	 * Process Webhook.
	 */
	public function process_webhooks() {
		if ( ( strtoupper( $_SERVER['REQUEST_METHOD'] ) !== 'POST' ) ) {
			exit;
		}

		// Read and sanitize the input
		$input = file_get_contents('php://input');
		$sanitized_input = sanitize_text_field($input);
		$event = json_decode($sanitized_input, true);

		// Get all headers
		$headers = getallheaders();

		// Check for the Secret Key header
		$secret_key = isset($headers['X-Secret-Key']) ? $headers['X-Secret-Key'] : '';

		// Verify the secret key
		if ($secret_key !== $this->webhook_secret) {
			return;
		}

		// Validate necessary fields in the event data
		if (!isset($event['status'], $event['paymentReference'])) {
			exit;
		}

		// Convert status to lowercase for comparison
		$status = strtolower($event['status']);

		if ($status !== 'succeeded' && $status !== 'authorized') {
			return;
		}

		// Ensure the event object is in the correct format
		if (!is_array($event) || !isset($event['paymentReference'])) {
			exit;
		}

		// Extract order details from payment reference
		$order_details = explode('_', sanitize_text_field($event['paymentReference']));

		sleep( 10 );

		// Validate order details format
		if (count($order_details) < 1 || !is_numeric($order_details[0])) {
			exit;
		}

		$order_id = (int) $order_details[0];
		$order = wc_get_order($order_id);

		if ( ! $order ) {
			return;
		}

		// Verify merchant reference
		/*
		if ($event['merchantReference'] !== $order->get_meta('merchantReference')) {
			exit;
		}
		*/

		// Respond with a 200 HTTP status code
		http_response_code(200);

		// Check order status and exit if already processed
		$order_status = strtolower($order->get_status());
		if (in_array($order_status, ['processing', 'completed', 'on-hold'], true)) {
			exit;
		}

		// Get currency details
		$order_currency = $order->get_currency();
		$currency_symbol = get_woocommerce_currency_symbol($order_currency);
		$order_total = $order->get_total();
		$amount_paid = floatval($event['amount']);
		$bictorys_ref = sanitize_text_field($event['id']);
		$payment_currency = strtoupper(sanitize_text_field($event['currency']));
		$gateway_symbol = get_woocommerce_currency_symbol($payment_currency);

		// check if the amount paid is equal to the order amount.
		if ( $amount_paid < $order_total ) {

			$order->update_status( 'on-hold', '' );

			$order->add_meta_data( '_transaction_id', $bictorys_ref, true );
			/*
			 * translators:
			 * %1$s: Line break
			 * %2$s: Line break
			 * %3$s: Line break
			 */
			$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'bictorys-payment-gateway-for-woocommerce' ), '<br />', '<br />', '<br />' );
			$notice_type = 'notice';

			// Add Customer Order Note.
			$order->add_order_note( $notice, 1 );

			// Add Admin Order Note.
			/*
			 * translators:
			 * %1$s: Line break
			 * %2$s: Line break
			 * %3$s: Line break
			 * %4$s: Currency symbol for Amount Paid
			 * %5$s: Amount Paid
			 * %6$s: Currency symbol for Total Order Amount
			 * %7$s: Total Order Amount
			 * %8$s: Line break
			 * %9$s: Bictorys Transaction Reference
			 */
			$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Bictorys Transaction Reference:</strong> %9$s', 'bictorys-payment-gateway-for-woocommerce' ), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $bictorys_ref );
			$order->add_order_note( $admin_order_note );

			function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

			wc_add_notice( $notice, $notice_type );

			WC()->cart->empty_cart();

		} else {

			if ( $payment_currency !== $order_currency ) {

				$order->update_status( 'on-hold', '' );

				$order->update_meta_data( '_transaction_id', $bictorys_ref );
				/*
				 * translators:
				 * %1$s: Line break
				 * %2$s: Line break
				 * %3$s: Line break
				 */
				$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'bictorys-payment-gateway-for-woocommerce' ), '<br />', '<br />', '<br />' );
				$notice_type = 'notice';

				// Add Customer Order Note.
				$order->add_order_note( $notice, 1 );

				// Add Admin Order Note.
				/*
				 * translators:
				 * %1$s: Line break
				 * %2$s: Line break
				 * %3$s: Line break
				 * %4$s: Order Currency
				 * %5$s: Currency Symbol for Order Currency
				 * %6$s: Payment Currency
				 * %7$s: Currency Symbol for Payment Currency
				 * %8$s: Line break
				 * %9$s: Bictorys Transaction Reference
				 */
				$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Bictorys Transaction Reference:</strong> %9$s', 'bictorys-payment-gateway-for-woocommerce' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $bictorys_ref );
				$order->add_order_note( $admin_order_note );

				function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

				wc_add_notice( $notice, $notice_type );

			} else {

				$order->payment_complete( $bictorys_ref );

				/*
				 * translators: %s: Transaction Reference.
				 */
				$order->add_order_note( sprintf( __( 'Payment via Bictorys successful (Transaction Reference: %s)', 'bictorys-payment-gateway-for-woocommerce' ), $bictorys_ref ) );


				WC()->cart->empty_cart();

				if ( $this->is_autocomplete_order_enabled( $order ) ) {
					$order->update_status( 'completed' );
				}
			}
		}

		$order->save();

		exit;
	}

	/**
	 * Save Customer Card Details.
	 *
	 * @param $bictorys_response
	 * @param $user_id
	 * @param $order_id
	 */
	public function save_card_details( $bictorys_response, $user_id, $order_id ) {
		$this->save_subscription_payment_token( $order_id, $bictorys_response );

		$order = wc_get_order( $order_id );

		$save_card = $order->get_meta( '_wc_bictorys_save_card' );

		if ( $user_id && $this->saved_cards && $save_card && $bictorys_response->data->authorization->reusable && 'card' == $bictorys_response->data->authorization->channel ) {

			$gateway_id = $order->get_payment_method();

			$last4          = $bictorys_response->data->authorization->last4;
			$exp_year       = $bictorys_response->data->authorization->exp_year;
			$brand          = $bictorys_response->data->authorization->card_type;
			$exp_month      = $bictorys_response->data->authorization->exp_month;
			$auth_code      = $bictorys_response->data->authorization->authorization_code;
			$customer_email = $bictorys_response->data->customer->email;

			$payment_token = "$auth_code###$customer_email";

			$token = new WC_Payment_Token_CC();
			$token->set_token( $payment_token );
			$token->set_gateway_id( $gateway_id );
			$token->set_card_type( strtolower( $brand ) );
			$token->set_last4( $last4 );
			$token->set_expiry_month( $exp_month );
			$token->set_expiry_year( $exp_year );
			$token->set_user_id( $user_id );
			$token->save();

			$order->delete_meta_data( '_wc_bictorys_save_card' );
			$order->save();
		}
	}

	/**
	 * Save payment token to the order for automatic renewal for further subscription payment.
	 *
	 * @param $order_id
	 * @param $bictorys_response
	 */
	public function save_subscription_payment_token( $order_id, $bictorys_response ) {
		if ( ! function_exists( 'wcs_order_contains_subscription' ) ) {
			return;
		}

		if ( $this->order_contains_subscription( $order_id ) && $bictorys_response->data->authorization->reusable && 'card' == $bictorys_response->data->authorization->channel ) {

			$auth_code      = $bictorys_response->data->authorization->authorization_code;
			$customer_email = $bictorys_response->data->customer->email;

			$payment_token = "$auth_code###$customer_email";

			// Also store it on the subscriptions being purchased or paid for in the order
			if ( function_exists( 'wcs_order_contains_subscription' ) && wcs_order_contains_subscription( $order_id ) ) {

				$subscriptions = wcs_get_subscriptions_for_order( $order_id );

			} elseif ( function_exists( 'wcs_order_contains_renewal' ) && wcs_order_contains_renewal( $order_id ) ) {

				$subscriptions = wcs_get_subscriptions_for_renewal_order( $order_id );

			} else {

				$subscriptions = array();

			}

			if ( empty( $subscriptions ) ) {
				return;
			}

			foreach ( $subscriptions as $subscription ) {
				$subscription->update_meta_data( '_bictorys_token', $payment_token );
				$subscription->save();
			}
		}

	}

	/**
	 * Get custom fields to pass to Bictorys.
	 *
	 * @param int $order_id WC Order ID
	 *
	 * @return array
	 */
	public function get_custom_fields( $order_id ) {
		$order = wc_get_order( $order_id );

		$custom_fields = array();

		$custom_fields[] = array(
			'display_name'  => 'Plugin',
			'variable_name' => 'plugin',
			'value'         => 'bictorys-payment-gateway-for-woocommerce',
		);
		
		if ( $this->custom_metadata ) {

			if ( $this->meta_order_id ) {

				$custom_fields[] = array(
					'display_name'  => 'Order ID',
					'variable_name' => 'order_id',
					'value'         => $order_id,
				);

			}

			if ( $this->meta_name ) {

				$custom_fields[] = array(
					'display_name'  => 'Customer Name',
					'variable_name' => 'customer_name',
					'value'         => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				);

			}

			if ( $this->meta_email ) {

				$custom_fields[] = array(
					'display_name'  => 'Customer Email',
					'variable_name' => 'customer_email',
					'value'         => $order->get_billing_email(),
				);

			}

			if ( $this->meta_phone ) {

				$custom_fields[] = array(
					'display_name'  => 'Customer Phone',
					'variable_name' => 'customer_phone',
					'value'         => $order->get_billing_phone(),
				);

			}

			if ( $this->meta_products ) {

				$line_items = $order->get_items();

				$products = '';

				foreach ( $line_items as $item_id => $item ) {
					$name     = $item['name'];
					$quantity = $item['qty'];
					$products .= $name . ' (Qty: ' . $quantity . ')';
					$products .= ' | ';
				}

				$products = rtrim( $products, ' | ' );

				$custom_fields[] = array(
					'display_name'  => 'Products',
					'variable_name' => 'products',
					'value'         => $products,
				);

			}

			if ( $this->meta_billing_address ) {

				$billing_address = $order->get_formatted_billing_address();
				$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

				$bictorys_params['meta_billing_address'] = $billing_address;

				$custom_fields[] = array(
					'display_name'  => 'Billing Address',
					'variable_name' => 'billing_address',
					'value'         => $billing_address,
				);

			}

			if ( $this->meta_shipping_address ) {

				$shipping_address = $order->get_formatted_shipping_address();
				$shipping_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $shipping_address ) );

				if ( empty( $shipping_address ) ) {

					$billing_address = $order->get_formatted_billing_address();
					$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

					$shipping_address = $billing_address;

				}
				$custom_fields[] = array(
					'display_name'  => 'Shipping Address',
					'variable_name' => 'shipping_address',
					'value'         => $shipping_address,
				);

			}

		}

		return $custom_fields;
	}

	/**
	 * Process a refund request from the Order details screen.
	 *
	 * @param int $order_id WC Order ID.
	 * @param float|null $amount Refund Amount.
	 * @param string $reason Refund Reason
	 *
	 * @return bool|WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		if ( ! ( $this->public_key && $this->secret_key ) ) {
			return false;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return false;
		}

		$order_currency = $order->get_currency();
		$transaction_id = $order->get_transaction_id();

        $bictorys_response = $this->get_bictorys_transaction( $transaction_id );

		if ( false !== $bictorys_response ) {

			if ( 'success' == $bictorys_response->data->status ) {

				/*
				 * translators: 1: Order ID, 2: Site URL.
				 */
				$merchant_note = sprintf( __( 'Refund for Order ID: #%1$s on %2$s', 'bictorys-payment-gateway-for-woocommerce' ), $order_id, get_site_url() );


				$body = array(
					'transaction'   => $transaction_id,
					'amount'        => $amount * 100,
					'currency'      => $order_currency,
					'customer_note' => $reason,
					'merchant_note' => $merchant_note,
				);

				$headers = array(
					'Authorization' => 'Bearer ' . $this->secret_key,
				);

				$args = array(
					'headers' => $headers,
					'timeout' => 60,
					'body'    => $body,
				);

				$refund_url = 'https://api.bictorys.com/refund';

				$refund_request = wp_remote_post( $refund_url, $args );

				if ( ! is_wp_error( $refund_request ) && 200 === wp_remote_retrieve_response_code( $refund_request ) ) {

					$refund_response = json_decode( wp_remote_retrieve_body( $refund_request ) );

					if ( $refund_response->status ) {
						$amount         = wc_price( $amount, array( 'currency' => $order_currency ) );
						$refund_id      = $refund_response->data->id;
						/*
						 * translators: 1: refunded amount, 2: refund ID, 3: reason for refund.
						 */
						$refund_message = sprintf( __( 'Refunded %1$s. Refund ID: %2$s. Reason: %3$s', 'bictorys-payment-gateway-for-woocommerce' ), $amount, $refund_id, $reason );
						$order->add_order_note( $refund_message );

						return true;
					}

				} else {

					$refund_response = json_decode( wp_remote_retrieve_body( $refund_request ) );

					if ( isset( $refund_response->message ) ) {
						return new WP_Error( 'error', $refund_response->message );
					} else {
						return new WP_Error( 'error', __( 'Can&#39;t process refund at the moment. Try again later.', 'bictorys-payment-gateway-for-woocommerce' ) );
					}
				}

			}

		}

	}

	/**
	 * Checks if WC version is less than passed in version.
	 *
	 * @param string $version Version to check against.
	 *
	 * @return bool
	 */
	public function is_wc_lt( $version ) {
		return version_compare( WC_VERSION, $version, '<' );
	}

	/**
	 * Checks if autocomplete order is enabled for the payment method.
	 *
	 * @since 1.0.0
	 * @param WC_Order $order Order object.
	 * @return bool
	 */
	protected function is_autocomplete_order_enabled( $order ) {
		$autocomplete_order = false;

		$payment_method = $order->get_payment_method();

		$bictorys_settings = get_option('woocommerce_' . $payment_method . '_settings');

		if ( isset( $bictorys_settings['autocomplete_order'] ) && 'yes' === $bictorys_settings['autocomplete_order'] ) {
			$autocomplete_order = true;
		}

		return $autocomplete_order;
	}

	/**
	 * Retrieve the payment channels configured for the gateway
	 *
	 * @since 1.0.0
	 * @param WC_Order $order Order object.
	 * @return array
	 */
	protected function get_gateway_payment_channels( $order ) {
		$payment_method = $order->get_payment_method();

		if ( 'bictorys' === $payment_method ) {
			return array();
		}

		$payment_channels = $this->payment_channels;

		if ( empty( $payment_channels ) ) {
			$payment_channels = array( 'card' );
		}

		return $payment_channels;
	}

	/**
	 * Retrieve a transaction from Bictorys.
	 *
	 * @since 1.0.0
	 * @param $bictorys_txn_ref
	 * @return false|mixed
	 */
	private function get_bictorys_transaction( $bictorys_txn_ref ) {
		$bictorys_url = 'https://api.bictorys.com/transaction/verify/' . $bictorys_txn_ref;

		$headers = array(
			'Authorization' => 'Bearer ' . $this->secret_key,
		);

		$args = array(
			'headers' => $headers,
			'timeout' => 60,
		);

		$request = wp_remote_get( $bictorys_url, $args );

		if ( ! is_wp_error( $request ) && $this->isResponseOk( $request ) ) {
			return json_decode( wp_remote_retrieve_body( $request ) );
		}

		return false;
	}

	/**
	 * Get Bictorys payment icon URL.
	 */
	public function get_logo_url() {
		if ( 'mobile_money' === $this->payment_page ) {
			$url = WC_HTTPS::force_https_url( plugins_url( 'assets/images/wc_bictorys_mobile.png', WC_BICTORYS_MAIN_FILE ) );
		} elseif ( 'card' === $this->payment_page ) {
			$url = WC_HTTPS::force_https_url( plugins_url( 'assets/images/wc_bictorys_card.png', WC_BICTORYS_MAIN_FILE ) );
		} elseif ( 'all' === $this->payment_page ) {
			$url = WC_HTTPS::force_https_url( plugins_url( 'assets/images/wc_bictorys_all.png', WC_BICTORYS_MAIN_FILE ) );
		} else {
			$url = WC_HTTPS::force_https_url( plugins_url( 'assets/images/wc_bictorys_all.png', WC_BICTORYS_MAIN_FILE ) );
		}

		return apply_filters( 'wc_bictorys_gateway_icon_url', $url, $this->id );
	}

	/**
	 * Check if an order contains a subscription.
	 *
	 * @param int $order_id WC Order ID.
	 *
	 * @return bool
	 */
	public function order_contains_subscription( $order_id ) {
		return function_exists( 'wcs_order_contains_subscription' ) && ( wcs_order_contains_subscription( $order_id ) || wcs_order_contains_renewal( $order_id ) );

	}
}
