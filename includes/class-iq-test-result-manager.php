<?php
class IQTestResultManager
{
    private static $instance = null;

    private function __construct()
    {}

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function processForm()
    {
        if (!isset($_POST['action']) || $_POST['action'] !== 'iq_test_save_responses') {
            wp_send_json_error(array('message' => 'Action not allowed.'));
        }

        $test_id = intval($_POST['test_id']);
        if ($test_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid test ID.'));
        }

        $questions = carbon_get_post_meta($test_id, 'questions');
        if (empty($questions)) {
            wp_send_json_error(array('message' => 'No questions found for this test.'));
        }

        $user_responses = $this->processResponses($questions);
        $total_correct_responses = $user_responses['total_correct_responses'];
        unset($user_responses['total_correct_responses']);

        if (!$user_responses) {
            wp_send_json_error(array('message' => 'Failed to create user responses.'));
        }


        $test = get_post($test_id);

        $current_user = wp_get_current_user();
        $question_scales = carbon_get_post_meta($test_id, 'question_scales');
        $iq = $this->calculateiq($question_scales, $total_correct_responses);


        if (!$iq) {
            wp_send_json_error(array('message' => 'Failed to create the iq score.'));
        }
        $result_post_id = $this->createResult($current_user->ID, $test_id, $user_responses, $total_correct_responses, $iq);
       
        if (!$result_post_id) {
            wp_send_json_error(array('message' => 'Failed to create result post.'));
        }


        $user_id = get_current_user_id();
       

        $userHasSubscription = $this->userHasValidSubscription();


      

        $subscription = $this->getActiveSubscription($user_id,176);
    

        wp_send_json_success([
            "user" =>[
                "has_valid_subscription" => (!$userHasSubscription)?false:true,
                "user_id" => $user_id,
                "subscription" =>$subscription
            ],
            "test" => [
                "questions" => $questions,
                'id' => $test_id,
                'title' => $test->post_title,
                'content' => $test->post_content,
                "correct_responses" => $total_correct_responses,
                "scales" => $question_scales,
            ],
            "results" => [
                "result_id" =>$result_post_id,
                "user" => $current_user->data->display_name,
                'total_correct_responses' => $total_correct_responses,
                'user_responses' => $user_responses,
                "iq" => $iq,
            ]]);
    }

    public function addToCartAndRedirectToCheckout()
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
    private function processResponses($questions)
    {
        $user_responses = array();
        $total_correct_responses = 0;

        foreach ($questions as $index => $question) {

            $i = $index;
            $user_response_key = 'q' . ($i + 1);

            if (isset($_POST[$user_response_key])) {
                $user_response_value = sanitize_text_field($_POST[$user_response_key]);
                $parts = explode('_', $user_response_value);
                $response = end($parts);

                // Save user response
                $user_responses[$i] = array(
                    'response_value' => $response,
                    "response_image" => $question['question_options'][$response - 1]["option_image"],
                );

                // Check if response is correct
                foreach ($question['question_options'] as $option_index => $option) {
                    $key = ($index + 1) . "_" . ($option_index + 1);
                    if ($option['correct'] === 'yes' && $key === $response) {
                        $total_correct_responses++;
                        break;
                    }
                }
            } else {
                $user_responses[$i] = array(
                    'response_value' => '',
                    "response_image" => null,
                );
            }
        }

        $user_responses['total_correct_responses'] = $total_correct_responses;
        return $user_responses;
    }

    private function createResult($user_id, $test_id, $responses, $total_correct, $iq)
    {
        $result_post_args = array(
            'post_title' => 'Test Result ' . date('Y-m-d H:i:s'),
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => $user_id,
            'post_type' => 'iq_result',
        );

        $result_post_id = wp_insert_post($result_post_args);

        if ($result_post_id) {
            carbon_set_post_meta($result_post_id, 'user_id', $user_id);
            carbon_set_post_meta($result_post_id, 'test_id', $test_id);
            carbon_set_post_meta($result_post_id, 'result_date', current_time('mysql'));
            carbon_set_post_meta($result_post_id, 'user_responses', $responses);
            carbon_set_post_meta($result_post_id, 'total_correct_responses', $total_correct);
            carbon_set_post_meta($result_post_id, 'total_score', $iq);
        }

        return $result_post_id;
    }

