<?php
/**
 * sobu info page
 *
 * @author 		Christian K. Fraunholz php10.de
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce-sobu_before_email' ); 

echo __( 'Publish via sobu', 'woocommerce-sobu' ) . ":\n";
echo $order->get_view_order_url() . $sobuPublishParamStr . "\n";
echo "\n****************************************************\n\n";

do_action( 'woocommerce-sobu_after_email' ); ?>