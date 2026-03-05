<?php

function cwcc_register_blocks() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	register_block_type(
		'cwcc/checkout',
		array(
			'editor_script'   => 'cwcc-blocks',
			'render_callback' => 'cwcc_render_checkout_block',
		)
	);
}

add_action( 'init', 'cwcc_register_blocks' );

function endpoint_point_register() {
	register_rest_route(
		'cwcc/v1',
		'/countries',
		array(
			'methods'             => 'GET',
			'callback'            => 'fetch_wc_countries',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'cwcc/v1',
		'/states',
		array(
			'methods'             => 'GET',
			'callback'            => 'fetch_wc_states',
			'permission_callback' => '__return_true',
			'args'                => array(
				'country' => array(
					'required'          => true,
					'validate_callback' => function ( $param ) {
						return is_string( $param );
					},
				),
			),
		)
	);
}

function fetch_wc_countries() {
	if ( class_exists( 'WC_Countries' ) ) {
		$countries_obj = new WC_Countries();
		$countries     = $countries_obj->get_countries();
		return rest_ensure_response( $countries );
	}
	return rest_ensure_response( array() );
}

function fetch_wc_states( $request ) {
	$country = $request->get_param( 'country' );
	$country = strtoupper( $country );
	$states  = WC()->countries->get_states( $country );
	if ( ! empty( $states ) ) {
		return new WP_REST_Response( $states, 200 );
	}
}

add_action( 'rest_api_init', 'endpoint_point_register' );

function cwcc_render_checkout_block() {
	ob_start();
	?>
	<div id="message-container"></div>

	<div class="cwcc-checkout-header">
		<ul class="cwcc-step-indicators">
			<li class="cwcc-step-indicator active">1 Billing</li>
			<li class="cwcc-step-indicator">2 Shipping</li>
			<li class="cwcc-step-indicator">3 Order</li>
			<li class="cwcc-step-indicator">4 Payment</li>
		</ul>
	</div>
	<form name="checkout" method="post" class="checkout woocommerce-checkout">
		<div id="cwcc-checkout-block">
			<div class="cwcc-step cwcc-billing-step">
				<h2><?php esc_html_e( 'Billing Details', 'cwcc' ); ?></h2>
				<div id="billing-form" class="cwcc-form">
					<!-- Billing fields -->
					<div class="cwcc-form-row">
						<div class="cwcc-form-group">
							<label for="billing_country"><?php esc_html_e( 'Country', 'cwcc' ); ?></label>
							<select id="billing_country" name="billing_country" class="cwcc-form-control">
								<option value=""><?php esc_html_e( 'Select a country', 'cwcc' ); ?></option>
							</select>
						</div>
						<div class="cwcc-form-group cwcc-state-wrapper" style="display: none;">
							<label for="billing_state"><?php esc_html_e( 'State', 'cwcc' ); ?></label>
							<select id="billing_state" name="billing_state" class="cwcc-form-control">
								<option value=""><?php esc_html_e( 'Select a state', 'cwcc' ); ?></option>
							</select>
						</div>
					</div>
					<div class="cwcc-form-row">
						<div class="cwcc-form-group">
							<label for="billing_first_name"><?php esc_html_e( 'First Name', 'cwcc' ); ?></label>
							<input id="billing_first_name" type="text" name="billing_first_name" class="cwcc-form-control" />
						</div>
						<div class="cwcc-form-group">
							<label for="billing_last_name"><?php esc_html_e( 'Last Name', 'cwcc' ); ?></label>
							<input id="billing_last_name" type="text" name="billing_last_name" class="cwcc-form-control" />
						</div>
					</div>
					<div class="cwcc-form-row">
						<div class="cwcc-form-group">
							<label for="billing_address_1"><?php esc_html_e( 'Address', 'cwcc' ); ?></label>
							<input id="billing_address_1" type="text" name="billing_address_1" class="cwcc-form-control" />
						</div>
						<div class="cwcc-form-group">
							<label for="billing_address_2"><?php esc_html_e( 'Apartment, suite, etc. (optional)', 'cwcc' ); ?></label>
							<input id="billing_address_2" type="text" name="billing_address_2" class="cwcc-form-control" />
						</div>
					</div>
					<div class="cwcc-form-row">
						<div class="cwcc-form-group">
							<label for="billing_city"><?php esc_html_e( 'City', 'cwcc' ); ?></label>
							<input id="billing_city" type="text" name="billing_city" class="cwcc-form-control" />
						</div>
						<div class="cwcc-form-group">
							<label for="billing_postcode"><?php esc_html_e( 'Postcode', 'cwcc' ); ?></label>
							<input id="billing_postcode" type="text" name="billing_postcode" class="cwcc-form-control" />
						</div>
					</div>
					<div class="cwcc-form-row">
						<div class="cwcc-form-group">
							<label for="billing_phone"><?php esc_html_e( 'Phone', 'cwcc' ); ?></label>
							<input id="billing_phone" type="text" name="billing_phone" class="cwcc-form-control" />
						</div>
						<div class="cwcc-form-group">
							<label for="billing_email"><?php esc_html_e( 'Email', 'cwcc' ); ?></label>
							<input id="billing_email" type="email" name="billing_email" class="cwcc-form-control" />
						</div>
					</div>
					<button class="cwcc-next"><?php esc_html_e( 'Next', 'cwcc' ); ?></button>
				</div>
			</div>
			<div class="cwcc-step cwcc-shipping-step" style="display: none;">
				<h2><?php esc_html_e( 'Order Notes', 'cwcc' ); ?></h2>
				<div id="shipping-form" class="cwcc-form">
					<div class="cwcc-form-group">
						<label for="order_comments"><?php esc_html_e( 'Order Notes', 'cwcc' ); ?></label>
						<textarea id="order_comments" name="order_comments" class="cwcc-form-control" rows="4"></textarea>
					</div>
					<button class="cwcc-prev"><?php esc_html_e( 'Previous', 'cwcc' ); ?></button>
					<button class="cwcc-next"><?php esc_html_e( 'Next', 'cwcc' ); ?></button>
				</div>
			</div>
			<div class="cwcc-step cwcc-order-summary-step" style="display: none;">
				<h2><?php esc_html_e( 'Order Summary', 'cwcc' ); ?></h2>
				<div id="order-summary" class="cwcc-form">
					<?php
					if ( WC()->cart && ! WC()->cart->is_empty() ) {
						wc_get_template( 'checkout/review-order.php' );
					} else {
						echo '<p>' . esc_html_e( 'Your cart is currently empty.', 'cwcc' ) . '</p>';
					}
					?>
					<button class="cwcc-prev"><?php esc_html_e( 'Previous', 'cwcc' ); ?></button>
					<button class="cwcc-next"><?php esc_html_e( 'Next', 'cwcc' ); ?></button>
				</div>
			</div>
			<div class="cwcc-step cwcc-payment-step" style="display: none;">
				<h2><?php esc_html_e( 'Payment', 'cwcc' ); ?></h2>
				<div id="payment-form" class="cwcc-form">
					<?php
					if ( WC()->cart && WC()->cart->needs_payment() ) {
						$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
						if ( ! empty( $available_gateways ) ) {
							foreach ( $available_gateways as $gateway ) {
								wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
							}
						}
					}
					?>
					<button class="cwcc-prev"><?php esc_html_e( 'Previous', 'cwcc' ); ?></button>
					<button id="place_order" type="submit" class="button alt"><?php esc_html_e( 'Place order', 'cwcc' ); ?></button>
				</div>
			</div>
			<!-- Back to Cart Button -->
			<div class="cwcc-back-to-cart">
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="button cwcc-back-to-cart-btn"><?php esc_html_e( 'Back to Cart', 'cwcc' ); ?></a>
			</div>
		</div>
		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</form>
	<?php
	return ob_get_clean();
}
