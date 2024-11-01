<?php
    
    
namespace YD;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class YD_Payment_Request {
    
    public function __construct() {
    
        $this->init();
    }
    
    public function init() {
    
        add_action( 'wp_ajax_wc_yd_2co_action_gateway', [ $this, 'prepare_payment' ] );
        add_action( 'wp_ajax_nopriv_wc_yd_2co_action_gateway', [ $this, 'prepare_payment' ] );
        
        add_action( 'wp_ajax_wc_yd_2co_action_wait', [ $this, 'wait_payment' ] );
        add_action( 'wp_ajax_nopriv_wc_yd_2co_action_wait', [ $this, 'wait_payment' ] );
    }
    
    public function prepare_payment() {
    
        $args = $_POST['args'];
        
        $data = [];
        
        if( is_array( $args ) ) {
            $data = $args;
        } else {
            parse_str( $args, $data );
        }
        unset( $_POST['args'] );
        $_POST = $data;
    
        $_REQUEST['woocommerce-process-checkout-nonce'] = $_POST['woocommerce-process-checkout-nonce'];
    
        WC()->checkout()->process_checkout();
        wp_die( 0 );
    }
    
    public function wait_payment() {
    
        if( ! isset( $_POST['order_id'] ) || empty( $_POST['order_id'] ) ) {
        
            $wc_order = new \WC_Order();
            echo json_encode([
                'redirect' => $wc_order->get_cancel_order_url()
            ]);
            exit;
        }
        
        $order_id = (int) $_POST['order_id'];
        $order    = wc_get_order( $order_id );
    
        if( ! is_object( $order ) ) {
    
            $wc_order = new \WC_Order();
            
            echo json_encode([
                'redirect' => $wc_order->get_cancel_order_url()
            ]);
            exit;
        }
        
        if( in_array( $order->get_status(), [ 'processing', 'completed' ] ) ) {
        
            echo json_encode( [
                'redirect' => $order->get_checkout_order_received_url()
            ] );
            exit;
        }
    
        echo json_encode( [
            'wait' => 10000
        ] );
        
        exit;
    }
}