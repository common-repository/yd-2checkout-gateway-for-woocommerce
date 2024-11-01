<?php
/**
 * Plugin Name: YD 2Checkout Gateway for WooCommerce
 * Plugin URI: https://yellowduck.me/
 * Description: Take credit card payments on your store using 2Checkout.
 * Author: Vitaly Kukin
 * Version: 0.2.2
 * Requires at least: 4.4
 * Tested up to: 5.6.1
 * WC requires at least: 3.7
 * WC tested up to: 5.0
 * Text Domain: wc-2co
 * License: MIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wc_2co_init() {
    
    define( 'WC2CO_VERSION', '0.2.1' );
    define( 'WC2CO_PATH',    untrailingslashit( plugin_dir_path( __FILE__ ) ) );
    define( 'WC2CO_URL',     untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
    define( 'WC2CO_ERROR',   wc_2co_check_woo() );
    
    add_action( 'init', function() {
        
        load_plugin_textdomain('wc-2co');
    } );
    
    if( WC2CO_ERROR ) {
        
        add_action( 'admin_notices', function() { echo WC2CO_ERROR; } );
        
        return;
    }
    
    require_once WC2CO_PATH . '/includes/autoloader.php';
    require_once WC2CO_PATH . '/includes/setup.php';
    require_once WC2CO_PATH . '/includes/core.php';
    
    register_activation_hook( __FILE__, 'wc_2co_install' );
    register_activation_hook( __FILE__, 'wc_2co_activate' );
}
add_action( 'plugins_loaded', 'wc_2co_init' );

function wc_2co_check_woo() {
    
    $layout = false;
    
    if ( ! class_exists( 'WooCommerce' ) ) {
        
        $layout = __( 'WooCommerce 2Checkout Gateway requires WooCommerce plugin for its proper work', 'wc-2co' );
    }
    
    return $layout ? sprintf( '<div class="notice notice-error"><p>%s</p></div>', $layout ) : false;
}