<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://github.com/cafeconlecheymedialunas
 * @since      1.0.0
 *
 * @package    Subscription_Iq_Test
 * @subpackage Subscription_Iq_Test/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Subscription_Iq_Test
 * @subpackage Subscription_Iq_Test/admin
 * @author     Mauro Gaitan <maurodeveloper86@gmail.com>
 */
class Subscription_Iq_Test_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Subscription_Iq_Test_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Subscription_Iq_Test_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/subscription-iq-test-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Subscription_Iq_Test_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Subscription_Iq_Test_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		 if (!is_admin()) {
			return;
		}
	
		// Obtener la pantalla actual
		$screen = get_current_screen();
	
		// Verificar si estamos en la pantalla de edición de 'iq_result'
		if ($screen->post_type === 'iq_result' && ($hook === 'post.php' || $hook === 'post-new.php')) {
			wp_enqueue_script("js_subs_admin", plugin_dir_url(__FILE__) . 'admin/js/subscription-iq-test-admin.js', array('jquery'), $this->version, false);
	
		}
	

	}

}
