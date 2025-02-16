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
	 * Squad sandbox api url.
	 *
	 * @var string
	 */
	public $test_api_url;

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
	 * Squad live api url.
	 *
	 * @var string
	 */
	public $live_api_url;

	/**
	 * Enable or disable recurring payment?
	 *
	 * @var bool
	 */
	public $is_recurring;

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
	 * API url
	 *
	 * @var string
	 */
	public $api_url;

    /**
	 * Squad Modal Url
	 *
	 * @var string
	 */
	public $checkout_modal_url;

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

		$this->payment_channels = $this->get_option( 'payment_channels' );

        $this->order_complete_message = $this->get_option( 'order_complete_message' );
        $this->order_failed_message = $this->get_option( 'order_failed_message' );

		$this->is_recurring = $this->get_option( 'is_recurring' ) === 'yes' ? true : false;

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

        $this->checkout_modal_url = 'https://checkout.squadco.com';
        $this->test_api_url = 'https://sandbox-api-d.squadco.com';
        $this->live_api_url = 'https://api-d.squadco.com';

        $this->api_url = $this->testmode ? $this->test_api_url : $this->live_api_url;



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
			'is_recurring' => array(
                'title'       => __( 'Recurring Payment', 'woo-squad' ),
				'label'       => __( 'Enable Recurring Payment', 'woo-squad' ),
				'type'        => 'checkbox',
				'description' => __( 'Enable squad recurring payments.', 'woo-squad' ),
				'default'     => 'no',
				'desc_tip'    => true
            ),
            'order_complete_message' => array(
				'title'       => __( 'Order Complete Message', 'woo-squad' ),
				'type'        => 'text',
				'description' => __( 'Enter message to output when order has completed.', 'woo-squad' ),
				'default'     => ''
			),
            'order_failed_message' => array(
				'title'       => __( 'Order Failed Message', 'woo-squad' ),
				'type'        => 'text',
				'description' => __( 'Enter message to output when order has failed.', 'woo-squad' ),
				'default'     => ''
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

		// if ( $this->supports( 'tokenization' ) && is_checkout() && is_user_logged_in() ) {
		// 	$this->tokenization_script();
		// 	$this->saved_payment_methods();
		// 	$this->save_payment_method_checkbox();
		// }

    }

    /**
	 * Outputs scripts used for squad payment.
	 */
	public function payment_scripts() {

        if ( isset( $_GET['pay_for_order'] ) || ! is_checkout_pay_page() ) {
			return;
		}

		if ( $this->enabled === 'no' ) {
			return;
		}

		$order_key = urldecode( $_GET['key'] );
		$order_id  = absint( get_query_var( 'order-pay' ) );

		$order = wc_get_order( $order_id );

		if ( $this->id !== $order->get_payment_method() ) {
			return;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'jquery' );

        wp_enqueue_script( 'squad', $this->checkout_modal_url . '/widget/squad.min.js', array( 'jquery' ), WC_SQUAD_VERSION, false );

        wp_enqueue_script( 'wc_squad', plugins_url( 'assets/js/squad' . $suffix . '.js', WC_SQUAD_MAIN_FILE), array( 'jquery', 'squad' ), WC_SQUAD_VERSION, false );

        $squad_params = array(
            'key' => $this->public_key
        );

        if ( is_checkout_pay_page() && get_query_var( 'order-pay' ) ) {

            $email         = $order->get_billing_email();
            $first_name	   = $order->get_billing_first_name();
			$last_name	   = $order->get_billing_last_name();
			$customer_name = $first_name . ' ' . $last_name;
			$amount        = $order->get_total() * 100;
			$txnref        = 'SQD' . '_' . $order_id . '_' . time();
			$the_order_id  = $order->get_id();
			$the_order_key = $order->get_order_key();
			$currency      = $order->get_currency();

            if ( $the_order_id == $order_id && $the_order_key == $order_key ) {

                $squad_params['email'] = $email;
                $squad_params['amount'] = $amount;
                $squad_params['transaction_ref'] = $txnref;
                $squad_params['currency_code'] = $currency;
                $squad_params['customer_name'] = $customer_name;
    
            }

			if($this->is_recurring){
				$squad_params['is_recurring'] = true;
			}

            if ( $this->custom_metadata ) {

                if ( $this->meta_order_id ) {
    
                    $squad_params['meta_order_id'] = $order_id;
    
                }
    
                if ( $this->meta_name ) {
    
                    $squad_params['meta_name'] = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    
                }
    
                if ( $this->meta_email ) {
    
                    $squad_params['meta_email'] = $email;
    
                }
    
                if ( $this->meta_phone ) {
    
                    $squad_params['meta_phone'] = $order->get_billing_phone();
    
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
    
                    $squad_params['meta_products'] = $products;
    
                }
    
                if ( $this->meta_billing_address ) {
    
                    $billing_address = $order->get_formatted_billing_address();
                    $billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );
    
                    $squad_params['meta_billing_address'] = $billing_address;
    
                }
    
                if ( $this->meta_shipping_address ) {
    
                    $shipping_address = $order->get_formatted_shipping_address();
                    $shipping_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $shipping_address ) );
    
                    if ( empty( $shipping_address ) ) {
    
                        $billing_address = $order->get_formatted_billing_address();
                        $billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );
    
                        $shipping_address = $billing_address;
    
                    }
    
                    $squad_params['meta_shipping_address'] = $shipping_address;
    
                }
    
            }

            $order->update_meta_data( '_squad_txn_ref', $txnref );
			$order->save();

        }

        $payment_channels = $this->get_gateway_payment_channels( $order );

        if ( ! empty( $payment_channels ) && $payment_channels != false ) {

            if ( in_array( 'card', $payment_channels, true ) ) {
                $squad_params['card_channel'] = 'true';
            }

            if ( in_array( 'bank', $payment_channels, true ) ) {
                $squad_params['bank_channel'] = 'true';
            }

            if ( in_array( 'ussd', $payment_channels, true ) ) {
                $squad_params['ussd_channel'] = 'true';
            }

            if ( in_array( 'transfer', $payment_channels, true ) ) {
                $squad_params['transfer_channel'] = 'true';
            }

        }

        wp_localize_script( 'wc_squad', 'wc_squad_params', $squad_params );

    }

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

        wp_enqueue_script( 'wc_squad_admin', plugins_url( 'assets/js/squad-admin' . $suffix . '.js', WC_SQUAD_MAIN_FILE ), array(), WC_SQUAD_VERSION, true );

        wp_localize_script( 'wc_squad_admin', 'wc_squad_admin_params', $squad_admin_params );

    }

    /**
	 * Process the payment.
	 *
	 * @param int $order_id
	 *
	 * @return array|void
	 */
	public function process_payment( $order_id ) {

        //$payment_token = 'wc-' . trim( $this->id ) . '-payment-token';

		// phpcs:ignore WordPress.Security.NonceVerification
		// if ( isset( $_POST[ $payment_token ] ) && 'new' !== wc_clean( $_POST[ $payment_token ] ) ) {

		// 	// phpcs:ignore WordPress.Security.NonceVerification
		// 	$token_id = wc_clean( $_POST[ $payment_token ] );
		// 	$token    = \WC_Payment_Tokens::get( $token_id );

		// 	if ( $token->get_user_id() !== get_current_user_id() ) {

		// 		wc_add_notice( 'Invalid token ID', 'error' );

		// 		return;
		// 	}

		// 	$token_payment_status = $this->process_token_payment( $token->get_token(), $order_id );

		// 	if ( ! $token_payment_status ) {
		// 		return;
		// 	}

		// 	$order = wc_get_order( $order_id );

		// 	return array(
		// 		'result'   => 'success',
		// 		'redirect' => $this->get_return_url( $order ),
		// 	);
		// }

		$order = wc_get_order( $order_id );

		// $new_payment_method = 'wc-' . trim( $this->id ) . '-new-payment-method';

		// // phpcs:ignore WordPress.Security.NonceVerification
		// if ( isset( $_POST[ $new_payment_method ] ) && ( true === (bool) $_POST[ $new_payment_method ] && $this->saved_cards ) && is_user_logged_in() ) {

        //     $order->update_meta_data( '_wc_squad_save_card', true );

		// 	$order->save();

        // }

        if ( 'redirect' === $this->payment_page ) {
			return $this->process_redirect_payment_option( $order_id );
		}

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true ),
		);

    }

    /**
	 * Process a redirect payment option payment.
	 *
	 * @since 5.7
	 * @param int $order_id
	 * @return array|void
	 */
	public function process_redirect_payment_option( $order_id ) {

		$order = wc_get_order( $order_id );
		$email         = $order->get_billing_email();
        $first_name	   = $order->get_billing_first_name();
		$last_name	   = $order->get_billing_last_name();
		$customer_name = $first_name . ' ' . $last_name;
		$amount        = $order->get_total() * 100;
		$txnref        = 'SQD' . '_' . $order_id . '_' . time();
		$currency      = $order->get_currency();
		$callback_url  = WC()->api_request_url( 'WC_Gateway_Squad' );

		$payment_channels = $this->get_gateway_payment_channels($order);

		$squad_params = array(
			'email' => $email,
			'amount' => $amount,
			'currency' => $currency,
			'customer_name' => $customer_name,
			'initiate_type' => "inline",
			'transaction_ref' => $txnref,
			'callback_url' => $callback_url
		);

		if( !empty($payment_channels) && $payment_channels != false ){
			$squad_params['payment_channels'] = $payment_channels;
		}else{
			$squad_params['payment_channels'] = array('card', 'bank', 'ussd', 'transfer');
		}

		if($this->is_recurring){
			$squad_params['is_recurring'] = true;
		}

		$squad_params['metadata']['custom_fields'] = $this->get_custom_fields($order_id);

		$order->update_meta_data( '_squad_txn_ref', $txnref );
		$order->save();

		$squad_url = $this->api_url . '/transaction/initiate';

		$headers = array(
			'Authorization' => 'Bearer ' . $this->secret_key,
			'Content-Type'  => 'application/json'
		);

		$args = array(
			'headers' => $headers,
			'timeout' => 60,
			'body'    => json_encode( $squad_params ),
		);

		$request = wp_remote_post( $squad_url, $args );

		if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {

			$squad_response = json_decode(wp_remote_retrieve_body( $request ));
			error_log(print_r($squad_response, true));

			return array(
				'result'   => 'success',
				'redirect' => $squad_response->data->checkout_url,
			);

		} else {

			wc_add_notice( __( 'Unable to process payment try again', 'woo-squad' ), 'error' );

			return;

		}
	}

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
	public function add_payment_method() {

        wc_add_notice( __( 'You can only add a new card when placing an order.', 'woo-squad' ), 'error' );

		return;

    }

    /**
	 * Displays the payment page.
	 *
	 * @param $order_id
	 */
	public function receipt_page( $order_id ) {

        $order = wc_get_order( $order_id );

        echo '<div id="wc-squad-form">';

        echo '<p>' . __( 'Thank you for your order, please click the button below to pay with Squad.', 'woo-squad' ) . '</p>';

        echo '<div id="squad_form"><form id="order_review" method="post" action="' . WC()->api_request_url( 'WC_Gateway_Squad' ) . '"></form><button class="button" id="squad-payment-button">' . __( 'Pay Now', 'woo-squad' ) . '</button>';

        if ( ! $this->remove_cancel_order_button ) {
			echo '  <a class="button cancel" id="squad-cancel-payment-button" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woo-squad' ) . '</a></div>';
		}

        echo '</div>';

    }

    /**
	 * Displays the thankyou page.
	 *
	 * @param $order_id
	 */
	public function thankyou_page( $order_id ) {

        $order = wc_get_order( $order_id );

		if($order->get_status() == 'failed'){
			if ($this->order_failed_message){
				$text = wptexturize($this->order_failed_message);
				$ptext = wpautop($text);
				$htext = str_replace('<p>', '<h6>', $ptext);
				$h6text = str_replace('</p>', '</h6>', $htext);
				echo wp_kses_post($h6text);
				return;
			}
			return;
		}

		if($order->get_status() == 'completed' || $order->get_status() == 'processing'){
			if ($this->order_complete_message){
				$text = wptexturize($this->order_complete_message);
				$ptext = wpautop($text);
				$htext = str_replace('<p>', '<h6>', $ptext);
				$h6text = str_replace('</p>', '</h6>', $htext);
				echo wp_kses_post($h6text);
				return;
			}
			return;
		}

    }

    /**
	 * Verify Squad payment.
	 */
	public function verify_squad_transaction() {

		if ( isset( $_REQUEST['squad_txnref'] ) ) {
			$squad_txn_ref = sanitize_text_field( $_REQUEST['squad_txnref'] );
		} elseif ( isset( $_REQUEST['transaction_ref'] ) ) {
			$squad_txn_ref = sanitize_text_field( $_REQUEST['transaction_ref'] );
		} else {
			$squad_txn_ref = false;
		}

		@ob_clean();

		if($squad_txn_ref){

			$squad_response = $this->get_squad_transaction($squad_txn_ref);

			if($squad_response !== false){

				if($squad_response->data->transaction_status == 'success'){

					$order_details = explode('_', $squad_response->data->transaction_ref);
					$order_id = (int) $order_details[1];
					$order = wc_get_order($order_id);

					if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {

						wp_redirect( $this->get_return_url( $order ) );

						exit;

					}

					$order_total      = $order->get_total();
					$order_currency   = $order->get_currency();
					$currency_symbol  = get_woocommerce_currency_symbol( $order_currency );
					$amount_paid = $squad_response->data->transaction_amount / 100;
					$squad_ref = $squad_response->data->transaction_ref;
					$payment_currency = strtoupper($squad_response->data->transaction_currency_id);
					$gateway_symbol = get_woocommerce_currency_symbol($payment_currency);

					// check if the amount paid is equal to the order amount.
					if ( $amount_paid < absint( $order_total ) ) {

						$order->update_status( 'on-hold', '' );

						$order->add_meta_data( '_transaction_id', $squad_ref, true );

						$notice      = sprintf( __( 'Thank you for your payment.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-squad' ), '<br />', '<br />', '<br />' );
						$notice_type = 'notice';

						// Add Customer Order Note
						$order->add_order_note( $notice, 1 );

						// Add Admin Order Note
						$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Squad Transaction Reference:</strong> %9$s', 'woo-squad' ), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $squad_ref );
						$order->add_order_note( $admin_order_note );

						function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

						wc_add_notice( $notice, $notice_type );

					} else {
						if ( $payment_currency !== $order_currency ) {

							$order->update_status( 'on-hold', '' );

							$order->update_meta_data( '_transaction_id', $squad_ref );

							$notice      = sprintf( __( 'Thank you for your payment.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-squad' ), '<br />', '<br />', '<br />' );
							$notice_type = 'notice';

							// Add Customer Order Note
							$order->add_order_note( $notice, 1 );

							// Add Admin Order Note
							$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Squad Transaction Reference:</strong> %9$s', 'woo-squad' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $squad_ref );
							$order->add_order_note( $admin_order_note );

							function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

							wc_add_notice( $notice, $notice_type );

						} else {

							$order->payment_complete( $squad_ref );
							$order->add_order_note( sprintf( __( 'Payment via Squad successful (Transaction Reference: %s)', 'woo-squad' ), $squad_ref ) );

							if ( $this->is_autocomplete_order_enabled( $order ) ) {
								$order->update_status( 'completed' );
							}

						}
					}

					$order->save();
					
					$this->save_subscription_payment_token( $order_id, $squad_response );

					WC()->cart->empty_cart();

				} else {

					$order_details = explode( '_', $squad_txn_ref );

					$order_id = (int) $order_details[1];

					$order = wc_get_order( $order_id );

					$order->update_status( 'failed', __( 'Squad payment was declined.', 'woo-squad' ) );

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

		error_log(print_r(json_encode($_SERVER), true));

	}

    /**
	 * Save Customer Card Details.
	 *
	 * @param $squad_response
	 * @param $user_id
	 * @param $order_id
	 */
	//public function save_card_details( $squad_response, $user_id, $order_id ) {}

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
	public function get_custom_fields( $order_id ) {

        $order = wc_get_order( $order_id );

		$custom_fields = array();

		$custom_fields[] = array(
			'display_name'  => 'Plugin',
			'variable_name' => 'plugin',
			'value'         => 'woo-squad',
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
	protected function is_autocomplete_order_enabled( $order ) {

        $autocomplete_order = false;

		$payment_method = $order->get_payment_method();

		$squad_settings = get_option('woocommerce_' . $payment_method . '_settings');

		if ( isset( $squad_settings['autocomplete_order'] ) && 'yes' === $squad_settings['autocomplete_order'] ) {
			$autocomplete_order = true;
		}

		return $autocomplete_order;

    }

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
	protected function get_gateway_payment_channels( $order ) {

        $payment_channels = $this->payment_channels;
		if ( empty( $payment_channels ) && ( 'squad' !== $order->get_payment_method() ) ) {
			$payment_channels = false;
		}

		/**
		 * Filter the list of payment channels.
		 *
		 * @param array $payment_channels A list of payment channels.
		 * @param string $id Payment method ID.
		 * @param WC_Order $order Order object.
		 * @since 5.8.2
		 */
		return apply_filters( 'wc_squad_payment_channels', $payment_channels, $this->id, $order );

    }

    /**
	 * Retrieve a transaction from Squad.
	 *
	 * @since 5.7.5
	 * @param $squad_txn_ref
	 * @return false|mixed
	 */
	private function get_squad_transaction( $squad_txn_ref ) {

		$squad_url = $this->api_url . '/transaction/verify/' . $squad_txn_ref;

		$headers = array(
			'Authorization' => 'Bearer ' . $this->secret_key,
		);

		$args = array(
			'headers' => $headers,
			'timeout' => 60,
		);

		$request = wp_remote_get( $squad_url, $args );

		if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {
			return json_decode( wp_remote_retrieve_body( $request ) );
		}

		return false;

	}

    /**
	 * Get Squad payment icon URL.
	 */
	public function get_logo_url() {

        $url = WC_HTTPS::force_https_url( plugins_url( 'assets/images/powered-by-squad.png', WC_SQUAD_MAIN_FILE ) );

        return apply_filters( 'wc_squad_gateway_icon_url', $url, $this->id );

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