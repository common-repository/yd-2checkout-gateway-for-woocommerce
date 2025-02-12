<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return apply_filters( 'wc_yd_wc_2co_settings', [
    'enabled' => [
        'title'   => __( 'Enable 2Checkout', 'wc-2co' ),
        'type'    => 'checkbox',
        'label'   => __( 'Yes', 'wc-2co' ),
        'default' => 'yes'
    ],
    'title'   => [
        'title'       => __( 'Title', 'wc-2co' ),
        'type'        => 'text',
        'description' => __( 'This controls the title which the user sees during checkout', 'wc-2co' ),
        'default'     => __( 'Credit Card (2Checkout)', 'wc-2co' ),
        'desc_tip'    => false,
    ],
    'description' => [
        'title'       => __( 'Description', 'wc-2co' ),
        'type'        => 'text',
        'description' => __( 'This controls the description which the user sees during checkout', 'wc-2co' ),
        'default'     => __( 'Pay with your credit card via 2Checkout', 'wc-2co' ),
        'desc_tip'    => false,
    ],
    'testMode' => [
        'title'   => __( 'Enable demo mode', 'wc-2co' ),
        'type'    => 'checkbox',
        'label'   => __( 'Yes', 'wc-2co' ),
        'default' => 'no'
    ],
    'locale' => [
        'title'       => __( 'Language', 'wc-2co' ),
        'label'       => __( 'Language', 'wc-2co' ),
        'description' => __( 'Select default language for checkout modal.', 'wc-2co' ),
        'default'     => 'en',
        'type'        => 'select',
        'desc_tip'    => false,
        'options'     => [
            'ar' 	=> __( 'Arabic', 'wc-2co' ),
            'pt-br' => __( 'Brazilian Portuguese', 'wc-2co' ),
            'bg' 	=> __( 'Bulgarian', 'wc-2co' ),
            'zy'	=> __( 'Chinese Mandarin Traditional', 'wc-2co' ),
            'zh'	=> __( 'Chinese Simplified(Cantonese)', 'wc-2co' ),
            'hr'	=> __( 'Croatian', 'wc-2co' ),
            'cs'	=> __( 'Czech', 'wc-2co' ),
            'da'	=> __( 'Danish', 'wc-2co' ),
            'nl'	=> __( 'Dutch', 'wc-2co' ),
            'en'	=> __( 'English', 'wc-2co' ),
            'fi'	=> __( 'Finnish', 'wc-2co' ),
            'fr'	=> __( 'French', 'wc-2co' ),
            'de'	=> __( 'German', 'wc-2co' ),
            'el'	=> __( 'Greek', 'wc-2co' ),
            'he'	=> __( 'Hebrew', 'wc-2co' ),
            'hi'	=> __( 'Hindi', 'wc-2co' ),
            'hu'	=> __( 'Hungarian', 'wc-2co' ),
            'it'	=> __( 'Italian', 'wc-2co' ),
            'ja'	=> __( 'Japanese', 'wc-2co' ),
            'ko'	=> __( 'Korean', 'wc-2co' ),
            'no'	=> __( 'Norwegian', 'wc-2co' ),
            'fa'	=> __( 'Persian', 'wc-2co' ),
            'pl'	=> __( 'Polish', 'wc-2co' ),
            'pt'	=> __( 'Portuguese', 'wc-2co' ),
            'ro'	=> __( 'Romanian', 'wc-2co' ),
            'ru'	=> __( 'Russian', 'wc-2co' ),
            'sr'	=> __( 'Serbian', 'wc-2co' ),
            'sk'	=> __( 'Slovak', 'wc-2co' ),
            'sl'	=> __( 'Slovenian', 'wc-2co' ),
            'es'	=> __( 'Spanish', 'wc-2co' ),
            'sv'	=> __( 'Swedish', 'wc-2co' ),
            'th'	=> __( 'Thai', 'wc-2co' ),
            'tr'	=> __( 'Turkish', 'wc-2co' ),
        ],
    ],
    'merchantCode' => [
        'title'       => __( 'Merchant Code', 'wc-2co' ),
        'type'        => 'text',
        'description' => __( 'Enter your merchant code.', 'wc-2co' ),
        'default'     => '',
        'desc_tip'    => false,
    ],
    'secretKey' => [
        'title'       => __( 'Secret Key', 'wc-2co' ),
        'type'        => 'text',
        'description' => __( 'Enter secret key from API settings.', 'wc-2co' ),
        'default'     => '',
        'desc_tip'    => false,
    ],
    'brand' => [
        'title'       => __( 'Brand Name', 'wc-2co' ),
        'type'        => 'text',
        'description' => __( 'Enter your store name or brand.', 'wc-2co' ),
        'default'     => '',
        'desc_tip'    => false,
    ],
    'ipnUrl' => [
        'title'       => __( 'IPN URL', 'wc-2co' ),
        'type'        => 'text',
        'description' => __( 'Insert the value of this field in IPN URL in your 2CO account -> Integration -> Webhooks & API -> IPN Settings.', 'wc-2co' ),
        'default'     => home_url( '/?wc_2co=' . md5( wc_2co_get_domain() ) ),
        'desc_tip'    => false,
    ],
] );
