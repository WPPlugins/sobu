<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WooCommerce sobu
 *
 * @package  Woocommerce_Sobu
 * @category sobu
 * @author   Dipl.-Ing. (FH) Christian Konrad Fraunholz
 */

if ( ! class_exists( 'WC_Integration_sobu' ) ) :

class WC_Integration_sobu extends WC_Integration {

	/**
	 * Init and hook in the integration.
	 */
	public function __construct() {
		global $woocommerce;

		$this->id                 = 'sobu';
		$this->method_title       = __( 'sobu', 'woocommerce-sobu' );
		$this->method_description = __( 'Publish via sobu and benefit', 'woocommerce-sobu' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->api_key          = $this->get_option( 'api_key' );
		$this->test_api_key     = $this->get_option( 'test_api_key' );
		$this->test             = $this->get_option( 'test' );
		$this->coupon_code      = $this->get_option( 'coupon_code' );
		$this->public_key       = $this->get_option( 'public_key' );
		$this->commission       = $this->get_option( 'commission' );
		$this->buddypage        = get_permalink( get_option( 'woocommerce-sobu_sobu_page_id' ) );
		$this->private_key      = $this->get_option( 'private_key' );
		$this->public_key       = $this->get_option( 'public_key' );
		$this->debug            = $this->get_option( 'debug' );
    
		// Logs
		if ( 'yes' == $this->debug ) {
			$this->log = new WC_Logger();
		}

		// Actions.
		add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_view_order', array( $this, 'view_order'));
		add_action( 'woocommerce_init', array( $this, 'loadido'));
		add_action( 'woocommerce_thankyou', array( $this, 'thankyou'));
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email'));

		// Filters.
		add_filter( 'woocommerce_settings_api_sanitized_fields_' . $this->id, array( $this, 'sanitize_settings' ) );
    add_shortcode( 'woocommerce-sobu_info',  __CLASS__ . '::info_page' );
    
	}


	/**
	 * Initialize integration settings form fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'api_key' => array(
				'title'             => __( 'API Key', 'woocommerce-sobu' ),
				'type'              => 'text',
				'description'       => __( 'You can find this in the sobu partner portal', 'woocommerce-sobu' ),
				'desc_tip'          => true,
				'default'           => ''
			),
			'test_api_key' => array(
				'title'             => __( 'Test API Key', 'woocommerce-sobu' ),
				'type'              => 'text',
				'description'       => __( 'You can find this in the sobu partner portal', 'woocommerce-sobu' ),
				'desc_tip'          => true,
				'default'           => ''
			),
			'test' => array(
				'title'             => __( 'Mode', 'woocommerce-sobu' ),
				'type'              => 'checkbox',
				'default'           => 'yes',
				'label'             => __( 'Test Mode', 'woocommerce-sobu' ),
			),
			'private_key' => array(
				'title'             => __( '', 'woocomerce-sobu' ),
				'type'              => 'hidden',
				'default'           => '' 
			),
			'coupon_code' => array(
				'title'             => __( 'Coupon code', 'woocommerce' ),
				'type'              => 'text',
				'description'       => __( 'Enter a valid Coupon code', 'woocommerce-sobu' ),
				'desc_tip'          => true,
				'default'           => ''
			),
			'commission' => array(
				'title'             => __( 'Commission', 'woocommerce-sobu' ),
				'type'              => 'text',
				'description'       => __( 'Commission for recommendations, either fixed or add a % on the end', 'woocommerce-sobu' ),
				'desc_tip'          => true,
				'default'           => ''
			),
			'buddypage' => array(
				'title'             => __( 'Benefit link', 'woocommerce-sobu' ),
				'type'              => 'text',
				'description'       => __( 'Copy the benefit link into your shop profile in the sobu partner portal', 'woocommerce-sobu' ),
				'desc_tip'          => true,
				'default'           => $this->buddypage
			),
			'public_key' => array(
				'title'             => __( 'Public Key', 'woocomerce-sobu' ),
				'type'              => 'textarea',
				'css'               => 'width:60%; height: 200px;',
				'description'       => __( 'Copy the public key into your shop profile in the sobu partner portal', 'woocommerce-sobu' ),
				'desc_tip'          => true,
				'default'           => $this->public_key 
			),
			'debug' => array(
				'title'             => __( 'Debug Log', 'woocommerce-sobu' ),
				'type'              => 'checkbox',
				'label'             => __( 'Enable logging', 'woocommerce-sobu' ),
				'default'           => 'no',
				'description'       => sprintf( __( 'Log sobu events, inside <code>%s</code>', 'woocommerce-sobu' ), wc_get_log_file_path( 'sobu' ) )
			)
		);
	}


	/**
	 * Generate Button HTML.
	 */
	public function generate_button_html( $key, $data ) {
		$field    = $this->plugin_id . $this->id . '_' . $key;
		$defaults = array(
			'class'             => 'button-secondary',
			'css'               => '',
			'custom_attributes' => array(),
			'desc_tip'          => false,
			'description'       => '',
			'title'             => '',
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<button class="<?php echo esc_attr( $data['class'] ); ?>" type="button" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php echo $this->get_custom_attribute_html( $data ); ?>><?php echo wp_kses_post( $data['title'] ); ?></button>
					<?php echo $this->get_description_html( $data ); ?>
				</fieldset>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}


	/**
	 * Santize our settings
	 * @see process_admin_options()
	 */
	public function sanitize_settings( $settings ) {
		// We're just going to make the api key all upper case characters since that's how our imaginary API works
		if ( isset( $settings ) ) {
      if ( isset( $settings['api_key'] ) ) {
        $settings['api_key'] = strtoupper( $settings['api_key'] );
      }
      if (! $settings['public_key'] ) {
        $keys = $this->create_ssl_keys();
        $settings['public_key']   = $keys['public'];
        $settings['private_key']  = $keys['private'];
      }
    }
    $settings['buddypage']  = $this->buddypage;
		return $settings;
	}


	/**
	 * Validate the API key
	 * @see validate_settings_fields()
	 */
	public function validate_api_key_field( $key ) {
		// get the posted value
		$value = $_POST[ $this->plugin_id . $this->id . '_' . $key ];

		// check if the API key is longer or shorter than 20 characters. Throw an error which will prevent the user from saving.
		if ( isset( $value ) &&
			 36 != strlen( $value ) ) {
			$this->errors[] = $key;
		}
		return $value;
	}

	/**
	 * Validate the test API key
	 * @see validate_settings_fields()
	 */
	public function validate_test_api_key_field( $key ) {
		// get the posted value
		$value = $_POST[ $this->plugin_id . $this->id . '_' . $key ];

		// check if the API key is longer or shorter than 20 characters. Throw an error which will prevent the user from saving.
		if ( isset( $value ) &&
			 36 != strlen( $value ) ) {
			$this->errors[] = $key;
		}
		return $value;
	}

	/**
	 * Validate the coupon code
	 * @see validate_settings_fields()
	 */
	public function validate_coupon_code_field( $key ) {
    global $wpdb;
    self::get_voucher();
		// get the posted value
		$value = $_POST[ $this->plugin_id . $this->id . '_' . $key ];

		// check if the coupon code exists. If not, throw an error which will prevent the user from saving.
		$coupon_id 	= $wpdb->get_var( $wpdb->prepare( apply_filters( 'woocommerce_coupon_code_query', "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish'" ), $value ) );
		if (! isset( $coupon_id )) {
			$this->errors[] = $key;
		}
		return $value;
	}

	/**
	 * Validate the commission
	 * @see validate_settings_fields()
	 */
	public function validate_commission_field( $key ) {
		// get the posted value
		$value = $_POST[ $this->plugin_id . $this->id . '_' . $key ];

		// check if no commission was entered. Throw an error which will prevent the user from saving.
		if ( isset( $value ) &&
			 ! strlen( $value ) ) {
			$this->errors[] = $key;
		}
		return $value;
	}

	/**
	 * Validate the public key
	 * @see validate_settings_fields()
	 */
	public function validate_public_key_field( $key ) {
		// get the posted value
		$value = $_POST[ $this->plugin_id . $this->id . '_' . $key ];

		// check if the public SSL key is longer or shorter than the obligatory number of characters. Throw an error which will prevent the user from saving.
		if ( $value &&
			 451 != strlen( $value ) ) {
       //$this->errors[] = strlen( $value );
		}
		return $value;
	}

	/**
	 * Validate the private key
	 * @see validate_settings_fields()
	 */
	public function validate_private_key_field( $key ) {
		// get the posted value
		$value = $_POST[ $this->plugin_id . $this->id . '_' . $key ];

		// check if the private SSL key is longer or shorter than the obligatory number characters. Throw an error which will prevent the user from saving.
		if ($value &&
       451 != strlen( $value ) ) {
       //$this->errors[] = $key;
		}
		return $value;
	}
  
  /**
   *  creates the private and public SSL key
   *
   * @param bool success
   */
  private function create_ssl_keys( $test = false ) {
		if ( 'yes' == $this->debug ) {
      $this->log->add( 'sobu', 'Generating new SSL keys' );
    }
    // create new private and public key
    $new_key_pair = openssl_pkey_new( 
      array(
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
      )
    );

    openssl_pkey_export( $new_key_pair, $private_key_pem );

    $details = openssl_pkey_get_details( $new_key_pair );
    $public_key_pem = $details['key'];

    if (! $public_key_pem || ! $private_key_pem ) {
      throw new Exception( '<b>ssl keys not set(create_ssl_keys)</b><br />' . $e );
    }

    $keyArr = array(
        'private' => $private_key_pem,
        'public' => $public_key_pem
    );
    return $keyArr;
  }
  

  /**
   *  returns hex converted signature
   *
   * @param string with content
   * @return string with signature
   */
  public static function get_hex_converted_signature( $signature ) {
    $final_singature = "";
    for ($i = 0, $ii = strlen( $signature ); $i < $ii; $i++) {
      $sChar = $signature[$i];
      $final_singature .= str_pad( dechex( ord( $sChar ) ), 2, 0, STR_PAD_LEFT );
    }
    return $final_singature;
  }

  /**
   *  creates the signature
   *
   * @param string with content
   * @return string with hex converted signature
   */
  public static function get_digital_signature( $data ) {

    $privateKey = self::get_private_key();
    $sPKeyID = openssl_get_privatekey( $privateKey );

    //create signature
    openssl_sign($data, $signature, $sPKeyID, OPENSSL_ALGO_SHA1);

// remove key from memory
    openssl_free_key( $sPKeyID );

    //return $signature;
    return self::get_hex_converted_signature( $signature );
  }
  
  /**
   *  get the 2 char iso language, or default
   *
   * @return string language
   */
  public static function get_lang() {
		$lang = substr( get_locale(), 0, 2 );
    $sobuLang = array( 'it' , 'de' , 'fr' , 'en' );
    $defaultLang = 'en';
    if ( in_array( $lang, $sobuLang ) ) {
      return $lang;
    }
    return $defaultLang;
  }
  
  /**
   *  get the voucher code
   *
   * @return string voucher code
   */
  public static function get_voucher_code() {
    $sobuConfig = get_option( 'woocommerce_sobu_settings', null );
    return $sobuConfig['coupon_code'];
  }
  
  /**
   *  get the voucher value or percent
   *
   * @return string voucher
   */
  public static function get_voucher() {
    global $wpdb;
    $currency_symbol = get_woocommerce_currency_symbol();
		$coupon_id 	= $wpdb->get_var( $wpdb->prepare( apply_filters( 'woocommerce_coupon_code_query', "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish'" ), self::get_voucher_code() ) );
    $couponSql = "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND ( meta_key = 'coupon_amount' OR meta_key = 'discount_type' ) LIMIT 2;";
		$couponValue  = $wpdb->get_var( $wpdb->prepare( $couponSql, $coupon_id ));
		$couponType   = $wpdb->get_var( $wpdb->prepare( $couponSql, $coupon_id ), 0, 1 );
		if ( $couponValue ) {
      if ( strpos( $couponType, 'percent' ) !== false ) {
        return $couponValue . '%';
      } else {
        return sprintf( get_woocommerce_price_format(), $currency_symbol, $couponValue );
      }
    }
    return '';
  }
  
  /**
   *  get the commission value or percent
   *
   * @return string commission
   */
  public static function get_commission() {
    $sobuConfig = get_option( 'woocommerce_sobu_settings', null );
    $currency_symbol = get_woocommerce_currency_symbol();
    $commission = (strpos($sobuConfig['commission'],'%') !== false)?
            $sobuConfig['commission']:sprintf( get_woocommerce_price_format(), $currency_symbol, (int) $sobuConfig['commission'] );
    return $commission;
  }
  
  /**
   *  get the mode. Depending on the mode the plugin uses different API keys 
   *
   * @return bool if test mode yes
   */
  public static function get_test_mode() {
    $sobuConfig = get_option( 'woocommerce_sobu_settings', null );
    return $sobuConfig['test'] == 'yes';
  }
  
  /**
   *  get the API key. Depending on the mode the plugin uses different API keys 
   *
   * @return string API key
   */
  public static function get_api_key() {
    $sobuConfig = get_option( 'woocommerce_sobu_settings', null );
    return self::get_test_mode() ? $sobuConfig['test_api_key'] : $sobuConfig['api_key'] ;
  }
  
  /**
   *  get the public key from the settings 
   *
   * @return string public key
   */
  public static function get_public_key() {
    $sobuConfig = get_option( 'woocommerce_sobu_settings', null );
    return $sobuConfig['public_key'];
  }
  
  /**
   *  get the private key from the settings 
   *
   * @return string private key
   */
  public static function get_private_key() {
    $sobuConfig = get_option( 'woocommerce_sobu_settings', null );
    return $sobuConfig['private_key'];
  }
  
  /**
   *  get the server name 
   *
   * @return string server name
   */
  public static function get_server_name( ) {
    $server_name = (strpos($_SERVER['SERVER_NAME'],'www.')===false)?'www.'.$_SERVER['SERVER_NAME']:$_SERVER['SERVER_NAME'];
    return $server_name;
  }
  
	/**
	 * Returns the main product image src
	 *
	 * @param string $size (default: 'shop_thumbnail')
	 * @return string
	 */
	public function get_product_image( $product_id) {
		$image = '';

		if ( has_post_thumbnail( $product_id ) ) {
      $post_thumbnail_id = get_post_thumbnail_id( $product_id );
      $src = wp_get_attachment_image_src( $post_thumbnail_id );
		} elseif ( ( $parent_id = wp_get_post_parent_id( $product_id ) ) && has_post_thumbnail( $parent_id ) ) {
      $post_thumbnail_id = get_post_thumbnail_id( $parent_id );
      $src = wp_get_attachment_image_src( $post_thumbnail_id );
		} else {
        self::log('sobu: no image found for product ' . $product_id);
			return '';
		}
		return $src[0];
	}
  
  /**
   *  creates the sobu order array
   *
   * @param integer with order_id
   * @return array with sobu ordered order array
   */
  public static function sobu_order_data( $order ) {
    $sobuArray = array();
    $sobuCtr = 0;
    $line_items = $order->get_items();
    $sobuTotal = 0;

		// Loop items
		foreach ( $line_items as $item ) {

      if ($item["line_total"] <= 0) continue;
      $sobuArray[$sobuCtr]["qty"] = $item["qty"];
      $sobuArray[$sobuCtr]["id"]  = $item["product_id"];
      $sobuArray[$sobuCtr]["name"] = $item["name"];
      $sobuArray[$sobuCtr]["final_price"] = $item["line_total"];
      $sobuArray[$sobuCtr]["image"] = self::get_product_image( $item["product_id"] );
      $sobuCtr++;
		}
    for ($i = 0; $i < $sobuCtr; ++$i) {
        $arr_data[] = array (
            "quantity" => (int) $sobuArray[$i]["qty"],
            "product" => array (
                "id" => $sobuArray[$i]["id"],
                "languageVersions" => array (
                    self::get_lang() => array (
                      "name" => $sobuArray[$i]["name"]
                    )
                  ),
                "image" =>  $sobuArray[$i]["image"]
            ),
            "id" => $sobuArray[$i]["id"],
            "editable" => "false",
            "GTIN" => "",
        );
        $sobuTotal += $sobuArray[$i]["final_price"];
        //$sobuTotal += $sobuArray[$i]["final_price"];
    }
    return array("arr_data" => $arr_data, "sobuTotal" => $sobuTotal);
  }
  
  /**
   *  sobu banner and publish button in the old order view
   *
   * @param bool success
   */
  public static function view_order( $order_id ) {
    
		if ( 0 < $order_id ) {
			$order = wc_get_order( $order_id );
      $orderArr = self::sobu_order_data( $order );
      $orderJson = '{"id":"' . $order_id . '", "items":' . json_encode($orderArr["arr_data"]) . '}';
      $sobuTotalStr = number_format($orderArr["sobuTotal"], 2, ".", "" ) . get_woocommerce_currency();
      $signature = self::get_digital_signature($orderJson);
      
      // coming from the email
      
      if ( isset( $_GET['sobuPublish'] ) ) {
        // Return from E-Mail Link
        $secret = self::get_public_key();

        $customer_id = $order->get_user_id();;
        $c = $_GET['c'];
        $u = md5( $order_id . $customer_id . $secret );

        if ($c !== $u) {
          self::log('sobu: md5 failure');
          return;
        } else {
          $submitSobuPublish = true;
        }
      }
      $file = SOBU__PLUGIN_DIR . 'views/order.php';
    }

		include( $file );
  }
  
  /**
   *  sobu banner and publish button in the order received page
   *
   * @param bool success
   */
  public static function thankyou( $order_id ) {
    
		if ( 0 < $order_id ) {
			$order = wc_get_order( $order_id );
      $orderArr = self::sobu_order_data( $order );
      $orderJson = '{"id":"' . $order_id . '", "items":' . json_encode($orderArr["arr_data"]) . '}';
      $sobuTotalStr = number_format($orderArr["sobuTotal"], 2, ".", "") . get_woocommerce_currency();
      $signature = self::get_digital_signature($orderJson);
      
      // Tracking
      $sobuClickId = WC()->session->get( 'sobuClickId', array() );
      if ( $sobuClickId ANd $sobuClickId > 0) {

        /** Register Sale (Tracking) **/
        $api_url = "https://www.sobu.ch/register";

        $param = "apiKey=".self::get_api_key()."&amp;orderId=" . $order_id . "&amp;total=" . $sobuTotalStr . "&amp;signature=" . self::get_digital_signature( $order_id . "#" . $sobuTotalStr ) . "&amp;clickId=" . (int) $sobuClickId;

        // hardcode
        $sobuTracking = "client-server";
        if ( $sobuTracking == "client-server" OR ! function_exists( curl_version() ) ) {
          $iFrame = "<iframe src=\"https://www.sobu.ch/register?".$param."\" style=\"visibility: hidden; position: absolute;\"></iframe>";
        } else {
          // CURL
          $postdata = array(
              "apiKey" => self::get_api_key(),
              "orderId" => $order_id,
              "total" => $sobuTotalStr,
              "signature" => get_digital_signature($order_id."#".$sobuTotalStr),
              "clickId" => $sobuClickId,
          );
          $ch = curl_init( $api_url );
          curl_setopt( $ch, CURLOPT_POST, true );
          curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $postdata ) );
          curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
          $result = json_decode( curl_exec( $ch ) );
          curl_close( $ch );

          if( $result->isError ){
              print_r( $result );
          }
        }
        // Remove Session Data
        WC()->session->set('sobuClickId', null);
      }
      $file = SOBU__PLUGIN_DIR . 'views/order.php';
    }

		include( $file );
  }

