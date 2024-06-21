<?php 
class IQTestResultManager
{
    private static $instance = null;

    private function __construct() {}

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
            wp_send_json_error(array('message' => 'Acción no permitida.'));
        }

        $test_id = intval($_POST['test_id']);
        if ($test_id <= 0) {
            wp_send_json_error(array('message' => 'ID de test no válido.'));
        }

        $questions = carbon_get_post_meta($test_id, 'questions');
        if (empty($questions)) {
            wp_send_json_error(array('message' => 'No se encontraron preguntas para este test.'));
        }

        $user_responses = $this->procesar_respuestas($questions);
        $total_correct_responses = $user_responses['total_correct_responses'];
        unset($user_responses['total_correct_responses']);

        $test = get_post($test_id);

        $current_user = wp_get_current_user();
        $question_scales = carbon_get_post_meta($test_id, 'question_scales');
        $iq = $this->obtener_iq($question_scales,$total_correct_responses);
        $result_post_id = $this->crear_resultado($current_user->ID, $test_id, $user_responses, $total_correct_responses,$iq);

        if (!$result_post_id) {
            wp_send_json_error(array('message' => 'No se pudo crear el post de resultado.'));
        }

        wp_send_json_success( ["test" => [
            "questions" => $questions,
            'id' => $test_id,
            'title' => $test->post_title,
            'content' => $test->post_content,
            "correct_responses" => $total_correct_responses,
            "baremos" => $question_scales,
        ],
        "results" => [
            "user" =>  $current_user->data->display_name,
            'total_correct_responses' => $total_correct_responses,
            'user_responses' => $user_responses,
            "iq" => $iq
        ]]);
    }

    private function procesar_respuestas($preguntas)
    {
        $respuestas_usuario = array();
        $total_correct_responses = 0;
    
        foreach ($preguntas as $index => $pregunta) {
            
            $i = $index;
            $respuesta_usuario_key = 'q' . $i + 1;
            
            
            if (isset($_POST[$respuesta_usuario_key])) {
                $respuesta_usuario_value = sanitize_text_field($_POST[$respuesta_usuario_key]);
                $partes = explode('_', $respuesta_usuario_value);
                $respuesta = end($partes);
                
                // Guardar la respuesta del usuario
                $respuestas_usuario[$i] = array(
                    'response_value' => $respuesta,
                    "response_image" => $pregunta['question_options'][$respuesta-1]["option_image"]
                );
    
                // Verificar si la respuesta es correcta
                foreach ($pregunta['question_options'] as $opcion_index => $opcion) {
                    $key = ($index + 1) . "_" . ($opcion_index + 1);
                    if ($opcion['correct'] === 'si' && $key === $respuesta) {
                        $total_correct_responses++;
                        break;
                    }
                }
            } else {
                $respuestas_usuario[$i] = array(
                    'response_value' => '',
                    "response_image" => null
                );
            }
        }
    
        $respuestas_usuario['total_correct_responses'] = $total_correct_responses;
        return $respuestas_usuario;
    }
    

    private function crear_resultado($user_id, $test_id, $respuestas, $total_correctas, $iq)
    {
        $result_post_args = array(
            'post_title' => 'Resultado de Test ' . date('Y-m-d H:i:s'),
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
            carbon_set_post_meta($result_post_id, 'user_responses', $respuestas);
            carbon_set_post_meta($result_post_id, 'total_correct_responses', $total_correctas);
            carbon_set_post_meta($result_post_id, 'total_score', $iq);
        }

        return $result_post_id;
    }

    public function ver_resultados($test_id) {
        $args = array(
            'post_type' => 'iq_test',
            'post_status' => 'publish',
            "post_id" => $test_id
        );
        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            return new WP_Error('no_results', 'No se encontraron test con ese id.', array('status' => 404));
        }
    
        $test = $query->posts[0];
    
        $current_user = wp_get_current_user();
    
        if (!$this->usuario_tiene_orden(1, $test_id)) {
            return new WP_Error('no_valid_order', 'No tiene una orden válida para este test.', array('status' => 403));
        }
    
        $args = array(
            'post_type' => 'iq_result',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'test',
                    'value' => $test_id,
                    'compare' => '=',
                ),
                array(
                    'key' => 'usuario',
                    'value' => $current_user->ID,
                    'compare' => '=',
                ),
            ),
        );
    
        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            return new WP_Error('no_results', 'No se encontraron resultados para este test.', array('status' => 404));
        }
    
        $result = $query->posts[0];
        $user_responses = carbon_get_post_meta($result->ID, 'user_responses');
        $total_correct_responses = carbon_get_post_meta($result->ID, 'total_correct_responses');
        $questions = carbon_get_post_meta($test_id, 'questions');
        $question_scales = carbon_get_post_meta($test_id, 'question_scales');
        
        $correct_responses = $this->obtener_respuestas_correctas($questions);
        $iq = $this->obtener_iq($question_scales, $total_correct_responses);
        $usuario = get_user_by("ID", carbon_get_post_meta($result->ID, 'user_id'));

        $response = array(
            "test" => [
                "questions" => $questions,
                'id' => $test_id,
                'title' => $test->post_title,
                'content' => $test->post_content,
                "correct_responses" => $correct_responses,
                "baremos" => $question_scales,
            ],
            "results" => [
                "user" => $usuario->data->display_name,
                'total_correct_responses' => $total_correct_responses,
                'user_responses' => $user_responses,
                "iq" => $iq
            ]
        );
    
        return $response;
    }

    public function obtener_iq($baremos, $correctas)
    {
        if (!$baremos) {
            return null; // o un valor por defecto si no se encuentran baremos
        }

        foreach ($baremos as $baremo) {
            if (isset($baremo['scale_correct_count']) && isset($baremo['scale_iq'])) {
                if ((int)$baremo['scale_correct_count'] == $correctas) {
                    return (int)$baremo['scale_iq'];
                }
            }
        }

        return null; // o un valor por defecto si no se encuentra un baremo correspondiente
    }

    public function obtener_respuestas_correctas($array_objetos) {
        $respuestas_correctas = [];
    
        foreach ($array_objetos as $objeto) {
            if (isset($objeto['question_options']) && is_array($objeto['question_options'])) {
                foreach ($objeto['question_options'] as $key => $opcion) {
                    if (isset($opcion['correct']) && $opcion['correct'] === 'yes') {
                        $respuestas_correctas[] = $key + 1;
                    }
                }
            }
        }
    
        return $respuestas_correctas;
    }

    private function usuario_tiene_orden($user_id, $test_id)
    {
        $customer_orders = wc_get_orders(array(
            'customer_id' => $user_id,
            'status' => array('completed'),
            'limit' => -1,
        ));

        foreach ($customer_orders as $order) {
            foreach ($order->get_items() as $item) {
                if ($item->get_product_id() == 176) {
                    return true;
                }
            }
        }

        return false;
    }
}
