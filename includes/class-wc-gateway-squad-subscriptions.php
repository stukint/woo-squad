<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_Gateway_Squad_Subscriptions
 */
class WC_Gateway_Squad_Subscriptions extends WC_Gateway_Squad {

	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct();

		if ( class_exists( 'WC_Subscriptions_Order' ) ) {

			add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment' ), 10, 2 );

		}
	}

	/**
	 * Process a trial subscription order with 0 total.
	 *
	 * @param int $order_id WC Order ID.
	 *
	 * @return array|void
	 */
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );

		// Check for trial subscription order with 0 total.
		if ( $this->order_contains_subscription( $order ) && $order->get_total() == 0 ) {

			$order->payment_complete();

			$order->add_order_note( __( 'This subscription has a free trial, reason for the 0 amount', 'woo-squad' ) );

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);

		} else {

			return parent::process_payment( $order_id );

		}

	}

	/**
	 * Process a subscription renewal.
	 *
	 * @param float    $amount_to_charge Subscription payment amount.
	 * @param WC_Order $renewal_order Renewal Order.
	 */
	public function scheduled_subscription_payment( $amount_to_charge, $renewal_order ) {

		$response = $this->process_subscription_payment( $renewal_order, $amount_to_charge );

		if ( is_wp_error( $response ) ) {

			$renewal_order->update_status( 'failed', sprintf( __( 'Squad Transaction Failed (%s)', 'woo-squad' ), $response->get_error_message() ) );

		}

	}


	/**
	 * Process a subscription renewal payment.
	 *
	 * @param WC_Order $order  Subscription renewal order.
	 * @param float    $amount Subscription payment amount.
	 *
	 * @return bool|WP_Error
	 */
	public function process_subscription_payment( $order, $amount ) {

		//Recurring payment is not currently enabled on the gateway. Code logic when it gets enabled.
		return new WP_Error( 'squad_error', __( 'This subscription can&#39;t be renewed automatically. The customer will have to login to their account to renew their subscription', 'woo-squad' ) );

	}
}