<?php
class Kvell_Extend_Warranty_Public {
    public $plugin_name;
    public $version;
    public $extend_assets;
    
    public function __construct() {        
        $this->version = KVELL_VERSION;
        $this->plugin_name = KVELL_PLUGIN_NAME;
        $this->extend_assets=KVELL_URL."public/extended/";
        $this->init_hooks();       
    }

    public function init_hooks(){
        # Add a custom product data tab        
        add_action( 'wp_enqueue_scripts', array($this,'public_scripts'));
        add_action( 'woocommerce_before_add_to_cart_button', array($this,'warrenty_fields_render'), 10);
        add_action( 'woocommerce_before_calculate_totals',array($this,'adjust_cart_item_pricing'));
        add_filter( 'woocommerce_add_cart_item_data',array( $this, 'add_fields_to_cart_item' ), 10, 3 );
        add_filter( 'woocommerce_get_item_data', array($this,'display_extended_warrenty'), 20, 2 );
        add_filter('woocommerce_cart_item_price', array($this,'display_cart_items_custom_price_details'), 20, 3 );
        add_filter( 'woocommerce_cart_item_subtotal', array($this,'add_warranty_in_subtotal'), 99, 3 );
        add_action( 'woocommerce_add_order_item_meta', array($this,'order_add_extended_warrenty'), 10 , 2 );                  
        // woocommerce_checkout_create_order_line_item        
        // add_action( 'woocommerce_calculate_totals', array( $this,'woocommerce_calculate_totals' ), 30 );

        add_filter( 'woocommerce_cart_subtotal', array( $this,'woocommerce_update_sub_total' ), 99, 3 );
        add_filter( 'woocommerce_cart_total', array( $this,'woocommerce_update_total' ), 99, 3 );
        add_action( 'woocommerce_checkout_create_order_line_item', array($this,'add_data_to_order_items'), 10, 4 );
		add_action( 'wp_ajax_nopriv_get_billing_field', 'billing_detail_ajax_handler' );
        add_action( 'wp_ajax_get_billing_field',  'billing_detail_ajax_handler' );      
    }

    public function woocommerce_update_sub_total(  $cart_subtotal, $compound, $obj  ){
        $total_price = 0 ;
        foreach ( $obj->cart_contents as $key => $product ) :             
            $product_id = $product['product_id'];
            
            if( isset($product['extended_warrenty']) ) {
                $warrenty_key=$product['extended_warrenty'];
                if(!$this->current_warrenty($product_id,$warrenty_key)) return $product_price;
                $warrenty=$this->current_warrenty($product_id,$warrenty_key);            
                $total_price = $total_price+$product['line_total']+$warrenty['extended_warrenty_price'];
            }else{
                $total_price = $total_price+$product['line_total'];
            }

        endforeach; 

        $cart_subtotal = wc_price($total_price);
        return $cart_subtotal;
    }

    public function woocommerce_update_total(  $cart_total_price  ){
        $total_warranty = 0 ;
        foreach ( WC()->cart->cart_contents as $key => $product ) : 
            $product_id = $product['product_id'];
            if( isset($product['extended_warrenty']) ) {
                $warrenty_key=$product['extended_warrenty'];
                if(!$this->current_warrenty($product_id,$warrenty_key)) return $product_price;
                $warrenty=$this->current_warrenty($product_id,$warrenty_key);            
                $total_price = $total_price+$product['line_total']+$warrenty['extended_warrenty_price'];
            }else{
                $total_price = $total_price+$product['line_total'];
            }

        endforeach; 
        $cart_total_price = wc_price($total_price);
        return $cart_total_price;
        /*
        
        foreach ( $obj->cart_contents as $key => $product ) :             
            $product_id = $product['product_id'];
            
            if( isset($product['extended_warrenty']) ) {
                $warrenty_key=$product['extended_warrenty'];
                if(!$this->current_warrenty($product_id,$warrenty_key)) return $product_price;
                $warrenty=$this->current_warrenty($product_id,$warrenty_key);            
                $warrenty['extended_warrenty_price']; 
                $total_price = $total_price+$product['line_total']+$warrenty['extended_warrenty_price'];
            }else{
                $total_price = $total_price+$product['line_total'];
            }

        endforeach; 

        $cart_total = wc_price($total_price);
        */
        return $cart_total_price;
    }

    function public_scripts(){
        global $wpdb;
        wp_enqueue_script('extended_js_public', $this->extend_assets."public.js",array('jquery'));
        wp_enqueue_style('extended_css_public', $this->extend_assets."public.css");                
		$user_id = get_current_user_id();
		$all_meta_for_user = get_user_meta( $user_id );        	
		wp_localize_script( 'extended_kvell_js', 'all_meta_for_user', $all_meta_for_user);
    }

