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
  <h2><?php esc_html_e( 'What is sobu?' , 'woocommerce-sobu');?></h2>
  <?php printf( __( 'sobu is an online shop platform from Swiss Post, built on the principle "buy, share and earn". Anyone who shares their purchase with their friends on social networks (Facebook, LinkedIn or Twitter) and prompts friends to make a purchase at %s can now earn cash!', 'woocommerce-sobu' ), esc_url( $server_name ) );?>
  <h2><?php printf( __( 'Earn % commission on your friends&#8217; purchases!' , 'woocommerce-sobu'), esc_attr( $commission ) );?></h2>
  <?php printf( __( 'Receive a great rate of %s commission on the cost of each purchase made by your friends as a result of your recommendation. This means that you earn cash regularly when your friends purchase from %s!' , 'woocommerce-sobu'), esc_attr( $commission ), esc_url( $server_name ) );?>
  <h2><?php esc_html_e( 'Get your money in three easy steps' , 'woocommerce-sobu');?></h2>
  <?php printf( __( 'it&#8217;s as simple as that:<br><br>
<b>Buy</b> After making a purchase, you will see advice in the online shop on how you can recommend the products you have purchased. Register once on the sobu platform.<br>
<b>Share</b> Publish the products you have purchased on social networks.<br>
<b>Earn</b> You will now automatically receive %s commission on each order that your friends make as a result of the published link. You can withdraw the accumulated sum of money from your sobu account.
    ' , 'woocommerce-sobu' ), esc_attr( $commission ) );?>
  <h2><?php printf( __( '%s discount for all your friends' , 'woocommerce-sobu'), esc_attr( $voucher ) );?></h2>
  <?php printf( __( 'It gets even better! Each of your friends benefits from a one-off discount of %s when they click on your recommendation link and make a purchase. This will be deducted from the cost of their purchase automatically. The discount of %s can only be redeemed once per person.<br><br>
    We hope you have lots of fun with your online shopping and earning! You can find more information about sobu at <a href="%s">sobu.ch</a>.<br><br><iframe width="560" height="315" src="http://www.youtube.com/embed/kDBBOT_jKrw" frameborder="0" allowfullscreen></iframe>' , 'woocommerce-sobu' ), esc_attr( $voucher ), esc_attr( $voucher ), esc_url( $sobu_url ) ) ;?>
<br>
<?php do_action( 'woocommerce-sobu_after_info' ); ?>