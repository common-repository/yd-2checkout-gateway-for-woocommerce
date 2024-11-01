<?php
/**
 * Author: Vitaly Kukin
 * Date: 26.09.2019
 * Time: 23:24
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Set plugin version and create table in DB
 */
function wc_2co_install() {

    update_option( 'wc_2co-version', WC2CO_VERSION  );
}

/**
 * Check installed plugin
 */
function wc_2co_installed() {

    if( ! current_user_can('install_plugins') )
        return;

    if( get_option( 'wc_2co-version', 0 ) < WC2CO_VERSION )
        wc_2co_install();
}
add_action( 'admin_init', 'wc_2co_installed' );

/**
 * When activate plugin
 */
function wc_2co_activate() {
    
    wc_2co_installed();
}
