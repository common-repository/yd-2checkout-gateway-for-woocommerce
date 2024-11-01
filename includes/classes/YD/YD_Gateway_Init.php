<?php

namespace YD;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WC_Gateway_YD_2Checkout
 *
 * @package YD
 */
class YD_Gateway_Init extends \WC_Payment_Gateway {
    
    /**
     * Demo mode parameter
     *
     * @var bool
     */
    public $testMode;
    
    /**
     * Locale in 2Checkout popup window
     *
     * @var string
     */
    public $locale;
    
    /**
     * Integrations -> Webhooks & API -> section API -> Merchant code
     *
     * @var string
     */
    public $merchantCode;
    
    /**
     * Integrations -> Webhooks & API -> section API -> Secret code
     *
     * @var string
     */
    public $secretKey;
    
    /**
     * Will placed in 2Checkout popup window like store name
     *
     * @var string
     */
    public $brand;
    
    /**
     * Listener url
     *
     * @var string
     */
    public $ipnUrl;
    
    public function __construct() {
        
        $this->id 				  = 'yd_wc_2co';
        $this->method_title       = '2Checkout in popup with Credit Card/PayPal';
        $this->method_description = sprintf( __( 'GoSell via 2Checkout payment system', 'wc-2co' ) );
        
        $this->has_fields   	  = true;
        $this->icon 			  = '';
    
        $this->title              = $this->get_option( 'title' );
        $this->description        = $this->get_option( 'description' );
        $this->enabled            = $this->get_option( 'enabled' );
        $this->testMode           = 'yes' === $this->get_option( 'testMode' );
        $this->locale             = $this->get_option( 'locale' );
        $this->merchantCode       = $this->get_option( 'merchantCode' );
        $this->secretKey          = $this->get_option( 'secretKey' );
        $this->brand              = $this->get_option( 'brand' );
        $this->ipnUrl             = $this->get_option( 'ipnUrl' );
    
        // Load the form fields
        $this->init_form_fields();
    
        // Load the settings
        $this->init_settings();
    
        // Hooks
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'payment_scripts' ] );
    }
    /**
     * Initialise Gateway Settings Form Fields
     */
    function init_form_fields() {
        
        $this->form_fields = require( WC2CO_PATH . '/core/settings.php' );
    }
    
    /**
     * Payment form on checkout page
     */
    public function payment_fields() {
        
        $this->elements_form();
    }
    
    /**
     * Renders the elements form
     */
    public function elements_form() {
        
        printf( '<span>%s</span>', $this->description );
        
        ?>
        <script type="text/javascript">

            (function (document, src, libName, config) {
                let script             = document.createElement('script');
                script.src             = src;
                script.async           = true;
                let firstScriptElement = document.getElementsByTagName('script')[0];
                script.onload          = function () {
                    for (let namespace in config) {
                        if (config.hasOwnProperty(namespace)) {
                            window[libName].setup.setConfig(namespace, config[namespace]);
                        }
                    }
                };

                firstScriptElement.parentNode.insertBefore(script, firstScriptElement);
            })(document, 'https://secure.avangate.com/checkout/client/twoCoInlineCart.js',
                'TwoCoInlineCart',{
                    "app":{ "merchant":"<?php echo $this->merchantCode ?>" },
                    "cart":{"host":"https:\/\/secure.2checkout.com","customization":"inline"}
                });

            function formSerialize( $el ) {

                let serialized = $el.serialize();

                if( ! serialized )
                    serialized = $el.find( 'input[name],select[name],textarea[name]' ).serialize();

                return serialized;
            }

            function waitPayment() {
                if ( window.jQuery ) {

                    jQuery(function($){

                        let $purchase = $('[name="ads_checkout"]'),
                            $form     = $('form[name="checkout"]'),
                            reset2CO  = false;

                        function getBasketData() {

                            if( reset2CO ) {
                                TwoCoInlineCart.billing.reset();
                                TwoCoInlineCart.shipping.reset();
                                TwoCoInlineCart.products.removeAll();
                            } else {
                                TwoCoInlineCart.setup.setMode('DYNAMIC');
                                TwoCoInlineCart.register();
                            }

                            $.ajax({
                                url      : ydAjax.ajaxurl,
                                type     : 'POST',
                                dataType : 'json',
                                async    : true,
                                data     : {
                                    action : 'wc_yd_2co_action_gateway',
                                    args   : formSerialize( $form )
                                },
                                success  : function (data) {
                                    
                                    if( $form.find('.woocommerce-NoticeGroup').length ) {
                                        $form.find('.woocommerce-NoticeGroup').remove();
                                    }

                                    if( data.hasOwnProperty('currency') ) {

                                        TwoCoInlineCart.billing.setData({
                                            name    : data.billing_name,
                                            email   : data.email,
                                            country : data.billing_country,
                                            phone   : data.billing_phone,
                                            state   : data.billing_state,
                                            city    : data.billing_city,
                                            address : data.billing_address,
                                            zip     : data.billing_zip
                                        });

                                        TwoCoInlineCart.shipping.setData({
                                            name    : data.name,
                                            email   : data.email,
                                            //country : data.country,
                                            phone   : data.phone,
                                            state   : data.state,
                                            city    : data.city,
                                            address : data.address,
                                            zip     : data.zip
                                        });

                                        TwoCoInlineCart.products.addMany( data.items );
                                        TwoCoInlineCart.cart.setCurrency( data.currency );

                                        TwoCoInlineCart.cart.setLanguage( data.lang );
                                        TwoCoInlineCart.cart.setSource('alidropship');
                                        TwoCoInlineCart.cart.setTest( <?php echo $this->testMode ? 1 : 0 ?> );
                                        TwoCoInlineCart.cart.setOrderExternalRef( data.reference );
                                        TwoCoInlineCart.cart.setExternalCustomerReference (data.reference );
                                        TwoCoInlineCart.cart.setReturnMethod({
                                            type : 'redirect',
                                            url  : data.link
                                        });

                                        setTimeout(
                                            function () {
                                                TwoCoInlineCart.cart.checkout();
                                            },
                                            1500
                                        );
                                    } else if( data.hasOwnProperty('result') && data.result === 'failure' ) {
                                        
                                        if( data.refresh ) {
                                            window.location.reload();
                                        } else if( data.reload ) {
                                            window.location.reload();
                                        } else {
                                            $form.prepend( $('<div>').addClass('woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout').html(data.messages) );
                                        }
                                    }
                                }
                            });

                            TwoCoInlineCart.events.subscribe('cart:opened', function () {
                                reset2CO = true;
                            });
                            
                            TwoCoInlineCart.events.subscribe('cart:closed', function () {
                                
                                //todo снести к ебеням анимацию с кнопки
                                $purchase.removeClass('checkout-spinner').prop( 'disabled', false );
                                if( $purchase.hasClass('btn-processed') ) {
                                    $purchase.removeClass('btn-processed');
                                }
                            });
                        }

                        $form.on( 'submit', function(e) {

                            if( $form.find('#payment_method_yd_wc_2co').is(':checked') ) {
                                
                                getBasketData();

                                e.preventDefault();
                                e.stopPropagation();
                                e.stopImmediatePropagation();
                            }
                            
                            return false;
                        });
                    });
                } else { window.setTimeout( waitPayment, 200 ); }
            }
            waitPayment();
        </script>
        <?php
    }
    
    /**
     * Payment_scripts function.
     *
     * Outputs scripts used for 2Checkout payment
     */
    public function payment_scripts() {
    
        if (
            ( ! is_product() && ! is_cart() && ! is_checkout() && ! is_add_payment_method_page() ) &&
            ( ! isset( $_GET['wc_yd_2co-order'] ) || empty( $_GET['wc_yd_2co-order'] ) )
        ) {
            return;
        }
        
        if ( 'no' === $this->enabled ) {
            return;
        }
    
        wp_register_script( 'yd-wc-2co', WC2CO_URL . '/assets/js/yd-wc-2co.js', [ 'jquery-payment' ], WC2CO_VERSION, true  );
        wp_localize_script( 'yd-wc-2co', 'ydAjax', [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] );
    
        wp_enqueue_script( 'yd-wc-2co' );
    }

    public function process_payment( $order_id ) {
    
        $currency = get_woocommerce_currency();
        
        $order = new \WC_Order( $order_id );
    
        $too = [
            'result'          => 'success',
            'link'            => home_url('/?wc_yd_2co-order=' . $order->get_id()),
            'lang'            => $this->locale,
            'reference'       => $order->get_id(),
            'total'           => (string) round( $order->get_total(), 2 ),
            'currency'        => $currency,
            'email'           => $order->get_billing_email(),
            'billing_name'    => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'billing_country' => $order->get_billing_country(),
            'billing_state'   => $this->get_real_state( $order->get_billing_country(), $order->get_billing_state() ),
            'billing_city'    => $order->get_billing_city(),
            'billing_address' => trim( $order->get_billing_address_1() . ' ' . $order->get_billing_address_2() ),
            'billing_zip'     => sanitize_text_field(
                wc_format_postcode( $order->get_billing_postcode(), $order->get_billing_country() )
            ),
            'billing_phone'   => $order->get_billing_phone(),
            'items'           => [ [
                'type'      => 'PRODUCT',
                'name'      => __( 'Order from', 'wc-2co' ) . ' ' . $this->brand,
                'tangible'  => true,
                'quantity'  => 1,
                'price'     => (string) round( $order->get_total(), 2 )
            ] ],
        ];
    
        if( $order->needs_shipping_address() ) {
            
            $too['name']         = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
            $too['phone_number'] = $order->get_billing_phone();
            $too['country']      = $order->get_shipping_city();
            $too['state']        = $this->get_real_state( $order->get_shipping_country(), $order->get_shipping_state() );
            $too['city']         = $order->get_shipping_country();
            $too['address']      = trim( $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2() );
            $too['zip']          = sanitize_text_field(
                wc_format_postcode( $order->get_shipping_postcode(), $order->get_shipping_country() )
            );
            
        } else {
    
            $too['name']         = $too['billing_name'];
            $too['country']      = $too['billing_country'];
            $too['state']        = $too['billing_state'];
            $too['city']         = $too['billing_city'];
            $too['address']      = $too['billing_address'];
            $too['zip']          = $too['billing_zip'];
            $too['phone_number'] = $too['billing_phone'];
        }
        
        return $too;
    }
    
    public static function payment_notify() {
    
        if( ! isset( $_GET['wc_2co'] ) || $_GET['wc_2co'] != md5( wc_2co_get_domain() ) )
            return;
    
        if( ! isset( $_POST['REFNOEXT'] ) || empty( $_POST['REFNOEXT'] ) )
            return;
    
        $order_id = absint( $_POST['REFNOEXT'] );
        $order    = wc_get_order( $order_id );
    
        if( ! is_object( $order ) ) {
            exit;
        }
    
        $status = $order->get_status();
    
        if( $status == 'completed' ) {
            exit;
        }
        
        $tnx_id = get_post_meta( $order_id, 'yd2co_tnx_id', true );
        
        if( $tnx_id && ! empty( $tnx_id ) ) {
            exit;
        }
        
        $settings = get_option( 'woocommerce_yd_wc_2co_settings', [] );
        
        $secretKey    = isset( $settings['secretKey'] ) ? $settings['secretKey'] : '';
        $merchantCode = isset( $settings['merchantCode'] ) ? $settings['merchantCode'] : '';
        
        
        $cp     = new YD_Payment_Complete_Purchase( $secretKey, $merchantCode );
        $result = $cp->init();
        
        if( isset( $result['error'] ) ) {
    
            $order->update_status('failed');
            $order->add_order_note( sprintf( __( 'Order ID %d failed', 'wc-2co' ), $order_id ) );

        } else {
        
            header("Content-type: text/plain");
            
            update_post_meta( $order_id, 'yd2co_tnx_id', (int) $_POST['REFNO'] );
            
            $order->payment_complete();
            $order->add_order_note( sprintf( __( '2Checkout charge complete REFNOEXT ID: %s', 'wc-2co'), $order_id) );
            
            WC()->cart->empty_cart(); // Remove cart.
        
            echo "<EPAYMENT>" . $result['date_return'] . "|" . $result['result_hash'] . "</EPAYMENT>";
        }
    
        exit;
    }
    
    public static function payment_wait() {
    
        if( ! isset( $_GET['wc_yd_2co-order'] ) || empty( $_GET['wc_yd_2co-order'] ) )
            return;
    
        $order_id = absint( $_GET['wc_yd_2co-order'] );
        $order    = wc_get_order( $order_id );
        
        if( ! is_object( $order ) ) {
            return;
        }
        
        $status = $order->get_status();
        
        if( $status == 'completed' ) {
            
            $url = $order->get_checkout_order_received_url();
    
            wp_redirect( $url );
            exit;
        }
    
        if( isset( $_GET['refno'] ) ) {
    
            $settings = get_option( 'woocommerce_yd_wc_2co_settings', [] );
    
            $secretKey    = isset( $settings['secretKey'] ) ? $settings['secretKey'] : '';
            $merchantCode = isset( $settings['merchantCode'] ) ? $settings['merchantCode'] : '';
            
            $cp     = new YD_Payment_Complete_Purchase( $secretKey, $merchantCode );
    
            $ref = (int) $_GET['refno'];
            
            $result = $cp->checkStatus( $order_id, $ref );
            
            if( $result && is_array( $result ) ) {
    
                if( isset( $result['status'] ) && $result['status'] == 'completed' ) {
    
                    $order->update_status('processing');
                    
                    WC()->cart->empty_cart(); // Remove cart.
                    
                    wp_redirect( $order->get_checkout_order_received_url() );
                    exit;
                }
    
                $order->update_status('failed');
    
                wp_redirect( $order->get_checkout_order_received_url() );
                exit;
            }
        }
        
        
        if( in_array( $status, [ 'pending', 'processing', 'on-hold' ] ) ) {
    
            wp_register_script( 'yd-wc-2co', WC2CO_URL . '/assets/js/yd-wc-2co.js', [ 'jquery-payment' ], WC2CO_VERSION, true  );
            wp_localize_script( 'yd-wc-2co', 'ydAjax', [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] );
    
            wp_enqueue_script('yd-wc-2co');
            
            echo self::waitPage( $order_id );
    
            exit;
        }
    
        $order->add_order_note( '#' . $order_id . ' ' . __( 'Order status not for waiting page', 'wc-2co' ) );
        
        exit;
    }
    
    public static function waitPage( $order_id ) {
    
        wp_enqueue_style( 'fonts', 'https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap', [], '1.0', false);
        
        ob_start(); ?>

        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" lang="en">
        <head>
            <meta charset="<?php bloginfo( 'charset' ); ?>">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

            <title><?php _e( 'Completion of payment', 'wc-2co' ) ?></title>
            
            <?php wp_head() ?>

            <style type="text/css">
                body{
                    margin:0;
                    padding:0;
                    font-family: 'Roboto', sans-serif;
                }
                .waitpage{
                    display: -webkit-box;
                    display: flex;
                    width:100%
                }
                .waitpage .container{
                    -webkit-box-flex: 1;
                    flex: 1 1 100%;
                    margin: auto;
                    width: 100%;
                }
                .waitpage .container.fill-height{
                    -webkit-box-align: center;
                    align-items: center;
                    display: -webkit-box;
                    display: flex;
                }
                .waitpage .application-wrap{
                    -webkit-box-flex: 1;
                    flex: 1 1 auto;
                    -webkit-backface-visibility: hidden;
                    backface-visibility: hidden;
                    display: -webkit-box;
                    display: flex;
                    -webkit-box-orient: vertical;
                    -webkit-box-direction: normal;
                    flex-direction: column;
                    min-height: 100vh;
                    max-width: 100%;
                    position: relative;
                }
                .waitpage .layout{
                    -webkit-box-flex: 1;
                    flex: 1 1 auto;
                    -webkit-box-orient: horizontal;
                    -webkit-box-direction: normal;
                    flex-direction: row;
                    -webkit-box-align: center;
                    align-items: center;
                    flex-wrap: wrap;
                    min-width: 0;
                }
                .waitpage .animate-box{
                    -webkit-box-flex: 1;
                    flex: 1 1 auto;
                    max-width: 530px;
                    margin-left: auto!important;
                    margin-right: auto!important;
                    text-align:center
                }
                div.circle {
                    border-radius: 50%;
                    background: #fff;
                    width: 13px;
                    height: 13px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                }

                div.circle:first-child {
                    animation: upload 0.8s cubic-bezier(0.39, 0.56, 0.57, 1) 0s infinite alternate-reverse;
                    background-color: #4285f4;
                    margin-right: 6px;
                }

                div.circle:nth-child(2) {
                    animation: upload 1.3s cubic-bezier(0.39, 0.56, 0.57, 1) 0s infinite alternate-reverse;
                    background-color: #34a853;
                    margin-right: 3px;
                }

                div.circle:nth-child(3) {
                    animation: upload 1.1s cubic-bezier(0.39, 0.56, 0.57, 1) 0s infinite alternate-reverse;
                    background-color: #fbbc05;
                    margin-left: 3px;
                }

                div.circle:last-child {
                    animation: upload 1.45s cubic-bezier(0.39, 0.56, 0.57, 1) 0s infinite alternate-reverse;
                    background-color: #ea4335;
                    margin-left: 6px;
                }
                @keyframes upload {
                    from { transform: translateY(35px); }
                    to { transform: translateY(-35px); }
                }

                h2{ display: inline-block;
                    padding: 5px;
                    margin: 36px auto 0;
                    text-transform: uppercase;
                    font-size: 15px;
                    color: #242424;
                }
            </style>
        </head>
        <body>
        <div class="waitpage">
            <div class="application-wrap">
                <div class="container fill-height">
                    <div class="layout">
                        <div class="animate-box">
                            <div class="circle-box">
                                <div class="circle"></div>
                                <div class="circle"></div>
                                <div class="circle"></div>
                                <div class="circle"></div>
                            </div>
                            <h2><?php _e( 'Transaction is in process', 'wc-2co' ) ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            function waitJQuery() {
                if ( window.jQuery ) {

                    jQuery(function($){

                        function getwaitpageData() {

                            $.ajax({
                                url      : ydAjax.ajaxurl,
                                type     : 'POST',
                                dataType : 'json',
                                async    : true,
                                data     : {
                                    action   : 'wc_yd_2co_action_wait',
                                    order_id : '<?php echo $order_id ?>'
                                },
                                success  : function (data) {

                                    if( data.hasOwnProperty('redirect') ) {

                                        setTimeout(
                                            function () {
                                                window.location.replace( data.redirect );
                                            },
                                            10000
                                        );
                                    } else {
                                        setTimeout(
                                            function () {
                                                getwaitpageData();
                                            },
                                            10000
                                        );
                                    }
                                }
                            });
                        }

                        getwaitpageData();
                    });
                } else { window.setTimeout( waitJQuery, 200 ); }
            }
            waitJQuery();
        </script>
        
        <?php wp_footer();?>
        </body>
        </html>
        
        <?php
        
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
    
    protected function get_real_state( $cc, $state ) {
        
        if ( 'US' === $cc ) {
            return $state;
        }
        
        $states = WC()->countries->get_states( $cc );
        
        if ( isset( $states[ $state ] ) ) {
            return $states[ $state ];
        }
        
        return $state;
    }
}