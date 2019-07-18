
<?php
/**
 * Hide free shipping rates when coupon  is available.
 * working with WooCommerce 3.x 
 *
 * @param array $rates Array of rates found for the package.
 * @return array
 */
function aris_hide_free_shipping_when_coupon_is_available( $rates ) {
	$with_coupon = array();
	$applied_coupons = WC()->session->get( 'applied_coupons', array() );
	foreach ( $rates as $rate_id => $rate ) {
	   
		if ( 'advanced_shipping' === $rate->method_id ) {
		     continue;
		}
		$with_coupon[ $rate_id ] = $rate;
	}
	return ! empty( $applied_coupons ) ? $with_coupon : $rates;
}
add_filter( 'woocommerce_package_rates', 'aris_hide_free_shipping_when_coupon_is_available', 100 );
