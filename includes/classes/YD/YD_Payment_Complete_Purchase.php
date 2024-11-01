<?php
    
    
namespace YD;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class YD_Payment_Complete_Purchase {
    
    private $secret;
    
    private $merchant;
    
    public function __construct( $secret, $merchantCode ) {
    
        $this->secret   = str_replace( '&amp;', '&', $secret );
        $this->merchant = $merchantCode;
    }
    
    public function checkStatus( $hash, $ref ) {
        
        $date = gmdate('Y-m-d H:i:s', time() );
        
        $hashMd5 = hash_hmac( 'md5',
            strlen( $this->merchant ) . $this->merchant . strlen( $date ) . $date, $this->secret
        );
        
        $header = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'X-Avangate-Authentication' => 'code="' . $this->merchant . '" date="' . $date . '" hash="' . $hashMd5 . '"'
        ];
        
        $response = wp_remote_post( 'https://api.2checkout.com/rest/5.0/orders/' . $ref . '/', [
            'method'    => 'GET',
            'timeout'   => 120,
            'sslverify' => false,
            'headers'   => $header,
            'body'      => ''
        ] );
        
        if ( is_wp_error( $response ) )
            return false;
        
        $foo = json_decode( $response['body'], true );
        
        if( isset( $foo['Status'] ) ) {
            
            $status    = sanitize_text_field( $foo['Status'] );
            $ap_status = sanitize_text_field( $foo['ApproveStatus'] );
            $v_status  = sanitize_text_field( $foo['VendorApproveStatus'] );
            $ext_hash  = sanitize_text_field( $foo['ExternalReference'] );
            
            if( in_array( $status, [ 'AUTHRECEIVED', 'PENDING', 'COMPLETE' ] ) &&
                in_array( $ap_status, [ 'WAITING', 'OK' ] ) ) {
                
                if( $hash != $ext_hash ) {
                    return [ 'status' => 'fail' ];
                }
                
                if( $v_status == 'OK' ) {
                    return [ 'status' => 'completed' ];
                }
                
            } else {
                return [ 'status' => 'fail' ];
            }
        }
        
        return false;
    }
    
    public function init() {
    
        $signature = isset( $_POST['HASH'] ) ? sanitize_text_field( $_POST['HASH'] ) : false;
        
        if( ! $signature )
            return [ 'error' => __( 'Signature not found', 'wc-2co' ) ];
    
        $prepareHash = ''; /* string for compute HASH for received data */
    
        foreach( $_POST as $key => $val ) {
        
            if( $key != 'HASH' ) {
                if( is_array( $val ) ) {
                    $prepareHash .= $this->arrayExpand( $val );
                } else {
                    $size        = strlen( stripslashes( $val ) );
                    $prepareHash .= $size . stripslashes( $val );
                }
            }
        }
        
        $hash = $this->hmac( $prepareHash ); /* HASH for data received */
        
        if( $hash == $signature ) {
        
            // Verified OK!
        
            $date_return = date( 'YmdGis' );
            
            $ipn_pid  = sanitize_text_field( $_POST['IPN_PID'][0] );
            $ipn_name = sanitize_text_field( $_POST['IPN_PNAME'][0] );
            $ipn_date = sanitize_text_field( $_POST['IPN_DATE'] );
            $refNo    = (int) $_POST['REFNO'];
            
            $info = $this->getOrderInfo( $refNo );
    
            if( is_array( $info ) )
                return $info;
            
            $return  = strlen( $ipn_pid ) . $ipn_pid . strlen( $ipn_name ) . $ipn_name;
            $return .= strlen( $ipn_date ) . $ipn_date . strlen( $date_return ) . $date_return;
            
            return [
                'date_return' => $date_return,
                'result_hash' => $this->hmac( $return )
            ];
        } else {
            // BAD IPN Signature
            return [ 'error' => __( 'BAD IPN Signature', 'wc-2co' ) ];
        }
    }
    
    private function arrayExpand( $array ) {
        
        $val = '';
        
        for( $i = 0; $i < sizeof($array); $i++ ) {
            $size = strlen( stripslashes( $array[$i] ) );
            $val .= $size . stripslashes( $array[$i] );
        }
        
        return $val;
    }
    
    private function hmac( $data ) {
        
        $key = $this->secret; /* pass to compute HASH */
        $b   = 64; // byte length for md5
        
        if( strlen( $key ) > $b ) {
            $key = pack( 'H*', md5( $key ) );
        }
        
        $key    = str_pad( $key, $b, chr(0x00) );
        $ipad   = str_pad( '', $b, chr(0x36) );
        $opad   = str_pad( '', $b, chr(0x5c) );
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;
        
        return md5( $k_opad . pack( 'H*', md5( $k_ipad . $data ) ) );
    }
    
    private function getOrderInfo( $refNo ) {
        
        $date = gmdate('Y-m-d H:i:s', time() );
        $hash = hash_hmac( 'md5',
            strlen( $this->merchant ) . $this->merchant . strlen( $date ) . $date, $this->secret
        );
        
        $header = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'X-Avangate-Authentication' => 'code="' . $this->merchant . '" date="' . $date . '" hash="' . $hash . '"'
        ];
        
        $response = wp_remote_post( 'https://api.2checkout.com/rest/5.0/orders/' . $refNo . '/', [
            'method'    => 'GET',
            'timeout'   => 120,
            'sslverify' => false,
            'headers'   => $header,
            'body'      => ''
        ] );
        
        if ( is_wp_error( $response ) ) {
            
            $error_message = $response->get_error_message();
            
            return [ 'error' => __( 'Could not connect', 'ads' ) . ' ' . $error_message ];
            
        } else {
            
            $foo = json_decode( $response['body'], true );
            
            return ( $foo['Status'] == 'COMPLETE' || $foo['ApproveStatus'] == 'OK' ) ? true : [
                'error' => __( 'Payment status is not complete', 'ads' ),
                'data'  => $foo
            ];
        }
    }
}