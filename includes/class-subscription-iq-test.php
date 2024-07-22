<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://https://github.com/cafeconlecheymedialunas
 * @since      1.0.0
 *
 * @package    SubscriptionIqTest
 * @subpackage SubscriptionIqTest/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    SubscriptionIqTest
 * @subpackage SubscriptionIqTest/includes
 * @author     Mauro Gaitan <maurodeveloper86@gmail.com>
 */
class SubscriptionIqTest {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      SubscriptionIqTestLoader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SubscriptionIqTest_VERSION' ) ) {
			$this->version = SubscriptionIqTest_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'subscription-iq-test';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - SubscriptionIqTestLoader. Orchestrates the hooks of the plugin.
	 * - SubscriptionIqTesti18n. Defines internationalization functionality.
	 * - SubscriptionIqTestAdmin. Defines all hooks for the admin area.
	 * - SubscriptionIqTestPublic. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-subscription-iq-test-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-subscription-iq-test-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-subscription-iq-test-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-subscription-iq-test-public.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-helper.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-add-to-cart-endpoint.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-iq-test.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-result.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cancelation.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-iq-test-result-manager.php';

		

		$this->loader = new SubscriptionIqTestLoader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the SubscriptionIqTesti18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new SubscriptionIqTesti18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new SubscriptionIqTestAdmin( $this->get_plugin_name(), $this->get_version() );

		$iq_test = IqTest::getInstance();
		$result = Result::getInstance();
		$cancelation = Cancelation::getInstance();
		

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'init', $iq_test,"register" );
		$this->loader->add_action('carbon_fields_register_fields', $iq_test,'registerFields');
		$this->loader->add_action( 'init', $result,"register" );
		$this->loader->add_action('carbon_fields_register_fields', $result,'registerFields');
		$this->loader->add_action( 'init', $cancelation,"register" );
		$this->loader->add_action('carbon_fields_register_fields', $cancelation,'registerFields');


	


		
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new SubscriptionIqTestPublic( $this->get_plugin_name(), $this->get_version() );
		$IQTestResultManager = IQTestResultManager::getInstance();


		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action('wp_ajax_iq_test_save_responses', $IQTestResultManager, 'processForm');
        $this->loader->add_action('wp_ajax_nopriv_iq_test_save_responses', $IQTestResultManager, 'processForm');
		

		$this->loader->add_filter( 'woocommerce_account_menu_items',$plugin_public, 'add_custom_menu_item_to_my_account' );
		$this->loader->add_filter( 'query_vars', $plugin_public,'add_custom_query_vars', 0 );
		$this->loader->add_action( 'init',$plugin_public, 'add_my_custom_endpoint' );
		$this->loader->add_filter('woocommerce_account_menu_items', $plugin_public,'remove_tabs_to_my_account', 9999);
		$this->loader->add_filter('woocommerce_checkout_fields', $plugin_public, 'custom_override_checkout_fields');
		//$this->loader->add_action('wp_ajax_add_to_cart_and_redirect', $IQTestResultManager,'addToCartAndRedirectToCheckout');
		//$this->loader->add_action('wp_ajax_nopriv_add_to_cart_and_redirect', $IQTestResultManager,'addToCartAndRedirectToCheckout');
	

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    SubscriptionIqTestLoader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