    public function viewAllResults()
    {
        $current_user =  wp_get_current_user();
     

        if (!$this->userHasValidSubscription()) {
            return new WP_Error('no_valid_order', 'You do not have a valid subscription to view result of this test.', array('status' => 403));
        }
        $args = array(
            'post_type' => 'iq_result',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'user_id',
                    'value' => $current_user->ID,
                    'compare' => '=',
                ),
            ),
        );
        $query = new WP_Query($args);
        if (!$query->have_posts()) {
            return new WP_Error('no_results', 'No results found for this user.', array('status' => 404));
        }
        return $query->posts;
    }

    public function viewResult($result_id)
    {
        $args = array(
            'post_type' => 'iq_result',
            'post_status' => 'publish',
            'p' => $result_id,
        );
        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            return new WP_Error('no_results', 'No test results found with this ID.', array('status' => 404));
        }

        $result = $query->posts[0];

        $current_user = wp_get_current_user();

        if (!$this->userHasValidSubscription()) {
            return new WP_Error('no_valid_order', 'You do not have a valid subscription to view results of tests.', array('status' => 403));
        }

 

        $result = $query->posts[0];
        $user_responses = carbon_get_post_meta($result->ID, 'user_responses');
        $total_correct_responses = carbon_get_post_meta($result->ID, 'total_correct_responses');
        $test_id = carbon_get_post_meta($result->ID, 'test_id');
        $test = get_post($test_id);
        $questions = carbon_get_post_meta($test_id, 'questions');
        $question_scales = carbon_get_post_meta($test_id, 'question_scales');

        $correct_responses = $this->getCorrectResponses($questions);
        $iq = $this->calculateIq($question_scales, $total_correct_responses);
        $user = get_user_by("ID", carbon_get_post_meta($result->ID, 'user_id'));

        $response = array(
            "test" => [
                "questions" => $questions,
                'id' => $test_id,
                'title' => $test->post_title,
                'content' => $test->post_content,
                "correct_responses" => $correct_responses,
                "scales" => $question_scales,
            ],
            "results" => [
                "user" => $user->data->display_name,
                'total_correct_responses' => $total_correct_responses,
                'user_responses' => $user_responses,
                "iq" => $iq,
            ],
        );

        return $response;
    }

    public function calculateIq($scales, $correct)
    {
        if (!$scales) {
            return null; // or a default value if no scales are found
        }

        foreach ($scales as $scale) {
            if (isset($scale['scale_correct_count']) && isset($scale['scale_iq'])) {
                if ((int) $scale['scale_correct_count'] == $correct) {
                    return (int) $scale['scale_iq'];
                }
            }
        }

        return null; // or a default value if no corresponding scale is found
    }

    public function getCorrectResponses($array_objects)
    {
        $correct_responses = [];

        foreach ($array_objects as $object) {
            if (isset($object['question_options']) && is_array($object['question_options'])) {
                foreach ($object['question_options'] as $key => $option) {
                    if (isset($option['correct']) && $option['correct'] === 'yes') {
                        $correct_responses[] = $key + 1;
                    }
                }
            }
        }

        return $correct_responses;
    }

    public function userHasValidSubscription()
    {
        global $wpdb;

        $user_id = get_current_user_id();
    
        $table_name = $wpdb->prefix . 'woocommerce_p24_subscription';
    
        $query = "SELECT * FROM $table_name WHERE user_id = %d AND valid_to > NOW()";
    
        // Ejecutar la consulta preparada
        $results = $wpdb->get_results($wpdb->prepare($query, $user_id));
    
        // Verificar si hay un error en la consulta
      
        if (empty($results)) {
            return false;
        }
    
        return true;
    }
    public function getCancelledSubscription($user_id,$product_id)
    {
        $args = array(
            'post_type' => 'cancelation',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'user',
                    'value' => $user_id,
                    'compare' => '=',
                ),
                array(
                    'key' => 'subscription',
                    'value' => $product_id,
                    'compare' => '=',
                ),
            ),
        );
        
        $query = new WP_Query($args);
    
        if (is_wp_error($query)) {
            return new WP_Error('query_error', 'There is a problem with query');
        }
    
        return (!empty($query->posts)) ? $query->posts[0] : false;
    }
    


    public function getActiveSubscription($user_id, $product_id){
        global $wpdb;

        $table_name = $wpdb->prefix . 'woocommerce_p24_subscription';

        $query = "
            SELECT * 
            FROM $table_name 
            WHERE user_id = %d 
            AND product_id = %d
            AND valid_to > NOW()
        ";

        $results = $wpdb->get_results($wpdb->prepare($query, $user_id, $product_id));
        
        if ($wpdb->last_error) {
            return false;
        }

        if (empty($results)) {
            return false;
        }

        return $results[0];
    }

    


    public function getLastIQResult($user_id) {
        $args = array(
            'post_type' => 'iq_result',
            'post_status' => 'publish',

            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => 1,
        );
    
        $query = new WP_Query($args);
    
        if ($query->have_posts()) {
            $result = $query->posts[0];
            
            if ($this->userHasValidSubscription()) {
                return $result;
            } else {
                return new WP_Error('no_valid_subscription', 'You do not have a valid subscription for this test.', array('status' => 403));
            }
        } else {
            return new WP_Error('no_results', 'No results found for this user.', array('status' => 404));
        }
    }
    

}


