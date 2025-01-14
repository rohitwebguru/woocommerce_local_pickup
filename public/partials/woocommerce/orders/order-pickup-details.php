<?php
/**
 * WooCommerce Local Pickup Plus
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Local Pickup Plus to newer
 * versions in the future. If you wish to customize WooCommerce Local Pickup Plus for your
 * needs please refer to http://docs.woocommerce.com/document/local-pickup-plus/
 *
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2020, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * WooCommerce Local Pickup Plus order pickup details template file.
 *
 * @type \WC_Order $order Order being displayed
 * @type array $pickup_data Pickup data for given order
 * @type \WC_Shipping_Local_Pickup_Plus $shipping_method Local Pickup Plus Shipping Method instance
 *
 * @version 2.0.0
 * @since 2.0.0
 */
$delivery= $order->get_meta( '_kvell_delivery' );

if($delivery=="different_address"):
?>

<h2 class="woocommerce-column__title">Shipping address</h2>
<address>
	<?php 

	if($order->data['shipping']['address_1']!=""){ echo $order->data['shipping']['address_1'] . "<br />";}
	if($order->data['shipping']['address_2']!="") echo $order->data['shipping']['address_2'] . "<br />";
	if($order->data['shipping']['city']!="") echo $order->data['shipping']['city'] . "<br />";
	if($order->data['shipping']['state']!="") echo $order->data['shipping']['state'] . "<br />";
	if($order->data['shipping']['postcode']!="") echo $order->data['shipping']['postcode'] . "<br />";
	if($order->data['shipping']['country']!="") echo $order->data['shipping']['country'] . "<br />";
?>
</address>
<?php endif; ?>
<?php if($delivery=="local_pickup"):
?>
<tr class="wc-local-pickup-plus">
	<th><?php echo esc_html( $shipping_method->get_method_title() ); ?>:</th>
	<td>
		<?php $package_number = 1; ?>
		<?php $packages_count = count( $pickup_data ); ?>
		<?php foreach ( $pickup_data as $pickup_meta ) : ?>

			<div>
				<?php if ( $packages_count > 1 ) : ?>
					<h5><?php echo sprintf( is_rtl() ? '#%2$s %1$s': '%1$s #%2$s', esc_html( $shipping_method->get_method_title() ), $package_number ); ?></h5>
				<?php endif; ?>

				<?php foreach ( $pickup_meta as $label => $value ) : ?>
					<?php if ( is_rtl() ) : ?>
						<small><?php echo wp_kses_post( $value ); ?> <strong>:<?php echo esc_html( $label ); ?></strong></small><br />
					<?php else : ?>
						<small><strong><?php echo esc_html( $label ); ?>:</strong> <?php echo wp_kses_post( $value ); ?></small><br />
					<?php endif; ?>
				<?php endforeach; ?>

				<?php if ( $packages_count > 1 && $package_number <=$packages_count ) : ?>
					<br />
				<?php endif; ?>

				<?php $package_number++; ?>
			</div>

		<?php endforeach; ?>
	</td>
</tr>
<?php endif; ?>
