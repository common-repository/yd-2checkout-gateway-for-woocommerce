<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

try {
    
    spl_autoload_register( function ( $className ) {
        
        $className = ltrim( $className, '\\' );
        $fileName  = '';
        
        if ( $lastNsPos = strrpos( $className, '\\' ) ) {
            $namespace = substr( $className, 0, $lastNsPos );
            $className = substr( $className, $lastNsPos + 1 );
            
            $fileName = str_replace( '\\', DIRECTORY_SEPARATOR, $namespace ) . DIRECTORY_SEPARATOR;
        }
        
        $fileName .= $className . '.php';
        
        $file = WC2CO_PATH . '/includes/classes/' . $fileName;
        
        if ( file_exists( $file ) ) {
            require( $file );
        }
    } );

} catch ( Exception $e ) {

    echo $e->getMessage();
}
