<?php 

class IQ_Test_Result_Manager
{

    private static $instance = null;

    private function __construct() {}

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    public function process_form()
    {
        if (!isset($_POST['action']) || $_POST['action'] !== 'iq_test_save_responses') {
            wp_send_json_error(array('message' => 'Acción no permitida.'));
        }

        $test_id = intval($_POST['test_id']);
        if ($test_id <= 0) {
            wp_send_json_error(array('message' => 'ID de test no válido.'));
        }

        $preguntas = carbon_get_post_meta($test_id, 'preguntas');
        if (empty($preguntas)) {
            wp_send_json_error(array('message' => 'No se encontraron preguntas para este test.'));
        }

        $respuestas_usuario = $this->procesar_respuestas($preguntas);
        $total_correctas = $respuestas_usuario['total_correctas'];
        unset($respuestas_usuario['total_correctas']);

        $current_user = wp_get_current_user();
        $result_post_id = $this->crear_resultado($current_user->ID, $test_id, $respuestas_usuario, $total_correctas);

        if ($result_post_id) {
            wp_send_json_success(array(
                'total_correctas' => $total_correctas,
                'respuestas_usuario' => $respuestas_usuario,
                'usuario' => $current_user->ID,
            ));
        } else {
            wp_send_json_error(array('message' => 'No se pudo crear el post de resultado.'));
        }
    }

    private function procesar_respuestas($preguntas)
    {
        $respuestas_usuario = array();
        $total_correctas = 0;

        foreach ($preguntas as $index => $pregunta) {
            $i = $index + 1;
            $respuesta = isset($_POST['q' . $i]) ? sanitize_text_field($_POST['q' . $i]) : '';
            $respuestas_usuario[$i] = array('respuesta' => $respuesta);

            foreach ($pregunta['pregunta_opciones'] as $opcion_index => $opcion) {
                $key = ($index + 1) . "_" . ($opcion_index + 1);
                if ($opcion['correcta'] === 'si' && $key === $respuesta) {
                    $total_correctas++;
                    break;
                }
            }
        }

        $respuestas_usuario['total_correctas'] = $total_correctas;
        return $respuestas_usuario;
    }

    private function crear_resultado($user_id, $test_id, $respuestas, $total_correctas)
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
            carbon_set_post_meta($result_post_id, 'usuario', $user_id);
            carbon_set_post_meta($result_post_id, 'test', $test_id);
            carbon_set_post_meta($result_post_id, 'fecha_resultados', current_time('mysql'));
            carbon_set_post_meta($result_post_id, 'respuestas', $respuestas);
            carbon_set_post_meta($result_post_id, 'total_resultados', $total_correctas);
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
    
        if (!usuario_tiene_orden($current_user->ID, $test_id)) {
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
                    'value' => 1,
                    'compare' => '=',
                ),
            ),
        );
    
        $query = new WP_Query($args);
    
       
    
        $query = new WP_Query($args);
        if (!$query->have_posts()) {
            return new WP_Error('no_results', 'No se encontraron resultados para este test.', array('status' => 404));
        }
    
        $result = $query->posts[0];
        $respuestas = carbon_get_post_meta($result->ID, 'respuestas');
        $total_correctas = carbon_get_post_meta($result->ID, 'total_resultados');
        $preguntas = carbon_get_post_meta($test_id,"preguntas");
        $respuestas_correctas = $this->obtener_respuestas_correctas($preguntas);
        $response = array(
           
           
            "test" =>[
                "preguntas" => $preguntas,
                'id' => $test_id,
                'title' => $test->post_title,
                'content' => $test->post_content,
                "respuestas_correctas" => $respuestas_correctas
            ],
            "results" => [
                'total_correctas' => $total_correctas,
                'respuestas_usuario' => $respuestas,
               
            ]
        );
    
        return $response;
    }

    private function obtener_respuestas_correctas($array_objetos) {
        $respuestas_correctas = [];
    
        foreach ($array_objetos as $objeto) {
            if (isset($objeto['pregunta_opciones']) && is_array($objeto['pregunta_opciones'])) {
                foreach ($objeto['pregunta_opciones'] as $key => $opcion) {
                    if (isset($opcion['correcta']) && $opcion['correcta'] === 'si') {
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
            'status' => array('completed', 'processing'),
            'limit' => -1,
        ));

        foreach ($customer_orders as $order) {
            foreach ($order->get_items() as $item) {
                if ($item->get_product_id() == $test_id) {
                    return true;
                }
            }
        }

        return false;
    }

}