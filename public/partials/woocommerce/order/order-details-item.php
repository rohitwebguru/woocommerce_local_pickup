<?php
/**
 * Order Item Details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details-item.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 4.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
	return;
}
$setting_options = wp_parse_args(get_option('woo_free_product_sample_settings'),array());
?>
<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order ) ); ?>">

	<td class="woocommerce-table__product-name product-name">
		<?php
			$is_visible        = $product && $product->is_visible();
			$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );
				$get_free      = '';	

				foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {		
					if( $meta->key == "SAMPLE_PRICE" && $item['subtotal'] == $meta->value ){		
						$get_free = 1;
					}
				}
			if( 1 == $get_free ) {
				if( get_locale() == "ja" ) {
					$sample =  esc_html__( 'サンプル - ', 'woo-free-product-sample' );
				} else {
					$sample =  esc_html__( 'Sample - ', 'woo-free-product-sample' );
				}				
			echo apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">'.$sample.' (%s)</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible );
			} else {
			echo apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible );					
			}
			echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', $item->get_quantity() ) . '</strong>', $item );

			do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

			//wc_display_item_meta( $item );

			do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
		?>
	</td>

	<td class="woocommerce-table__product-total product-total">
		<?php echo $order->get_formatted_line_subtotal( $item ); ?>
	</td>

</tr>
<tr class="woocommerce-table__product-purchase-note product-purchase-note">
	<?php if ( $meta_data = $item->get_formatted_meta_data( '' ) ) : ?>
	<?php foreach ( $meta_data as $meta_id => $meta ) : 
		if($meta->display_key == 'Extended Warrenty' ):
		?>
		<td class="woocommerce-table__product-name product-name"><?php echo wp_kses_post( $meta->display_key ); ?></td>
		<td class="woocommerce-table__product-total product-total"><?php echo wp_kses_post( force_balance_tags( $meta->display_value ) ); ?></td> 
	<?php endif; endforeach; ?>
	<?php endif;  ?>
</tr>
<tr class="woocommerce-table__product-purchase-note product-purchase-note">
	<?php if ( $meta_data = $item->get_formatted_meta_data( '' ) ) : ?>
	<?php foreach ( $meta_data as $meta_id => $meta ) : 
		if($meta->display_key == 'Warrenty Price' ):
		?>
		<td class="woocommerce-table__product-name product-name"><?php echo wp_kses_post( $meta->display_key ); ?></td>
		<td class="woocommerce-table__product-total product-total"><?php echo wp_kses_post( force_balance_tags( $meta->display_value ) ); ?></td> 
	<?php endif; endforeach; ?>
	<?php endif;  ?>
</tr>
<?php if ( $show_purchase_note && $purchase_note ) : ?>

<tr class="woocommerce-table__product-purchase-note product-purchase-note">

	<td colspan="2"><?php echo wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ); ?></td>

</tr>

<?php endif; ?>
