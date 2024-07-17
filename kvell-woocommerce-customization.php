<?php
/**
 * @wordpress-plugin
 * Plugin Name:Kvell Customization
 * 
 * */

define( 'KVELL_VERSION', '2.1' );
define( 'KVELL_PLUGIN_NAME', 'kvell-woocommerce-customization' );

define( 'KVELL_URL', plugins_url( '/', __FILE__ ) );
define( 'KVELL_PATH', plugin_dir_path( __FILE__ ) );

define( 'KVELL_PUBLIC', KVELL_URL.'public/' );




require_once dirname( __FILE__ ) . '/functions.php';

require_once dirname( __FILE__ ) . '/public/class-kvell-order-sample-product-public.php';

new Kvell_Order_Sample_Product_Public();

require_once dirname( __FILE__ ) . '/public/class-kvell-customization-public.php';

new Kvell_Customization_Public();

require_once dirname( __FILE__ ) . '/public/class-kvell-local-pickup-public.php';

new Kvell_Local_Pickup_Public();

require_once dirname( __FILE__ ) . '/public/class-kvell-extended-warranty-admin.php';

require_once dirname( __FILE__ ) . '/public/class-kvell-extended-warranty-public.php';








