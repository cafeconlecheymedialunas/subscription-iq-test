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
    SubscriptionIqTestActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-subscription-iq-test-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-subscription-iq-test-deactivator.php';
	SubscriptionIqTestDeactivator::deactivate();
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

	$plugin = new SubscriptionIqTest();
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

add_action( 'woocommerce_account_iq-test_endpoint', 'add_content_to_my_account' );
function add_content_to_my_account()
{
    $iq_test_manager = IQTestResultManager::getInstance();
    ob_start();
    if (isset($_GET["result_id"]) && !empty($_GET["result_id"])) {
        $result_id = intval($_GET["result_id"]);

        $result = $iq_test_manager->viewResult($result_id);

        require_once plugin_dir_path( __FILE__ ) . 'public/partials/subscription-iq-test-show.php';

    } else {
        $testResults = $iq_test_manager->viewAllResults();
        require_once plugin_dir_path( __FILE__ ) .'public/partials/subscription-iq-test-list.php';
    }

    $content = ob_get_clean(); // Obtiene y limpia el contenido del búfer de salida
    echo $content;
}







function iq_test_subscription_plans($atts)
{

    ob_start(); 

    require_once plugin_dir_path(__FILE__) . 'public/partials/subscription-iq-test-plans.php';

    return ob_get_clean(); // Devuelve el contenido del búfer de salida
}
add_shortcode('iq_test_subscription_plans', 'iq_test_subscription_plans');


function add_login_logout_register_menu($items, $args) {
    // Obtener el idioma actual usando Polylang
    $current_lang = pll_current_language();

    // Solo añadir los enlaces si es el menú principal
    if ($args->theme_location != '') {
        return $items;
    }

    // Definir los textos según el idioma
    $login_text_en = 'Log In';
    $login_text_pl = 'Log In';

    $logout_text_en = 'Profile';
    $logout_text_pl = 'Profil';

    // Estructura HTML para los enlaces
    $logout_link = '<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-logout"><a href="' . wc_get_account_endpoint_url( 'iq-test' ). 'iq-test'.'" class="elementor-item menu-link">' . ($current_lang == 'pl' ? $logout_text_pl : $logout_text_en) . '</a></li>';
    
    $login_link = '<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-login"><a href="' . wc_get_account_endpoint_url( 'iq-test' ) .'" class="elementor-item menu-link">' . ($current_lang == 'pl' ? $login_text_pl : $login_text_en) . '</a></li>';

    // Añadir los enlaces al menú
    if (is_user_logged_in()) {
        $items .= $logout_link;
    } else {
        $items .= $login_link;
    }

    return $items;
}

add_filter('wp_nav_menu_items', 'add_login_logout_register_menu', 199, 2);



use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action( 'carbon_fields_register_fields', 'crb_attach_theme_options' );
function crb_attach_theme_options() {
    Container::make( 'theme_options', __( 'Subscription Iq Options' ) )
        ->add_fields( array(
            Field::make( 'association', 'url_subscription_plans', 'Subscription Plans Page' )->set_types( array(
                array(
                    'type'      => 'post',
                    'post_type' => 'page',
                )
            ) ),
           
        ) );
        
}




add_action('wp_ajax_add_to_cart_and_redirect', 'addToCartAndRedirectToCheckout');
add_action('wp_ajax_nopriv_add_to_cart_and_redirect', 'addToCartAndRedirectToCheckout');

function addToCartAndRedirectToCheckout()
{
    if (isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $result_id = intval($_POST['result_id']);
        $quantity = 1;
        $cart = WC()->cart->get_cart();
        $product_in_cart = false;

        // Verificar si el producto ya está en el carrito
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
        $cart_item_data = array('result_id' => $result_id);
        $added = WC()->cart->add_to_cart($product_id, $quantity, '', '', $cart_item_data);

        if (!$added) {
            wp_send_json_error('Error al agregar el producto al carrito.');
        }

        wp_send_json_success();

    } else {
        wp_send_json_error('ID de producto no válido.');
    }

    wp_die();
}




add_action('wp_ajax_cancel_subscription', 'cancel_subscription');
add_action('wp_ajax_nopriv_cancel_subscription', 'cancel_subscription');

function cancel_subscription()
{
    if (isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);

        $product = get_post($product_id);

        if(is_wp_error($product)){
            wp_send_json_error('Error creating cancelation post  .');
        }
    
        $user = wp_get_current_user();
        $current_time = current_time('mysql');


        $iqResultManager = IQTestResultManager::getInstance();

        $cancelation =  $iqResultManager->getCancelled($user->ID,$product_id);

        if($cancelation){
            wp_send_json_error("This order has already been canceled before");
        }
        // Crear el post de cancelación
        $cancelation_post = array(
            'post_title'    => 'Cancelation for ' . $product->post_title . ". Usuario:". $user->name. " - ".  $current_time,
            'post_status'   => 'publish',
            'post_type'     => 'cancelation',
        );

        $post_id = wp_insert_post($cancelation_post);

        // Guardar los campos personalizados
        if (!is_wp_error($post_id)) {
            carbon_set_post_meta($post_id, 'user', [$user->ID]);
            carbon_set_post_meta($post_id, 'subscription_id', $product_id);
            carbon_set_post_meta($post_id, 'cancelation_date', $current_time);


            wp_send_json_success("Your subscription has been successfully canceled. You will continue to have access until the expiration date, after which it will not be automatically renewed.");
        } else {
            wp_send_json_error('Error creating cancelation post.');
        }
    } else {
        wp_send_json_error('Invalid product ID or result ID.');
    }

    wp_die();
}

add_action('woocommerce_checkout_create_order', 'add_result_id_to_order', 20, 2);
function add_result_id_to_order($order, $data)
{
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['result_id'])) {
            $order->update_meta_data('result_id', $cart_item['result_id']);
        }
    }
}


add_action('woocommerce_thankyou', 'redirect_after_checkout_and_get_last_iq_result', 10, 1);
function redirect_after_checkout_and_get_last_iq_result($order_id)
{
    $order = wc_get_order($order_id);
    $result_id = $order->get_meta('result_id');
    $myaccount_url = get_permalink(get_option('woocommerce_myaccount_page_id'));

    if ($result_id) {
        wp_redirect($myaccount_url . '/iq-test/?result_id=' . $result_id);
    }

    exit;
}