	/**
	 * showing the info page content
	 * @static
	 */
	public static function info_page() {
    
    if (! isset( $_GET['sobuClickId']) ) {
      wc_get_template( 'info.php', array(
          'voucher'         => self::get_voucher(),
          'commission'      => self::get_commission(),
          'server_name'     => self::get_server_name(),
          'lang'            => self::get_lang(),
          'sobu_url'        => 'http://www.sobu.ch',
      ), null, SOBU__PLUGIN_DIR . 'views/' );
    } else {
      if (! isset( $_GET['vouchercode']) ) {
        $coupon_code = self::get_voucher_code();
      } else {
        $coupon_code = $_GET['vouchercode'];
      }
      $info_page_id = get_option( 'woocommerce-sobu_sobu_page_id' );
      error_log(get_page_uri($info_page_id));
      wc_get_template( 'buddy.php', array(
          'voucher'         => self::get_voucher(),
          'lang'            => self::get_lang(),
          'info_url'        => get_page_uri($info_page_id),
      ), null, SOBU__PLUGIN_DIR . 'views/' );
      // Buddypage
    }
    
  }

	/**
	 * buddy tracking
	 * @static
	 */
	public static function loadido() {
    if (isset(WC()->cart)) {
      $cart = WC()->cart;
      $cart->get_cart_from_session();
      // tracking, give buddy the voucher
      if ( isset( $_GET['sobuClickId'] ) ) {
        if (! isset( $_GET['vouchercode']) ) {
          $coupon_code = self::get_voucher_code();
        } else {
          $coupon_code = $_GET['vouchercode'];
        }
        // Check if applied
        if (! $cart->has_discount( $coupon_code ) ) {
          $cart->add_discount( sanitize_text_field( $coupon_code ) );
        }
        WC()->session->set('sobuClickId', (int) $_GET['sobuClickId']);
      }
    }
  }
  
