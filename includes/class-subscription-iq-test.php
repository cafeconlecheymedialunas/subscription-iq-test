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
 * @package    Subscription_Iq_Test
 * @subpackage Subscription_Iq_Test/includes
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
 * @package    Subscription_Iq_Test
 * @subpackage Subscription_Iq_Test/includes
 * @author     Mauro Gaitan <maurodeveloper86@gmail.com>
 */
class Subscription_Iq_Test {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Subscription_Iq_Test_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'SUBSCRIPTION_IQ_TEST_VERSION' ) ) {
			$this->version = SUBSCRIPTION_IQ_TEST_VERSION;
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
	 * - Subscription_Iq_Test_Loader. Orchestrates the hooks of the plugin.
	 * - Subscription_Iq_Test_i18n. Defines internationalization functionality.
	 * - Subscription_Iq_Test_Admin. Defines all hooks for the admin area.
	 * - Subscription_Iq_Test_Public. Defines all hooks for the public side of the site.
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

		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-add-to-cart-endpoint.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-iq-test.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-result.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-iq-test-result-manager.php';

		$this->loader = new Subscription_Iq_Test_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Subscription_Iq_Test_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Subscription_Iq_Test_i18n();

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

		$plugin_admin = new Subscription_Iq_Test_Admin( $this->get_plugin_name(), $this->get_version() );

		$iq_test = new IqTest();
		$result = new Result();
		

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'init', $iq_test,"register" );
		$this->loader->add_action('carbon_fields_register_fields', $iq_test,'register_fields');
		$this->loader->add_action( 'init', $result,"register" );
		$this->loader->add_action('carbon_fields_register_fields', $result,'register_fields');


		
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Subscription_Iq_Test_Public( $this->get_plugin_name(), $this->get_version() );
		$iq_test_result_manager = IQ_Test_Result_Manager::get_instance();


		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action('wp_ajax_iq_test_save_responses', $iq_test_result_manager, 'process_form');
        $this->loader->add_action('wp_ajax_nopriv_iq_test_save_responses', $iq_test_result_manager, 'process_form');

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
	 * @return    Subscription_Iq_Test_Loader    Orchestrates the hooks of the plugin.
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
