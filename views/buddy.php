<?php
/**
 * sobu info page
 *
 * @author 		Christian K. Fraunholz php10.de
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce-sobu_before_info' ); ?>


<p><img src="<?php echo esc_url(SOBU__PLUGIN_URL . 'assets/sobu-banner-' . $lang . '.png')?>" alt="<?php esc_html_e( 'sobu buy share earn' , 'woocommerce-sobu');?>" /> <br />
  <h2><?php printf( __( '%s benefit for you' , 'woocommerce-sobu' ), esc_attr($voucher ) );?></h2>
  <?php printf( __( 'If you order now you will benefit from an extra discount of %s. This discount will be deducted automatically from the total amount at the end of your order.', 'woocommerce-sobu' ), esc_attr( $voucher ) );?><br><br>
<a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce-sobu_sobu_page_id' ) ) )?>"><?php esc_html_e( 'What is sobu?' , 'woocommerce-sobu');?></a>
<form id="redeem_voucher" action="<?php echo esc_url( get_permalink( wc_get_page_id( 'cart' ) ) )?>" method="POST"><br>
<div style="padding-right:15px;">
  <input type="submit" name="redeem_voucher" value="<?php printf( __( 'Redeem voucher worth %s now' , 'woocommerce-sobu' ), $voucher ) ;?>" style='background-color: #c9c72e; cursor:pointer'/>
</div>
</form><br>
<?php do_action( 'woocommerce-sobu_after_info' ); ?>