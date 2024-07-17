<?php

class Kvell_Local_Pickup_Public {

    public $plugin_name;

    public $version;

    public $local_pickup_discount=0;

    public function __construct() {
        $this->init_hooks();
    }

    public function init_hooks(){
        add_action( 'init', array($this,'registering_my_session'));
        add_filter( 'script_loader_tag', array($this,'gioga_add_defer_attribute'), 10, 2);
        add_filter( 'woocommerce_checkout_fields', array($this,'checkout_user_location'), 10 );
        add_filter( 'wcfmmp_is_allow_checkout_user_location', array($this,'disable_checkout_user_location'), 60 );
        add_action( 'woocommerce_checkout_update_order_meta', array( &$this, 'pickup_checkout_user_location_save' ), 50 );
        add_action( 'woocommerce_after_checkout_billing_form', array( &$this, 'local_pick_up_map'), 40 );
        add_action( 'wp_enqueue_scripts', array( $this, 'location_script' ),40 );
        add_action( 'wp_head',  array( $this,'remove_default_local_pickup') );
        add_action( 'wp_ajax_wc_set_location_discount',        array( $this, 'set_location_discount' ) );
        add_action( 'wp_ajax_nopriv_wc_set_location_discount', array( $this, 'set_location_discount' ) );
        add_action( 'wp_ajax_calculate_address',        array( $this, 'calculate_address' ) );
        add_action( 'wp_ajax_nopriv_calculate_address', array( $this, 'calculate_address' ) );
        add_filter( 'woocommerce_cart_calculate_fees', array( $this, 'add_discount_fees'), 10, 1 );
        add_filter( 'wc_local_pickup_plus_validate_pickup_checkout', array($this,'validate_location_pickup_posted_data'), 60,3 );
        add_action( 'woocommerce_saved_order_items', array($this,'action_woocommerce_saved_order_items'), 10, 2 ); 
        add_action( 'woocommerce_before_order_itemmeta', array( $this, 'reset_sessiton_fee' ), 1, 2 );
        add_filter( 'wc_get_template',array( $this, 'group_lpp_method_label' ), 10, 6 );
        add_filter( 'wc_local_pickup_plus_settings',array( $this, 'local_pickup_settings' ), 10);
    }

    function registering_my_session() {
        if( !session_id() )
          session_start();
    }
    
    function group_lpp_method_label($located, $template_name, $args, $template_path, $default_path ){
        if ( 'cart/cart-shipping.php' === $template_name && is_checkout()) {
            ?>
            <?php if(!isset($_SESSION['local_pickup_discount']) || $_SESSION['local_pickup_discount']==0): ?>
            <style>
                .review-order-totals .shop_table{
                    display:none;
                }
            </style>
            <?php endif; ?>
            <p class="shipping_kvell">Shipping Address : <b>   
            <?php           
            if (  isset($_SESSION['delivery_option_new']) && is_checkout()){
                $tmp=$_SESSION['delivery_option_new'];

                if(isset($_SESSION[$tmp.'_address'])) echo $_SESSION[$tmp.'_address'];
            }
            ?>        
        </b></p>
            <?php
        }

        return $located;
    }

    function reset_sessiton_fee($a,$b){           
        $_SESSION['local_pickup_discount']=0;
    }

    function local_pickup_settings($form_fields){

        $form_fields['discount_applicable'] = [
            'title'   => __( 'Discount Applicable Price', 'woocommerce-shipping-local-pickup-plus' ),
            'type'    => 'number',
            'label'   => __( 'Discount Applicable Price', 'woocommerce-shipping-local-pickup-plus' ),
            'default' => 'no',
        ];
        return $form_fields;

    }

    function validate_location_pickup_posted_data($error_messages, $package_id, $posted_data ){
        if($_POST['kvell_delivery']=='local_pickup'){
            $_POST['_shipping_method_pickup_location_id'][0]=$_POST['pickup_location_addresss'];
        }
        else{
            $_POST['_shipping_method_pickup_location_id'][0]=null;
        }    
    }

    function action_woocommerce_saved_order_items( $order_id, $items){
        $_SESSION['local_pickup_discount']=0; 
    }
    
    function add_discount_fees( $cart ) {
        if (  isset($_SESSION['local_pickup_discount']) && is_checkout())
        $this->local_pickup_discount = (float) $_SESSION['local_pickup_discount'];
        $cart->add_fee( 'Local Pickup discount', $this->local_pickup_discount );
    }

    function set_location_discount(){
        $_SESSION['delivery_option_new']=$_POST['val'];
        $this->calculate_address();     
        $legacy_options = get_option( 'woocommerce_local_pickup_plus_settings' );
        $discount_applicable=$legacy_options['discount_applicable'];
        $cart_total=WC()->cart->total;
        if($_POST['val']=='local_pickup' && $cart_total>=$discount_applicable){
            $default = '';
            // we don't use $this->get_option() as this is a composite option handled differently
            $value   = get_option( 'woocommerce_local_pickup_plus_default_price_adjustment', $default );            
            $_SESSION['local_pickup_discount']=$value;

            $this->local_pickup_discount=$value;

        }else{
            $this->local_pickup_discount=0;
            $_SESSION['local_pickup_discount']=0;  
        }
    }

