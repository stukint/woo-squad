<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Gateway_Squad extends WC_Payment_Gateway_CC{

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
	 * Squad payment page type.
	 *
	 * @var string
	 */
	public $payment_page;

    /**
	 * Squad test public key.
	 *
	 * @var string
	 */
	public $test_public_key;

	/**
	 * Squad test secret key.
	 *
	 * @var string
	 */
	public $test_secret_key;

	/**
	 * Squad live public key.
	 *
	 * @var string
	 */
	public $live_public_key;

	/**
	 * Squad live secret key.
	 *
	 * @var string
	 */
	public $live_secret_key;

	/**
	 * Should we save customer cards?
	 *
	 * @var bool
	 */
	public $saved_cards;

    /**
	 * Should the cancel & remove order button be removed on the pay for order page.
	 *
	 * @var bool
	 */
	public $remove_cancel_order_button;

    /**
	 * Should custom metadata be enabled?
	 *
	 * @var bool
	 */
	public $custom_metadata;

	/**
	 * Should the order id be sent as a custom metadata to Squad?
	 *
	 * @var bool
	 */
	public $meta_order_id;

	/**
	 * Should the customer name be sent as a custom metadata to Squad?
	 *
	 * @var bool
	 */
	public $meta_name;

	/**
	 * Should the billing email be sent as a custom metadata to Squad?
	 *
	 * @var bool
	 */
	public $meta_email;

	/**
	 * Should the billing phone be sent as a custom metadata to Squad?
	 *
	 * @var bool
	 */
	public $meta_phone;

	/**
	 * Should the billing address be sent as a custom metadata to Squad?
	 *
	 * @var bool
	 */
	public $meta_billing_address;

	/**
	 * Should the shipping address be sent as a custom metadata to Squad?
	 *
	 * @var bool
	 */
	public $meta_shipping_address;

	/**
	 * Should the order items be sent as a custom metadata to Squad?
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
	 * Gateway disabled message
	 *
	 * @var string
	 */
	public $msg;

	/**
	 * Payment channels.
	 *
	 * @var array
	 */
	public $payment_channels = array();

    /**
	 * Success thank you message.
	 *
	 * @var array
	 */
    public $order_complete_message;

    /**
	 * Failure thank you message.
	 *
	 * @var array
	 */
    public $order_failed_message;


    /**
	 * Constructor
	 */
	public function __construct() {
        $this->id = 'squad';
        $this->method_title = __( 'Squad', 'woo-squad' );
        $this->method_description = sprintf( __( 'Squad provides merchants with the tools and services needed to accept online payments from local and international customers using Mastercard, Visa, Verve Cards and Bank Accounts. <a href="%1$s" target="_blank">Sign up</a> for a Squad account, and <a href="%2$s" target="_blank">get your API keys</a>.', 'woo-squad' ), 'https://www.squadco.com', 'https://dashboard.squadco.com/merchant-settings/api-webhooks' );
        $this->has_fields = true;
        
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

        //Get settings value
        $this->title = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled = $this->get_option( 'enabled' );
		$this->testmode = $this->get_option( 'testmode' ) === 'yes' ? true : false;
		$this->autocomplete_order = $this->get_option( 'autocomplete_order' ) === 'yes' ? true : false;

		$this->test_public_key = $this->get_option( 'test_public_key' );
		$this->test_secret_key = $this->get_option( 'test_secret_key' );

		$this->live_public_key = $this->get_option( 'live_public_key' );
		$this->live_secret_key = $this->get_option( 'live_secret_key' );

		$this->saved_cards = $this->get_option( 'saved_cards' ) === 'yes' ? true : false;

        $this->custom_metadata = $this->get_option( 'custom_metadata' ) === 'yes' ? true : false;

		$this->meta_order_id = $this->get_option( 'meta_order_id' ) === 'yes' ? true : false;
		$this->meta_name = $this->get_option( 'meta_name' ) === 'yes' ? true : false;
		$this->meta_email = $this->get_option( 'meta_email' ) === 'yes' ? true : false;
		$this->meta_phone = $this->get_option( 'meta_phone' ) === 'yes' ? true : false;
		$this->meta_billing_address = $this->get_option( 'meta_billing_address' ) === 'yes' ? true : false;
		$this->meta_shipping_address = $this->get_option( 'meta_shipping_address' ) === 'yes' ? true : false;
		$this->meta_products = $this->get_option( 'meta_products' ) === 'yes' ? true : false;

		$this->public_key = $this->testmode ? $this->test_public_key : $this->live_public_key;
		$this->secret_key = $this->testmode ? $this->test_secret_key : $this->live_secret_key;

        //Hooks
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
        add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ));

        // Payment listener/API hook.
        add_action( 'woocommerce_api_wc_gateway_squad', array( $this, 'verify_squad_transaction' ) );

        // Webhook listener/API hook.
        add_action( 'woocommerce_api_wc_squad_webhook', array( $this, 'process_webhooks' ) );

        // Check if the gateway can be used.
		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = false;
		}

    }

    /**
	 * Check if this gateway is enabled and available in the user's country.
	 */
	public function is_valid_for_use() {

        if ( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_squad_supported_currencies', array( 'NGN', 'USD' ) ) ) ) {

            $this->msg = sprintf( __( 'Squad does not support your store currency. Kindly set it to either NGN (&#8358) or USD (&#36;) <a href="%s">here</a>', 'woo-squad' ), admin_url( 'admin.php?page=wc-settings&tab=general' ) );

            return false;

        }

        return true;

    }

    /**
	 * Display squad payment icon.
	 */
	public function get_icon() {

        $icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/powered-by-squad.png', WC_SQUAD_MAIN_FILE ) ) . '" alt="Squad Payment Options" />';

        return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );

    }

    /**
	 * Check if Squad merchant details is filled.
	 */
	public function admin_notices() {

        if ( $this->enabled == 'no' ) {
			return;
		}

        // Check required fields.
		if ( ! ( $this->public_key && $this->secret_key ) ) {
			echo '<div class="error"><p>' . sprintf( __( 'Please enter your Squad merchant details <a href="%s">here</a> to be able to use the Squad WooCommerce plugin.', 'woo-squad' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=squad' ) ) . '</p></div>';
			return;
		}

    }

    /**
	 * Check if Squad gateway is enabled.
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

		<h2><?php _e( 'Squad', 'woo-squad' ); ?>
		<?php
		if ( function_exists( 'wc_back_link' ) ) {
			wc_back_link( __( 'Return to payments', 'woo-squad' ), admin_url( 'admin.php?page=wc-settings&tab=checkout' ) );
		}
		?>
		</h2>

		<h4>
			<strong><?php printf( __( 'Optional: To avoid situations where bad network makes it impossible to verify transactions, set your webhook URL <a href="%1$s" target="_blank" rel="noopener noreferrer">here</a> to the URL below<span style="color: red"><pre><code>%2$s</code></pre></span>', 'woo-squad' ), 'https://dashboard.squadco.com/merchant-settings/api-webhooks', WC()->api_request_url( 'WC_Squad_Webhook' ) ); ?></strong>
		</h4>

		<?php

		if ( $this->is_valid_for_use() ) {

			echo '<table class="form-table">';
			$this->generate_settings_html();
			echo '</table>';

		} else {
			?>
			<div class="inline error"><p><strong><?php _e( 'Squad Payment Gateway Disabled', 'woo-squad' ); ?></strong>: <?php echo $this->msg; ?></p></div>

			<?php
		}

    }

    /**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {

        $form_fields = array(
            'enabled' => array(
                'title'       => __( 'Enable/Disable', 'woo-squad' ),
				'label'       => __( 'Enable Squad', 'woo-squad' ),
				'type'        => 'checkbox',
				'description' => __( 'Enable Squad as a payment option on the checkout page.', 'woo-squad' ),
				'default'     => 'no',
				'desc_tip'    => true
            ),
            'title' => array(
                'title'       => __( 'Title', 'woo-squad' ),
				'type'        => 'text',
				'description' => __( 'This controls the payment method title which the user sees during checkout.', 'woo-squad' ),
				'default'     => __( 'Debit/Credit Cards', 'woo-squad' ),
				'desc_tip'    => true
            ),
            'description' => array(
                'title'       => __( 'Description', 'woo-squad' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the payment method description which the user sees during checkout.', 'woo-squad' ),
				'default'     => __( 'Make payment using your debit and credit cards', 'woo-squad' ),
				'desc_tip'    => true
            ),
            'testmode' => array(
                'title'       => __( 'Test mode', 'woo-squad' ),
				'label'       => __( 'Enable Test Mode', 'woo-squad' ),
				'type'        => 'checkbox',
				'description' => __( 'Test mode enables you to test payments before going live. <br />Once the LIVE MODE is enabled on your Squad account uncheck this.', 'woo-squad' ),
				'default'     => 'yes',
				'desc_tip'    => true
            ),
            'payment_page' => array(
                'title'       => __( 'Payment Option', 'woo-squad' ),
				'type'        => 'select',
				'description' => __( 'Modal shows the payment modal on the page while Redirect will redirect the customer to Squad to make payment.', 'woo-squad' ),
				'default'     => '',
				'desc_tip'    => false,
				'options'     => array(
					''          => __( 'Select One', 'woo-squad' ),
					'inline'    => __( 'Modal', 'woo-squad' ),
					'redirect'  => __( 'Redirect', 'woo-squad' )
				)
            ),
            'test_secret_key' => array(
                'title'       => __( 'Test Secret Key', 'woo-squad' ),
				'type'        => 'password',
				'description' => __( 'Enter your Test Secret Key here', 'woo-squad' ),
				'default'     => ''
            ),
            'test_public_key' => array(
                'title'       => __( 'Test Public Key', 'woo-squad' ),
				'type'        => 'text',
				'description' => __( 'Enter your Test Public Key here.', 'woo-squad' ),
				'default'     => ''
            ),
            'live_secret_key' => array(
                'title'       => __( 'Live Secret Key', 'woo-squad' ),
				'type'        => 'password',
				'description' => __( 'Enter your Live Secret Key here.', 'woo-squad' ),
				'default'     => ''
            ),
            'live_public_key' => array(
                'title'       => __( 'Live Public Key', 'woo-squad' ),
				'type'        => 'text',
				'description' => __( 'Enter your Live Public Key here.', 'woo-squad' ),
				'default'     => ''
            ),
            'payment_channels' => array(
                'title'             => __( 'Payment Channels', 'woo-squad' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select wc-squad-payment-channels',
				'description'       => __( 'The payment channels enabled for this gateway', 'woo-squad' ),
				'default'           => '',
				'desc_tip'          => true,
				'select_buttons'    => true,
				'options'           => $this->channels(),
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select payment channels', 'woo-squad' ),
				)
            ),
            'autocomplete_order' => array(
                'title'       => __( 'Autocomplete Order After Payment', 'woo-squad' ),
				'label'       => __( 'Autocomplete Order', 'woo-squad' ),
				'type'        => 'checkbox',
				'class'       => 'wc-squad-autocomplete-order',
				'description' => __( 'If enabled, the order will be marked as complete after successful payment', 'woo-squad' ),
				'default'     => 'no',
				'desc_tip'    => true
            ),
            'remove_cancel_order_button' => array(
                'title'       => __( 'Remove Cancel Order & Restore Cart Button', 'woo-squad' ),
				'label'       => __( 'Remove the cancel order & restore cart button on the pay for order page', 'woo-squad' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
            ),
            'custom_gateways' => array(
                'title'       => __( 'Additional Squad Gateways', 'woo-squad' ),
				'type'        => 'select',
				'description' => __( 'Create additional custom Squad based gateways. This allows you to create additional Squad gateways using custom filters. You can create a gateway that accepts only cards, a gateway that accepts only bank payment.', 'woo-squad' ),
				'default'     => '',
				'desc_tip'    => true,
				'options'     => array(
					''  => __( 'Select One', 'woo-squad' ),
					'1' => __( '1 gateway', 'woo-squad' ),
					'2' => __( '2 gateways', 'woo-squad' )
				)
            ),
            'saved_cards' => array(
                'title'       => __( 'Saved Cards', 'woo-squad' ),
				'label'       => __( 'Enable Payment via Saved Cards', 'woo-squad' ),
				'type'        => 'checkbox',
				'description' => __( 'If enabled, users will be able to pay with a saved card during checkout. Card details are saved on Squad servers, not on your store.<br>Note that you need to have a valid SSL certificate installed.', 'woo-squad' ),
				'default'     => 'no',
				'desc_tip'    => true
            ),
            'custom_metadata' => array(
                'title'       => __( 'Custom Metadata', 'woo-squad' ),
				'label'       => __( 'Enable Custom Metadata', 'woo-squad' ),
				'type'        => 'checkbox',
				'class'       => 'wc-squad-metadata',
				'description' => __( 'If enabled, you will be able to send more information about the order to Squad.', 'woo-squad' ),
				'default'     => 'no',
				'desc_tip'    => true
            ),
            'meta_order_id' => array(
                'title'       => __( 'Order ID', 'woo-squad' ),
				'label'       => __( 'Send Order ID', 'woo-squad' ),
				'type'        => 'checkbox',
				'class'       => 'wc-squad-meta-order-id',
				'description' => __( 'If checked, the Order ID will be sent to Squad', 'woo-squad' ),
				'default'     => 'no',
				'desc_tip'    => true
            ),
            'meta_name' => array(
                'title'       => __( 'Customer Name', 'woo-squad' ),
				'label'       => __( 'Send Customer Name', 'woo-squad' ),
				'type'        => 'checkbox',
				'class'       => 'wc-squad-meta-name',
				'description' => __( 'If checked, the customer full name will be sent to Squad', 'woo-squad' ),
				'default'     => 'no',
				'desc_tip'    => true
            ),
            'meta_email' => array(
                'title'       => __( 'Customer Email', 'woo-squad' ),
				'label'       => __( 'Send Customer Email', 'woo-squad' ),
				'type'        => 'checkbox',
				'class'       => 'wc-squad-meta-email',
				'description' => __( 'If checked, the customer email address will be sent to Squad', 'woo-squad' ),
				'default'     => 'no',
				'desc_tip'    => true
            ),
            'meta_phone' => array(
                'title'       => __( 'Customer Phone', 'woo-squad' ),
				'label'       => __( 'Send Customer Phone', 'woo-squad' ),
				'type'        => 'checkbox',
				'class'       => 'wc-squad-meta-phone',
				'description' => __( 'If checked, the customer phone will be sent to Squad', 'woo-squad' ),
				'default'     => 'no',
				'desc_tip'    => true
            ),
            'meta_billing_address' => array(
                'title'       => __( 'Order Billing Address', 'woo-squad' ),
				'label'       => __( 'Send Order Billing Address', 'woo-squad' ),
				'type'        => 'checkbox',
				'class'       => 'wc-squad-meta-billing-address',
				'description' => __( 'If checked, the order billing address will be sent to Squad', 'woo-squad' ),
				'default'     => 'no',
				'desc_tip'    => true
            ),
            'meta_shipping_address' => array(
                'title'       => __( 'Order Shipping Address', 'woo-squad' ),
				'label'       => __( 'Send Order Shipping Address', 'woo-squad' ),
				'type'        => 'checkbox',
				'class'       => 'wc-squad-meta-shipping-address',
				'description' => __( 'If checked, the order shipping address will be sent to Squad', 'woo-squad' ),
				'default'     => 'no',
				'desc_tip'    => true
            ),
            'meta_products' => array(
                'title'       => __( 'Product(s) Purchased', 'woo-squad' ),
				'label'       => __( 'Send Product(s) Purchased', 'woo-squad' ),
				'type'        => 'checkbox',
				'class'       => 'wc-squad-meta-products',
				'description' => __( 'If checked, the product(s) purchased will be sent to Squad', 'woo-squad' ),
				'default'     => 'no',
				'desc_tip'    => true
            )
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
			echo wpautop( wptexturize( $this->description ) );
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
	 * Outputs scripts used for squad payment.
	 */
	public function payment_scripts() {}

    /**
	 * Load admin scripts.
	 */
	public function admin_scripts() {

        if ( 'woocommerce_page_wc-settings' !== get_current_screen()->id ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$squad_admin_params = array(
			'plugin_url' => WC_SQUAD_URL,
		);

        wp_enqueue_script( 'wc_squad_admin', plugins_url( 'assets/js/squad-admin' . $suffix . 'js', WC_SQUAD_MAIN_FILE ), array(), WC_SQUAD_VERSION, true );

        wp_localize_script( 'wc_squad_admin', 'wc_squad_admin_params', $squad_admin_params );

    }

    /**
	 * Process the payment.
	 *
	 * @param int $order_id
	 *
	 * @return array|void
	 */
	public function process_payment( $order_id ) {}

    /**
	 * Process a redirect payment option payment.
	 *
	 * @since 5.7
	 * @param int $order_id
	 * @return array|void
	 */
	public function process_redirect_payment_option( $order_id ) {}

    /**
	 * Process a token payment.
	 *
	 * @param $token
	 * @param $order_id
	 *
	 * @return bool
	 */
	public function process_token_payment( $token, $order_id ) {}

    /**
	 * Show new card can only be added when placing an order notice.
	 */
	public function add_payment_method() {}

    /**
	 * Displays the payment page.
	 *
	 * @param $order_id
	 */
	public function receipt_page( $order_id ) {}

    /**
	 * Displays the thankyou page.
	 *
	 * @param $order_id
	 */
	public function thankyou_page( $order_id ) {}

    /**
	 * Verify Squad payment.
	 */
	public function verify_squad_transaction() {}

    /**
	 * Process Webhook.
	 */
	public function process_webhooks() {}

    /**
	 * Save Customer Card Details.
	 *
	 * @param $squad_response
	 * @param $user_id
	 * @param $order_id
	 */
	public function save_card_details( $squad_response, $user_id, $order_id ) {}

    /**
	 * Save payment token to the order for automatic renewal for further subscription payment.
	 *
	 * @param $order_id
	 * @param $squad_response
	 */
	public function save_subscription_payment_token( $order_id, $squad_response ) {}

    /**
	 * Get custom fields to pass to Squad.
	 *
	 * @param int $order_id WC Order ID
	 *
	 * @return array
	 */
	public function get_custom_fields( $order_id ) {}

    /**
	 * Process a refund request from the Order details screen.
	 *
	 * @param int $order_id WC Order ID.
	 * @param float|null $amount Refund Amount.
	 * @param string $reason Refund Reason
	 *
	 * @return bool|WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {}

    /**
	 * Checks if WC version is less than passed in version.
	 *
	 * @param string $version Version to check against.
	 *
	 * @return bool
	 */
	public function is_wc_lt( $version ) {}

    /**
	 * Checks if autocomplete order is enabled for the payment method.
	 *
	 * @since 5.7
	 * @param WC_Order $order Order object.
	 * @return bool
	 */
	protected function is_autocomplete_order_enabled( $order ) {}

    /**
	 * Payment Channels.
	 */
	public function channels() {

		return array(
			'card'          => __( 'Cards', 'woo-squad' ),
			'bank'          => __( 'Pay with Bank', 'woo-squad' ),
			'ussd'          => __( 'USSD', 'woo-squad' ),
			'transfer' => __( 'Bank Transfer', 'woo-squad' ),
		);

	}

    /**
	 * Retrieve the payment channels configured for the gateway
	 *
	 * @since 5.7
	 * @param WC_Order $order Order object.
	 * @return array
	 */
	protected function get_gateway_payment_channels( $order ) {}

    /**
	 * Retrieve a transaction from Squad.
	 *
	 * @since 5.7.5
	 * @param $squad_txn_ref
	 * @return false|mixed
	 */
	private function get_squad_transaction( $squad_txn_ref ) {}

    /**
	 * Get Squad payment icon URL.
	 */
	public function get_logo_url() {}

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