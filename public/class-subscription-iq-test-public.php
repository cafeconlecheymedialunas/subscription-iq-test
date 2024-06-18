<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://github.com/cafeconlecheymedialunas
 * @since      1.0.0
 *
 * @package    Subscription_Iq_Test
 * @subpackage Subscription_Iq_Test/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Subscription_Iq_Test
 * @subpackage Subscription_Iq_Test/public
 * @author     Mauro Gaitan <maurodeveloper86@gmail.com>
 */
class Subscription_Iq_Test_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	private $order_manager;

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->order_manager = new AddToCartEndpoint();

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/subscription-iq-test-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		 wp_enqueue_script("js_subs", PLUGIN_URL. 'public/js/subscription-iq-test-public.js', array('jquery'), "1.0.0", false);

		 wp_localize_script("js_subs", 'wc_add_to_cart_params', array(
			 'ajax_url' => admin_url('admin-ajax.php'),
			 'nonce' => wp_create_nonce('add_to_cart_nonce'),
			 'redirect_url' => wc_get_checkout_url(),
		 ));
	 
		 wp_enqueue_style(
			 'css_subs', // Handle
			 PLUGIN_URL . 'public/css/subscription-iq-test-public.css', // URL to the stylesheet
			 array(), // Dependencies
			 '1.0.0', // Version
			 'all' // Media
		 );

	}


	public function add_product_to_cart(){
		
		$this->order_manager->add_product_to_cart();
	}

}

