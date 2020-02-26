
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


/**
 * Buy one Get one Free.
 * working with WooCommerce 3.x 
 *
 * @param array prices.
 * @param array quantities.
 * @return array
 */

add_action('woocommerce_cart_calculate_fees', 'buy_one_get_one_free', 10, 1 );
function buy_one_get_one_free( $wc_cart ){
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

    $cart_item_count = $wc_cart->get_cart_contents_count();
    if ( $cart_item_count < 2 ) return;

    // Set HERE your coupon codes
    $coupons_codes = array('2for1wow', 'anothercouponcode');
    $discount = 0; // initializing

    $matches = array_intersect( $coupons_codes, $wc_cart->get_applied_coupons() );
    if( count($matches) == 0 ) return;

    // Iterating through cart items
    foreach ( $wc_cart->get_cart() as $key => $cart_item ) {
        $qty = intval( $cart_item['quantity'] );
        // Iterating through item quantities
        for( $i = 0; $i < $qty; $i++ )
            $items_prices[] = floatval( wc_get_price_excluding_tax( $cart_item['data'] ) );
    }
    asort($items_prices); // Sorting cheapest prices first

    // Get the number of free items (detecting odd or even number of prices)
    if( $cart_item_count % 2 == 0 ) $free_item_count = $cart_item_count / 2;
    else  $free_item_count = ($cart_item_count - 1) / 2;

    // keeping only the cheapest free items prices in the array
    $free_item_prices = array_slice($items_prices, 0, $free_item_count);

    // summing prices for this free items
    foreach( $free_item_prices as $key => $item_price )
        $discount -= $item_price;

    if( $discount != 0 ){
        // The discount
        $label = '"'.reset($matches).'" '.__("discount");
        $wc_cart->add_fee( $label, number_format( $discount, 2 ), true, 'standard' );
        # Note: Last argument in add_fee() method is related to applying the tax or not to the discount (true or false)
    }
}

/**
 * @snippet       Buy 1, Get Free Product as a Gift - WooCommerce
 * @how-to        Watch tutorial @ https://businessbloomer.com/?p=19055
 * @sourcecode    https://businessbloomer.com/?p=21732
 * @author        Rodolfo Melogli
 * @compatible    Woo 3.5.3
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */
 
add_filter( 'woocommerce_add_to_cart_validation', 'bbloomer_add_gift_if_sku_added_cart', 10, 3 );
 
function bbloomer_add_gift_if_sku_added_cart( $passed, $product_id, $quantity ) {
global $woocommerce;
 
/* enter array of SKUs that give gift */
$skuswithgift = array('armpar','armsup');
 
/* enter SKU of product gift */
$giftsku = 'starter';
 
/* enter name of coupon that gives 100% discount for specific product gift */
$coupon_code = 'xxxyyyzzz'; 
 
$product = wc_get_product( $product_id );
 
if ( $product->get_sku() && in_array( $product->get_sku(), $skuswithgift ) ) {
   WC()->cart->add_to_cart( wc_get_product_id_by_sku($giftsku) );
   wc_add_notice( __( 'Hey, Supporter! As promised, I added #CustomizeWoo Video Lessons for Free - you will be able to access them in a single place (~ My Courses ~) and get premium support.', 'woocommerce' ), 'success' );
   $woocommerce->cart->add_discount( $coupon_code );
}
return $passed;
}

/**
 * @snippet       Display image per Parent Category
 * @how-to        N/A
 * @sourcecode    Snippet
 * @author        ArisTech & GMAnentis
 * @compatible    Woo 3.5.3
 */

$category_id = $wp_query->get_queried_object()->term_id;
$anc = get_ancestors( $category_id, 'product_cat' );
if($category_id == 1026 || $anc[0] == 1026)
{
	echo "<img width='100%' src='/wp-content/uploads/2019/08/3-1-flavour-shots-category.jpg' />";

}

/**
 * Sale flash | Badges & percentage --- Ellesi 5shoes on theme
 */
if(!function_exists('elessi_add_custom_sale_flash')) :
    function elessi_add_custom_sale_flash() {
        global $product;
        
        $badges = '';
        
        /**
         * Custom Badge
         */
        $nasa_bubble_hot = elessi_get_custom_field_value($product->get_id(), '_bubble_hot');
        $badges .= $nasa_bubble_hot ? '<div class="badge hot-label">' . $nasa_bubble_hot . '</div>' : '';

        if ($product->is_on_sale()):
            
            /**
             * Sale
             */
            $product_type = $product->get_type();
            if ($product_type == 'variable') :
				$var_maximumper = 0;
				$var_sale_price     =  $product->get_variation_sale_price( 'min', true );
        		$var_regular_price  =  $product->get_variation_regular_price( 'max', true );
				if(is_numeric($var_sale_price)) :
                    $var_percentage = $var_regular_price ? round(((($var_regular_price - $var_sale_price) / $var_regular_price) * 100), 0) : 0;
                    if ($var_percentage > $var_maximumper) :
                        $var_maximumper = $var_percentage;
                    endif;
                    
                    $badges .= '<div class="badge sale-label">'. sprintf(esc_html__('%s', 'elessi-theme'), $var_maximumper . '%') . '</div>';
				else :
	    			 $badges .= '<div class="badge sale-label"></div>';
                endif;
           
                
            else :
                $maximumper = 0;
                $regular_price = $product->get_regular_price();
                $sales_price = $product->get_sale_price();
                if(is_numeric($sales_price)) :
                    $percentage = $regular_price ? round(((($regular_price - $sales_price) / $regular_price) * 100), 0) : 0;
                    if ($percentage > $maximumper) :
                        $maximumper = $percentage;
                    endif;
                    
                    $badges .= '<div class="badge sale-label"><span class="sale-label-text">' . esc_html__('SALE', 'elessi-theme') . '</span>' . '-' . sprintf(esc_html__('%s', 'elessi-theme'), $maximumper . '%') . '</div>';
                endif;
            endif;
            
            /**
             * Style show with Deal product
             */
            $badges .= '<div class="badge deal-label">' . esc_html__('LIMITED', 'elessi-theme') . '</div>';
        endif;
        
        /**
         * Out of stock
         */
        $stock_status = $product->get_stock_status();
        if ($stock_status == "outofstock"):
            $badges .= '<div class="badge out-of-stock-label">' . esc_html__('Sold Out', 'elessi-theme') . '</div>';
        endif;
        
        echo ('' !== $badges) ? '<div class="nasa-badges-wrap">' . $badges . '</div>' : '';
    }
endif;
