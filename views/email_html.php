<?php
/**
 * sobu info page
 *
 * @author 		Christian K. Fraunholz php10.de
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce-sobu_before_email' ); ?>

<p><img src="<?php echo esc_url(SOBU__PLUGIN_URL . 'assets/sobu-banner-' . self::get_lang() . '.png')?>" alt="<?php esc_html_e( 'sobu buy share earn' , 'woocommerce-sobu');?>" /> <br />
<b><?php esc_html_e( 'sobu: buy - share - earn' , 'woocommerce-sobu');?></b><br>
<?php printf( __( 'Publish your purchase now via sobu on social networks (Facebook, LinkedIn or Twitter) and give your friends a voucher worth %s . In return you will receive a great rate of %s commission on the cost of each purchase made by your friends as a result of your recommendation.' , 'woocommerce-sobu' ), esc_html( self::get_voucher() ), esc_html( self::get_commission() ) );?><br /><br />
<a href="<?php echo $order->get_view_order_url() . $sobuPublishParamStr?>"><?php esc_html_e( 'Publish via sobu' , 'woocommerce-sobu');?></a>

<?php do_action( 'woocommerce-sobu_after_email' ); ?>