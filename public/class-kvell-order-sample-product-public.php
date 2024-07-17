<?php
class Kvell_Order_Sample_Product_Public {

    public $plugin_name;

    public $version;

    public $_optionName  = 'woo_free_product_sample_settings';

    public $_defaultOptions = array(
		'button_label'          => 'Order a Sample',
		'max_qty_per_order'		=> 5 
	);

	public $cart_sample_total=0;

    
    public function __construct() {

        $this->version = '2.1';
        $this->plugin_name = 'kvell-woocommerce-customization';
        $this->init_hooks();

    }

    public function init_hooks(){

        add_action( 'wp_enqueue_scripts', array($this,'load_style'), 10 );

        add_action( 'woocommerce_after_add_to_cart_button', array($this,'order_button'), 10 );

        add_action( 'wp_loaded', array($this,'add_to_cart_action'), 10 );

        add_action( 'woocommerce_add_order_item_meta', array($this,'kvell_add_sample_order_data'), 10 , 2 );

        add_filter( 'woocommerce_add_to_cart_validation', array($this,'kvell_set_limit_per_order'), 10 , 4 );

        add_filter( 'woocommerce_add_cart_item_data', array($this,'kvell_sample_order_data'), 10 , 2 );

        add_filter( 'woocommerce_before_calculate_totals', array($this,'kvell_sample_price_apply'), 10 );

        add_filter( 'woocommerce_update_cart_validation', array($this,'kvell_cart_update_limit_order'), 10 , 4 );

        add_filter( 'woocommerce_cart_item_price', array($this,'kvell_cart_item_price_filter'), 10 , 4 );

        add_filter( 'woocommerce_cart_item_name', array($this,'kvell_alter_item_name'), 10 , 3 );

        add_action( 'woocommerce_product_options_pricing', array($this,'wc_add_sample_price') );

        add_action( 'woocommerce_process_product_meta', array($this,'wc_save_sample_price') );

    }

    public function btn_txt(){
        return  "Order A Sample";
    }

    public function load_style(){
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/style.css', array(), $this->version, 'all' );
    }

    public function order_button() {

        global $product;
        $id=$product->get_id();

        $sample_price=self::product_sample_price($id);

        if ( $product->is_in_stock() && $sample_price > 0) {

            switch ( self::product_type() ) {
                case "simple":
                    $button = '<button type="submit" name="kvell-order-sample-button" value="'.get_the_ID().'" class="kvell-order-button">'.$this->btn_txt().'</button>';
                    break;
                case "variable":
                    $button = '<button type="submit" name="kvell-order-sample-button" value="'.get_the_ID().'" class="kvell-order-button">'.$this->btn_txt().'</button>';
                    break;			
                default:
                    $button = '';
            } 
            
        echo $button;						
        }

    }

    public static function product_type() {
        global $product;
        if( $product->is_type( 'simple' ) ) {
            return 'simple';
        } else if( $product->is_type( 'variable' ) ) {
            return 'variable';
        } else {
            return NULL;
        }
    }