  /**
   *  sobu link in order email
   *
   * @param bool success
   */
  public static function email( $order, $sent_to_admin = false, $plain_text = false ) {
    $secret =  self::get_public_key();
    $userId = $order->get_user_id();

    $c = md5($order->id . $userId . $secret);

    $sobuPublishParamStr = "&sobuPublish=1"
                        . "&order_id="    . $order->id
                        . "&customer_id=" . $userId
                        . "&c="           . $c;
    $mail = new WC_Email_Customer_Processing_Order();
    $mail_type = $mail->get_email_type();
    if ( 'plain' == $mail_type ) {
      $file = SOBU__PLUGIN_DIR . 'views/email_plain.php';
    } else {
      $file = SOBU__PLUGIN_DIR . 'views/email_html.php';
    }
    include $file;
    return;
		if ( 0 < $order_id ) {
			$order = wc_get_order( $order_id );
      $orderArr = self::sobu_order_data( $order );
      $orderJson = '{"id":"' . $order_id . '", "items":' . json_encode($orderArr["arr_data"]) . '}';
      $sobuTotalStr = number_format($orderArr["sobuTotal"], 2, ".", "" ) . get_woocommerce_currency();
      $signature = self::get_digital_signature($orderJson);
      $file = SOBU__PLUGIN_DIR . 'views/order.php';
    }

		include( $file );
  }

	/**
	 * Display errors by overriding the display_errors() method
	 * @see display_errors()
	 */
	public function display_errors( ) {

		// loop through each error and display it
		foreach ( $this->errors as $key => $value ) {
      switch ($value) {
        case 'api_key':
          $msg = 'Make sure it is 36 characters';
          break;
        case 'test_api_key':
          $msg = 'Make sure it is 36 characters';
          break;
        case 'coupon_code':
          $msg = __( 'Coupon does not exist!', 'woocommerce' );
          break;
        case 'commission':
          $msg = 'Please enter a commission for recommendations, either fixed or add a % on the end';
          break;
        case 'private_key':
          $msg = 'An error occured when trying to create the SSL keys';
          break;
        default:
          $msg = '';
      }
			?>
			<div class="error">
				<p><?php _e( 'Looks like you made a mistake with the ' . $value . ' field. ' . $msg, 'woocommerce-sobu' ) . $msg; ?></p>
			</div>
			<?php
		}
	}

	public static function log( $debug ) {
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG )
			error_log( print_r( compact( 'debug' ), 1 ) ); //send message to debug.log when in debug mode
	}

}

endif;