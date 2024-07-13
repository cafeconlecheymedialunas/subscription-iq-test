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

// Función para redireccionar después del checkout y obtener el último resultado del test
add_action('woocommerce_thankyou', 'redirect_after_checkout_and_get_last_iq_result', 10, 1);
function redirect_after_checkout_and_get_last_iq_result($order_id) {
    // Obtener el ID del usuario que realizó el pedido
    $user_id = get_current_user_id();

    // Obtener el último resultado del test IQ
    $IQTestResultManager = IQTestResultManager::getInstance();
    $result = $IQTestResultManager->getLastIQResult($user_id);

    if (is_wp_error($result)) {
        // Manejar el caso de error

        wp_redirect('/my-account/iq-test/');
        
    }

    // Redirigir a la página del resultado del test con el ID del test
    wp_redirect('/my-account/iq-test/?result_id=' . $result->ID);
    exit;
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












