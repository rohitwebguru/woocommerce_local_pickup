<?php
class Kvell_Extend_Warranty_Admin {

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
        add_filter( 'woocommerce_product_data_tabs', array($this,'woo_new_product_tab') );
        add_filter( 'woocommerce_product_data_panels', array($this,'extend_warrenty_data_product_tab_content') ); // WC 2.6 and up        
        add_action( 'admin_enqueue_scripts', array($this,'load_scripts'));
        add_action( 'wp_ajax_woocommerce_save_warrenty', array($this,'save_warrenty'));                
    }

    function load_scripts(){
        if(!get_the_ID()) return;
        wp_enqueue_style('extended_css', $this->extend_assets."extend.css");
        wp_enqueue_script('extended_js', $this->extend_assets."extend.js");
        $data=array();
        $data['product_id']=get_the_ID();
        $data['ajax_url']=admin_url( 'admin-ajax.php');
        $data['action']="woocommerce_save_warrenty";
        $data['symbol']=get_woocommerce_currency_symbol();
        wp_localize_script('extended_js','extend_warrenty_data',$data);        
    }

    function save_warrenty(){
        $warranties=array();
        $id=$_POST['post_id'];
        parse_str($_POST['data'],$warranties);    
        update_post_meta($id,'warranties',$warranties);   
        return true; 
    }

    function woo_new_product_tab( $tabs ) {
        $tabs['extend_warrenty_tab'] = array(
            'label'    => __( 'Extend Warrenty', 'woocommerce' ),
            'target'   => 'extend_warrenty_data',
            'class'    => array(''),
            'priority' => 100,
        );
        return $tabs;
    }

    function extend_warrenty_data_product_tab_content() {
        include KVELL_PATH."public/extended/extend_warrenty_data.php";	
    }






}

return new Kvell_Extend_Warranty_Admin();
