<div class="subscription-iq-test-form">
    <?php
    $atts = shortcode_atts(array(
        'test_id' => '', // ID del test
    ), $atts);

    $test_id = intval($atts['test_id']);

    if ($test_id <= 0) {
        echo '<p>Se requiere proporcionar un ID de test válido.</p>';
        return;
    }

    // Obtener el test por su ID
    $test = get_post($test_id);

    if (!$test || $test->post_type !== 'iq_test') {
        echo '<p>No se encontró ningún test con el ID proporcionado.</p>';
        return;
    }

    $preguntas = carbon_get_post_meta($test_id, 'preguntas'); // Obtener las preguntas del test

    if (!$preguntas) {
        echo '<p>No hay preguntas disponibles para este test.</p>';
        return;
    }
    ?>
    <form id="iq-test-form" method="post">
        <input type="hidden" name="action" value="iq_test_save_responses">
        <input type="hidden" name="test_id" value="<?php echo $test_id; ?>">
        <input type="hidden" name="total_steps" value="<?php echo count($preguntas); ?>">
        <?php
        $step = 1; // Contador para los pasos del formulario
        foreach ($preguntas as $index => $pregunta) {
            $index = $index + 1;
            ?>
            <div class="step" id="step-<?php echo $step; ?>">
                <h2><?php echo $pregunta['pregunta_titulo']; ?></h2> 
                <?php if ($pregunta['pregunta_imagen']): ?>
                    <img src="<?php echo wp_get_attachment_image_url($pregunta['pregunta_imagen']); ?>"/>
                <?php endif; ?>
                <div class="options">
                    <?php foreach ($pregunta['pregunta_opciones'] as $subIndex => $opcion): 
                      
                        $subIndex = $subIndex + 1;
                        $key = $index . "_" . $subIndex;
                        ?>
                        <label for="<?php echo $key; ?>">
                            <input type="radio" id="<?php echo $key; ?>" name="q<?php echo $index; ?>" value="<?php echo $key; ?>"/>
                            <img src="<?php echo wp_get_attachment_image_url($opcion['opcion']); ?>" />
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="button-container">
                    <?php if ($step > 1): ?>
                        <button type="button" class="prev-step" data-step="<?php echo $step; ?>">Anterior</button>
                    <?php endif; ?>
                    <?php if ($step < count($preguntas)): ?>
                        <button type="button" class="next-step" data-step="<?php echo $step; ?>" disabled>Siguiente</button>
                    <?php else: ?>
                        <button id="custom-add-to-cart" data-product-id="176">Suscribirme para ver el resultado</button>
                    <?php endif; ?>
                </div>
               
            </div>
            <?php
            $step++; // Incrementar el contador de pasos
        }
        ?>
    </form>
</div>

<script>
    jQuery(document).ready(function($) {
        // Lógica para el formulario de pasos
        var currentStep = 1;
        $('.step').hide();
        $('#step-' + currentStep).show();

        function checkOptions(step) {
            if ($('input[name="q' + step + '"]:checked').length > 0) {
                $('.next-step[data-step="' + step + '"]').prop('disabled', false);
                if (step == $('.step').length) {
                    $('.submit-btn').prop('disabled', false);
                }
            } else {
                $('.next-step[data-step="' + step + '"]').prop('disabled', true);
                if (step == $('.step').length) {
                    $('.submit-btn').prop('disabled', true);
                }
            }
        }

        // Inicialmente deshabilitar el botón "Next" y "Submit" si no hay una opción seleccionada
        checkOptions(currentStep);

        $('input[type="radio"]').change(function() {
            var step = $(this).closest('.step').attr('id').split('-')[1];
            checkOptions(step);
        });

        $('.next-step').click(function() {
            $('#step-' + currentStep).hide();
            currentStep++;
            $('#step-' + currentStep).show();
            checkOptions(currentStep);
        });

        $('.prev-step').click(function() {
            $('#step-' + currentStep).hide();
            currentStep--;
            $('#step-' + currentStep).show();
            checkOptions(currentStep);
        });

        $('#iq-test-form').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                data: formData,
                success: function(response) {
                    console.log(response);
                    // After form submission is successful, add the product to the cart
                    addProductToCart();
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        });

        function addProductToCart() {
            var product_id = $('#custom-add-to-cart').data('product-id');

            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                data: {
                    action: 'add_to_cart_and_redirect',
                    product_id: product_id,
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = '<?php echo wc_get_checkout_url(); ?>';
                    } else {
                        alert('Error al agregar el producto al carrito.');
                    }
                }
            });
        }

        $('#custom-add-to-cart').on('click', function(e) {
            e.preventDefault();
            // Trigger form submission
            $('#iq-test-form').submit();
        });
    });
</script>
