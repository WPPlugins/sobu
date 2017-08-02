<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * @package 
 * @version 0.1-alpha
 */
/*
	Plugin Name: WooCommerce sobu
	Plugin URI: http://wordpress.org/plugins/sobu/
	Description: This is a plugin, it makes it possible to integrate a WooCommerce shop into the <cite>sobu</cite> network.
	Author: Christian Fraunholz php10.de
	Version: 0.1-alpha
	Author URI: http://php10.de/sobu/
	Text Domain: woocommerce-sobu
	Domain Path: /lang
 */

/*  Copyright 2015  Dipl.-Ing. (FH) Christian Konrad Fraunholz  (email : christian@php10.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
define( 'SOBU_VERSION', '0.1' );
define( 'SOBU__MINIMUM_WP_VERSION', '3.1' );
define( 'SOBU__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SOBU__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

register_activation_hook( __FILE__, array( 'WC_Sobu', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'WC_Sobu', 'plugin_deactivation' ) );

if ( ! class_exists( 'WC_Sobu' ) ) :

class WC_Sobu {

	/**
	* Construct the plugin.
	*/
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	* Initialize the plugin.
	*/
	public function init() {

		// Checks if WooCommerce is installed.
		if ( class_exists( 'WC_Integration' ) ) {
      do_action( 'before_woocommerce-sobu_init' );

      // Set up localisation
      $this->load_plugin_textdomain();
      
			// Include our integration class.
			include_once '_inc/class-wc-sobu.php';

			// Register the integration.
			add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
      
      // Init action
      do_action( 'woocommerce-sobu_init' );
		} else {
			// throw an admin error if you like
		}
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
	 * Add a new integration to WooCommerce.
	 */
	public function add_integration( $integrations ) {
		$integrations[] = 'WC_Integration_sobu';
		return $integrations;
	}


  /**
   * Load Localisation files.
   *
   * Note: the first-loaded translation file overrides any following ones if the same translation is present
   */
  public function load_plugin_textdomain() {
    $locale = apply_filters( 'plugin_locale', get_locale());
		$dir    = trailingslashit( dirname(__FILE__) );

    /**
     * global Locale. Looks in:
     *
     * 	 	- woocommerce-sobu/languages/LOCALE.mo (which if not found falls back to:)
     * 	 	- woocommerce-sobu/languages/en.mo
     */
    load_textdomain( 'woocommerce-sobu', $dir . 'languages/' . self::get_lang() . '.mo' );
  }

	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activation() {
		if ( version_compare( $GLOBALS['wp_version'], SOBU__MINIMUM_WP_VERSION, '<' ) ) {
			load_plugin_textdomain( 'woocommerce-sobu' );
			
			$message = '<strong>'.sprintf(esc_html__( 'sobu %s requires WordPress %s or higher.' , 'woocommerce-sobu'), SOBU_VERSION, SOBU__MINIMUM_WP_VERSION ).'</strong> '.sprintf(__('Please <a href="%1$s">upgrade WordPress</a> to a current version.', 'woocommerce-sobu'), 'https://codex.wordpress.org/Upgrading_WordPress');

			WC_Sobu::bail_on_activation( $message );
		} else if (! extension_loaded( 'openssl' ) ) {
			$message = '<strong>'.sprintf(esc_html__( 'sobu requires the openssl extension to be loaded.' , 'woocommerce-sobu') ).'</strong> ';

			WC_Sobu::bail_on_activation( $message );
    } else {
    
      // add the sobu info page
      $pages = apply_filters( 'woocommerce-info_create_pages', array(
        'sobu' => array(
          'name'    => _x( 'sobu-info', 'Page slug', 'woocommerce-sobu' ),
          'title'   => _x( 'sobu', 'Page title', 'woocommerce-sobu' ),
          'content' => '[woocommerce-sobu_info]'
        )
      ) ) ;

      foreach ( $pages as $key => $page ) {
        $page_id = wc_create_page( esc_sql( $page['name'] ), 'woocommerce-sobu_' . $key . '_page_id', $page['title'], $page['content'], ! empty( $page['parent'] ) ? wc_get_page_id( $page['parent'] ) : '' );
        add_option( 'woocommerce-sobu_' . $key . '_page_id', $page_id, '', 'no' );
      }
    }
  }
  
  
	private static function bail_on_activation( $message, $deactivate = true ) {
?>
<!doctype html>
<html>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<style>
* {
	text-align: center;
	margin: 0;
	padding: 0;
	font-family: "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
}
p {
	margin-top: 1em;
	font-size: 18px;
}
</style>
<body>
<p><?php echo esc_html( $message ); ?></p>
</body>
</html>
<?php
		if ( $deactivate ) {
			$plugins = get_option( 'active_plugins' );
			$sobu = plugin_basename( SOBU__PLUGIN_DIR . 'woocommerce-sobu.php' );
			$update  = false;
			foreach ( $plugins as $i => $plugin ) {
				if ( $plugin === $sobu ) {
					$plugins[$i] = false;
					$update = true;
				}
			}

			if ( $update ) {
				update_option( 'active_plugins', array_filter( $plugins ) );
			}
		}
		exit;
	}

	/**
	 * Removes all connection options
	 * @static
	 */
	public static function plugin_deactivation( ) {
    global $wpdb;
    
		//tidy up
    
    // pages
    $info_page_id = get_option( 'woocommerce-sobu_sobu_page_id' );
    wp_delete_post( $info_page_id , true );
    
    // coupons
//    $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ( 'shop_coupon' ) AND post_name ILIKE 'sObu';" );error_log("DELETE FROM {$wpdb->posts} WHERE post_type IN ( 'shop_coupon' ) AND post_name LIKE 'sobu';" );
//    $wpdb->query( "DELETE FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE wp.ID IS NULL;" );
	}
}

$WC_Sobu = new WC_Sobu( __FILE__ );

endif;
