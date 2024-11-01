<?php
/**
 * Author: Vitaly Kukin
 * Date: 26.09.2019
 * Time: 23:34
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
    
use YD\YD_Gateway_Init;

if ( ! function_exists( 'pr' ) ) {
    
    function pr( $any ) {
        
        print_r( "<pre><code>" );
        print_r( $any );
        print_r( "</code></pre>" );
    }
}

function wc_2co_get_domain() {
    
    $url  = get_bloginfo('url');
    $path = parse_url( $url );
    
    $host = isset( $path['host'] ) ? $path['host'] : $path['path'];
    $pos  = strpos( $host, '/' );
    if( $pos ) {
        $host = substr( $host, 0, $pos );
    }
    
    $m = preg_match( "/[^\.\/]+\.[^\.\/]+$/", $host, $matches );
    
    if( $m === FALSE )
        return false;
    
    $domain = $matches[ 0 ];
    $main_domain = explode( '.', $domain );
    
    if( strlen( $main_domain[0] ) <= 3 ) {
        
        if( substr_count( $host, '.' ) == 2 ) {
            $domain = $host;
        } else {
            $d = explode( '.', $host );
            $d = array_reverse( $d );
            
            $domain = "{$d[2]}.{$d[1]}.{$d[0]}";
        }
    }
    
    return $domain;
}

add_action( 'wp', function() {
    
    YD_Gateway_Init::payment_wait();
} );

add_action( 'init', function() {
    
    YD_Gateway_Init::payment_notify();
} );

function wc_2co_add_gateways( $methods ) {
    
    $methods[] = 'YD\YD_Gateway_Init';
    
    return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'wc_2co_add_gateways' );

new YD\YD_Payment_Request();
