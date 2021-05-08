<?php
/**
 * Plugin Name: AffiliateWP - Pushover Notifications
 * Plugin URI: http://affiliatewp.com/addons/pushover-notifications
 * Description: Adds Pushover support to AffiliateWP
 * Author: Chris Klosowski
 * Author URI: https://filament-studios.com
 * Version: 1.0.2
 * Text Domain: affiliate-wp-pushover
 * Domain Path: languages
 *
 * AffiliateWP is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * AffiliateWP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AffiliateWP. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package AffiliateWP Pushover Notifications
 * @category Core
 * @author Chris Klosowski
 * @version 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

final class AffiliateWP_Pushover {

	/** Singleton *************************************************************/

	/**
	 * @var AffiliateWP_Pushover The one true AffiliateWP_Pushover
	 * @since 1.0
	 */
	private static $instance;

	private static $plugin_dir;
	private static $version;

	/**
	 * Main AffiliateWP_Pushover Instance
	 *
	 * Insures that only one instance of AffiliateWP_Pushover exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0
	 * @static
	 * @staticvar array $instance
	 * @return The one true AffiliateWP_Pushover
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AffiliateWP_Pushover ) ) {
			self::$instance = new AffiliateWP_Pushover;

			self::$plugin_dir = plugin_dir_path( __FILE__ );
			self::$version    = '1.0.2';

			self::$instance->load_textdomain();
			self::$instance->includes();
			self::$instance->init();

		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliate-wp-pushover' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliate-wp-pushover' ), '1.0' );
	}

	/**
	 * Loads the plugin language files
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory
		$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$lang_dir = apply_filters( 'aff_wp_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter
		$locale   = apply_filters( 'plugin_locale',  get_locale(), 'affiliate-wp-pushover' );
		$mofile   = sprintf( '%1$s-%2$s.mo', 'affiliate-wp-pushover', $locale );

		// Setup paths to current locale file
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/affiliate-wp-pushover/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/affiliate-wp-pushover/ folder
			load_textdomain( 'affiliate-wp-pushover', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/affiliate-wp-pushover/languages/ folder
			load_textdomain( 'affiliate-wp-pushover', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'affiliate-wp-pushover', false, $lang_dir );
		}
	}

	/**
	 * Include required files
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function includes() {

		require_once self::$plugin_dir . 'includes/hooks.php';

		if( is_admin() ) {

			require_once self::$plugin_dir . 'includes/admin/settings.php';

		}

	}

	/**
	 * Any filters needed at init
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function init() {

		if( is_admin() ) {
			self::$instance->updater();
		}

	}

	public function updater() {

		if( class_exists( 'AffWP_AddOn_Updater' ) ) {
			$updater = new AffWP_AddOn_Updater( 13199, __FILE__, self::$version );
		}
	}

}

/**
 * The main function responsible for returning the one true AffiliateWP_Pushover
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 *
 * @since 1.0
 * @return object The one true AffiliateWP_Pushover Instance
 */
function affiliate_wp_pushover() {
	return AffiliateWP_Pushover::instance();
}
add_action( 'plugins_loaded', 'affiliate_wp_pushover', 100 );
