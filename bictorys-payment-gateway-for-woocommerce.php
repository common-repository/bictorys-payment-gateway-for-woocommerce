<?php
/**
 * Plugin Name: Bictorys Payment Gateway for WooCommerce
 * Plugin URI: https://docs.bictorys.com/docs/woocommerce
 * Description: Bictorys Payment Gateway for WooCommerce
 * Version: 1.0.5
 * Author: Bictorys
 * Author URI: https://docs.bictorys.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * WC requires at least: 7.0
 * WC tested up to: 8.4
 * Text Domain: bictorys-payment-gateway-for-woocommerce
 * Domain Path: /languages
 */

use Automattic\WooCommerce\Admin\Notes\Note;
use Automattic\WooCommerce\Admin\Notes\Notes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_BICTORYS_MAIN_FILE', __FILE__ );
define( 'WC_BICTORYS_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );

define( 'WC_BICTORYS_VERSION', '1.0.5' );

/**
 * Initialize Bictorys Payment Gateway for WooCommerce.
 *
 * This function initializes the Bictorys Payment Gateway for WooCommerce,
 * loading the plugin text domain, checking if WooCommerce is installed,
 * displaying a notice if WooCommerce is not installed, initializing the
 * plugin, and adding the Bictorys Payment Gateway to WooCommerce's payment
 * gateways.
 *
 * @since 1.0.0
 */
function bictorys_pg_wc_bictorys_init() {

	load_plugin_textdomain( 'bictorys-payment-gateway-for-woocommerce', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		add_action( 'admin_notices', 'bictorys_pg_wc_missing_notice' );
		return;
	}

	add_action( 'admin_init', 'bictorys_pg_wc_testmode_notice' );

	require_once dirname( __FILE__ ) . '/includes/class-bictorys-pg-wc-gateway.php';

	add_filter( 'woocommerce_payment_gateways', 'bictorys_pg_wc_add_bictorys_gateway', 99 );

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bictorys_pg_wc_plugin_action_links' );

}
add_action( 'plugins_loaded', 'bictorys_pg_wc_bictorys_init', 99 );

/**
 * Add Settings link to the plugin entry in the plugins menu.
 *
 * @param array $links Plugin action links.
 *
 * @return array
 **/
function bictorys_pg_wc_plugin_action_links( $links ) {

	$settings_link = array(
		'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bictorys' ) . '" title="' . __( 'View Bictorys WooCommerce Settings', 'bictorys-payment-gateway-for-woocommerce' ) . '">' . __( 'Settings', 'bictorys-payment-gateway-for-woocommerce' ) . '</a>',
	);

	return array_merge( $settings_link, $links );

}

/**
 * Add Bictorys Gateway to WooCommerce.
 *
 * @param array $methods WooCommerce payment gateways methods.
 *
 * @return array
 */
function bictorys_pg_wc_add_bictorys_gateway( $methods ) {
	$methods[] = 'Bictorys_PG_WC_Gateway';

	return $methods;

}

/**
 * Display a notice if WooCommerce is not installed
 */
function bictorys_pg_wc_missing_notice() {
	/*
	* translators: %s: link to install WooCommerce.
	*/
	echo '<div class="error"><p><strong>' . esc_html( sprintf( __( 'Bictorys requires WooCommerce to be installed and active. Click %s to install WooCommerce.', 'bictorys-payment-gateway-for-woocommerce' ), '<a href="' . esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539' ) ) . '" class="thickbox open-plugin-details-modal">here</a>' ) ) . '</strong></p></div>';
}

/**
 * Display the test mode notice.
 **/
function bictorys_pg_wc_testmode_notice() {

	if ( ! class_exists( Notes::class ) ) {
		return;
	}

	if ( ! class_exists( WC_Data_Store::class ) ) {
		return;
	}

	if ( ! method_exists( Notes::class, 'get_note_by_name' ) ) {
		return;
	}

	$test_mode_note = Notes::get_note_by_name( 'bictorys-test-mode' );

	if ( false !== $test_mode_note ) {
		return;
	}

	$bictorys_settings = get_option( 'woocommerce_bictorys_settings' );
	$test_mode         = $bictorys_settings['testmode'] ?? '';

	if ( 'yes' !== $test_mode ) {
		Notes::delete_notes_with_name( 'bictorys-test-mode' );

		return;
	}

	$note = new Note();
	$note->set_title( __( 'Bictorys test mode enabled', 'bictorys-payment-gateway-for-woocommerce' ) );
	$note->set_content( __( 'Bictorys test mode is currently enabled. Remember to disable it when you want to start accepting live payment on your site.', 'bictorys-payment-gateway-for-woocommerce' ) );
	$note->set_type( Note::E_WC_ADMIN_NOTE_INFORMATIONAL );
	$note->set_layout( 'plain' );
	$note->set_is_snoozable( false );
	$note->set_name( 'bictorys-test-mode' );
	$note->set_source( 'bictorys-payment-gateway-for-woocommerce' );
	$note->add_action( 'disable-bictorys-test-mode', __( 'Disable Bictorys test mode', 'bictorys-payment-gateway-for-woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bictorys' ) );
	$note->save();
}

add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Registers WooCommerce Blocks integration.
 */
function bictorys_pg_wc_block_support() {
	if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
		require_once __DIR__ . '/includes/class-bictorys-pg-wc-gateway-blocks-support.php';
		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			static function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
				$payment_method_registry->register( new Bictorys_PG_WC_Gateway_Blocks_Support() );
			}
		);
	}
}
add_action( 'woocommerce_blocks_loaded', 'bictorys_pg_wc_block_support' );

/**
 * Registers and enqueues the 'bictorys-block-styles' style for the callback_for_setting_up_scripts function.
 *
 * @throws Some_Exception_Class description of exception
 */
function bictorys_pg_wc_callback_for_setting_up_scripts() {
    if ( ! is_admin() ) {
        wp_register_style( 'bictorys-block-styles', plugin_dir_url( __FILE__ ) . 'includes/custom-style.css', array(), '1.0.0' );
        wp_enqueue_style( 'bictorys-block-styles' );
    }
}
add_action( 'wp_enqueue_scripts', 'bictorys_pg_wc_callback_for_setting_up_scripts' );