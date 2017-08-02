<!--<h2>sobu</h2>-->
<img src="<?php echo esc_url( SOBU__PLUGIN_URL . 'assets/sobu-logo-'.self::get_lang().'.png' )?>" border="0" alt="<?php esc_html_e( 'sobu: buy - share - earn' , 'woocommerce-sobu' );?>"></div>
<b><?php esc_html_e( 'Publish via sobu and benefit' , 'woocommerce-sobu');?></b><br>
<?php printf( __( 'Publish your purchase now via sobu on social networks (Facebook, LinkedIn or Twitter) and give your friends a voucher worth %s . In return you will receive a great rate of %s commission on the cost of each purchase made by your friends as a result of your recommendation.' , 'woocommerce-sobu' ), esc_html( self::get_voucher() ), esc_html( self::get_commission() ) );?>
<form id="publish" action="<?php echo esc_url( 'https://www.sobu.ch/publish' )?>" method="POST">
  <input type="hidden" value="<?php esc_html_e( self::get_api_key() )?>" name="apiKey" />
  <input type="hidden" name="customerId" value="<?php esc_html_e( $order->get_user_id() )?>" />
  <input type="hidden" name="order" value='<?php esc_html_e( $orderJson )?>' />
  <input type="hidden" name="benefit" value="" />
  <input type="hidden" name="signature" value="<?php esc_html_e( $signature )?>" /><input type="hidden" name="total" value="<?php esc_html_e( $sobuTotalStr )?>" /><br>
<div style="padding-right:15px;">
  <input type="submit" name="sobu_publish" value="<?php esc_html_e( 'Publish via sobu' , 'woocommerce-sobu');?>" style='background-color: #c9c72e; cursor:pointer'/>
</div>
<?php if ( isset ( $submitSobuPublish ) ) {
  echo '
<script type="text/javascript">
window.onload = function() {
  document.forms.publish.submit();
}
</script>';
}
?>
</form>
<!-- sobu iframe -->
<?php if ( isset( $iFrame ) ) echo $iFrame ?>