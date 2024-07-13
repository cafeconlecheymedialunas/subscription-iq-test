<?php
$args = array(
    'post_type' => 'product',
    'tax_query' => array(
        array(
            'taxonomy' => 'product_type',
            'field'    => 'slug',
            'terms'    => 'p24_subscription', // Tipo de producto (simple, grouped, variable, etc.)
        ),
    ),
);

$query = new WP_Query($args);

if ($query->have_posts()) : ?>
    <div class="pricing-container">
        <ul class="pricing-list bounce-invert">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php 
                // Obtener los metadatos de suscripción
                $subscription_price = get_post_meta(get_the_ID(), '_subscription_price', true);
                $days = get_post_meta(get_the_ID(), '_days', true);
                ?>
                <li data-type="monthly" class="is-visible">
                    <header class="pricing-header">
                        <h2><?php the_title(); ?></h2>
                        <div class="price">
                            <span class="currency"><?php echo get_woocommerce_currency_symbol(); ?></span>
                            <span class="value"><?php echo esc_html($subscription_price); ?></span>
                            <span class="duration"><?php echo esc_html($days), " days"; ?></span>
                        </div>
                    </header>
                    <div class="pricing-body">
                        <ul class="pricing-features">
                           <?php the_content();?>
                        </ul>
                    </div>
                    <footer class="pricing-footer">
                            <button id="custom-add-to-cart" data-product-id="<?php the_ID(  );?>"  class="select">Subscribe now
                                <span class="spinner" style="display:none;">&#x21BB;</span> <!-- Spinner -->
                            </button>
                           
                    </footer>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <?php wp_reset_postdata(); ?>
<?php else : ?>
    <p>No products found</p>
<?php endif; ?>
<script>
    jQuery(document).ready(function($) {
        function addProductToCart() {
        var product_id = $('#custom-add-to-cart').data('product-id');
        var $button = $('#custom-add-to-cart');
        var $spinner = $button.find('.spinner');
        $spinner.show()
        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            data: {
                action: 'add_to_cart_and_redirect',
                product_id: product_id,
            },
            success: function(response) {
              
                
                if (response.success) {
                    $spinner.hide();
                    window.location.href = '<?php echo wc_get_checkout_url(); ?>';
                } else {
                    alert('Error adding product to cart.');
                    // Re-enable the button and hide the spinner in case of error
                    $button.prop('disabled', false);
                }
            },
            error: function() {
                var $button = $('#custom-add-to-cart');
                var $spinner = $button.find('.spinner');
                $spinner.hide();
                alert('Error adding product to cart.');
                // Re-enable the button and hide the spinner in case of error
                $button.prop('disabled', false);
            }
        });
    }

    $('#custom-add-to-cart').on('click', function(e) {
        e.preventDefault();
        // Trigger form submission
        addProductToCart()
    });
});
</script>
