<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://github.com/cafeconlecheymedialunas
 * @since      1.0.0
 *
 * @package    SubscriptionIqTest
 * @subpackage SubscriptionIqTest/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    SubscriptionIqTest
 * @subpackage SubscriptionIqTest/public
 * @author     Mauro Gaitan <maurodeveloper86@gmail.com>
 */
class SubscriptionIqTestPublic {

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
		 * defined in SubscriptionIqTestLoader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The SubscriptionIqTestLoader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/subscription-iq-test-public.css', array(), $this->version, 'all' );

		wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

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
		 * defined in SubscriptionIqTestLoader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The SubscriptionIqTestLoader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		 wp_enqueue_script("js_subs", PLUGIN_URL. 'public/js/subscription-iq-test-public.js', array('jquery'), "1.0.0", false);

		 wp_localize_script("js_subs", 'ajax_object', array(
			 'ajax_url' => admin_url('admin-ajax.php'),
			 'nonce' => wp_create_nonce('add_to_cart_nonce'),
			 'checkout_url' => wc_get_checkout_url(),
			 "subscription_plans_url" =>  carbon_get_theme_option("url_subscription_plans")
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

	public function add_content_to_my_account()
    {
        $iq_test_manager = IQTestResultManager::getInstance();
        ob_start();
        if (isset($_GET["test_id"]) && !empty($_GET["test_id"])) {
            $test_id = intval($_GET["test_id"]);

            $result = $iq_test_manager->viewResult($test_id);

            require_once PLUGIN_URL . 'public/partials/subscription-iq-test-show.php';

        } else {
            $testResults = $iq_test_manager->getTestResultsByUser();
            require_once PLUGIN_URL . 'public/partials/subscription-iq-test-list.php';
        }

        $content = ob_get_clean(); // Obtiene y limpia el contenido del búfer de salida
        echo $content;
    }

	public function custom_override_checkout_fields($fields)
    {

        unset($fields['billing']['billing_phone']);
        unset($fields['billing']['billing_company']);
        unset($fields['billing']['billing_country']);
        unset($fields['billing']['billing_address_1']);
        unset($fields['billing']['billing_address_2']);
        unset($fields['billing']['billing_state']);
        unset($fields['billing']['billing_city']);
        unset($fields['billing']['billing_postcode']);
        return $fields;
    }

    public function remove_tabs_to_my_account($items)
    {
        unset($items['edit-address']);
        unset($items['downloads']);
        unset($items['wishlist']);
        return $items;
    }

    public function add_my_custom_endpoint()
    {
        add_rewrite_endpoint('iq-test', EP_ROOT | EP_PAGES);
    }

    public function add_custom_query_vars($vars)
    {
        $vars[] = 'iq-test';
        return $vars;
    }

    public function add_custom_menu_item_to_my_account($items)
    {
        $items['iq-test'] = 'Iq Tests';
        return $items;
    }

}

