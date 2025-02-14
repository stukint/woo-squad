jQuery( function( $ ) {

    let squad_submit = false;

    wcSquadFormHandler();

    jQuery( '#squad-payment-button' ).click( function() {
		return wcSquadFormHandler();
	} );

    jQuery( '#squad_form form#order_review' ).submit( function() {
		return wcSquadFormHandler();
	} );

    function wcSquadCustomFields() {

        let custom_fields = [
			{
				"display_name": "Plugin",
				"variable_name": "plugin",
				"value": "woo-squad"
			}
		];

        if ( wc_squad_params.meta_order_id ) {

			custom_fields.push( {
				display_name: "Order ID",
				variable_name: "order_id",
				value: wc_squad_params.meta_order_id
			} );

		}

		if ( wc_squad_params.meta_name ) {

			custom_fields.push( {
				display_name: "Customer Name",
				variable_name: "customer_name",
				value: wc_squad_params.meta_name
			} );
		}

		if ( wc_squad_params.meta_email ) {

			custom_fields.push( {
				display_name: "Customer Email",
				variable_name: "customer_email",
				value: wc_squad_params.meta_email
			} );
		}

		if ( wc_squad_params.meta_phone ) {

			custom_fields.push( {
				display_name: "Customer Phone",
				variable_name: "customer_phone",
				value: wc_squad_params.meta_phone
			} );
		}

		if ( wc_squad_params.meta_billing_address ) {

			custom_fields.push( {
				display_name: "Billing Address",
				variable_name: "billing_address",
				value: wc_squad_params.meta_billing_address
			} );
		}

		if ( wc_squad_params.meta_shipping_address ) {

			custom_fields.push( {
				display_name: "Shipping Address",
				variable_name: "shipping_address",
				value: wc_squad_params.meta_shipping_address
			} );
		}

		if ( wc_squad_params.meta_products ) {

			custom_fields.push( {
				display_name: "Products",
				variable_name: "products",
				value: wc_squad_params.meta_products
			} );
		}

        return custom_fields;

    }

    function wcPaymentChannels() {

        let payment_channels = [];

        if ( wc_squad_params.bank_channel ){
            payment_channels.push( 'bank' );
        }

        if ( wc_squad_params.card_channel ){
            payment_channels.push( 'card' );
        }

        if ( wc_squad_params.ussd_channel ){
            payment_channels.push( 'ussd' );
        }

        if ( wc_squad_params.transfer_channel ){
            payment_channels.push( 'transfer' );
        }

        return payment_channels;

    }

    function wcSquadFormHandler(){
        
        $( '#wc-squad-form' ).hide();

        if( squad_submit ){
            squad_submit = false;
            return true;
        }

        let $form = $( 'form#payment-form, form#order_review' ); 
        let squad_txnref = $form.find( 'input.squad_txnref' );

        squad_txnref.val( '' );

        let amount = Number( wc_squad_params.amount );

        let squad_success_callback = function(transaction){
            console.log(transaction);
            $form.append( '<input type="hidden" class="squad_txnref" name="squad_txnref" value="' + transaction.transaction_ref + '"/>' );
			squad_submit = true;

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

        let squad_close_callback = function(){

            $form.append( '<input type="hidden" class="squad_txnref" name="squad_txnref" value="' + wc_squad_params.transaction_ref + '"/>' );
			squad_submit = true;

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
            onClose: squad_close_callback,
            onLoad: ()=>{console.log('Modal has loaded')},
            onSuccess: squad_success_callback,
            key: wc_squad_params.key,
            email: wc_squad_params.email,
            ammount: amount,
            transaction_ref: wc_squad_params.transaction_ref,
            currency_code: wc_squad_params.currency_code,
            customer_name: wc_squad_params.customer_name,
            metadata: {
                custom_fields: wcSquadCustomFields
            }
        }

        if ( Array.isArray( wcPaymentChannels() ) && wcPaymentChannels().length ) {
            paymentData[ 'payment_channels' ] = wcPaymentChannels();
        }

        const squadInstance = new squad(paymentData);
        squadInstance.setup();
        squadInstance.open();

    }

} );