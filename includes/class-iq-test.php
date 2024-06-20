<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;
class IqTest
{
    public function register()
    {
        $labels = array(
            'name' => 'Tests',
            'singular_name' => 'Test',
            'menu_name' => 'Tests IQ',
            'name_admin_bar' => 'Test',
            'add_new' => 'Añadir Nuevo',
            'add_new_item' => 'Añadir Nuevo Test',
            'new_item' => 'Nuevo Test',
            'edit_item' => 'Editar Test',
            'view_item' => 'Ver Test',
            'all_items' => 'Todos los Tests',
            'search_items' => 'Buscar Tests',
            'not_found' => 'No se encontraron Tests',
            'not_found_in_trash' => 'No se encontraron Tests en la papelera',

        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor'),
            'show_in_rest' => true,
            "menu_icon" => "dashicons-performance",
        );
        register_post_type('iq_test', $args);
    }

    public function register_fields(){
        Container::make('post_meta', 'Preguntas')
        ->where('post_type', '=', 'iq_test')
        ->add_fields(array(
            Field::make('complex', 'preguntas', __('Preguntas'))
                ->set_layout("tabbed-horizontal")
                ->add_fields(array(
                    Field::make('text', 'pregunta_titulo', __('Pregunta')),
                    Field::make('image', 'pregunta_imagen', __('Imagen principal'))->set_required(true),
                    Field::make('complex', 'pregunta_opciones', __('Opciones'))
                        ->set_layout("tabbed-horizontal")
                        ->add_fields(array(
                            Field::make('image', 'opcion', __('Imagen de la Opcion'))->set_required(true),
                            Field::make('text', 'leyenda', __('Leyenda')),
                            Field::make('radio', 'correcta', __('¿Es la correcta?'))
                                ->add_options(array(
                                    'no' => 'No',
                                    'si' => 'Sí',

                                )),
                        )),

                       
                )),
                Field::make('complex', 'pregunta_baremos', __('Baremos IQ'))
                ->set_layout("tabbed-horizontal")
                ->add_fields(array(
                    Field::make('text', 'baremo_cantidad_iq', __('Cantidad de respuestas correctas')),
                    Field::make('text', 'baremo_iq', __('IQ')),
                    
                )),
        ));
    }
}