<?php
/**
 * Plugin Name: WooCommerce Steps Checkout
 * Description: Converts the default WooCommerce checkout into a multi-step checkout process to improve user experience and simplify the checkout flow.
 * Author: Karthick M
 * Version: 1.0.0
 * Text Domain: steps-checkout
 * WC requires at least: 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cwcc_enqueue_assets() {

	// Enqueue WooCommerce checkout scripts
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		wp_enqueue_script( 'wc-checkout' );
	}

	wp_enqueue_script(
		'cwcc-blocks',
		plugin_dir_url( __FILE__ ) . 'build/index.js',
		array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'build/index.js' )
	);
	wp_enqueue_style(
		'cwcc-styles',
		plugin_dir_url( __FILE__ ) . 'build/style.css',
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'build/style.css' )
	);
	wp_enqueue_script(
		'cwcc-checkout',
		plugin_dir_url( __FILE__ ) . 'src/checkout.js',
		array( 'jquery' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'src/checkout.js' ),
		true
	);

	// Localize the script with WooCommerce params
	wp_localize_script(
		'cwcc-checkout',
		'wc_checkout_params',
		array(
			'checkout_url'              => WC_AJAX::get_endpoint( 'checkout' ),
			'ajax_url'                  => admin_url( 'admin-ajax.php' ),
			'apply_coupon_url'          => WC_AJAX::get_endpoint( 'apply_coupon' ),
			'update_order_review_nonce' => wp_create_nonce( 'update-order-review' ),
			'checkout_nonce'            => wp_create_nonce( 'woocommerce-process_checkout' ),
			'remove_coupon_nonce'       => wp_create_nonce( 'remove_coupon_nonce' ),
			'apply_coupon_nonce'        => wp_create_nonce( 'apply_coupon_nonce' ),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'cwcc_enqueue_assets' );
add_action( 'wp_enqueue_scripts', 'cwcc_enqueue_assets' );


require_once plugin_dir_path( __FILE__ ) . 'includes/register-blocks.php';
