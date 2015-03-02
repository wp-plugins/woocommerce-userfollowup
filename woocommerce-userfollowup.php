<?php
/*
Plugin Name: WooCommerce UserFollowUp
Plugin URI: http://userfollowup.com
Description: WooCommerce UserFollowUp connects events triggered by your customers to automatic follow up emails sent in your name, automating common tasks, reminding them of incomplete actions, brigging back customers and creating better service.
Version: 1.0
Author: WidgiLabs
Author URI: http://userfollowup.com
License: GPL2
*/

/**
 * wp_userfollowup class
 *
 * This plugin is a class because reasons, #blamenacin
 *
 */
if ( !class_exists( 'wp_userfollowup' ) ) {

	class wp_userfollowup {

		// Holds the api key value
		var $api_key;
		var $event;
		var $action;
		
		// instance
		static $instance;

		/**
		 * Add init hooks on class construction
		 */
		function wp_userfollowup() {

			// allow this instance to be called from outside the class
			self::$instance = $this;

			add_action( 'init', array( $this, 'init' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		}

		/**
		 * Init callback 
		 * 
		 * Load translations and add iframe code, if present
		 *
		 */
		function init() {

			load_plugin_textdomain( 'wp-userfollowup', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

			$wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );

			if ( !empty( $wp_userfollowup_vars['apikey'] ) ) {
				$this->api_key = $wp_userfollowup_vars['apikey'];
				add_action( 'wp_head', array( $this, 'add_code' ), 999 );
                                add_action( 'login_head', array( $this, 'add_code' ), 999 );

                                add_filter( 'the_content', array( $this, 'postPageView' ), 999 ); // Post/page views
                                
                                add_filter( 'the_content', array( $this, 'linksPostPage' ), 999 ); // track links
                                add_filter( 'comment_text', array( $this, 'linksComments' ), 999 );
                                
                                
                                add_action( 'login_footer', array($this, 'track_register_view' ),999 );//Track sign up page view                                
                                
                                
                                add_action( 'wp_login', array($this, 'track_login' ) ,999); //Track login events
//                                add_action( 'login_footer', array($this, 'track_register' ),999 );//Track registration event
                                add_action( 'user_register', array($this, 'track_register' ) ,999); //Track login events
                                add_action( 'comment_form', array( $this, 'track_comments' ), 999 ); //Track comments
                                
                                $wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );
                                if( $wp_userfollowup_vars["wpcp-search"] ) {
                                    add_action( 'wp_head', array($this, 'track_search' ),999 );
                                }
                                
                                if( $wp_userfollowup_vars["wpcp-social"] ) {
                                    add_action( 'wp_head', array( $this, 'track_social' ), 999 );
                                }
//                                wooCommerce
                                 add_action( 'woocommerce_after_single_product', array( $this, 'wooProductView' ), 999 ); // Product is viewed
                                 
                                 
                                 
                                 //Woo Do we need this template actions?
                                 add_action( 'get_product_search_form', array( $this, 'woo_get_product_search_form' ), 999 ); // Output Product search forms 
                                 add_action( 'woocommerce_after_cart', array( $this, 'woo_woocommerce_after_cart' ), 999 ); 
                                 add_action( 'woocommerce_after_cart_contents', array( $this, 'woo_woocommerce_after_cart_contents' ), 999 ); 
                                 add_action( 'woocommerce_after_cart_table', array( $this, 'woo_woocommerce_after_cart_table' ), 999 ); 
                                 add_action( 'woocommerce_after_cart_totals', array( $this, 'woo_woocommerce_after_cart_totals' ), 999 ); 
                                 
                                 add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'woo_woocommerce_after_checkout_billing_form' ), 999, 1); 
                                 add_action( 'woocommerce_after_checkout_form', array( $this, 'woo_woocommerce_after_checkout_form' ), 999, 1); 
                                 add_action( 'woocommerce_after_checkout_registration_form', array( $this, 'woo_woocommerce_after_checkout_registration_form' ), 999, 1); 
                                 add_action( 'woocommerce_after_checkout_shipping_form', array( $this, 'woo_woocommerce_after_checkout_shipping_form' ), 999, 1); 
                                
                                 add_action( 'woocommerce_after_customer_login_form', array( $this, 'woo_woocommerce_after_customer_login_form' ), 999 );
                                 add_action( 'woocommerce_after_main_content', array( $this, 'woo_woocommerce_after_main_content' ), 999 );
                                 add_action( 'woocommerce_after_mini_cart', array( $this, 'woo_woocommerce_after_mini_cart' ), 999 );
                                 add_action( 'woocommerce_after_my_account', array( $this, 'woo_woocommerce_after_my_account' ), 999 );
                                 add_action( 'woocommerce_after_order_notes', array( $this, 'woo_woocommerce_after_order_notes' ), 999, 1); 
                                 add_action( 'woocommerce_after_shipping_calculator', array( $this, 'woo_woocommerce_after_shipping_calculator' ), 999 );
                                 add_action( 'woocommerce_after_shop_loop', array( $this, 'woo_woocommerce_after_shop_loop' ), 999 );
                                 add_action( 'woocommerce_after_shop_loop_item', array( $this, 'woo_woocommerce_after_shop_loop_item' ), 999 );
                                 add_action( 'woocommerce_after_shop_loop_item_title', array( $this, 'woo_woocommerce_after_shop_loop_item_title' ), 999 );
//                                 add_action( 'woocommerce_after_single_product', array( $this, 'woo_woocommerce_after_single_product' ), 999 );
//                                 add_action( 'woocommerce_after_single_product_summary', array( $this, 'woo_woocommerce_after_single_product_summary' ), 999 );
                                 add_action( 'woocommerce_after_subcategory', array( $this, 'woo_woocommerce_after_subcategory' ), 999, 1);
                                 add_action( 'woocommerce_after_subcategory_title', array( $this, 'woo_woocommerce_after_subcategory_title' ), 999, 1);
                                 
                                 add_action( 'woocommerce_archive_description', array( $this, 'woo_woocommerce_archive_description' ), 999 );
                                 add_action( 'woocommerce_available_download_end', array( $this, 'woo_woocommerce_available_download_end' ), 999, 1);
                                 add_action( 'woocommerce_available_download_start', array( $this, 'woo_woocommerce_available_download_start' ), 999, 1);
                                 
                                 add_action( 'woocommerce_before_cart', array( $this, 'woo_woocommerce_before_cart' ), 999 );
                                 add_action( 'woocommerce_before_cart_contents', array( $this, 'woo_woocommerce_before_cart_contents' ), 999 );
                                 add_action( 'woocommerce_before_cart_table', array( $this, 'woo_woocommerce_before_cart_table' ), 999 );
                                 add_action( 'woocommerce_before_cart_totals', array( $this, 'woo_woocommerce_before_cart_totals' ), 999 );
                                 add_action( 'woocommerce_before_checkout_billing_form', array( $this, 'woo_woocommerce_before_checkout_billing_form' ), 999, 1);
                                 add_action( 'woocommerce_before_checkout_registration_form', array( $this, 'woo_woocommerce_before_checkout_registration_form' ), 999, 1);
                                 add_action( 'woocommerce_before_checkout_shipping_form', array( $this, 'woo_woocommerce_before_checkout_shipping_form' ), 999, 1);
                                 add_action( 'woocommerce_before_customer_login_form', array( $this, 'woo_woocommerce_before_customer_login_form' ), 999 );
                                 add_action( 'woocommerce_before_main_content', array( $this, 'woo_woocommerce_before_main_content' ), 999 );
                                 add_action( 'woocommerce_before_mini_cart', array( $this, 'woo_woocommerce_before_mini_cart' ), 999 );
                                 add_action( 'woocommerce_before_my_account', array( $this, 'woo_woocommerce_before_my_account' ), 999 );
                                 add_action( 'woocommerce_before_order_notes', array( $this, 'woo_woocommerce_before_order_notes' ), 999, 1);
                                 add_action( 'woocommerce_before_shipping_calculator', array( $this, 'woo_woocommerce_before_shipping_calculator' ), 999 );
                                 add_action( 'woocommerce_before_shop_loop', array( $this, 'woo_woocommerce_before_shop_loop' ), 999 );
                                 add_action( 'woocommerce_before_shop_loop_item', array( $this, 'woo_woocommerce_before_shop_loop_item' ), 999 );
                                 add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'woo_woocommerce_before_shop_loop_item_title' ), 999 );
                                 add_action( 'woocommerce_before_single_product', array( $this, 'woo_woocommerce_before_single_product' ), 999 );
                                 add_action( 'woocommerce_before_single_product_summary', array( $this, 'woo_woocommerce_before_single_product_summary' ), 999 );
                                 add_action( 'woocommerce_before_subcategory', array( $this, 'woo_woocommerce_before_subcategory' ), 999, 1);
                                 add_action( 'woocommerce_before_subcategory_title', array( $this, 'woo_woocommerce_before_subcategory_title' ), 999, 1);
                                 add_action( 'woocommerce_cart_collaterals', array( $this, 'woo_woocommerce_cart_collaterals' ), 999 );
                                 add_action( 'woocommerce_cart_contents', array( $this, 'woo_woocommerce_cart_contents' ), 999 );
                                 add_action( 'woocommerce_cart_coupon', array( $this, 'woo_woocommerce_cart_coupon' ), 999 );
                                 add_action( 'woocommerce_cart_has_errors', array( $this, 'woo_woocommerce_cart_has_errors' ), 999 );
                                 add_action( 'woocommerce_cart_is_empty', array( $this, 'woo_woocommerce_cart_is_empty' ), 999 );
                                 add_action( 'woocommerce_cart_totals_after_order_total', array( $this, 'woo_woocommerce_cart_totals_after_order_total' ), 999 );
                                 add_action( 'woocommerce_cart_totals_after_shipping', array( $this, 'woo_woocommerce_cart_totals_after_shipping' ), 999 );
                                 add_action( 'woocommerce_cart_totals_before_order_total', array( $this, 'woo_woocommerce_cart_totals_before_order_total' ), 999 );
                                 add_action( 'woocommerce_cart_totals_before_shipping', array( $this, 'woo_woocommerce_cart_totals_before_shipping' ), 999 );
                                 add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'woo_woocommerce_checkout_after_customer_details' ), 999 );
                                 add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'woo_woocommerce_checkout_before_customer_details' ), 999 );
                                 add_action( 'woocommerce_checkout_billing', array( $this, 'woo_woocommerce_checkout_billing' ), 999 );
                                 add_action( 'woocommerce_checkout_order_review', array( $this, 'woo_woocommerce_checkout_order_review' ), 999 ); // Display review order table
                                 add_action( 'woocommerce_checkout_shipping', array( $this, 'woo_woocommerce_checkout_shipping' ), 999 );
                                 add_action( 'woocommerce_email_after_order_table', array( $this, 'woo_woocommerce_email_after_order_table' ), 999, 1);
                                 add_action( 'woocommerce_email_before_order_table', array( $this, 'woo_woocommerce_email_before_order_table' ), 999, 1);
                                 add_action( 'woocommerce_email_footer', array( $this, 'woo_woocommerce_email_footer' ), 999 );
                                 add_action( 'woocommerce_email_header', array( $this, 'woo_woocommerce_email_header' ), 999, 1);
                                 add_action( 'woocommerce_email_order_meta', array( $this, 'woo_woocommerce_email_order_meta' ), 999, 1);
                                 add_action( 'woocommerce_order_details_after_order_table', array( $this, 'woo_woocommerce_order_details_after_order_table' ), 999, 1);
                                 add_action( 'woocommerce_order_items_table', array( $this, 'woo_woocommerce_order_items_table' ), 999, 1);
                                 add_action( 'woocommerce_proceed_to_checkout', array( $this, 'woo_woocommerce_proceed_to_checkout' ), 999 );
                                 add_action( 'woocommerce_product_meta_end', array( $this, 'woo_woocommerce_product_meta_end' ), 999 );
                                 add_action( 'woocommerce_product_meta_start', array( $this, 'woo_woocommerce_product_meta_start' ), 999 );
                                 add_action( 'woocommerce_product_thumbnails', array( $this, 'woo_woocommerce_product_thumbnails' ), 999 );
                                 add_action( 'woocommerce_review_order_after_cart_contents', array( $this, 'woo_woocommerce_review_order_after_cart_contents' ), 999 );
                                 add_action( 'woocommerce_review_order_after_order_total', array( $this, 'woo_woocommerce_review_order_after_order_total' ), 999 );
                                 add_action( 'woocommerce_review_order_after_shipping', array( $this, 'woo_woocommerce_review_order_after_shipping' ), 999 );
                                 add_action( 'woocommerce_review_order_after_submit', array( $this, 'woo_woocommerce_review_order_after_submit' ), 999 );
                                 add_action( 'woocommerce_review_order_before_cart_contents', array( $this, 'woo_woocommerce_review_order_before_cart_contents' ), 999 );
                                 add_action( 'woocommerce_review_order_before_order_total', array( $this, 'woo_woocommerce_review_order_before_order_total' ), 999 );
                                 add_action( 'woocommerce_review_order_before_shipping', array( $this, 'woo_woocommerce_review_order_before_shipping' ), 999 );
                                 add_action( 'woocommerce_review_order_before_submit', array( $this, 'woo_woocommerce_review_order_before_submit' ), 999 );
                                 add_action( 'woocommerce_share', array( $this, 'woo_woocommerce_share' ), 999 );
                                 add_action( 'woocommerce_sidebar', array( $this, 'woo_woocommerce_sidebar' ), 999 );
                                 add_action( 'woocommerce_single_product_summary', array( $this, 'woo_woocommerce_single_product_summary' ), 999 );
                                 add_action( 'woocommerce_thankyou', array( $this, 'woo_woocommerce_thankyou' ), 999, 1);
                                 add_action( 'woocommerce_view_order', array( $this, 'woo_woocommerce_view_order' ), 999, 1);
                                 add_action( 'woocommerce_widget_shopping_cart_before_buttons', array( $this, 'woo_woocommerce_widget_shopping_cart_before_buttons' ), 999 );
                                 
                                 //Class actions
                                 add_action( 'after_woocommerce_pay', array( $this, 'woo_after_woocommerce_pay' ), 999 );
                                 add_action( 'before_woocommerce_pay', array( $this, 'woo_before_woocommerce_pay' ), 999 );
                                 add_action( 'woocommerce_add_order_item_meta', array( $this, 'woo_woocommerce_add_order_item_meta' ), 999, 2 );
                                 add_action( 'woocommerce_add_to_cart', array( $this, 'wooAdd_to_cart' ), 999, 6 ); // Something was added to the cart
                                 add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'woo_woocommerce_after_cart_item_quantity_update' ), 999, 2 );
                                 add_action( 'woocommerce_after_checkout_validation', array( $this, 'woo_woocommerce_after_checkout_validation' ), 999, 1 );
                                 add_action( 'woocommerce_applied_coupon', array( $this, 'woo_woocommerce_applied_coupon' ), 999, 1 );
                                 add_action( 'woocommerce_before_calculate_totals', array( $this, 'woo_woocommerce_before_calculate_totals' ), 999, 1 );
                                 add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'woo_woocommerce_before_cart_item_quantity_zero' ), 999, 1 );
                                 add_action( 'woocommerce_before_checkout_process', array( $this, 'woo_woocommerce_before_checkout_process' ), 999 );
                                 add_action( 'woocommerce_calculate_totals', array( $this, 'woo_woocommerce_calculate_totals' ), 999, 1 );
                                 add_action( 'woocommerce_calculated_shipping', array( $this, 'woo_woocommerce_calculated_shipping' ), 999 );
                                 add_action( 'woocommerce_cart_emptied', array( $this, 'woo_woocommerce_cart_emptied' ), 999 );
                                 add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'woo_woocommerce_cart_loaded_from_session' ), 999, 1 );
                                 add_action( 'woocommerce_cart_updated', array( $this, 'woo_woocommerce_cart_updated' ), 999 );
                                 add_action( 'woocommerce_check_cart_items', array( $this, 'woo_woocommerce_check_cart_items' ), 999 );
                                 add_action( 'woocommerce_checkout_init', array( $this, 'woo_woocommerce_checkout_init' ), 999 );
                                 add_action( 'woocommerce_checkout_order_processed', array( $this, 'woo_woocommerce_checkout_order_processed' ), 999, 2 );
                                 add_action( 'woocommerce_checkout_process', array( $this, 'woo_woocommerce_checkout_process' ), 999 );
                                 add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'woo_woocommerce_checkout_update_order_meta' ), 999, 2 );
                                 add_action( 'woocommerce_checkout_update_user_meta', array( $this, 'woo_woocommerce_checkout_update_user_meta' ), 999, 2 );
                                 add_action( 'woocommerce_coupon_loaded', array( $this, 'woo_woocommerce_coupon_loaded' ), 999, 1 );
                                 add_action( 'woocommerce_created_customer', array( $this, 'woo_woocommerce_created_customer' ), 999, 1 );
                                 add_action( 'woocommerce_customer_reset_password', array( $this, 'woo_woocommerce_customer_reset_password' ), 999, 1 );
                                 add_action( 'woocommerce_email', array( $this, 'woo_woocommerce_email' ), 999, 1 );
                                  
                                 add_action( 'woocommerce_integrations_init', array( $this, 'woo_woocommerce_integrations_init' ), 999 );
                                 add_action( 'woocommerce_load_shipping_methods', array( $this, 'woo_woocommerce_load_shipping_methods' ), 999, 1 );
                                 add_action( 'woocommerce_low_stock', array( $this, 'woo_woocommerce_low_stock' ), 999, 1 );
                                 add_action( 'woocommerce_new_customer_note', array( $this, 'woo_woocommerce_new_customer_note' ), 999, 1 );
                                 add_action( 'woocommerce_new_order', array( $this, 'woo_woocommerce_new_order' ), 999, 1 );
                                 add_action( 'woocommerce_no_stock', array( $this, 'woo_woocommerce_no_stock' ), 999, 1 );
                                 add_action( 'woocommerce_order_status_changed', array( $this, 'woo_woocommerce_order_status_changed' ), 999, 3 );
                                 add_action( 'woocommerce_payment_complete', array( $this, 'woo_woocommerce_payment_complete' ), 999, 1 );
                                 add_action( 'woocommerce_product_on_backorder', array( $this, 'woo_woocommerce_product_on_backorder' ), 999, 1 );
                                 add_action( 'woocommerce_product_query', array( $this, 'woo_woocommerce_product_query' ), 999, 2 );
                                 add_action( 'woocommerce_product_set_stock_status', array( $this, 'woo_woocommerce_product_set_stock_status' ), 999, 2 );
                                 add_action( 'woocommerce_reduce_order_stock', array( $this, 'woo_woocommerce_reduce_order_stock' ), 999, 1 );
                                 add_action( 'woocommerce_register_post', array( $this, 'woo_woocommerce_register_post' ), 999, 3 );
                                 add_action( 'woocommerce_reset_password_notification', array( $this, 'woo_woocommerce_reset_password_notification' ), 999, 2 );
                                 add_action( 'woocommerce_resume_order', array( $this, 'woo_woocommerce_resume_order' ), 999, 1 );
                                 add_action( 'woocommerce_resume_order', array( $this, 'woo_woocommerce_resume_order' ), 999, 1 );
                                 add_action( 'woocommerce_shipping_init', array( $this, 'woo_woocommerce_shipping_init' ), 999);
                                 add_action( 'woocommerce_shipping_method_chosen', array( $this, 'woo_woocommerce_shipping_method_chosen' ), 999, 1 );
                                 add_action( 'woocommerce_track_order', array( $this, 'woo_woocommerce_track_order' ), 999, 1 );
                                 
                                 //Other actions
                                 add_action( 'before_woocommerce_init', array( $this, 'woo_before_woocommerce_init' ), 999);
                                 add_action( 'product_variation_linked', array( $this, 'woo_product_variation_linked' ), 999, 1);
                                 add_action( 'woocommerce_after_template_part', array( $this, 'woo_woocommerce_after_template_part' ), 999, 4);
                                 add_action( 'woocommerce_ajax_added_to_cart', array( $this, 'woo_woocommerce_ajax_added_to_cart' ), 999, 1);
                                 add_action( 'woocommerce_api_wc_gateway_paypal', array( $this, 'woo_woocommerce_api_wc_gateway_paypal' ), 999);
                                 add_action( 'woocommerce_before_delete_order_item', array( $this, 'woo_woocommerce_before_delete_order_item' ), 999, 1);
                                 add_action( 'woocommerce_before_template_part', array( $this, 'woo_woocommerce_before_template_part' ), 999, 4);
                                 add_action( 'woocommerce_cancelled_order', array( $this, 'woo_woocommerce_cancelled_order' ), 999, 1);
                                 add_action( 'woocommerce_checkout_update_order_review', array( $this, 'woo_woocommerce_checkout_update_order_review' ), 999, 1);
                                 add_action( 'woocommerce_create_product_variation', array( $this, 'woo_woocommerce_create_product_variation' ), 999, 1);
                                 add_action( 'woocommerce_create_product_variation', array( $this, 'woo_woocommerce_create_product_variation' ), 999, 1);
                                 add_action( 'woocommerce_customer_change_password', array( $this, 'woo_woocommerce_customer_change_password' ), 999, 1);
                                 add_action( 'woocommerce_customer_save_address', array( $this, 'woo_woocommerce_customer_save_address' ), 999, 1);
                                 add_action( 'woocommerce_delete_order_item', array( $this, 'woo_woocommerce_delete_order_item' ), 999, 1);
                                 add_action( 'woocommerce_download_product', array( $this, 'woo_woocommerce_download_product' ), 999, 6);
                                 add_action( 'woocommerce_init', array( $this, 'woo_woocommerce_init' ), 999);
                                 add_action( 'woocommerce_loaded', array( $this, 'woo_woocommerce_loaded' ), 999);
                                 add_action( 'woocommerce_new_order_item', array( $this, 'woo_woocommerce_new_order_item' ), 999, 3);
                                 add_action( 'woocommerce_ordered_again', array( $this, 'woo_woocommerce_ordered_again' ), 999, 1);
                                 add_action( 'woocommerce_register_post_type', array( $this, 'woo_woocommerce_register_post_type' ), 999);
                                 add_action( 'woocommerce_register_taxonomy', array( $this, 'woo_woocommerce_register_taxonomy' ), 999);
                                 add_action( 'woocommerce_restore_order_stock', array( $this, 'woo_woocommerce_restore_order_stock' ), 999, 1);
                                 
                                 
                                 //Dynamic actions
                                 
                               //this is template part
                                add_action( 'woocommerce_simple_add_to_cart', array( $this, 'woo_woocommerce_simple_add_to_cart' ), 999 );
                                add_action( 'woocommerce_grouped_add_to_cart', array( $this, 'woo_woocommerce_grouped_add_to_cart' ), 999 );
                                add_action( 'woocommerce_variable_add_to_cart', array( $this, 'woo_woocommerce_variable_add_to_cart' ), 999 );
                                add_action( 'woocommerce_external_add_to_cart', array( $this, 'woo_woocommerce_external_add_to_cart' ), 999 );
                                
                                //coupons
                                add_action( 'woocommerce_cart_discount_after_tax_fixed_cart', array( $this, 'woo_woocommerce_cart_discount_after_tax_fixed_cart' ), 999, 1);
                                add_action( 'woocommerce_cart_discount_after_tax_percent', array( $this, 'woo_woocommerce_cart_discount_after_tax_percent' ), 999, 1);
                                
                                add_action( 'woocommerce_product_discount_after_tax_fixed_cart', array( $this, 'woo_woocommerce_product_discount_after_tax_fixed_cart' ), 999, 3);
                                add_action( 'woocommerce_product_discount_after_tax_percent', array( $this, 'woo_woocommerce_product_discount_after_tax_percent' ), 999, 3);
                                
                                //order status
                                add_action( 'woocommerce_order_status_cancelled', array( $this, 'woo_woocommerce_order_status_cancelled' ), 999, 1);
                                add_action( 'woocommerce_order_status_completed', array( $this, 'woo_woocommerce_order_status_completed' ), 999, 1);
                                add_action( 'woocommerce_order_status_failed', array( $this, 'woo_woocommerce_order_status_failed' ), 999, 1);
                                add_action( 'woocommerce_order_status_on-hold', array( $this, 'woo_woocommerce_order_status_on-hold' ), 999, 1);
                                add_action( 'woocommerce_order_status_pending', array( $this, 'woo_woocommerce_order_status_pending' ), 999, 1);
                                add_action( 'woocommerce_order_status_processing', array( $this, 'woo_woocommerce_order_status_processing' ), 999, 1);
                                add_action( 'woocommerce_order_status_refunded', array( $this, 'woo_woocommerce_order_status_refunded' ), 999, 1);
                                
                                //payment options
                                add_action( 'woocommerce_receipt_bacs', array( $this, 'woo_woocommerce_receipt_bacs' ), 999, 1); //Direct Bank Transfer
                                add_action( 'woocommerce_receipt_cheque', array( $this, 'woo_woocommerce_receipt_cheque' ), 999, 1);//Cheque Payment
                                add_action( 'woocommerce_receipt_paypal', array( $this, 'woo_woocommerce_receipt_paypal' ), 999, 1);
                                add_action( 'woocommerce_receipt_other', array( $this, 'woo_woocommerce_receipt_other' ), 999, 1);
                                
                                add_action( 'woocommerce_thankyou_bacs', array( $this, 'woo_woocommerce_thankyou_bacs' ), 999, 1);
                                add_action( 'woocommerce_thankyou_cheque', array( $this, 'woo_woocommerce_thankyou_cheque' ), 999, 1);
                                add_action( 'woocommerce_thankyou_paypal', array( $this, 'woo_woocommerce_thankyou_paypal' ), 999, 1);
                                add_action( 'woocommerce_thankyou_other', array( $this, 'woo_woocommerce_thankyou_other' ), 999, 1);
                                 
                                
                                
                                //Multibanco Gateway
                                add_action( 'woocommerce_thankyou_ifmb', array( $this, 'woo_woocommerce_thankyou_ifmb' ), 999, 1);
                                
                                
                                                                
			}
			
			if ( !empty( $wp_userfollowup_vars['event'] ) ) {
				$this->event = $wp_userfollowup_vars['event'];
			}
			
			if ( !empty( $wp_userfollowup_vars['action'] ) ) {
				$this->action = $wp_userfollowup_vars['action'];
			}
		}


		/**
		 * Admin init callback
		 * 
		 * Register options, add settings page
		 *
		 */
		function admin_init() {

			register_setting(
				'wp_userfollowup_vars_group',
				'wp_userfollowup_vars',
				array( $this, 'validate_form' ) );
                                                
			add_settings_section(
				'wp_userfollowup_vars_id',
				__( 'Settings', 'wp-userfollowup' ),
				array( $this, 'overview' ),
				'WP UserFollowUp Settings' );

			add_settings_field(
				'wpcp-apikey',
				__( 'Tracking ID:', 'wp-userfollowup' ),
				array( $this, 'render_field' ),
				'WP UserFollowUp Settings',
				'wp_userfollowup_vars_id' );
			
//			add_settings_section(
//				'wp_userfollowup_events_id',
//				__( 'Dashboard', 'wp-userfollowup' ),
//				array( $this, 'events_overview' ),
//				'WP UserFollowUp Settings' );
//			
//			add_settings_field(
//					'wpcp-event',
//					__( 'WordPress Event:', 'wp-userfollowup' ),
//					array( $this, 'render_wordpress_event' ),
//					'WP UserFollowUp Settings',
//					'wp_userfollowup_events_id' );
//			
//			add_settings_field(
//					'wpcp-action',
//					__( 'Action to Trigger:', 'wp-userfollowup' ),
//					array( $this, 'render_action' ),
//					'WP UserFollowUp Settings',
//					'wp_userfollowup_events_id' );
//                        
                        //Identity
                        add_settings_section(
				'wp_userfollowup_identity',
				__( 'Identity', 'wp-userfollowup' ),
				'',
				'WP UserFollowUp Settings' );
                        
                                            
                        $args[]     = array ('name' => 'wpcp-auth','section'=>'wp_userfollowup_identity','title' => __( "Identify authenticated users:", "wp-userfollowup" ));
                        $args[]     = array ('name' => 'wpcp-login','section'=>'wp_userfollowup_identity','title' => __( "Track login events:", "wp-userfollowup" ));
                        
                        
                         //Registration
                        add_settings_section(
				'wp_userfollowup_registration',
				__( 'Registration', 'wp-userfollowup' ),
				'',
				'WP UserFollowUp Settings' );
                        
                         $args[]     = array ('name' => 'wpcp-signup','section'=>'wp_userfollowup_registration','title' => __( "Track 'Sign up' page view:", "wp-userfollowup" ));
                         $args[]     = array ('name' => 'wpcp-registration','section'=>'wp_userfollowup_registration','title' => __( "Track registration event:", "wp-userfollowup" ));
                        
                          //General tracking
                        add_settings_section(
				'wp_userfollowup_general',
				__( 'General tracking', 'wp-userfollowup' ),
				'',
				'WP UserFollowUp Settings' );
                        
                        $args[]     = array ('name' => 'wpcp-postPage','section'=>'wp_userfollowup_general','title' => __( "Post/page views:", "wp-userfollowup" ));
                        $args[]     = array ('name' => 'wpcp-linksPostPage','section'=>'wp_userfollowup_general','title' => __( "Links clicks in post/pages:", "wp-userfollowup" ));
                        $args[]     = array ('name' => 'wpcp-linksComments','section'=>'wp_userfollowup_general','title' => __( "Links clicks in comments:", "wp-userfollowup" ));
                        $args[]     = array ('name' => 'wpcp-social','section'=>'wp_userfollowup_general','title' => __( "Social buttons clicks (Facebook, Twitter):", "wp-userfollowup" ));
                        $args[]     = array ('name' => 'wpcp-search','section'=>'wp_userfollowup_general','title' => __( "Search queries:", "wp-userfollowup" ));
                        
                         
                        //Comments
                        add_settings_section(
				'wp_userfollowup_comments',
				__( 'Comments', 'wp-userfollowup' ),
				'',
				'WP UserFollowUp Settings' );
                        
                        $args[]     = array ('name' => 'wpcp-comment','section'=>'wp_userfollowup_comments','title' => __( "Track comment submission:", "wp-userfollowup" ));
                        
                          //We need special server action for this process
//                        $args[]     = array ('name' => 'wpcp-idByComment','section'=>'wp_userfollowup_comments','title' => __( "Identify unregistered users by comment email:", "wp-userfollowup" ));
                        
                        
                        $id=0;
                        foreach ($args as $arg) {
                            $id++;
                            add_settings_field(
					'wpcp-'.$id,
					$arg["title"],
					array( $this, 'render_actions' ),
					'WP UserFollowUp Settings',
					$arg["section"],
                                        $arg );
                        }
                        
                        
                        
                        //WooCommerce
                        register_setting(
				'wp_userfollowup_woocommerce_vars_group',
				'wp_userfollowup_woocommerce_vars' );


                        add_settings_section(
				'wp_userfollowup_woocommerce_template',
				__( 'WooCommerce events', 'wp-userfollowup' ),
				'',
				'WP UserFollowUp WooCommerce Settings');
                        
                        
                        
                        //templates
                        $argswoo[]     = array ('name' => 'woo-woocommerce_simple_add_to_cart','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Simple product added to cart:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_grouped_add_to_cart','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Grouped product added to cart:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_variable_add_to_cart','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Variable product added to cart:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_external_add_to_cart','section'=>'wp_userfollowup_woocommerce_template','title' => __( "External product added to cart:", "wp-userfollowup" ));
                        
                        
                        $argswoo[]     = array ('name' => 'woo-get_product_search_form','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Get product search form:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_cart','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After cart:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_cart_contents','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After cart contents:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_cart_table','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After cart table:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_cart_totals','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After cart totals:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_checkout_form','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After checkout form:", "wp-userfollowup" ));                        
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_checkout_registration_form','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After checkout registration form:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_checkout_shipping_form','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After checkout shipping form:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_customer_login_form','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After customer login form:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_main_content','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After main content:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_mini_cart','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After mini cart:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_my_account','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After my account:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_order_notes','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After order notes:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_shipping_calculator','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After shipping calculator:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_shop_loop','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After shop loop:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_shop_loop_item','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After shop loop item:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_shop_loop_item_title','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After shop loop item title:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_single_product','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Single product viewed:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_single_product_summary','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After single product summary:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_subcategory','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After subcategory:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_subcategory_title','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After subcategory title:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_archive_description','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After archive description:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_available_download_end','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After available download end:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_available_download_start','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After download start:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_cart','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before cart:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_cart_contents','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before cart contents:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_cart_table','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before cart table:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_cart_totals','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before cart totals:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_checkout_billing_form','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before checkout billing form:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_checkout_form','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before checkout form:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_checkout_registration_form','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before checkout registration form:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_checkout_shipping_form','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before checkout shipping form:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_customer_login_form','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before customer login form:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_main_content','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before main content:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_mini_cart','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before mini cart:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_my_account','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before my account:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_order_notes','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before order notes:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_shipping_calculator','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before shipping calculator:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_shop_loop','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before shop loop:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_shop_loop_item','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before shop loop item:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_shop_loop_item_title','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before shop loop item title:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_single_product','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before single product:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_single_product_summary','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before single product summary:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_subcategory','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before subcategory:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_subcategory_title','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before subcategory title:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_cart_collaterals','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Cart collaterals:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_cart_contents','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Cart contents:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_cart_coupon','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Cart coupon:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_cart_has_errors','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Cart has errors:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_cart_is_empty','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Cart is empty:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_cart_totals_after_order_total','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Cart totals after order total:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_cart_totals_after_shipping','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Cart totals after shipping:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_cart_totals_before_order_total','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Cart totals before order total:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_cart_totals_before_shipping','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Cart totals before shipping:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_checkout_after_customer_details','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Checkout after customer details:", "wp-userfollowup" ));
                        
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_checkout_before_customer_details','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_checkout_before_customer_details:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_checkout_billing','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_checkout_billing:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_checkout_order_review','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_checkout_order_review:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_checkout_shipping','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_checkout_shipping:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_email_after_order_table','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_email_after_order_table:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_email_before_order_table','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_email_before_order_table:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_email_footer','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_email_footer:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_email_header','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_email_header:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_email_order_meta','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_email_order_meta:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_order_details_after_order_table','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_order_details_after_order_table:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_order_items_table','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_order_items_table:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_proceed_to_checkout','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_proceed_to_checkout:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_product_meta_end','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_product_meta_end:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_product_meta_start','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_product_meta_start:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_product_thumbnails','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_product_thumbnails:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_review_order_after_cart_contents','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_review_order_after_cart_contents:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_review_order_after_order_total','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_review_order_after_order_total:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_review_order_after_shipping','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_review_order_after_shipping:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_review_order_after_submit','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_review_order_after_submit:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_review_order_before_cart_contents','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_review_order_before_cart_contents:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_review_order_before_order_total','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_review_order_before_order_total:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_review_order_before_shipping','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_review_order_before_shipping:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_review_order_before_submit','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_review_order_before_submit:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_share','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_share:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_sidebar','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_sidebar:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_single_product_summary','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Single product summary viewed:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_thankyou','section'=>'wp_userfollowup_woocommerce_template','title' => __( "'Thank you' page viewed:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_view_order','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_view_order:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_widget_shopping_cart_before_buttons','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_widget_shopping_cart_before_buttons:", "wp-userfollowup" ));
                        
                        //Class Hooks
                        
                              
                        $argswoo[]     = array ('name' => 'woo-woocommerce_cart_discount_after_tax_fixed_cart','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Cart discount after tax fixed cart:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_cart_discount_after_tax_percent','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Cart discount after tax percent:", "wp-userfollowup" ));
                        
                        $argswoo[]     = array ('name' => 'woo-woo_woocommerce_product_discount_after_tax_fixed_cart','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Product discount after tax fixed cart:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_product_discount_after_tax_percent','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Product discount after tax percent:", "wp-userfollowup" ));
           
                        $argswoo[]     = array ('name' => 'woo-woocommerce_order_status_cancelled','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Order status cancelled:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_order_status_completed','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Order status completed:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_order_status_failed','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Order status failed:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_order_status_on_hold','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Order status on-hold:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_order_status_pending','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Order status pending:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_order_status_processing','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Order status processing:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_order_status_refunded','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Order status refunded:", "wp-userfollowup" ));
                        
                        
                        $argswoo[]     = array ('name' => 'woo-woocommerce_receipt_bacs','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Receipt Direct Bank Transfer:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_receipt_cheque','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Receipt Cheque:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_receipt_paypal','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Receipt paypal:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_receipt_other','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Receipt other payment method:", "wp-userfollowup" ));

                        $argswoo[]     = array ('name' => 'woo-woocommerce_thankyou_bacs','section'=>'wp_userfollowup_woocommerce_template','title' => __( "'Thankyou' Direct Bank Transfer:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_thankyou_cheque','section'=>'wp_userfollowup_woocommerce_template','title' => __( "'Thankyou' Cheque:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_thankyou_paypal','section'=>'wp_userfollowup_woocommerce_template','title' => __( "'Thankyou' PayPal:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_thankyou_other','section'=>'wp_userfollowup_woocommerce_template','title' => __( "'Thankyou' other payment method:", "wp-userfollowup" ));
                        
                       
                        
                        
//                        $argswoo[]     = array ('name' => 'woo-after_woocommerce_pay','section'=>'wp_userfollowup_woocommerce_template','title' => __( "after_woocommerce_pay:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-before_woocommerce_pay','section'=>'wp_userfollowup_woocommerce_template','title' => __( "before_woocommerce_pay:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_add_order_item_meta','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Add order item meta:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_add_to_cart','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Add to cart:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_cart_item_quantity_update','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After cart item quantity update:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_checkout_validation','section'=>'wp_userfollowup_woocommerce_template','title' => __( "After checkout validation:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_applied_coupon','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Applied coupon:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_calculate_totals','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before calculate totals:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_cart_item_quantity_zero','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before cart item quantity zero:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_checkout_process','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Before checkout process:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_calculate_totals','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Calculate totals:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_calculated_shipping','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Calculated shipping:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_cart_emptied','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Cart emptied:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_cart_loaded_from_session','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Cart loaded from session:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_cart_updated','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Cart updated:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_check_cart_items','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Check cart items:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_checkout_init','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Checkout init:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_checkout_order_processed','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Checkout order processed:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_checkout_process','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Checkout process:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_checkout_update_order_meta','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Checkout update order meta:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_checkout_update_user_meta','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Checkout update user meta:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_coupon_loaded','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Coupon loaded:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_customer_reset_password','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Customer reset password:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_email','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Email:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_email_footer','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_email_footer:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_email_header','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_email_header:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_integrations_init','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Integrations init:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_load_shipping_methods','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Load shipping methods:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_low_stock','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Low stock:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_new_customer_note','section'=>'wp_userfollowup_woocommerce_template','title' => __( "New customer note:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_new_order','section'=>'wp_userfollowup_woocommerce_template','title' => __( "New order:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_no_stock','section'=>'wp_userfollowup_woocommerce_template','title' => __( "No stock:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_order_status_changed','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Order status changed:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_payment_complete','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Payment complete:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_product_on_backorder','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Product on backorder:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_product_query','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Product query:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_product_set_stock_status','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Product set stock status:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_register_post','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Register_post:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_reset_password_notification','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Reset password notification:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_resume_order','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Resume order:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_shipping_init','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Shipping init:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_shipping_method_chosen','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Shipping method chosen:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_track_order','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Track order:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_view_order','section'=>'wp_userfollowup_woocommerce_template','title' => __( "View order:", "wp-userfollowup" ));
                        
                        //Other Hooks
//                        $argswoo[]     = array ('name' => 'woo-before_woocommerce_init','section'=>'wp_userfollowup_woocommerce_template','title' => __( "before_woocommerce_init:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-product_variation_linked','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Product variation linked:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_after_template_part','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_after_template_part:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_ajax_added_to_cart','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Ajax added to cart:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_api_wc_gateway_paypal','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_api_wc_gateway_paypal:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_delete_order_item','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_before_delete_order_item:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_before_template_part','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_before_template_part:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_cancelled_order','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Cancelled order:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_checkout_order_review','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Checkout order review:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_checkout_update_order_review','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Checkout update order review:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_create_product_variation','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Create product variation:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_created_customer','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Created customer:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_customer_change_password','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Customer change password:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_customer_save_address','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Customer save address:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_delete_order_item','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Delete order item:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_download_product','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Download product:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_init','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_init:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_loaded','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_loaded:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_new_order_item','section'=>'wp_userfollowup_woocommerce_template','title' => __( "New order item:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_ordered_again','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Ordered again:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_reduce_order_stock','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Reduce order stock:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_register_post_type','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_register_post_type:", "wp-userfollowup" ));
//                        $argswoo[]     = array ('name' => 'woo-woocommerce_register_taxonomy','section'=>'wp_userfollowup_woocommerce_template','title' => __( "woocommerce_register_taxonomy:", "wp-userfollowup" ));
                        $argswoo[]     = array ('name' => 'woo-woocommerce_restore_order_stock','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Restore order stock:", "wp-userfollowup" ));
                        
                        $argswoo[]     = array ('name' => 'woo-woocommerce_thankyou_ifmb','section'=>'wp_userfollowup_woocommerce_template','title' => __( "Multibanco Thankyou page:", "wp-userfollowup" ));
                        
                        
                          $idw=0;
                        foreach ($argswoo as $argwoo) {
                            $idw++;
                            add_settings_field(
					'wpcp-woo'.$idw,
					$argwoo["title"],
					array( $this, 'render_actions_woo' ),
					'WP UserFollowUp WooCommerce Settings',
					$argwoo["section"],
                                        $argwoo );
                        }

		}

		/**
		 * Build the menu and settings page callback
		 * 
		 */
		function admin_menu() {

			if ( !function_exists( 'current_user_can' ) || !current_user_can( 'manage_options' ) )
				return;

			if ( function_exists( 'add_options_page' ) )
				add_options_page( __( 'WP UserFollowUp Settings', 'wp-usrfollowup' ), __( 'WP UserFollowUp', 'wp-usrfollowup' ), 'manage_options', 'wp_userfollowup', array( $this, 'show_form' ) );
			
		}

		/**
		 * Show instructions
		 * 
		 */
		function overview() {

			printf( __( '<p>You need to have a valid UserFollowUp tracking ID. Example: <strong>21</strong> is the tracking ID for the code <code>http://app.userfollowup.com/21.js</code>', 'wp-userfollowup' ), 'http://app.UserFollowUp.com/' );

			_e( '<p>Please <strong>enter only the ID</strong> on the field below.</p>', 'wp-userfollowup' ) . '</p>';

		}

		/**
		 * Show instructions
		 *
		 */
		function events_overview() {
			printf( __( '<p>Select the event and the action you want to trigger.</p>', 'wp-userfollowup' ), 'http://app.UserFollowUp.com/' );
		}
		
		function render_wordpress_event()
		{
			$wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );

			$items = array("User Login", "User Logout");
			
			echo "<select id='wp_events' name='wp_userfollowup_vars[wpcp-event]'>";
		
			foreach($items as $item) {
				$selected = ($wp_userfollowup_vars['wpcp-event']==$item) ? 'selected="selected"' : '';
				echo "<option value='$item' $selected>$item</option>";
			}
			echo "</select>";                          
		}
		
		function render_action()
		{
			$wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );
			?>
			<input type="text" name="wp_userfollowup_vars[wpcp-action]" value="<?php echo $wp_userfollowup_vars['wpcp-action']; ?>" ></input>
			<?php			
		}
                function render_actions(array $args)
		{                    
			$wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );
			?>                        
                        <input type="checkbox" name="wp_userfollowup_vars[<?php echo $args["name"]?>]" type="checkbox" <?php if( $wp_userfollowup_vars[$args["name"]] ) echo 'checked="checked"'; ?> ></input>
			<?php			
		}
              
                
                function render_actions_woo(array $args)
		{                    
			$wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			?>                        
                                                <input type="checkbox" name="wp_userfollowup_woocommerce_vars[<?php echo $args["name"]?>]" type="checkbox" <?php if((isset($wp_userfollowup_woocommerce_vars[$args["name"]])&&$wp_userfollowup_woocommerce_vars[$args["name"]])) echo 'checked="checked"'; ?> ></input>

			<?php			
		}
              
		
            
		/**
		 * Render options field
		 * 
		 */ 
		function render_field() {
			$wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );

		 ?>
                    <input id="wpcp-apikey" name="wp_userfollowup_vars[apikey]" class="regular-text" value="<?php echo $wp_userfollowup_vars['apikey']; ?>" />
                    <?php
		}
		
		/**
		 * Validate user options
		 * 
		 */ 
		function validate_form( $input ) {

			//print_r($input);
			
			$wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );

			if ( isset( $input['apikey'] ) ) {
				// Strip all HTML and PHP tags and properly handle quoted strings
				$wp_userfollowup_vars['apikey'] = strip_tags( stripslashes( $input['apikey'] ) );
			}
//			if ( isset( $input['wpcp-event'] ) && isset( $input['wpcp-action'] ) ) {
//				$wp_userfollowup_vars['wpcp-event'] = strip_tags( stripslashes( $input['wpcp-event'] ) );
//				$wp_userfollowup_vars['wpcp-action'] = strip_tags( stripslashes( $input['wpcp-action'] ) );
                                $wp_userfollowup_vars['wpcp-auth'] = strip_tags( stripslashes( $input['wpcp-auth'] ) );
                                $wp_userfollowup_vars['wpcp-login'] = strip_tags( stripslashes( $input['wpcp-login'] ) );
                                $wp_userfollowup_vars['wpcp-signup'] = strip_tags( stripslashes( $input['wpcp-signup'] ) );
                                $wp_userfollowup_vars['wpcp-registration'] = strip_tags( stripslashes( $input['wpcp-registration'] ) );
                                $wp_userfollowup_vars['wpcp-postPage'] = strip_tags( stripslashes( $input['wpcp-postPage'] ) );
                                $wp_userfollowup_vars['wpcp-linksPostPage'] = strip_tags( stripslashes( $input['wpcp-linksPostPage'] ) );
                                $wp_userfollowup_vars['wpcp-linksComments'] = strip_tags( stripslashes( $input['wpcp-linksComments'] ) );
                                $wp_userfollowup_vars['wpcp-social'] = strip_tags( stripslashes( $input['wpcp-social'] ) );
                                $wp_userfollowup_vars['wpcp-search'] = strip_tags( stripslashes( $input['wpcp-search'] ) );
                                $wp_userfollowup_vars['wpcp-comment'] = strip_tags( stripslashes( $input['wpcp-comment'] ) );
                                $wp_userfollowup_vars['wpcp-idByComment'] = strip_tags( stripslashes( $input['wpcp-idByComment'] ) );
//			}
			
			
			return $wp_userfollowup_vars;
		}

		/**
		 * Render options page
		 * 
		 */ 
		function show_form() {
			$wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );

?>
                                <div class="wrap">
                                        <?php screen_icon( "options-general" ); ?>
                                        <h2><?php _e( 'WP UserFollowUp Settings', 'wp-userfollowup' ); ?></h2>
                                       <?php
                                       if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
                                         $tabs = array( 'homepage' => 'WordPress Events', 'woocommerce' => 'WooCommerce Options' );
                                            echo '<h2 class="nav-tab-wrapper">';
                                            $current= $_GET['tab'];
                                            if (!$current){
                                                $current='homepage';
                                            }
                                            foreach( $tabs as $tab => $name ){
                                                $class = ( $tab == $current ) ? ' nav-tab-active' : '';
                                                echo "<a class='nav-tab$class' href='?page=wp_userfollowup&tab=$tab'>$name</a>";

                                            }
                                            echo '</h2>';
                                       } else {
                                           
                                       }
                                       
                                            ?>
                                        <?php if((isset($current)&&($current=='woocommerce'))&&(in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ))))){ ?>
                                        <form action="options.php" method="post">
                                               
                                                <?php settings_fields( 'wp_userfollowup_woocommerce_vars_group' ); ?>
                                                <?php do_settings_sections( 'WP UserFollowUp WooCommerce Settings' ); ?>
                                                 <br/><br/>
                                                 <a href="#" onClick="checkAll(true);">Check All</a> / <a href="#" onClick="checkAll(false);">Uncheck All</a>  
                                                  

                                                <p class="submit">
                                                        <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wp-userfollowup' ); ?>" />
                                                        <br/><br/>
                                                        Set up your rules: <a href="http://app.userfollowup.com/rules.php">http://app.userfollowup.com/rules.php</a>
                                                        <script>
                                                                function checkAll($a) {
                                                                    var aa= document.getElementsByTagName("input");
                                                                    for (var i =0; i < aa.length; i++){
                                                                        if (aa[i].type == 'checkbox')
                                                                            aa[i].checked = $a;
                                                                    }
                                                                    return false;
                                                                };
                                                        </script>
                                                </p>
                                        </form>
                                        <?php } else { ?>
                                        <form action="options.php" method="post">
                                                <?php settings_fields( 'wp_userfollowup_vars_group' ); ?>
                                                <?php do_settings_sections( 'WP UserFollowUp Settings' ); ?>
                                            
                                                <br/><br/>
                                                 <a href="#" onClick="checkAll(true);">Check All</a> / <a href="#" onClick="checkAll(false);">Uncheck All</a>  
                                                 <br/><br/>
                                                <p class="submit">
                                                        <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wp-userfollowup' ); ?>" />
                                                        <br/><br/>
                                                        Set up your rules: <a href="http://app.userfollowup.com/rules.php">http://app.userfollowup.com/rules.php</a>
                                                </p>
                                        </form>
                                        <script>
                                                                function checkAll($a) {
                                                                    var aa= document.getElementsByTagName("input");
                                                                    for (var i =0; i < aa.length; i++){
                                                                        if (aa[i].type == 'checkbox')
                                                                            aa[i].checked = $a;
                                                                    }
                                                                    return false;
                                                                };
                                                        </script>
                                        <?php } ?>
                                        
                                </div>
                        <?php
		}
                

		/**
		 * Add iframe code to the site's footer
		 * 
		 */ 
		function add_code() {
			echo "\n<script src='http://app.userfollowup.com/".sanitize_text_field( $this->api_key ).".js'></script>";
//                        echo "\n<script src='http://localhost/userfollowup-backoffice/followup.php?user=".sanitize_text_field( $this->api_key )."'></script>";
                         $wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );
			if( $wp_userfollowup_vars["wpcp-auth"] ) {
                            if (is_user_logged_in()){
                                 global $current_user;
                                 get_currentuserinfo();
                                 $phone = get_user_meta( $current_user->ID, 'billing_phone', TRUE ); 
                
                                echo "\n<script>userLoggedIn('". $current_user->user_email ."','". $current_user->user_login .",".$phone."');</script>";
                            } else {
                            echo "\n<script>userLoggedIn('none','unregistered');</script>";
                            }
                       }
                       else {
                            echo "\n<script>userLoggedIn('none','unregistered');</script>";
                        }
		}
                

			
                 /**
                  *Creates a "View post" event for single post views when the_content is called.
		 */
		function postPageView($text) {
                        $wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );
			$is_post = is_single();
                        
			if( ( $is_post || is_page() ) && $wp_userfollowup_vars["wpcp-postPage"] ) {
				global $post;
				$is_post = is_single();

				if( $is_post ) {
					//Post
					$info[] = array(
						'ID' => $post->ID,
						'Title' => get_the_title()
					);
//                                        echo "\n<script>_fu('record','Post viewed - ".$info[0]['ID']."',".json_encode($info).");</script>";
                                        triger_action("Post viewed - ".$info[0]['ID']);
				} else {
					//Page
					$info[] = array(
						'ID' => $post->ID,
						'Title' => get_the_title()
					);
//                                        echo  "\n<script>_fu('record','Page viewed - ".$info[0]['ID']."',".json_encode($info).");</script>";
                                        triger_action("Page viewed - ".$info[0]['ID']);
				}				
			}
                        return $text;
		}


                
                
                function linksPostPage($text){
                    
                    $link_regex = '/<a (.*?)href="(.*?)"(.*?)>(.*?)<\/a>/i';
                    
                    $wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );
			if( $wp_userfollowup_vars["wpcp-linksPostPage"] ) {
                      	$text = preg_replace_callback($link_regex, array( $this, 'parse_content_link' ), $text );
                             
                        }
                  return $text;
                }
                
                 function linksComments($text){
                    
                    $link_regex = '/<a (.*?)href="(.*?)"(.*?)>(.*?)<\/a>/i';
                    
                    $wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );
			if( $wp_userfollowup_vars["wpcp-linksComments"] ) {
                      	$text = preg_replace_callback($link_regex, array( $this, 'parse_comment_link' ), $text );
                             
                        }
                  return $text;
                } 
                
                
                function parse_content_link( $matches ) {
			return $this->parse_link( get_the_title(), 'Article', $matches );
		}
                
                function parse_comment_link( $matches ) {
			return $this->parse_link( get_the_title(), 'Comment', $matches );
		}
                
                function get_domain( $uri ) {
			$parsed_uri = parse_url( $uri );
			if( isset( $parsed_uri['host'] ) )
				$host = $parsed_uri['host'];
			else
				$host = '';

			preg_match( '/[^\.\/]+\.[^\.\/]+$/', $host, $domain );

			if( !count( $domain ) )
				$domain = array( '' );

			return array( 'domain' => $domain[0], 'host' => $host );
		}
                
                
		function parse_link( $page_title, $type, $link_part ) {
			static $id_attr_regex = '/id\s*=\s*[\'"](.+?)[\'"]/i';
                        
			$target = $this->get_domain( $link_part[2] );
			$id = '';

			// Search the link's id
			preg_match( $id_attr_regex, $link_part[1], $id_attr );
			if( !$id_attr ) {
				preg_match( $id_attr_regex, $link_part[3], $id_attr );
				//if no link id - assign unique
				if( !$id_attr ) {
					$id = uniqid( 'link_' );
				} else {
					$id = $id_attr[1];
				}
			} else {
				$id = $id_attr[1];
			}

			$params = array(
				'linkType' => ( $target['domain'] !== $origin['domain'] ) ? 'Outbound' : 'Inner',
				'id' => $id,
				'Title' => $link_part[4],
				'PostPage' => $page_title
				
			);
                        $action="_fu(\"record\",\"".$type." link clicked\",'".json_encode($params)."');";
			$link='<a onclick="link_'.$id.'_clicked()" ' . $link_part[1] .'href="' . $link_part[2] . '"' . $link_part[3]
			        . ( !$id_attr ? ' id="' . $id .'"' : '' ) . ">"
			        . $link_part[4]
			        . '</a>'
                                 . '<script>function link_'.$id.'_clicked(){'.$action.'}</script>';
			        ;
                        
                        return $link;
		}
                
                
                function track_social() {
                             echo  "\n<script type='text/javascript'>
                                 function socialtrack(){
                                                if(window.twttr) {
                                                    window.twttr.events.bind('tweet', function (event) {
                                                      _fu('record','Tweet');
                                                    });

                                                    window.twttr.events.bind('follow', function (event) {
                                                          _fu('record','Twitter Follow', { 'Username': 'event.data.screen_name' });
                                                    });
                                                    
                                                  }

                                               if(window.FB) {
                                                    window.FB.Event.subscribe('edge.create', function (url) {
                                                       _fu('record','Like',{ 'Page': '".get_the_title()."' });
                                                    });

                                                    window.FB.Event.subscribe('edge.remove', function (url) {
                                                      _fu('record','Unlike',{ 'Page': '".get_the_title()."' });
                                                    });

                                                    window.FB.Event.subscribe('auth.login', function (url) {
                                                        _fu('record','Facebook Connect');
                                                    });

                                                    window.FB.Event.subscribe('auth.logout', function (url) {
                                                      _fu('record','Facebook Logout');
                                                    });
                                                      
                                                  }
                                         };
                                     socialtrack();
                                    </script>";
                }
                
                function track_register_view() {
                        $wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );
			if( $wp_userfollowup_vars["wpcp-signup"] ) {
                      
                             echo  "\n<script>
                                        function signup(){

                                               if(document.getElementById('registerform')) {
                                                   _fu('record','Viewed signup page');
                                               }
                                         };
                                         signup();
                                    </script>";
                        }
                }
              
                
                function track_search() {
                        $wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );
			if( $wp_userfollowup_vars["wpcp-search"] ) {
                      
                             echo  "\n<script>
                                        function getQueryVariable(variable) {
                                            var query = window.location.search.substring(1);
                                            var vars = query.split('&');
                                            for (var i = 0; i < vars.length; i++) {
                                                var pair = vars[i].split('=');
                                                if (decodeURIComponent(pair[0]) == variable) {
                                                    _fu('record','Site search','{\"search\":\"'+decodeURIComponent(pair[1])+'\"}');
                                                }
                                            }
                                        }

                                       getQueryVariable('s'); 
                                    </script>";
                        }
                }
                
                function track_login() {
                    $wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );
                    if( $wp_userfollowup_vars["wpcp-login"] ) {                      
                        triger_action("User loggedin");
                    }
                }
                
                function track_register() {
                    $wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );
                    if( $wp_userfollowup_vars["wpcp-registration"] ) {                      
                        triger_action("User registered");
                    }
                }
 
                
                function track_comments(){
                    $wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );
                    if( ( is_single() || is_page() ) && $wp_userfollowup_vars["wpcp-comment"] ) {
                        echo  "\n<script>
                                        function comment_track(){
                                                var commented = function() {";
                                                if (is_user_logged_in()){
                                                         global $current_user;
                                                         get_currentuserinfo();
                                                        echo "_fu('record', 'Commented', '{\"name\":\"". $current_user->user_login . "\",\"email\":\"". $current_user->user_email ."\",\"comment\":\"'+document.getElementById('comment').value +'\"}');";
                                                    } else {
                                                    echo "_fu('record', 'Commented', '{\"name\":\"'+ document.getElementById('author').value+'\",\"email\":\"'+document.getElementById('email').value+'\",\"comment\":\"'+document.getElementById('comment').value +'\"}');";
                                                    }
                        echo"                                            },
                                                    el = document.getElementById('submit');

                                                if(el.addEventListener) {
                                                        el.addEventListener('mousedown', commented, false);
                                                } else if(el.attachEvent)  {
                                                        el.attachEvent('onmousedown', commented);
                                                }
                                         };
                                         comment_track();
                                    </script>";
                    
                    }
                          
                }


                //wooCommerce functions
                
                function wooProductView() {
                    global $post;
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_single_product"] ) {  
                            //set additional parameters  
                            $params['title']=get_the_title();
                            $params['product_id']=$post->ID;
                        
                            $parameters=json_encode($params);
                            triger_action("Product viewed", $parameters);
                        }
                }
                
                function wooAdd_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
                     //set additional parameters  
                        $params['product_id']=$product_id;
                        $params['cart_item_key']=$cart_item_key;
                        $params['quantity']=$quantity;
                        $params['variation_id']=$variation_id;
                        $params['variation']=$variation;
                        $params['cart_item_data']=$cart_item_data;
                        
                        $parameters=json_encode($params);
                              
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_add_to_cart"] ) {   
                            $product=''; //Currently added product
                            triger_action("Something was added to the cart",$parameters);
                        }
                }
                
                
                
                function woo_woocommerce_after_cart() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_cart"] ) {                       
                            triger_action("woocommerce_after_cart");
                        }
                }
                
                function woo_woocommerce_after_cart_contents() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_cart_contents"] ) {                       
                            triger_action("woocommerce_after_cart_contents");
                        }
                }
                
                function woo_woocommerce_after_cart_table() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_cart_table"] ) {                       
                            triger_action("woocommerce_after_cart_table");
                        }
                }
                
                function woo_woocommerce_after_cart_totals() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_cart_totals"] ) {                       
                            triger_action("woocommerce_after_cart_totals");
                        }
                }
                
                function woo_woocommerce_after_checkout_billing_form($checkout) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_checkout_billing_form"] ) {                      
                            $params['checkout']=$checkout;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_after_checkout_billing_form",$parameters);
                        }
                }
                
                function woo_woocommerce_after_checkout_form($checkout) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_checkout_form"] ) {                      
                            $params['checkout']=$checkout;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_after_checkout_form",$parameters);
                        }
                }
                
                function woo_woocommerce_after_checkout_registration_form($checkout) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_checkout_registration_form"] ) {                      
                            $params['checkout']=$checkout;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_after_checkout_registration_form",$parameters);
                        }
                }
                
                function woo_woocommerce_after_checkout_shipping_form($checkout) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_checkout_shipping_form"] ) {                      
                            $params['checkout']=$checkout;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_after_checkout_shipping_form",$parameters);
                        }
                }
                
                function woo_woocommerce_after_customer_login_form() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_customer_login_form"] ) {                       
                            triger_action("woocommerce_after_customer_login_form");
                        }
                }
                
                function woo_woocommerce_after_main_content() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_main_content"] ) {                       
                            triger_action("woocommerce_after_main_content");
                        }
                }
                
                function woo_woocommerce_after_mini_cart() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_mini_cart"] ) {                       
                            triger_action("woocommerce_after_mini_cart");
                        }
                }
                
                function woo_woocommerce_after_my_account() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_my_account"] ) {                       
                            triger_action("woocommerce_after_my_account");
                        }
                }
                
                function woo_woocommerce_after_order_notes($checkout) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_order_notes"] ) {                      
                            $params['checkout']=$checkout;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_after_order_notes",$parameters);
                        }
                }
                
                function woo_woocommerce_after_shipping_calculator() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_shipping_calculator"] ) {                       
                            triger_action("woocommerce_after_shipping_calculator");
                        }
                }
                
                function woo_woocommerce_after_shop_loop() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_shop_loop"] ) {                       
                            triger_action("woocommerce_after_shop_loop");
                        }
                }
                
                function woo_woocommerce_after_shop_loop_item() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_shop_loop_item"] ) {                       
                            triger_action("woocommerce_after_shop_loop_item");
                        }
                }
                
                function woo_woocommerce_after_shop_loop_item_title() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_shop_loop_item_title"] ) {                       
                            triger_action("woocommerce_after_shop_loop_item_title");
                        }
                }
                
                function woo_woocommerce_after_single_product() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_single_product"] ) {                       
                            triger_action("woocommerce_after_single_product");
                        }
                }
                
                function woo_woocommerce_after_single_product_summary() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_single_product_summary"] ) {                       
                            triger_action("woocommerce_after_single_product_summary");
                        }
                }
                
                function woo_woocommerce_after_subcategory($category) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_subcategory"] ) {                      
                            $params['category']=$category;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_after_subcategory",$parameters);
                        }
                }
                
                function woo_woocommerce_after_subcategory_title($category) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_subcategory_title"] ) {                      
                            $params['category']=$category;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_after_subcategory_title",$parameters);
                        }
                }
                
                function woo_woocommerce_archive_description() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_archive_description"] ) {                       
                            triger_action("woocommerce_archive_description");
                        }
                }
                
                function woo_woocommerce_available_download_end($download) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_available_download_end"] ) {                      
                            $params['download']=$download;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_available_download_end",$parameters);
                        }
                }
                
                function woo_woocommerce_available_download_start($download) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_available_download_start"] ) {                      
                            $params['download']=$download;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_available_download_start",$parameters);
                        }
                }
                
                function woo_woocommerce_before_cart() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_cart"] ) {                       
                            triger_action("woocommerce_before_cart");
                        }
                }
                
                function woo_woocommerce_before_cart_contents() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_cart_contents"] ) {                       
                            triger_action("woocommerce_before_cart_contents");
                        }
                }
                
                function woo_woocommerce_before_cart_table() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_cart_table"] ) {                       
                            triger_action("woocommerce_before_cart_table");
                        }
                }
                
                function woo_woocommerce_before_cart_totals() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_cart_totals"] ) {                       
                            triger_action("woocommerce_before_cart_totals");
                        }
                }
                
                function woo_woocommerce_before_checkout_billing_form($checkout) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_checkout_billing_form"] ) {                      
                            $params['checkout']=$checkout;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_before_checkout_billing_form",$parameters);
                        }
                }
                
                function woo_woocommerce_before_checkout_registration_form($checkout) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_checkout_registration_form"] ) {                      
                            $params['checkout']=$checkout;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_before_checkout_registration_form",$parameters);
                        }
                }
                
                function woo_woocommerce_before_checkout_shipping_form($checkout) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_checkout_shipping_form"] ) {                      
                            $params['checkout']=$checkout;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_before_checkout_shipping_form",$parameters);
                        }
                }
                
                function woo_woocommerce_before_customer_login_form() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_customer_login_form"] ) {                       
                            triger_action("woocommerce_before_customer_login_form");
                        }
                }
                
                function woo_woocommerce_before_main_content() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_main_content"] ) {                       
                            triger_action("woocommerce_before_main_content");
                        }
                }
                
                function woo_woocommerce_before_mini_cart() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_mini_cart"] ) {                       
                            triger_action("woocommerce_before_mini_cart");
                        }
                }
                
                function woo_woocommerce_before_my_account() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_my_account"] ) {                       
                            triger_action("woocommerce_before_my_account");
                        }
                }
                
                function woo_woocommerce_before_order_notes($checkout) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_order_notes"] ) {                      
                            $params['checkout']=$checkout;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_before_order_notes",$parameters);
                        }
                }
                
                function woo_woocommerce_before_shipping_calculator() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_shipping_calculator"] ) {                       
                            triger_action("woocommerce_before_shipping_calculator");
                        }
                }
                
                function woo_woocommerce_before_shop_loop() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_shop_loop"] ) {                       
                            triger_action("woocommerce_before_shop_loop");
                        }
                }
                
                function woo_woocommerce_before_shop_loop_item() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_shop_loop_item"] ) {                       
                            triger_action("woocommerce_before_shop_loop_item");
                        }
                }
                
                function woo_woocommerce_before_shop_loop_item_title() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_shop_loop_item_title"] ) {                       
                            triger_action("woocommerce_before_shop_loop_item_title");
                        }
                }
                
                function woo_woocommerce_before_single_product() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_single_product"] ) {                       
                            triger_action("woocommerce_before_single_product");
                        }
                }
                
                function woo_woocommerce_before_single_product_summary() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_single_product_summary"] ) {                       
                            triger_action("woocommerce_before_single_product_summary");
                        }
                }
                
                function woo_woocommerce_before_subcategory($category) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_subcategory"] ) {                      
                            $params['category']=$category;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_before_subcategory",$parameters);
                        }
                }
                
                function woo_woocommerce_before_subcategory_title($category) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_subcategory_title"] ) {                      
                            $params['category']=$category;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_before_subcategory_title",$parameters);
                        }
                }
                
                function woo_woocommerce_cart_collaterals() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_cart_collaterals"] ) {                       
                            triger_action("woocommerce_cart_collaterals");
                        }
                }
                
                function woo_woocommerce_cart_contents() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_cart_contents"] ) {                       
                            triger_action("woocommerce_cart_contents");
                        }
                }
                
                function woo_woocommerce_cart_coupon() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_cart_coupon"] ) {                       
                            triger_action("woocommerce_cart_coupon");
                        }
                }
                
                function woo_woocommerce_cart_has_errors() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_cart_has_errors"] ) {                       
                            triger_action("woocommerce_cart_has_errors");
                        }
                }
                
                function woo_woocommerce_cart_is_empty() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_cart_is_empty"] ) {                       
                            triger_action("woocommerce_cart_is_empty");
                        }
                }
                
                function woo_woocommerce_cart_totals_after_order_total() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_cart_totals_after_order_total"] ) {                       
                            triger_action("woocommerce_cart_totals_after_order_total");
                        }
                }
                
                function woo_woocommerce_cart_totals_after_shipping() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_cart_totals_after_shipping"] ) {                       
                            triger_action("woocommerce_cart_totals_after_shipping");
                        }
                }
                
                function woo_woocommerce_cart_totals_before_order_total() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_cart_totals_before_order_total"] ) {                       
                            triger_action("woocommerce_cart_totals_before_order_total");
                        }
                }
                
                function woo_woocommerce_cart_totals_before_shipping() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_cart_totals_before_shipping"] ) {                       
                            triger_action("woocommerce_cart_totals_before_shipping");
                        }
                }
                
                function woo_woocommerce_checkout_after_customer_details() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_checkout_after_customer_details"] ) {                       
                            triger_action("woocommerce_checkout_after_customer_details");
                        }
                }
                
                function woo_woocommerce_checkout_before_customer_details() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_checkout_before_customer_details"] ) {                       
                            triger_action("woocommerce_checkout_before_customer_details");
                        }
                }
                
                function woo_woocommerce_checkout_billing() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_checkout_billing"] ) {                       
                            triger_action("woocommerce_checkout_billing");
                        }
                }
                
                function woo_woocommerce_checkout_order_review() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_checkout_order_review"] ) {                       
                            triger_action("Checkout order review");
                        }
                }
                
                function woo_woocommerce_checkout_shipping() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_checkout_shipping"] ) {                       
                            triger_action("woocommerce_checkout_shipping");
                        }
                }
                
                function woo_woocommerce_email_after_order_table($order) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_email_after_order_table"] ) {                      
                            $params['order']=$order;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_email_after_order_table",$parameters);
                        }
                }
                
                function woo_woocommerce_email_before_order_table($order) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_email_before_order_table"] ) {                      
                            $params['order']=$order;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_email_before_order_table",$parameters);
                        }
                }
                
                function woo_woocommerce_email_footer() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_email_footer"] ) {                       
                            triger_action("woocommerce_email_footer");
                        }
                }
                
                function woo_woocommerce_email_header($email_heading) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_email_header"] ) {                      
                            $params['email_heading']=$email_heading;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_email_header",$parameters);
                        }
                }
                
                function woo_woocommerce_email_order_meta($order) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_email_order_meta"] ) {                      
                            $params['order']=$order;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_email_order_meta",$parameters);
                        }
                }
                
                function woo_woocommerce_order_details_after_order_table($order) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_order_details_after_order_table"] ) {                      
                            $params['order']=$order;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_order_details_after_order_table",$parameters);
                        }
                }
                
                function woo_woocommerce_order_items_table($order) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_order_items_table"] ) {                      
                            $params['order']=$order;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_order_items_table",$parameters);
                        }
                }
                
                function woo_woocommerce_proceed_to_checkout() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_proceed_to_checkout"] ) {                       
                            triger_action("woocommerce_proceed_to_checkout");
                        }
                }
                
                function woo_woocommerce_product_meta_end() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_product_meta_end"] ) {                       
                            triger_action("woocommerce_product_meta_end");
                        }
                }
                
                function woo_woocommerce_product_meta_start() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_product_meta_start"] ) {                       
                            triger_action("woocommerce_product_meta_start");
                        }
                }
                
                function woo_woocommerce_product_thumbnails() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_product_thumbnails"] ) {                       
                            triger_action("woocommerce_product_thumbnails");
                        }
                }
                
                function woo_woocommerce_review_order_after_cart_contents() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_review_order_after_cart_contents"] ) {                       
                            triger_action("woocommerce_review_order_after_cart_contents");
                        }
                }
                
                function woo_woocommerce_review_order_after_order_total() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_review_order_after_order_total"] ) {                       
                            triger_action("woocommerce_review_order_after_order_total");
                        }
                }
                
                function woo_woocommerce_review_order_after_shipping() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_review_order_after_shipping"] ) {                       
                            triger_action("woocommerce_review_order_after_shipping");
                        }
                }
                
                function woo_woocommerce_review_order_after_submit() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_review_order_after_submit"] ) {                       
                            triger_action("woocommerce_review_order_after_submit");
                        }
                }
                
                function woo_woocommerce_review_order_before_cart_contents() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_review_order_before_cart_contents"] ) {                       
                            triger_action("woocommerce_review_order_before_cart_contents");
                        }
                }
                
                function woo_woocommerce_review_order_before_order_total() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_review_order_before_order_total"] ) {                       
                            triger_action("woocommerce_review_order_before_order_total");
                        }
                }
                
                function woo_woocommerce_review_order_before_shipping() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_review_order_before_shipping"] ) {                       
                            triger_action("woocommerce_review_order_before_shipping");
                        }
                }
                
                function woo_woocommerce_review_order_before_submit() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_review_order_before_submit"] ) {                       
                            triger_action("woocommerce_review_order_before_submit");
                        }
                }
                
                function woo_woocommerce_share() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_share"] ) {                       
                            triger_action("woocommerce_share");
                        }
                }
                
                function woo_woocommerce_sidebar() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_sidebar"] ) {                       
                            triger_action("woocommerce_sidebar");
                        }
                }
                
                function woo_woocommerce_single_product_summary() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_single_product_summary"] ) {                       
                            triger_action("Single product summary");
                        }
                }
                
                function woo_woocommerce_thankyou($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_thankyou"] ) {                      
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("Payment complete",$parameters);
                        }
                }
                
                function woo_woocommerce_view_order($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_view_order"] ) {                      
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("View order",$parameters);
                        }
                }
                
                function woo_woocommerce_widget_shopping_cart_before_buttons() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_widget_shopping_cart_before_buttons"] ) {                       
                            triger_action("woocommerce_widget_shopping_cart_before_buttons");
                        }
                }
                
                //Class actions
                function woo_after_woocommerce_pay() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-after_woocommerce_pay"] ) {                       
                            triger_action("after_woocommerce_pay");
                        }
                }
                
                
                function woo_before_woocommerce_pay() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-before_woocommerce_pay"] ) {                       
                            triger_action("before_woocommerce_pay");
                        }
                }
                
                function woo_woocommerce_add_order_item_meta($item_id,$values) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_add_order_item_meta"] ) {                      
                            $params['item_id']=$item_id;
                            $params['values']=$values;
                            $parameters=json_encode($params);
                            triger_action("Add order item meta",$parameters);
                        }
                }
                
                function woo_woocommerce_after_cart_item_quantity_update($cart_item_key,$quantity) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_cart_item_quantity_update"] ) {                      
                            $params['cart_item_key']=$cart_item_key;
                            $params['quantity']=$quantity;
                            $parameters=json_encode($params);
                            triger_action("After cart item quantity update",$parameters);
                        }
                }
                
                function woo_woocommerce_after_checkout_validation($posted) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_checkout_validation"] ) {                      
                            $params['posted']=$posted;
                            $parameters=json_encode($params);
                            triger_action("After checkout validation",$parameters);
                        }
                }
                
                function woo_woocommerce_applied_coupon($coupon_code) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_applied_coupon"] ) {                      
                            $params['coupon_code']=$coupon_code;
                            $parameters=json_encode($params);
                            triger_action("Applied coupon",$parameters);
                        }
                }
                
                function woo_woocommerce_before_calculate_totals($thiss) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_calculate_totals"] ) {                      
                            $params['thiss']=$thiss;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_before_calculate_totals",$parameters);
                        }
                }
                function woo_woocommerce_before_cart_item_quantity_zero($cart_item_key) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_cart_item_quantity_zero"] ) {                      
                            $params['cart_item_key']=$cart_item_key;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_before_cart_item_quantity_zero",$parameters);
                        }
                }
                
                function woo_woocommerce_before_checkout_process() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_checkout_process"] ) {                       
                            triger_action("woocommerce_before_checkout_process");
                        }
                }
                
                function woo_woocommerce_calculate_totals($thiss) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_calculate_totals"] ) {                      
                            $params['thiss']=$thiss;
                            $parameters=json_encode($params);
                            triger_action("Calculate totals",$parameters);
                        }
                }
                
                function woo_woocommerce_calculated_shipping() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_calculated_shipping"] ) {                       
                            triger_action("Calculated shipping");
                        }
                }
                
                function woo_woocommerce_cart_emptied() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_cart_emptied"] ) {                       
                            triger_action("Cart emptied");
                        }
                }
                
                function woo_woocommerce_cart_loaded_from_session($thiss) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_cart_loaded_from_session"] ) {                      
                            $params['thiss']=$thiss;
                            $parameters=json_encode($params);
                            triger_action("Cart loaded from session",$parameters);
                        }
                }
                
                function woo_woocommerce_cart_updated() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_cart_updated"] ) {                       
                            triger_action("Cart updated");
                        }
                }
                
                function woo_woocommerce_check_cart_items() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_check_cart_items"] ) {                       
                            triger_action("Check cart items");
                        }
                }
                
                function woo_woocommerce_checkout_init($thiss) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_checkout_init"] ) {                      
                            $params['thiss']=$thiss;
                            $parameters=json_encode($params);
                            triger_action("Checkout init",$parameters);
                        }
                }
                
                function woo_woocommerce_checkout_order_processed($order_id,$this_posted) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_checkout_order_processed"] ) {                      
                            $params['order_id']=$order_id;
                            $params['this_posted']=$this_posted;
                            $parameters=json_encode($params);
                            triger_action("Checkout order processed",$parameters);
                        }
                }
                
                function woo_woocommerce_checkout_process() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_checkout_process"] ) {                       
                            triger_action("Checkout process");
                        }
                }
                
                function woo_woocommerce_checkout_update_order_meta($order_id,$this_posted) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_checkout_update_order_meta"] ) {                      
                            $params['order_id']=$order_id;
                            $params['this_posted']=$this_posted;
                            $parameters=json_encode($params);
                            triger_action("Checkout update order meta",$parameters);
                        }
                }
                
                function woo_woocommerce_checkout_update_user_meta($customer_id,$posted) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_checkout_update_user_meta"] ) {                      
                            $params['customer_id']=$customer_id;
                            $params['posted']=$posted;
                            $parameters=json_encode($params);
                            triger_action("Checkout update user meta",$parameters);
                        }
                }
                
                function woo_woocommerce_coupon_loaded($thiss) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_coupon_loaded"] ) {                      
                            $params['thiss']=$thiss;
                            $parameters=json_encode($params);
                            triger_action("Coupon loaded",$parameters);
                        }
                }
                
                function woo_woocommerce_created_customer($customer_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_created_customer"] ) {                      
                            $params['customer_id']=$customer_id;
                            $parameters=json_encode($params);
                            triger_action("Created customer",$parameters);
                        }
                }
                
                function woo_woocommerce_customer_reset_password($user) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_customer_reset_password"] ) {                      
                            $params['user']=$user;
                            $parameters=json_encode($params);
                            triger_action("Customer reset password",$parameters);
                        }
                }
                
                function woo_woocommerce_email($thiss) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_email"] ) {                      
                            $params['thiss']=$thiss;
                            $parameters=json_encode($params);
                            triger_action("WooCommerce email",$parameters);
                        }
                }
                
                
               
                
                function woo_woocommerce_integrations_init() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_integrations_init"] ) {                       
                            triger_action("woocommerce_integrations_init");
                        }
                }
                
                function woo_woocommerce_load_shipping_methods($package) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_load_shipping_methods"] ) {                      
                            $params['package']=$package;
                            $parameters=json_encode($params);
                            triger_action("Load shipping methods",$parameters);
                        }
                }
                
                function woo_woocommerce_low_stock($product) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_low_stock"] ) {                      
                            $params['product']=$product;
                            $parameters=json_encode($params);
                            triger_action("Low stock",$parameters);
                        }
                }
                
                function woo_woocommerce_new_customer_note($note) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_new_customer_note"] ) {                      
                            $params['note']=$note;
                            $parameters=json_encode($params);
                            triger_action("New customer note",$parameters);
                        }
                }
                
                function woo_woocommerce_new_order($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_new_order"] ) {                      
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("New order",$parameters);
                        }
                }
                
                function woo_woocommerce_no_stock($product) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_no_stock"] ) {                      
                            $params['product']=$product;
                            $parameters=json_encode($params);
                            triger_action("No stock",$parameters);
                        }
                }
                
                function woo_woocommerce_order_status_changed($id,$status,$newstatus) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_order_status_changed"] ) {
                            //get order owner data and trigger action as this user. Else it will be always trigerred as admin
                            $order = new WC_Order($id);
                            $user_info = get_userdata($order->user_id);
                            $username=$user_info->user_login;
                            $useremail=$user_info->user_email;
                            $params['id']=$id;
                            $params['username']=$username;
                            $params['status']=$status;
                            $params['newstatus']=$newstatus;
                            $parameters=json_encode($params);

                            triger_action("Order status changed",$parameters,$username,$useremail);
                        }
                }
                
                function woo_woocommerce_payment_complete($id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_payment_complete"] ) {                      
                            $params['id']=$id;
                            $parameters=json_encode($params);
                            triger_action("Payment complete",$parameters);
                        }
                }
                
                function woo_woocommerce_product_on_backorder($info) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_product_on_backorder"] ) {                      
                            $params['info']=$info;
                            $parameters=json_encode($params);
                            triger_action("Product on backorder",$parameters);
                        }
                }
                
                function woo_woocommerce_product_query($q, $thiss) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_product_query"] ) {                      
                            $params['q']=$q;
                            $params['thiss']=$thiss;
                            $parameters=json_encode($params);
                            triger_action("Product search",$parameters);
                        }
                }
                
                function woo_woocommerce_product_set_stock_status($id,$status) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_product_set_stock_status"] ) {                      
                            $params['id']=$id;
                            $params['status']=$status;
                            $parameters=json_encode($params);
                            triger_action("Product set stock status",$parameters);
                        }
                }
                
                function woo_woocommerce_reduce_order_stock($order) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_reduce_order_stock"] ) {                      
                            $params['order']=$order;
                            $parameters=json_encode($params);
                            triger_action("Reduce order stock",$parameters);
                        }
                }
                
                function woo_woocommerce_register_post($username,$email,$errors) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_register_post"] ) {                      
                            $params['username']=$username;
                            $params['billing_email']=$email;
                            $params['reg_errors']=$errors;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_register_post",$parameters);
                        }
                }
                
                function woo_woocommerce_reset_password_notification($user_login,$key) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_reset_password_notification"] ) {                      
                            $params['user_login']=$user_login;
                            $params['key']=$key;
                            $parameters=json_encode($params);
                            triger_action("Reset password notification",$parameters);
                        }
                }
                
                function woo_woocommerce_resume_order($order) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_resume_order"] ) {                      
                            $params['order id']=$order;
                            $parameters=json_encode($params);
                            triger_action("Resume order",$parameters);
                        }
                }
                
                function woo_woocommerce_shipping_init() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_shipping_init"] ) {                       
                            triger_action("Shipping init");
                        }
                }
                
                function woo_woocommerce_shipping_method_chosen($method) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_shipping_method_chosen"] ) {                      
                            $params['chosen method']=$method;
                            $parameters=json_encode($params);
                            triger_action("Shipping method chosen",$parameters);
                        }
                }
                
                function woo_woocommerce_track_order($order) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_track_order"] ) {                      
                            $params['order id']=$order;
                            $parameters=json_encode($params);
                            triger_action("Track order",$parameters);
                        }
                }
                
                
                
                function woo_get_product_search_form() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-get_product_search_form"] ) {                       
                            triger_action("Output Product search forms");
                        }
                }
                
                //Other actions
                function woo_before_woocommerce_init() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-before_woocommerce_init"] ) {                       
                            triger_action("before_woocommerce_init");
                        }
                }
                
                function woo_product_variation_linked($variation_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-product_variation_linked"] ) {                      
                            $params['variation_id']=$variation_id;
                            $parameters=json_encode($params);
                            triger_action("product_variation_linked",$parameters);
                        }
                }
                
                function woo_woocommerce_after_template_part($template_name,$template_path,$located,$args) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_after_template_part"] ) {                      
                            $params['template_name']=$template_name;
                            $params['template_path']=$template_path;
                            $params['located']=$located;
                            $params['args']=$args;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_after_template_part",$parameters);
                        }
                }
                
                function woo_woocommerce_ajax_added_to_cart($product_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_ajax_added_to_cart"] ) {                      
                            $params['product_id']=$product_id;
                            $parameters=json_encode($params);
                            triger_action("Ajax added to cart",$parameters);
                        }
                }
                
                function woo_woocommerce_api_wc_gateway_paypal() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_api_wc_gateway_paypal"] ) {                       
                            triger_action("woocommerce_api_wc_gateway_paypal");
                        }
                }
                
                function woo_woocommerce_before_delete_order_item($item_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_delete_order_item"] ) {                      
                            $params['item_id']=$item_id;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_before_delete_order_item",$parameters);
                        }
                }
                
                function woo_woocommerce_before_template_part($template_name,$template_path,$located,$args) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_before_template_part"] ) {                      
                            $params['template_name']=$template_name;
                            $params['template_path']=$template_path;
                            $params['located']=$located;
                            $params['args']=$args;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_before_template_part",$parameters);
                        }
                }
                
                function woo_woocommerce_cancelled_order($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_cancelled_order"] ) {                      
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("Cancelled order",$parameters);
                        }
                }
                
                
                function woo_woocommerce_checkout_update_order_review($post_data) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_checkout_update_order_review"] ) {                      
                            $params['post_data']=$post_data;
                            $parameters=json_encode($params);
                            triger_action("Checkout update order review",$parameters);
                        }
                }
                
                function woo_woocommerce_create_product_variation($variation_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_create_product_variation"] ) {                      
                            $params['variation_id']=$variation_id;
                            $parameters=json_encode($params);
                            triger_action("Create product variation",$parameters);
                        }
                }
                
                function woo_woocommerce_customer_change_password($user_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_customer_change_password"] ) {                      
                            $params['user_id']=$user_id;
                            $parameters=json_encode($params);
                            triger_action("Customer change password",$parameters);
                        }
                }
                
                function woo_woocommerce_customer_save_address($user_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_customer_save_address"] ) {                      
                            $params['user_id']=$user_id;
                            $parameters=json_encode($params);
                            triger_action("Customer save address",$parameters);
                        }
                }
                
                function woo_woocommerce_delete_order_item($item_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_delete_order_item"] ) {                      
                            $params['item_id']=$item_id;
                            $parameters=json_encode($params);
                            triger_action("Delete order item",$parameters);
                        }
                }
                
                function woo_woocommerce_download_product($email,$order_key,$product_id,$user_id,$download_id,$order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_download_product"] ) {                      
                            $params['email']=$email;
                            $params['order_key']=$order_key;
                            $params['product_id']=$product_id;
                            $params['user_id']=$user_id;
                            $params['download_id']=$download_id;
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("Download product",$parameters);
                        }
                }
                
                function woo_woocommerce_init() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_init"] ) {                       
                            triger_action("woocommerce_init");
                        }
                }
                
                function woo_woocommerce_loaded() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_loaded"] ) {                       
                            triger_action("woocommerce_loaded");
                        }
                }
               
                function woo_woocommerce_new_order_item($item_id,$item,$order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_new_order_item"] ) {                      
                            $params['item_id']=$item_id;
                            $params['item']=$item;
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("New order item",$parameters);
                        }
                }
                
                function woo_woocommerce_ordered_again($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_ordered_again"] ) {                      
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("Ordered again",$parameters);
                        }
                }
                

                
                function woo_woocommerce_register_post_type() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_register_post_type"] ) {                       
                            triger_action("woocommerce_register_post_type");
                        }
                }
                
                function woo_woocommerce_register_taxonomy() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_register_taxonomy"] ) {                       
                            triger_action("woocommerce_register_taxonomy");
                        }
                }
                
                function woo_woocommerce_restore_order_stock($order) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_restore_order_stock"] ) {                      
                            $params['order']=$order;
                            $parameters=json_encode($params);
                            triger_action("Restore order stock",$parameters);
                        }
                }
                
                
                
                //Dynamic 
                function woo_woocommerce_simple_add_to_cart() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_simple_add_to_cart"] ) {                       
                            triger_action("woocommerce_simple_add_to_cart");
                        }
                }
                
                function woo_woocommerce_grouped_add_to_cart() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_grouped_add_to_cart"] ) {                       
                            triger_action("woocommerce_grouped_add_to_cart");
                        }
                }
                
                function woo_woocommerce_variable_add_to_cart() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_variable_add_to_cart"] ) {                       
                            triger_action("woocommerce_variable_add_to_cart");
                        }
                }
                
                function woo_woocommerce_external_add_to_cart() {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_external_add_to_cart"] ) {                       
                            triger_action("woocommerce_external_add_to_cart");
                        }
                }
                
                function woo_woocommerce_cart_discount_after_tax_fixed_cart($coupon) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_cart_discount_after_tax_fixed_cart"] ) {                      
                            $params['coupon']=$coupon;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_cart_discount_after_tax_fixed_cart",$parameters);
                        }
                }
                
                function woo_woocommerce_cart_discount_after_tax_percent($coupon) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_cart_discount_after_tax_percent"] ) {                      
                            $params['coupon']=$coupon;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_cart_discount_after_tax_percent",$parameters);
                        }
                }
                
                
                function woo_woocommerce_product_discount_after_tax_fixed_cart($coupon, $values, $price) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_product_discount_after_tax_fixed_cart"] ) {                      
                            $params['coupon']=$coupon;
                            $params['values']=$values;
                            $params['price']=$price;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_product_discount_after_tax_fixed_cart",$parameters);
                        }
                }
                
                function woo_woocommerce_product_discount_after_tax_percent($coupon, $values, $price) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_product_discount_after_tax_percent"] ) {                      
                            $params['coupon']=$coupon;
                            $params['values']=$values;
                            $params['price']=$price;
                            $parameters=json_encode($params);
                            triger_action("woocommerce_product_discount_after_tax_percent",$parameters);
                        }
                }
                
                function woo_woocommerce_order_status_cancelled($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_order_status_cancelled"] ) {                      

                            $order = new WC_Order($order_id);
                            $user_info = get_userdata($order->user_id);
                            $username=$user_info->user_login;
                            $useremail=$user_info->user_email;
                            
                            $params['username']=$username;
                            $params['order_id']=$order_id;
                            
                            $parameters=json_encode($params);

                            triger_action("Order status cancelled",$parameters,$username,$useremail);
                        }
                }
                
                function woo_woocommerce_order_status_completed($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_order_status_completed"] ) {                      
                            
                            $order = new WC_Order($order_id);
                            $user_info = get_userdata($order->user_id);
                            $username=$user_info->user_login;
                            $useremail=$user_info->user_email;
                            
                            $params['username']=$username;
                            $params['order_id']=$order_id;
                            
                            $parameters=json_encode($params);

                            triger_action("Order status completed",$parameters,$username,$useremail);
                        }
                }
                
                function woo_woocommerce_order_status_failed($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_order_status_failed"] ) {                      
                            
                            $order = new WC_Order($order_id);
                            $user_info = get_userdata($order->user_id);
                            $username=$user_info->user_login;
                            $useremail=$user_info->user_email;
                            
                            $params['username']=$username;
                            $params['order_id']=$order_id;
                            
                            $parameters=json_encode($params);
                            triger_action("Order status failed",$parameters,$username,$useremail);
                        }
                }
                
                function woo_woocommerce_order_status_on_hold($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_order_status_on-hold"] ) {                      
                            
                            $order = new WC_Order($order_id);
                            $user_info = get_userdata($order->user_id);
                            $username=$user_info->user_login;
                            $useremail=$user_info->user_email;
                            
                            $params['username']=$username;
                            $params['order_id']=$order_id;
                            
                            $parameters=json_encode($params);
                            triger_action("Order status on-hold",$parameters,$username,$useremail);
                        }
                }
                
                function woo_woocommerce_order_status_pending($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_order_status_pending"] ) {                      
                            
                            $order = new WC_Order($order_id);
                            $user_info = get_userdata($order->user_id);
                            $username=$user_info->user_login;
                            $useremail=$user_info->user_email;
                            
                            $params['username']=$username;
                            $params['order_id']=$order_id;
                            
                            $parameters=json_encode($params);
                            triger_action("Order status pending",$parameters,$username,$useremail);
                        }
                }
                
                function woo_woocommerce_order_status_processing($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_order_status_processing"] ) {                      
                            
                            $order = new WC_Order($order_id);
                            $user_info = get_userdata($order->user_id);
                            $username=$user_info->user_login;
                            $useremail=$user_info->user_email;
                            
                            $params['username']=$username;
                            $params['order_id']=$order_id;
                            
                            $parameters=json_encode($params);
                            triger_action("Order status processing",$parameters,$username,$useremail);
                        }
                }
                
                function woo_woocommerce_order_status_refunded($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_order_status_refunded"] ) {                      
                            
                            $order = new WC_Order($order_id);
                            $user_info = get_userdata($order->user_id);
                            $username=$user_info->user_login;
                            $useremail=$user_info->user_email;
                            
                            $params['username']=$username;
                            $params['order_id']=$order_id;
                            
                            $parameters=json_encode($params);
                            triger_action("Order status refunded",$parameters,$username,$useremail);
                        }
                }
                
                function woo_woocommerce_receipt_bacs($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_receipt_bacs"] ) {                      
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("Receipt bacs",$parameters);
                        }
                }
                
                function woo_woocommerce_receipt_cheque($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_receipt_cheque"] ) {                      
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("Receipt cheque",$parameters);
                        }
                }
                
                function woo_woocommerce_receipt_paypal($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_receipt_paypal"] ) {                      
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("Receipt paypal",$parameters);
                        }
                }
                
                function woo_woocommerce_receipt_other($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_receipt_other"] ) {                      
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("Receipt other payment method",$parameters);
                        }
                }
                
                function woo_woocommerce_thankyou_bacs($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_thankyou_bacs"] ) {                      
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("Thankyou - bacs",$parameters);
                        }
                }
                
                function woo_woocommerce_thankyou_cheque($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_thankyou_cheque"] ) {                      
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("Thankyou - cheque",$parameters);
                        }
                }
                
                function woo_woocommerce_thankyou_paypal($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_thankyou_paypal"] ) {                      
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("Thankyou - paypal",$parameters);
                        }
                }
                
                function woo_woocommerce_thankyou_other($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_thankyou_other"] ) {                      
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("Thankyou - other payment method",$parameters);
                        }
                }
                
                function woo_woocommerce_thankyou_ifmb($order_id) {
                        $wp_userfollowup_woocommerce_vars = get_option( 'wp_userfollowup_woocommerce_vars' );
			if(is_array($wp_userfollowup_woocommerce_vars)&&$wp_userfollowup_woocommerce_vars["woo-woocommerce_thankyou_ifmb"] ) {                      
                            $params['order_id']=$order_id;
                            $parameters=json_encode($params);
                            triger_action("Thankyou - multibanco payment method",$parameters);
                        }
                }
                                        
              
                
	}
        
        
        function triger_action($actionName,$parameters = NULL,$username = NULL,$useremail = NULL,$phone = NULL){
            $wp_userfollowup_vars = get_option( 'wp_userfollowup_vars' );
            if ( !empty( $wp_userfollowup_vars['apikey'] ) ) {
		  $api_key = $wp_userfollowup_vars['apikey'];
            }
          
            $response = get_web_page("http://app.userfollowup.com/".sanitize_text_field($api_key).".js");
//            $response = get_web_page("http://localhost/userfollowup-backoffice/followup.php?user=".sanitize_text_field($api_key));
            $tokenstart=strpos($response,"'");
            $tokenend=strpos($response,";");
            $tokenlength=$tokenend-$tokenstart-2;
            $token = substr($response, $tokenstart+1, $tokenlength);
            $clientstart=strpos($response,'&c="+');
            $client = substr($response, $clientstart+5, 2);

            //Identify client 
            
            if (isset($useremail)&&(isset($username))){
                $post_data['email'] = $useremail;
                $post_data['name'] = $username;
                 $post_data['phone'] = $phone;
            }
            else if (is_user_logged_in()){
                
                $user = wp_get_current_user();
                $user->data;
                //create array of data to be posted
                $post_data['email'] = $user->user_email;
                $post_data['name'] = $user->user_login;
                
                //get user phone
                $phone = get_user_meta( $user->ID, 'billing_phone', TRUE ); 
                
                
            } else if (get_userdatabylogin($_REQUEST["log"])){
                $user = get_userdatabylogin($_REQUEST["log"]);
                $user->data;
                $post_data['email'] = $user->user_email;
                $post_data['name'] = $user->user_login;
            } else if ($_REQUEST["action"]=="register"){
                $user = get_userdatabylogin($_REQUEST["log"]);
                $user->data;
                $post_data['email'] = $_REQUEST["user_email"];
                $post_data['name'] = $_REQUEST["user_login"];
            } else {
                $post_data['email'] = 'unregistered';
                $post_data['name'] = 'unregistered';
            }
            
            $post_data['phone'] = $phone;
            
            $post_data['token'] = $token;
            $post_data['id'] = sanitize_text_field($api_key);
            //traverse array and prepare data for posting (key1=value1)
            foreach ( $post_data as $key => $value) {
            $post_items[] = $key . '=' . $value;
            }
            //create the final string to be posted using implode()
            $post_string = implode ('&', $post_items);

            //create cURL connection
            $curl_connection = curl_init('http://app.userfollowup.com/user.php');
//            $curl_connection = curl_init('http://localhost/userfollowup-backoffice/user.php');
            //set options
            curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
            curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
            //set data to be posted
            curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
            //perform our request
            $result = curl_exec($curl_connection);
            curl_close($curl_connection);

           $client=$result;

            //trigger action
            $action=urlencode($actionName);
            $url="http://app.userfollowup.com/fu.php?o=record&a=".$action."&t=".$token."&id=".sanitize_text_field($api_key)."&c=".$client."&p=".$parameters;
//            $url="http://localhost/userfollowup-backoffice/fu.php?o=record&a=".$action."&t=".$token."&id=".sanitize_text_field($api_key)."&c=".$client."&p=".$parameters;
            
            $response2 = get_web_page($url);
        }
        
        function get_web_page($url) {
            $options = array (CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER => false, // don't return headers
            CURLOPT_FOLLOWLOCATION => true, // follow redirects
            CURLOPT_ENCODING => "", // handle compressed
            CURLOPT_USERAGENT => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)", // who am i
            CURLOPT_AUTOREFERER => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT => 120, // timeout on response
            CURLOPT_MAXREDIRS => 10 ); // stop after 10 redirects

            $ch = curl_init ( $url );
            curl_setopt_array ( $ch, $options );
            $content = curl_exec ( $ch );
            $err = curl_errno ( $ch );
            $errmsg = curl_error ( $ch );
            $header = curl_getinfo ( $ch );
            $httpCode = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );

            curl_close ( $ch );

            $header ['errno'] = $err;
            $header ['errmsg'] = $errmsg;
            $header ['content'] = $content;
            return $header ['content'];
        }
        
        
        
        
	new wp_userfollowup();

}