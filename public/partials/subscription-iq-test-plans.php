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

$IQTestResultManager = IQTestResultManager::getInstance();




$result_id = isset($_GET['result_id']) ? intval($_GET['result_id']) : '';

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
                    <?php
                        $subscription = $IQTestResultManager->getActiveSubscription(get_current_user_id(), get_the_ID());
                        $subscription_cancelled = $IQTestResultManager->getCancelledSubscription( get_current_user_id(),get_the_ID());
                        if (is_wp_error($subscription)) {
                            echo '<div>Error retrieving subscription: ' . $subscription->get_error_message() . '</div>';
                        } elseif (is_wp_error($subscription_cancelled)) {
                            echo '<div>Error retrieving cancellation: ' . $subscription_cancelled->get_error_message() . '</div>';
                        } else {
                            if (!$subscription) : ?>
                                <form method="post" class="subscription-form">
                                    <input type="hidden" name="result_id" value="<?php echo $result_id; ?>">
                                    <input type="hidden" name="product_id" value="<?php echo get_the_ID(); ?>">
                                    <button type="submit" class="subscribe-button"><?php echo __("Subscribe now"); ?>
                                        <span class="spinner" style="display:none;">&#x21BB;</span>
                                    </button>
                                </form>
                            <?php else :
                                if (!$subscription_cancelled) : ?>
                                    <form method="post" class="cancel-subscription">
                                        <input type="hidden" name="product_id" value="<?php echo get_the_ID(); ?>">
                                        <input type="hidden" name="result_id" value="<?php echo $result_id; ?>">
                                        <button type="submit" class="subscribe-button">Cancel Subscription
                                            <span class="spinner" style="display:none;">&#x21BB;</span>
                                        </button>
                                    </form>
                                <?php else : ?>
                                    <div>
                                        <h3>Your subscription has been canceled.</h3>
                                        <p>You will continue to have access until the <?php echo $subscription->valid_to; ?>. After which it will not be automatically renewed.</p>
                                    </div>
                                <?php endif; ?>
                            <?php endif;
                        } ?>
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
