<?php
/*
Plugin Name: Woocommerce Add to cart Ajax for variable products
Plugin URI: http://www.rcreators.com/woocommerce-ajax-add-to-cart-variable-products
Description: Ajax based add to cart for varialbe products in woocommerce.
Author: Rishi Mehta - rcreators.com
Version: 1.0.0
Author URI: http://rcreators.com
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
   
   if ( ! function_exists( 'woocommerce_template_loop_add_to_cart' ) ) {
		
		function woocommerce_template_loop_add_to_cart() {
			 global $product;
	
			 //The product_type property is deprecated. Use get_type() to get internal type.
			 if ($product->get_type() == "variable" ) {
				 woocommerce_variable_add_to_cart();
			 }
			 else {
				 //Deprecated : woocommerce_get_template is obsolete since version 3.0! Use wc_get_template instead.
				 wc_get_template( 'loop/add-to-cart.php' );
			 }
		 }
	}
	
	function ajax_add_to_cart_script() {
	
	  wp_enqueue_script( 'add-to-cart-variation', plugins_url() . '/woocommerce-ajax-add-to-cart-variable-products/js/add-to-cart-variation.js', array('jquery'), '', true );
	  wp_localize_script( 'add-to-cart-variation', 'AddToCartAjax', array(
		// URL to wp-admin/admin-ajax.php to process the request
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
	 
	  ));
	}
	add_action( 'wp_enqueue_scripts', 'ajax_add_to_cart_script' );
	
	/* AJAX add to cart variable added by Rishi Mehta - rishi@rcreators.com */
	
	add_action( 'wp_ajax_woocommerce_add_to_cart_variable_rc', 'woocommerce_add_to_cart_variable_rc_callback' );
	
	function woocommerce_add_to_cart_variable_rc_callback() {
		$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
		$quantity = empty( $_POST['quantity'] ) ? 1 : apply_filters( 'woocommerce_stock_amount', $_POST['quantity'] );
		$variation_id = $_POST['variation_id'];
		$variation  = $_POST['variation'];
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
	
		if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation  ) ) {
			do_action( 'woocommerce_ajax_added_to_cart', $product_id );
			if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 'yes' ) {
			wc_add_to_cart_message( $product_id );
		}
	
			// Return fragments
		$this->get_refreshed_fragments();
		} else {
			$this->json_headers();
	
			// If there was an error adding to the cart, redirect to the product page to show any errors
		$data = array(
			'error' => true,
			'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
			);
		echo json_encode( $data );
		}
		die();
	}  
}
?>
