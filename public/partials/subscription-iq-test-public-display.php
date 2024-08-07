<div class="subscription-iq-test-form">
    <?php
    // Código PHP original
 

    $atts = shortcode_atts(array(
        'test_id' => '', // ID del test
    ), $atts);

    $test_id = intval($atts['test_id']);
    

    if ($test_id <= 0) {
        echo '<p>A valid test ID is required.</p>';
        return;
    }

    $test = get_post($test_id);

    if (!$test || $test->post_type !== 'iq_test') {
        echo '<p>No test found with the provided ID.</p>';
        return;
    }

    $preguntas = carbon_get_post_meta($test_id, 'questions'); // Get test questions

    if (!$preguntas) {
        echo '<p>No questions available for this test.</p>';
        return;
    }

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

    $userHasSubscription = $IQTestResultManager->userHasValidSubscription();


    if(is_wp_error($query) || isset($query->posts[0])):
    ?>
    <div id="test-cover" class="test-cover active">
        <h1><?php echo get_the_title($test_id); ?></h1>
        <div><?php echo apply_filters('the_content', $test->post_content); ?></div>
        <button id="start-test">Start</button>
    </div>

    <form id="iq-test-form" method="post" style="display:none;">
        <input type="hidden" name="action" value="iq_test_save_responses">
        <input type="hidden" name="test_id" value="<?php echo $test_id; ?>">
        <input type="hidden" name="total_steps" value="<?php echo count($preguntas); ?>">
        <?php
        $step = 1; // Form step counter
        foreach ($preguntas as $index => $pregunta) {
            $index = $index + 1;
        ?>
            <div class="step" id="step-<?php echo $step; ?>" style="display:none;">
                <h2><?php echo $pregunta['question_title']; ?></h2>
                <?php if ($pregunta['question_image']): ?>
                    <img src="<?php echo wp_get_attachment_image_url($pregunta['question_image']); ?>"/>
                <?php endif; ?>
                <div class="options">
                    <?php foreach ($pregunta['question_options'] as $subIndex => $opcion):
                        $subIndex = $subIndex + 1;
                        $key = $index . "_" . $subIndex;
                    ?>
                        <label for="<?php echo $key; ?>">
                            <div class="label-container">
                            <input type="radio" id="<?php echo $key; ?>" name="q<?php echo $index; ?>" value="<?php echo $key; ?>"/>
                            <img src="<?php echo wp_get_attachment_image_url($opcion['option_image']); ?>" />
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="button-container">
                    
                    <?php if ($step > 1): ?>
                        <button type="button" class="prev-step" data-step="<?php echo $step; ?>">Previous</button>
                    <?php endif; ?>
                    <?php if ($step < count($preguntas)): ?>
                        <button type="button" class="next-step" data-step="<?php echo $step; ?>" disabled>Next</button>
                    <?php else: 
                   
                        ?>
                        <button id="custom-add-to-cart" data-product-id="">
                            <?php echo ($userHasSubscription) ?"View Results"  : "Subscribe to see the result"; ?>
                            <span class="spinner" style="display:none;">&#x21BB;</span> <!-- Spinner -->
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php
            $step++; // Increment the step counter
        }
        ?>
    </form>
    <?php else:?>
        <h2><?php echo "Something went wrong, contact us or try again later.";?></h2>

    <?php endif;?>
</div>

<script>
jQuery(document).ready(function($) {
    var currentStep = 1;

    // Show initial cover and hide form
    $('#test-cover').show();
    $('#iq-test-form').hide();

    // When "Start the test" is clicked
    $('#start-test').click(function() {
        $('#test-cover').removeClass('active');
        setTimeout(function() {
            $('#test-cover').hide();
            $('#iq-test-form').show();
            $('#step-' + currentStep).addClass('active').show();
        }, 500); // Match the duration of your CSS transition
    });

    function checkOptions(step) {
        if ($('input[name="q' + step + '"]:checked').length > 0) {
            $('.next-step[data-step="' + step + '"]').prop('disabled', false);
            if (step == $('.step').length) {
                $('#custom-add-to-cart').prop('disabled', false);
            }
        } else {
            $('.next-step[data-step="' + step + '"]').prop('disabled', true);
            if (step == $('.step').length) {
                $('#custom-add-to-cart').prop('disabled', true);
            }
        }
    }

    // Initially disable "Next" and "Submit" buttons if no option is selected
    checkOptions(currentStep);

    $('input[type="radio"]').change(function() {
        var step = $(this).closest('.step').attr('id').split('-')[1];
        var $options = $(this).closest('.options').find('label');
        $options.removeClass('current'); // Remove 'current' class from all options
        $(this).closest('label').addClass('current'); // Add 'current' class to the selected option
        checkOptions(step);
    });

    $('.next-step').click(function() {
        $('#step-' + currentStep).removeClass('active');
        setTimeout(function() {
            $('#step-' + currentStep).hide();
            currentStep++;
            $('#step-' + currentStep).addClass('active').show();
            checkOptions(currentStep);
        }, 500); // Match the duration of your CSS transition
    });

    $('.prev-step').click(function() {
        $('#step-' + currentStep).removeClass('active');
        setTimeout(function() {
            $('#step-' + currentStep).hide();
            currentStep--;
            $('#step-' + currentStep).addClass('active').show();
            checkOptions(currentStep);
        }, 500); // Match the duration of your CSS transition
    });

    $('#iq-test-form').on('submit', function(e) {
        e.preventDefault();
        console.log(e)

        var formData = $(this).serialize();

        // Disable the button and show the spinner
        var $button = $('#custom-add-to-cart');
        var $spinner = $button.find('.spinner');
        $button.prop('disabled', true);
        $spinner.show();

        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            data: formData,
            success: function(response) {
                console.log(response);
                // After form submission is successful, add the product to the cart
                const {data} = response;

                if(response.success){
                    

                    const {user,test,results} = data;


                    if(user.has_valid_subscription){
                       const url = "<?php echo wc_get_account_endpoint_url( 'iq-test' );?>?result_id="+ results.result_id

                       window.location.href = url
                       
                    }else{
                        <?php $page = carbon_get_theme_option("url_subscription_plans");
                        
                            $page =get_permalink($page[0]["id"]);
                        
                        ?>
                        
                        const url = "<?php echo $page;?>?result_id="+ results.result_id

                        window.location.href = url
                    }

                    
                }else{
                    //console.log(response)
                    alert(data)
                }
                
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                // Re-enable the button and hide the spinner in case of error
                $button.prop('disabled', false);
                $spinner.hide();
            }
        });
    });

   


});
</script>

<?php 