    function calculate_address(){
        #setting default delivery option if not set
        if(!isset($_SESSION['delivery_option_new'])) $_SESSION['delivery_option_new']='same';
        if(isset($_POST['data'])){
            #parsing quer data as array
            parse_str($_POST['data'], $data);
            #processing billing address as same address
            $same_address="";
            if(!empty($data['billing_address_1'])) $same_address.=$data['billing_address_1'].", ";
            if(!empty($data['billing_address_2'])) $same_address.=$data['billing_address_2'] .", ";
            if(!empty($data['billing_postcode'])) $same_address.=$data['billing_postcode'] .", ";
            if(!empty($data['billing_city'])) $same_address.=$data['billing_city'] .", ";
            if(!empty($data['billing_state'])) $same_address.=$data['billing_state'] .", ";
            if(!empty($data['billing_country'])) $same_address.=$data['billing_country'] ."";
            #processing google api pickup address
            if(!empty($data['pickup_location_addresss'])) $pickup_id=$data['pickup_location_addresss'] ;            
            global $wpdb;
            $location=$wpdb->get_row("SELECT l.* FROM `{$wpdb->prefix}woocommerce_pickup_locations_geodata` as l JOIN {$wpdb->prefix}posts as p on p.ID=l.post_id WHERE p.post_status='publish' and l.post_id=$pickup_id");            
            $address_line=$location->address_1;
            $pincode=$location->postcode;
            $city=$location->city;
            $state=$location->state;
            $country=$location->country;
            $pickup_address="";
            if(!empty($address_line)) $pickup_address.=$address_line .", ";
            if(!empty($pincode)) $pickup_address.=$pincode .", ";
            if(!empty($city)) $pickup_address.=$city .", ";
            if(!empty($state)) $pickup_address.=$state .", ";
            if(!empty($country)) $pickup_address.=$country ."";
            #processing shipping address as different address
            $shipping_address="";
            if(!empty($data['shipping_address_1'])) $shipping_address.=$data['shipping_address_1'].", ";
            if(!empty($data['shipping_address_2'])) $shipping_address.=$data['shipping_address_2'] .", ";
            if(!empty($data['shipping_postcode'])) $shipping_address.=$data['shipping_postcode'] .", ";
            if(!empty($data['shipping_city'])) $shipping_address.=$data['shipping_city'] .", ";
            if(!empty($data['shipping_state'])) $shipping_address.=$data['shipping_state'] .", ";
            if(!empty($data['shipping_country'])) $shipping_address.=$data['shipping_country'] ."";
            #Saving result of the applicable address in to sessions            
            $_SESSION['same_address']=$same_address;
            $_SESSION['local_pickup_address']=$pickup_address;
            $_SESSION['different_address_address']=$shipping_address;
        }
        return;        
    }

    function post_unserialize( $key ){
        $post_data = array();
        $post_data = $_POST[ $key ];
        unset($_POST[ $key ]);
        parse_str($post_data, $post_data);
        $_POST = array_merge($_POST, $post_data);
    }
            
    public function remove_default_local_pickup(){   
        $this->remove_filters_with_method_name('woocommerce_after_shipping_rate','output_pickup_package_form',999);        
        $this->remove_filters_with_method_name('woocommerce_cart_calculate_fees','apply_pickup_discount',10);  
        $this->remove_filters_with_method_name('woocommerce_shipping_packages','add_template_variables_to_packages',10);  
        $this->remove_filters_with_method_name('woocommerce_before_template_part','add_package_group_row_start',10);  		
    }

    public function disable_checkout_user_location($default){
        return false;
    }

    function pickup_checkout_user_location_save( $order_id ) {
			if ( ! empty( $_POST['kvell_delivery'] ) ) {
				update_post_meta( $order_id, '_kvell_delivery', sanitize_text_field( $_POST['kvell_delivery'] ) );
			}
			if ( ! empty( $_POST['pickup_location_addresss'] ) ) {
				update_post_meta( $order_id, '_pickup_location_addresss', sanitize_text_field( $_POST['pickup_location_addresss'] ) );
			}		
    }
   
