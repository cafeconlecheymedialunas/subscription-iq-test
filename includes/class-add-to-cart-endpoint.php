<?php
class AddToCartEndpoint {
    public function __construct() {
        add_action('wp_ajax_add_product_to_cart', array($this, 'add_product_to_cart'));
        add_action('wp_ajax_nopriv_add_product_to_cart', array($this, 'add_product_to_cart'));
    }

    public function add_product_to_cart() {
        // Verifica el nonce de seguridad
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'add_to_cart_nonce')) {
            wp_send_json_error('Invalid nonce');
            wp_die();
        }

        // ID del producto a agregar
        $product_id = intval($_POST['product_id']);

        // Verifica si el producto ya está en el carrito
        $found = false;
        foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
            $cart_product = $values['data'];
            if ($product_id == $cart_product->get_id()) {
                $found = true;
                break;
            }
        }

        // Si no está en el carrito, agrega el producto
        if (!$found) {
            WC()->cart->add_to_cart($product_id);
        }

        // Responde con la URL de redirección al checkout
        wp_send_json(array('redirect' => wc_get_checkout_url()));
    }
}
new AddToCartEndpoint();