    public function add_warranty_in_subtotal( $subtotal, $cart_item, $cart_item_key ){
        //echo $subtotal; exit;         
        //echo '<pre>'; print_r( $cart_item ); exit; 
        if( isset($cart_item['extended_warrenty']) ) {
            $product = $cart_item['data'];
            $product_price  = wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) );
            $warrenty_key=$cart_item['extended_warrenty'];
            if(!$this->current_warrenty($product->get_id(),$warrenty_key)) return $product_price;
            $warrenty=$this->current_warrenty($product->get_id(),$warrenty_key);            
            $subtotal = wc_price( ($cart_item['data']->get_price() * $cart_item['quantity'])+$warrenty['extended_warrenty_price'] ); 
        }

        return $subtotal;
    }

    function warrenty_fields_render(){
        include KVELL_PATH."public/extended/extend_warrenty_front_end_field.php";	
    }  

    function adjust_cart_item_pricing($cart_obj){
        foreach( $cart_obj->get_cart() as $key=>$item ) {
            if(!isset($item['extended_warrenty'])) continue;
            $product = wc_get_product($item['product_id']);
            $warrenty_key=$item['extended_warrenty'];          
            if(!$this->current_warrenty($product->get_id(),$warrenty_key)) continue;
            $warrenty=$this->current_warrenty($product->get_id(),$warrenty_key);                   
            $base=$product->get_price();
            $warrenty_price=$warrenty['extended_warrenty_price'];
            $item['data']->set_price($base);
        }
    }

    function add_fields_to_cart_item($cart_item_data, $product_id, $variation_id){      
        if(!isset($_REQUEST['extended_warrenty']))
            return $cart_item_data;                
            
        $cart_item_data['extended_warrenty'] = $_REQUEST['extended_warrenty'];
        return $cart_item_data;
    }
                                                
    function display_cart_items_custom_price_details( $product_price, $cart_item, $cart_item_key ){
        if( isset($cart_item['extended_warrenty']) ) {
            $product = $cart_item['data'];
            $product_price  = wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) );
            $warrenty_key=$cart_item['extended_warrenty'];
            if(!$this->current_warrenty($product->get_id(),$warrenty_key)) return $product_price;
            $warrenty=$this->current_warrenty($product->get_id(),$warrenty_key);
            $product_price .= '<br>' . wc_price( $warrenty['extended_warrenty_price'] ).'&nbsp;';
            $product_price .= __("Waranty Price:", KVELL_PLUGIN_NAME );
        }
        return $product_price;
    }

    function display_extended_warrenty($item_data,$cart_item){
        if(!isset($cart_item['extended_warrenty'])) return $item_data;
        $product_id=$cart_item['product_id'];        
        $warrenty_key=$cart_item['extended_warrenty'];
        if(!$this->current_warrenty($product_id,$warrenty_key)) return $item_data;
        $warrenty=$this->current_warrenty($product_id,$warrenty_key);
    
        $item_data[] = array(
            'key'     => __( 'Extended Warrenty', KVELL_PLUGIN_NAME ),
            'value'   => $warrenty['extended_warrenty'],
            'display' => ''
        );   
    
        $item_data[] = array(
            'key'     => __( 'Warrenty Price', KVELL_PLUGIN_NAME ),
            'value'   => $warrenty['extended_warrenty_price'],
            'display' => ''
        );   
        return $item_data;
    }

    public function add_data_to_order_items( $item, $cart_item_key, $values, $order ) {
        $item->add_meta_data( __( 'Extended Warrenty', KVELL_PLUGIN_NAME ), '10 years' );
        /*
        if( isset( $values['extended_warrenty'] )){
            $item->add_meta_data( __( 'Extended Warrenty', KVELL_PLUGIN_NAME ), $values['extended_warrenty'] );
        }
                                
        if( isset( $values['extended_warrenty_price'] )){
            $item->add_meta_data( __( 'Warrenty Price', KVELL_PLUGIN_NAME ), $values['extended_warrenty_price'] );
        }
        */
    }

    function order_add_extended_warrenty( $itemID,$cart_item){ 
        wc_add_order_item_meta( $itemID, 'Custom Price','50'); 
        /*         
        if(!isset($cart_item['extended_warrenty'])) return; 
        $product_id=$cart_item['product_id'];        
        $warrenty_key=$cart_item['extended_warrenty'];
        if(!$this->current_warrenty($product_id,$warrenty_key)) return;
        $warrenty=$this->current_warrenty($product_id,$warrenty_key);      
        //echo '<pre>'; print_r( $warrenty );

        wc_add_order_item_meta( $itemID, 'Extended Warrenty',$warrenty['extended_warrenty']);          
        wc_add_order_item_meta( $itemID, 'Warrenty Price',$warrenty['extended_warrenty_price']);         
        */
    }

    function current_warrenty($product_id,$warrenty_key){
        $warrenties=get_post_meta($product_id,'warranties',true);  
        if(!$warrenties) return false;
        foreach($warrenties['extended_warrenty'] as $key=>$value){          
            $title=$warrenties['extended_warrenty'][$key];
            $start=$warrenties['extended_start_price'][$key];
            $end=$warrenties['extended_end_price'][$key];            
            $price=$warrenties['extended_warrenty_price'][$key];            
            if($warrenty_key==$key){
                $warrenty=array(
                    'extended_warrenty'=>$title,
                    'extended_start_price'=>$start,
                    'extended_end_price'=>$end,
                    'extended_warrenty_price'=>$price,
                );
                return $warrenty;
            }
        }
        return false;
    }

    function warrenty_applicable($product_id,$amount=0){
        $warrenties=get_post_meta($product_id,'warranties',true);  
        if(!$warrenties) return false;
        foreach($warrenties['extended_warrenty'] as $key=>$value){          
            $title=$warrenties['extended_warrenty'][$key];
            $start=$warrenties['extended_start_price'][$key];
            $end=$warrenties['extended_end_price'][$key];            
            if($amount>=$start && $amount<=$end){
                $warrenty=array(
                    'extended_warrenty'=>$title,
                    'extended_start_price'=>$title,
                    'extended_end_price'=>$title,
                );
                return $warrenty;
            }
        }
        return false;        
    }

}

return new Kvell_Extend_Warranty_Public();