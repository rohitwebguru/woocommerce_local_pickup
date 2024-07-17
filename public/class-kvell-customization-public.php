<?php
class Kvell_Customization_Public {

    public $plugin_name;

    public $version;


    public function __construct() {

        $this->init_hooks();


    }


    public function init_hooks(){

//    	if ( has_term( array( 'laminate', 'luxury-vinyl'), 'product_cart' ) ) 

		    add_filter( 'woocommerce_add_to_cart_validation', array($this,'add_to_cart_action'), 10 , 4 );

		    add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'render_cart_key' ), 2 );

		    add_filter( 'woocommerce_locate_template',array($this,'kvell_locate_template'), 5 , 3 );

		    add_filter( 'woocommerce_get_item_data', array($this,'display_total_user_area'), 20, 2 );

		    add_action( 'woocommerce_add_order_item_meta', array($this,'kvell_add_user_area_order_data'), 10 , 2 );

		    add_action( 'woocommerce_before_main_content',array($this,'edit_cart_product'),40);

		    add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_edit_product_scripts' ),40 );

		    add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 3 );


    }

    function add_cart_item_data($cart_item_data, $product_id, $variation_id){

        $cart_item_data['pricing_item_meta_data']=array(
            '_quantity'=>$_POST['_quantity'],
            'length'=>$_POST['length_needed'],
            'width'=>$_POST['width_needed'],
        );

        return $cart_item_data;

    }


    function enqueue_edit_product_scripts(){

        wp_enqueue_script( 'wc-user-measurement-product', KVELL_PUBLIC. 'js/wc-user-measurement-calculator.js' ,array( 'jquery', 'jquery-cookie', 'jquery-tiptip' ));

        if(!isset($_GET['key'])){
            wp_enqueue_script( 'wc-add-product', KVELL_PUBLIC. 'js/add-product.js' ,array( 'jquery', 'jquery-cookie', 'jquery-tiptip' ));
        }else{
            wp_enqueue_script( 'wc-edit-product', KVELL_PUBLIC. 'js/edit-product.js' ,array( 'jquery', 'jquery-cookie', 'jquery-tiptip' ));

            $data=array();
            global $woocommerce;
            $key=$_GET['key'];
            $item = $woocommerce->cart->get_cart_item($key);
            $data['length_needed']=$item['pricing_item_meta_data']['length'];
            $data['width_needed']=$item['pricing_item_meta_data']['width'];
            $data['quantity']=$item['quantity'];
            if(isset($item['extended_warrenty'])){
                $data['extended_warrenty']=$item['extended_warrenty']; 
            }
            wp_localize_script( 'wc-edit-product', 'pricing_item_meta_data', $data );
        }
       

    }

    function render_cart_key(){

        if(!isset($_GET['key'])){
            return;
        }

        $key=$_GET['key'];


        echo "<input type='hidden' value='{$key}' name='key' />";


    }

    function add_to_cart_action(){

     


        if ( 
			! isset( $_REQUEST['add-to-cart'] ) || 
			! is_numeric( wp_unslash( $_REQUEST['add-to-cart'] ) )
		)			 
		{
			return;
        }


        if(isset( $_REQUEST['key'] )){
 
            $key=$_REQUEST['key'];

            WC()->cart->remove_cart_item($key);

        }

    }

    function edit_cart_product(){
        if(!isset($_GET['key'])){
            return;
        }

        global $woocommerce;

        $key=$_GET['key'];

 

        $item = $woocommerce->cart->get_cart_item($key);
        // echo "<pre>";
        // print_r($item);
        // echo "</pre>";
        
        
      


    }

   


    function display_total_user_area($item_data, $cart_item ){

        $user_area=0;
        if(!$cart_item['pricing_item_meta_data']['length']) $cart_item['pricing_item_meta_data']['length']=1;
        if(!$cart_item['pricing_item_meta_data']['width']) $cart_item['pricing_item_meta_data']['width']=1;
        if(isset($cart_item['pricing_item_meta_data']['length']) && isset($cart_item['pricing_item_meta_data']['width'])){
            $user_area=$cart_item['pricing_item_meta_data']['length'] * $cart_item['pricing_item_meta_data']['width'];
            $item_data[] = array(
                'key'     => __( 'Total User Area  (sq. ft.)', KVELL_PLUGIN_NAME ),
                'value'   => wc_clean( $user_area ),
                'display' => ''
            );
        }


        return $item_data;

    }
   

    function kvell_add_user_area_order_data( $itemID, $values ) {

		if(isset($values['pricing_item_meta_data']['length']) && isset($values['pricing_item_meta_data']['width'])){

            $user_area=$values['pricing_item_meta_data']['length'] * $values['pricing_item_meta_data']['width'];

			wc_add_order_item_meta( $itemID, 'Total User Area (sq. ft.)',$user_area);
			
		}
		
    }




    function kvell_locate_template( $template, $template_name, $template_path ) {

        global $woocommerce;
        $_template = $template;
        if ( ! $template_path ) {
            $template_path = $woocommerce->template_url;
        }		

        $plugin_path  = dirname( __FILE__ ) . '/partials/woocommerce/';
        $template = locate_template(
            array(
                $template_path . $template_name,
                $template_name
            )
        );


        if ( ! $template && file_exists( $plugin_path . $template_name ) )
            $template = $plugin_path . $template_name;

        if ( ! $template )
            $template = $_template;

        return $template;		
        
    }


    



}