    public function add_to_cart_action(){
        if ( 
			! isset( $_REQUEST['kvell-order-sample-button'] ) || 
			! is_numeric( wp_unslash( $_REQUEST['kvell-order-sample-button'] ) )
		)			 
		{
			return;
		}

		wc_nocache_headers();

		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( wp_unslash( $_REQUEST['kvell-order-sample-button'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		$was_added_to_cart = false;
		$adding_to_cart    = wc_get_product( $product_id );

		if ( ! $adding_to_cart ) {
			return;
		}
		
		$add_to_cart_handler = apply_filters( 'woocommerce_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

		if ( 'variable' === $add_to_cart_handler || 'variation' === $add_to_cart_handler ) {
			$was_added_to_cart = self::kvell_add_to_cart_handler_variable( $product_id );
		} else {
			$was_added_to_cart = self::kvell_add_to_cart_handler_simple( $product_id );
		}

		// If we added the product to the cart we can now optionally do a redirect.
		if ( $was_added_to_cart && 0 === wc_notice_count( 'error' ) ) {
			$url = apply_filters( 'woocommerce_add_to_cart_redirect', $url, $adding_to_cart );

			if ( $url ) {
				wp_safe_redirect( $url );
				exit;
			} elseif ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				wp_safe_redirect( wc_get_cart_url() );
				exit;
			}
		}

    }

    private static function kvell_add_to_cart_handler_simple( $product_id ) {

		$quantity          = 1; // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );	

		if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity ) ) {
			wc_add_to_cart_message( array( $product_id => $quantity ), true );
			return true;
		}
		return false;
	}

    private static function kvell_add_to_cart_handler_variable( $product_id ) {
		try {
			$variation_id       = empty( $_REQUEST['variation_id'] ) ? '' : absint( wp_unslash( $_REQUEST['variation_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			$quantity           = empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_REQUEST['quantity'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			$missing_attributes = array();
			$variations         = array();
			$adding_to_cart     = wc_get_product( $product_id );

			if ( ! $adding_to_cart ) {
				return false;
			}				

			// If the $product_id was in fact a variation ID, update the variables.
			if ( $adding_to_cart->is_type( 'variation' ) ) {
				$variation_id   = $product_id;
				$product_id     = $adding_to_cart->get_parent_id();
				$adding_to_cart = wc_get_product( $product_id );

				if ( ! $adding_to_cart ) {
					return false;
				}
			}

			// Gather posted attributes.
			$posted_attributes = array();

			foreach ( $adding_to_cart->get_attributes() as $attribute ) {
				if ( ! $attribute['is_variation'] ) {
					continue;
				}
				$attribute_key = 'attribute_' . sanitize_title( $attribute['name'] );

				if ( isset( $_REQUEST[ $attribute_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
					if ( $attribute['is_taxonomy'] ) {
						// Don't use wc_clean as it destroys sanitized characters.
						$value = sanitize_title( wp_unslash( $_REQUEST[ $attribute_key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
					} else {
						$value = html_entity_decode( wc_clean( wp_unslash( $_REQUEST[ $attribute_key ] ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
					}

					$posted_attributes[ $attribute_key ] = $value;
				}
			}

			// If no variation ID is set, attempt to get a variation ID from posted attributes.
			if ( empty( $variation_id ) ) {
				$data_store   = WC_Data_Store::load( 'product' );
				$variation_id = $data_store->find_matching_product_variation( $adding_to_cart, $posted_attributes );
			}

			// Do we have a variation ID?
			if ( empty( $variation_id ) ) {
				throw new Exception( __( 'Please choose product options&hellip;', 'woocommerce' ) );
			}

			// Check the data we have is valid.
			$variation_data = wc_get_product_variation_attributes( $variation_id );

			foreach ( $adding_to_cart->get_attributes() as $attribute ) {
				if ( ! $attribute['is_variation'] ) {
					continue;
				}

				// Get valid value from variation data.
				$attribute_key = 'attribute_' . sanitize_title( $attribute['name'] );
				$valid_value   = isset( $variation_data[ $attribute_key ] ) ? $variation_data[ $attribute_key ] : '';

				/**
				 * If the attribute value was posted, check if it's valid.
				 *
				 * If no attribute was posted, only error if the variation has an 'any' attribute which requires a value.
				 */
				if ( isset( $posted_attributes[ $attribute_key ] ) ) {
					$value = $posted_attributes[ $attribute_key ];

					// Allow if valid or show error.
					if ( $valid_value === $value ) {
						$variations[ $attribute_key ] = $value;
					} elseif ( '' === $valid_value && in_array( $value, $attribute->get_slugs(), true ) ) {
						// If valid values are empty, this is an 'any' variation so get all possible values.
						$variations[ $attribute_key ] = $value;
					} else {
						/* translators: %s: Attribute name. */
						throw new Exception( sprintf( __( 'Invalid value posted for %s', 'woocommerce' ), wc_attribute_label( $attribute['name'] ) ) );
					}
				} elseif ( '' === $valid_value ) {
					$missing_attributes[] = wc_attribute_label( $attribute['name'] );
				}
			}
			if ( ! empty( $missing_attributes ) ) {
				/* translators: %s: Attribute name. */
				throw new Exception( sprintf( _n( '%s is a required field', '%s are required fields', count( $missing_attributes ), 'woocommerce' ), wc_format_list_of_items( $missing_attributes ) ) );
			}
		} catch ( Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' );
			return false;
		}

		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );

		if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations ) ) {
			wc_add_to_cart_message( array( $product_id => $quantity ), true );
			return true;
		}

		return false;
    }
    
    /**
	 * Limit Order
	 */		
	public function kvell_set_limit_per_order( $valid, $product_id ) {
	
		global $woocommerce;
		$setting_options   = $this->setting_options();

		if(isset($_POST['kvell-order-sample-button'])){
			$_POST['length_needed']=10;
			$_POST['width_needed']=10;
		}

	


		$notice_type 	   = isset( $setting_options['limit_per_order'] ) ? $setting_options['limit_per_order'] : null;
        $disable_limit 	   = isset( $setting_options['disable_limit_per_order'] ) ? $setting_options['disable_limit_per_order'] : null;
        

		if( ! isset( $disable_limit ) ) :
			foreach( $woocommerce->cart->get_cart() as $key => $val ) :
				
				if( 'product' == $notice_type ) {

					if( ( isset( $val['free_sample'] ) && $product_id == $val['free_sample'] ) && ( $setting_options['max_qty_per_order'] <= $val['quantity'] ) && ( isset( $_REQUEST['kvell-order-sample-button'] ) || isset( $_REQUEST['variable-add-to-cart'] ) ) ) {
				
                        wc_add_notice( esc_html__( 'You can order this product '.$setting_options['max_qty_per_order'].' quantity per order.', $this->plugin_name ), 'error' );
											
						exit( wp_redirect( get_permalink($product_id) ) );						
					}	

				} else if( 'all' == $notice_type ) {

					if( ( isset( $val['free_sample'] ) ) && ( $setting_options['max_qty_per_order'] <= $this->kvell_cart_total() ) && ( isset( $_REQUEST['kvell-order-sample-button'] ) || isset( $_REQUEST['variable-add-to-cart'] ) ) ) {
		
                        wc_add_notice( esc_html__( 'You can order sample product maximum '.$setting_options['max_qty_per_order'].' quantity per order.', $this->plugin_name ), 'error' );
										
						exit( wp_redirect( get_permalink($product_id) ) );						
					}

				}
			endforeach; 
		endif; 
		return $valid;

    }

    public function kvell_cart_update_limit_order( $passed, $cart_item_key, $values, $updated_quantity ) {

		if( isset( $values['free_sample'] ) ) {				
			$this->cart_sample_total += $values['quantity'];
		}

        $setting_options   = $this->setting_options();
		$notice_type 	   = isset( $setting_options['limit_per_order'] ) ? $setting_options['limit_per_order'] : null;
		$disable_limit 	   = isset( $setting_options['disable_limit_per_order'] ) ? $setting_options['disable_limit_per_order'] : null;


			if( 'product' == $notice_type ) {

				if( ( $values['free_sample'] == $values['product_id'] ) && ( $setting_options['max_qty_per_order'] < $updated_quantity ) ) {			
				
					$product = wc_get_product( $values['product_id'] );				
	
						wc_add_notice( esc_html__( 'You can order '.$product->get_name().' maximum  '.$setting_options['max_qty_per_order'].'  per order.', $this->plugin_name ), 'error' );
		
					
					$passed = false;
				
				}

			} else if( 'all' == $notice_type ) {

				if( ( isset( $values['free_sample'] ) ) && ( $setting_options['max_qty_per_order'] < $this->kvell_cart_update_total() ) ) {
					wc_add_notice( esc_html__( 'You can order sample product maximum '.$setting_options['max_qty_per_order'].' quantity per order.', $this->plugin_name ), 'error' );								
					$passed = false;
				
				}

			}

      
		return $passed;

	}	
    
    public function setting_options(){
        $options=array(
            'button_label'=>'Order A Sample',
            'max_qty_per_order'=>2,
            'limit_per_order'=>'all'
        );

        return $options;
    }

    public function kvell_cart_total( ) {

		global $woocommerce;
		$total = 0;
		foreach( $woocommerce->cart->get_cart() as $key => $val ) {
			if( isset( $val['free_sample'] ) ) {				
				$total += $val['quantity'];
			}
		}
		return $total;		

	}
		
    public function kvell_cart_update_total() {

		if(!isset($_POST['cart'])) return 0;

		$total = 0;
		global $woocommerce;

	
		foreach($_POST['cart'] as $key=>$value){
	

			$item = $woocommerce->cart->get_cart_item($key);
			if(isset($item['free_sample'])){
				$total += $value['qty'] ;
			}

		}

		return $total;		

    }	

    public function kvell_sample_order_data( $cart_item ) {

		if( isset( $_REQUEST['kvell-order-sample-button'] ) || isset( $_REQUEST['variable-add-to-cart'] ) ) {
			$cart_item['free_sample']  = isset( $_REQUEST['kvell-order-sample-button'] ) ? sanitize_text_field( $_REQUEST['kvell-order-sample-button'] ) : sanitize_text_field( $_REQUEST['variable-add-to-cart'] );
			$product_id = isset( $_REQUEST['kvell-order-sample-button'] ) ? sanitize_text_field( $_REQUEST['kvell-order-sample-button'] ) : sanitize_text_field( $_REQUEST['variable-add-to-cart'] );
			$cart_item['sample_price'] = self::product_sample_price( $product_id );			
		}			
		return $cart_item; 
	}	

    public function kvell_sample_price_apply( $cart ) {
	
		foreach ( $cart->get_cart() as $key => $value ) {
			if( isset( $value["sample_price"] ) ) {
				$value['data']->set_price($value["sample_price"]);				
			}				

		}   
    }
    
    public static function product_sample_price($product_id){

        $sample_price=get_post_meta( $product_id, '_wc_sample_price', TRUE);

        if(!$sample_price){
            $sample_price=0; 
        }

        return $sample_price;
    }

    public function kvell_alter_item_name ( $product_name, $cart_item, $cart_item_key ) {

        if ( isset( $cart_item['free_sample'] ) ) {
            $product_name   = esc_html__( 'Sample - ', $this->plugin_name).$product_name;
		}

		return $product_name;
	}


    public function kvell_add_sample_order_data( $itemID, $values ) {

		if ( isset( $values['free_sample'] ) ) {
			wc_add_order_item_meta( $itemID, 'product_type', 'Sample' );
			wc_add_order_item_meta( $itemID, 'sample_price', $values['sample_price'] );
		}
		
    }
    
    public function kvell_cart_item_price_filter( $price, $cart_item, $cart_item_key ) {
		if( isset( $cart_item["sample_price"] ) ) {
            $sample_price = self::product_sample_price( $cart_item['product_id'] );
        
            $price = wc_price( $sample_price );		
        }
		return $price;
    }
    
    public function wc_add_sample_price() {

        woocommerce_wp_text_input(
            array(
                'id'                => '_wc_sample_price',
                'wrapper_class'     => 'sample_price',
                'class'             => 'wc_input_sample_price',
                'label'             => sprintf( __( 'Sample Price (%s)', $this->plugin_name ), get_woocommerce_currency_symbol() ),
                'type'              => 'number',
                'custom_attributes' => array(
                    'step' => 'any',
                    'min'  => '0',
                ),
            )
        );
    }

    public function wc_save_sample_price( $post_id ) {

        if ( isset( $_POST['_wc_sample_price'] ) ) {
            update_post_meta( $post_id, '_wc_sample_price', $_POST['_wc_sample_price'] );
        }

    }
    
    



}
