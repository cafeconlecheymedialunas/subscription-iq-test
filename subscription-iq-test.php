<?php
/*
Plugin Name:       Subscription IQ Test
Plugin URI:        https://https://github.com/cafeconlecheymedialunas/subscription-iq-test
Description:       A Subscription Woocommerce Plugin
Version:           1.0.0
Author:            Mauro Gaitan
Author URI:        https://https://github.com/cafeconlecheymedialunas/
License:           GPL-2.0+
License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain:       subscription-iq-test
Domain Path:       /languages
 */

// If this file is called directly, abort.
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );
define("PLUGIN_URL",plugin_dir_url(__FILE__));


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-subscription-iq-test-activator.php
 */
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-subscription-iq-test-activator.php';
    Subscription_Iq_Test_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-subscription-iq-test-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-subscription-iq-test-deactivator.php';
	Subscription_Iq_Test_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-subscription-iq-test.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name() {

	$plugin = new Subscription_Iq_Test();
	$plugin->run();

}
run_plugin_name();


// Registrar el Custom Post Type para los Tests


add_action('after_setup_theme', 'crb_load');
function crb_load()
{
    require_once 'vendor/autoload.php';
    \Carbon_Fields\Carbon_Fields::boot();
}

// Función para generar el HTML del formulario
function iq_test_form_shortcode($atts)
{

    ob_start(); // Inicia el almacenamiento en búfer de salida

    require_once plugin_dir_path(__FILE__) . 'public/partials/subscription-iq-test-public-display.php';

    return ob_get_clean(); // Devuelve el contenido del búfer de salida
}
add_shortcode('iq_test_form', 'iq_test_form_shortcode');


function iq_test_show_shortcode($atts)
{

    ob_start(); // Inicia el almacenamiento en búfer de salida

    require_once plugin_dir_path(__FILE__) . 'public/partials/subscription-iq-test-show.php';

    return ob_get_clean(); // Devuelve el contenido del búfer de salida
}
add_shortcode('iq_test_show', 'iq_test_show_shortcode');



add_action('wp_ajax_add_to_cart_and_redirect', 'add_to_cart_and_redirect');
add_action('wp_ajax_nopriv_add_to_cart_and_redirect', 'add_to_cart_and_redirect');

function add_to_cart_and_redirect()
{
    if (isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $quantity = 1;
        $cart = WC()->cart->get_cart();
        $product_in_cart = false;
        foreach ($cart as $cart_item_key => $cart_item) {
            if ($cart_item['product_id'] == $product_id) {
                $product_in_cart = true;
                break;
            }
        }

        if ($product_in_cart) {
            wp_send_json_success();
        }

        // Si el producto no está en el carrito, añadirlo

        $added = WC()->cart->add_to_cart($product_id, $quantity);

        if (!$added) {
            wp_send_json_error('Error al agregar el producto al carrito.');
        }
        wp_send_json_success();

    } else {
        wp_send_json_error();
    }

    wp_die();
}

add_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields');

// Our hooked in function - $fields is passed via the filter!
function custom_override_checkout_fields($fields)
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

add_filter('woocommerce_account_menu_items', 'bbloomer_remove_address_my_account', 9999);

function bbloomer_remove_address_my_account($items)
{
    unset($items['edit-address']);
    unset($items['downloads']);
    unset($items['wishlist']);
    return $items;
}

function ac_add_my_custom_endpoint() {
    add_rewrite_endpoint( 'iq-test', EP_ROOT | EP_PAGES );
}
  
add_action( 'init', 'ac_add_my_custom_endpoint' );
function ac_add_custom_query_vars( $vars ) {
    $vars[] = 'iq-test';
    return $vars;
}
  
add_filter( 'query_vars', 'ac_add_custom_query_vars', 0 );

function ac_add_custom_menu_item_my_account( $items ) {
    $items['iq-test'] = 'Iq Tests';
    return $items;
}
  
add_filter( 'woocommerce_account_menu_items', 'ac_add_custom_menu_item_my_account' );

function ac_iq_test_content_my_account() {
    echo 'Congratulations! You just created a iq tests. You can add any content here or add shortcode.';
    echo do_shortcode( ' /* your shortcode here */ ' );
 }
   
 add_action( 'woocommerce_account_iq-test_endpoint', 'ac_iq_test_content_my_account' );





