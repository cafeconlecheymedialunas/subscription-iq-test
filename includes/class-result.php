<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;
class Result{
    public function __construct(){
        
        add_action('init', array($this, 'register'));
        add_action('carbon_fields_register_fields', array($this,'register_fields') );
    }

    public function register(){
        $labels2 = array(
            'name' => 'Respuesta',
            'singular_name' => 'Resultado',
            'menu_name' => 'Resultados IQ Test',
            'name_admin_bar' => 'Resultado',
            'add_new' => 'Añadir Nueva',
            'add_new_item' => 'Añadir Nueva Resultado',
            'new_item' => 'Nueva Resultado',
            'edit_item' => 'Editar Resultado',
            'view_item' => 'Ver Resultado',
            'all_items' => 'Todas las Resultados',
            'search_items' => 'Buscar Resultados',
            'not_found' => 'No se encontraron Resultados',
            'not_found_in_trash' => 'No se encontraron Resultados en la papelera',
        );
    
        $args2 = array(
            'labels' => $labels2,
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor'),
            'show_in_rest' => true,
            "menu_icon" => "dashicons-list-view",
        );
    
        register_post_type('iq_result', $args2);
    }

    public function register_fields(){
        Container::make('post_meta', 'Resultados Iq Test')
        ->where('post_type', '=', 'iq_result')
        ->add_fields(array(

            Field::make('text', 'usuario', __('Id del usuario'))->set_classes('readonly-field'),
            Field::make('text', 'test', __('Id del test'))->set_classes('readonly-field'),
            Field::make('text', 'total_resultados', __('Resultado total del test'))->set_classes('readonly-field'),
            Field::make('date_time', 'fecha_resultados', __('Fecha del test'))->set_classes('readonly-field'),
            Field::make('complex', 'respuestas', __('Respuestas del test'))
                ->set_layout("tabbed-horizontal")
                ->add_fields(array(
                    Field::make('text', 'respuesta', __('Respuesta Nro.'))->set_classes('readonly-field'),
                )),
        ));
    }
}