    function checkout_user_location($fields){
        $default='same';
        if ( isset($_SESSION['delivery_option_new']) ){
            $default=$_SESSION['delivery_option_new'];
        }
        $fields['billing']['kvell_delivery'] = array(
            'type'      =>  'radio',
            'label'     => __( 'Delivery Option', KVELL_PLUGIN_NAME ),
            'placeholder'   => _x( 'Insert your address ..', 'placeholder', KVELL_PLUGIN_NAME ),
            'required'  => true,
            'class'     => array('form-row-wide'),
            'clear'     => true,
            'priority'  => 9999,
            'default'=>$default,
            'options'   =>array(
                'same' => 'Same Address',
                'local_pickup' => 'Local Pickups.',
                'different_address' => 'Different address'
            )
        );
        $fields['billing']['pickup_location_addresss'] = array(
            'type'      =>  'select',
            'label'     => __( 'Pick UP address', KVELL_PLUGIN_NAME ),
            'placeholder'   => _x( 'Select pickup address ..', 'placeholder', KVELL_PLUGIN_NAME ),
            'required'  => false,
            'class'     => array('select2'),
            'clear'     => true,
            'priority'  => 10000,
            'options'   =>$this->get_pickup_formatted_address()
        );
        return $fields;
    }
    /**
	 * Local PickUp Map
	 */
	function local_pick_up_map( $checkout ) {
            ?>
			<div class="woocommerce-billing-fields__field-wrapper">
				<div class="local-pick-up-map" id="local-pick-up-map"></div>
				<div class="local-pick-up-map" id="local-pick-up-selected-map"></div>
			</div>
			<?php		
    }

    function get_pickup_formatted_address(){
        global $wpdb;
        $locations=$wpdb->get_results("SELECT l.* FROM `{$wpdb->prefix}woocommerce_pickup_locations_geodata` as l JOIN {$wpdb->prefix}posts as p on p.ID=l.post_id WHERE p.post_status='publish'");
        $location_meta=array();
        foreach($locations as $location){        
            $address="";
            $address_line=$location->address_1;
            $pincode=$location->postcode;
            $city=$location->city;
            $state=$location->state;
            $country=$location->country;
            if(!empty($address_line)) $address.=$address_line .", ";
            if(!empty($pincode)) $address.=$pincode .", ";
            if(!empty($city)) $address.=$city .", ";
            if(!empty($state)) $address.=$state .", ";
            if(!empty($country)) $address.=$country ."";            
            $location_meta[$location->post_id]=$address;
        }
        return $location_meta;
    }
    
    function location_script(){
        wp_enqueue_script( 'wc-picup-custom-js', KVELL_PUBLIC. 'js/custom.js');
        $data=array();
        $data['ajax_url']= admin_url( 'admin-ajax.php' ); 
        $data['ajax_action']= "wc_set_location_discount"; 
        $data['form_update']= "calculate_address"; 
        $default='same';
        if ( isset($_SESSION['delivery_option_new']) ){
            $default=$_SESSION['delivery_option_new'];
        }
        $data['delivery']=$default;
        wp_localize_script( 'wc-picup-custom-js', 'pickpup_cart_discount',$data );
        if(is_cart()){
            unset($_SESSION['delivery_option_new']);
            $_SESSION['local_pickup_discount']=0;
        }
        if( is_checkout()) {
            $data['pickup_locations']=array();        
            global $wpdb;
            $locations=$wpdb->get_results("SELECT l.* FROM `{$wpdb->prefix}woocommerce_pickup_locations_geodata` as l JOIN {$wpdb->prefix}posts as p on p.ID=l.post_id WHERE p.post_status='publish'");
            foreach($locations as $location){
                $location_meta=array();
                $address="";
                $address_line=$location->address_1;
                $pincode=$location->postcode;
                $city=$location->city;
                $state=$location->state;
                $country=$location->country;
                if(!empty($address_line)) $address.=$address_line .", ";
                if(!empty($pincode)) $address.=$pincode .", ";
                if(!empty($city)) $address.=$city .", ";
                if(!empty($state)) $address.=$state .", ";
                if(!empty($country)) $address.=$country ."";
                $location_meta['address']=$address;
                $location_meta['pick_id']=floatval($location->post_id);
                $location_meta['lat']=floatval($location->lat);
                $location_meta['lng']=floatval($location->lon);            
                array_push($data['pickup_locations'],$location_meta);
            }
            wp_enqueue_script( 'googleapis', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBUswWMnnqwD-SoBFUD_KxzWBnVq53aEe8&callback=initMap&libraries=geometry&v=weekly', array(), null, true );
            wp_enqueue_script( 'wc-picup-location', KVELL_PUBLIC. 'js/location.js');        
            wp_localize_script('wc-picup-location','map_pickup_data',$data);    
        }
    }

    function gioga_add_defer_attribute($tag, $handle) {
        if ( 'googleapis' !== $handle )
        return $tag;
        return str_replace( ' src', ' defer src', $tag );
    }

    public function remove_filters_with_method_name( $hook_name = '', $method_name = '', $priority = 0 ) {
        global $wp_filter;
        // Take only filters on right hook name and priority
        if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
            return false;
        }
        // Loop on filters registered
        foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
            // Test if filter is an array ! (always for class/method)
            if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
                // Test if object is a class and method is equal to param !
                if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && $filter_array['function'][1] == $method_name ) {
                    // Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
                    if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
                        unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
                    } else {
                        unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
                    }
                }
            }
        }
        return false;
    }

}
