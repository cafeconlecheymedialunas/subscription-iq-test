<?php echo do_shortcode('[SHORTCODE_ELEMENTOR id="1015"]'); 
?>


<div class="list-test">
<?php if (!is_wp_error($testResults )): 

foreach ($testResults as $result):
    $test_id = carbon_get_post_meta($result->ID, "test_id");
    $date = carbon_get_post_meta($result->ID, "result_date");
    $responses = carbon_get_post_meta($result->ID, "total_correct_responses");
    $time = Helper::pass_time($date);
    $questions = carbon_get_post_meta($test_id, "questions");
    $test = get_post($test_id);
    $current_url = add_query_arg(null, null); // obtiene la URL actual
    $test_url = add_query_arg('test_id', $test_id, $current_url); // agrega el parámetro test_id a la URL actual
    ?>
    <a href="<?php echo esc_url($test_url); ?>">
    <div class="test-item">
        <div class="data">
            <h3><?php echo $test->post_title; ?></h3>
            <span><?php echo $time; ?></span>
        </div>
        <div class="result">
            <span><?php echo $responses . " / " . count($questions); ?></span>
            <span class="iq">IQ: <?php echo carbon_get_post_meta($result->ID, "total_score"); ?></span>
        </div>
    </div>
    </a>

<?php endforeach;?>
<?php else: ?>
        <p><?php echo $result->get_error_message(); ?></p>
    <?php endif;?>
</div>
