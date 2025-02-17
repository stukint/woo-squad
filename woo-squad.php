<?php
/**
 * Plugin Name: Squad WooCommerce Payment Gateway
 * Plugin URI: https://www.squadco.com
 * Description: WooCommerce payment gateway for Squad
 * Version: 1.0.1
 * Author: Netsave Technologies
 * Author URI: https://www.netsavetech.com.ng
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires Plugins: woocommerce
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 9.1
 * Text Domain: woo-squad
 */

use Automattic\WooCommerce\Admin\Notes\Note;
use Automattic\WooCommerce\Admin\Notes\Notes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_SQUAD_MAIN_FILE', __FILE__ );
define( 'WC_SQUAD_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );

define( 'WC_SQUAD_VERSION', '1.0.1' );

/**
 * Initialize Squad WooCommerce payment gateway.
 */
function wc_squad_init() {

    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        add_action( 'admin_notices', 'wc_squad_wc_missing_notice' );
		return;
    }

    add_action( 'admin_init', 'wc_squad_testmode_notice' );

    require_once __DIR__ . '/includes/class-wc-gateway-squad.php';

    require_once __DIR__ . '/includes/class-wc-gateway-squad-subscriptions.php';

    require_once __DIR__ . '/includes/custom-gateways/class-wc-gateway-custom-squad.php';

    require_once __DIR__ . '/includes/custom-gateways/gateway-one/class-wc-gateway-squad-one.php';
    require_once __DIR__ . '/includes/custom-gateways/gateway-two/class-wc-gateway-squad-two.php';

    add_filter( 'woocommerce_payment_gateways', 'wc_add_squad_gateway', 99 );

    add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woo_squad_plugin_action_links' );

}
add_action( 'plugins_loaded', 'wc_squad_init', 99 );

/**
 * Add Settings link to the plugin entry in the plugins menu.
 *
 * @param array $links Plugin action links.
 *
 * @return array
 **/
function woo_squad_plugin_action_links( $links ) {
    $settings_link = array(
		'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=squad' ) . '" title="' . __( 'View Squad WooCommerce Settings', 'woo-squad' ) . '">' . __( 'Settings', 'woo-squad' ) . '</a>',
	);

    return array_merge( $settings_link, $links );
}

/**
 * Add Squad Gateway to WooCommerce.
 *
 * @param array $methods WooCommerce payment gateways methods.
 *
 * @return array
 */
function wc_add_squad_gateway( $methods ) {

    if ( class_exists( 'WC_Subscriptions_Order' ) && class_exists( 'WC_Payment_Gateway_CC' ) ) {
		$methods[] = 'WC_Gateway_Squad_Subscriptions';
	} else {
		$methods[] = 'WC_Gateway_Squad';
	}

    if ( 'NGN' === get_woocommerce_currency() ) {

        $settings        = get_option( 'woocommerce_squad_settings', '' );
        $custom_gateways = isset( $settings['custom_gateways'] ) ? $settings['custom_gateways'] : '';

        switch ( $custom_gateways ) {

            case '2':
				$methods[] = 'WC_Gateway_Squad_One';
				$methods[] = 'WC_Gateway_Squad_Two';
				break;
            
            case '1':
				$methods[] = 'WC_Gateway_Squad_One';
				break;
            
            default:
				break;

        }
    }

    return $methods;
    
}

/**
 * Display a notice if WooCommerce is not installed
 */
function wc_squad_wc_missing_notice() {

    echo '<div class="error"><p><strong>' . sprintf( __( 'Squad requires WooCommerce to be installed and active. Click %s to install WooCommerce.', 'woo-squad' ), '<a href="' . admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539' ) . '" class="thickbox open-plugin-details-modal">here</a>' ) . '</strong></p></div>';

}

/**
 * Display the test mode notice.
 **/
function wc_squad_testmode_notice() {

    if ( ! class_exists( Notes::class ) ) {
		return;
	}

	if ( ! class_exists( WC_Data_Store::class ) ) {
		return;
	}

	if ( ! method_exists( Notes::class, 'get_note_by_name' ) ) {
		return;
	}

	$test_mode_note = Notes::get_note_by_name( 'squad-test-mode' );

	if ( false !== $test_mode_note ) {
		return;
	}

    $squad_settings = get_option( 'woocommerce_squad_settings' );
	$test_mode         = $squad_settings['testmode'] ?? '';

    if ( 'yes' !== $test_mode ) {
		Notes::delete_notes_with_name( 'squad-test-mode' );

		return;
	}

    $note = new Note();
	$note->set_title( __( 'Squad test mode enabled', 'woo-squad' ) );
	$note->set_content( __( 'Squad test mode is currently enabled. Remember to disable it when you want to start accepting live payment on your site.', 'woo-squad' ) );
	$note->set_type( Note::E_WC_ADMIN_NOTE_INFORMATIONAL );
	$note->set_layout( 'plain' );
	$note->set_is_snoozable( false );
	$note->set_name( 'squad-test-mode' );
	$note->set_source( 'woo-squad' );
	$note->add_action( 'disable-squad-test-mode', __( 'Disable Squad test mode', 'woo-squad' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=squad' ) );
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
function wc_gateway_squad_woocommerce_block_support() {

    if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {

        require_once __DIR__ . '/includes/class-wc-gateway-squad-blocks-support.php';
        require_once __DIR__ . '/includes/custom-gateways/class-wc-gateway-custom-squad-blocks-support.php';
        require_once __DIR__ . '/includes/custom-gateways/gateway-one/class-wc-gateway-squad-one-blocks-support.php';
        require_once __DIR__ . '/includes/custom-gateways/gateway-two/class-wc-gateway-squad-two-blocks-support.php';
        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            static function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
                //Register blocks after creation
				$payment_method_registry->register( new WC_Gateway_Squad_Blocks_Support() );
            }
        );

    }

}
add_action( 'woocommerce_blocks_loaded', 'wc_gateway_squad_woocommerce_block_support' );