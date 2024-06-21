<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class IqTest
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
    
    public function register()
    {
        $labels = [
            'name' => 'Tests',
            'singular_name' => 'Test',
            'menu_name' => 'IQ Tests',
            'name_admin_bar' => 'Test',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Test',
            'new_item' => 'New Test',
            'edit_item' => 'Edit Test',
            'view_item' => 'View Test',
            'all_items' => 'All Tests',
            'search_items' => 'Search Tests',
            'not_found' => 'No tests found',
            'not_found_in_trash' => 'No tests found in trash',
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor'],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-performance',
        ];

        register_post_type('iq_test', $args);
    }

    public function registerFields()
    {
        Container::make('post_meta', 'Questions')
            ->where('post_type', '=', 'iq_test')
            ->add_fields([
                Field::make('complex', 'questions', __('Questions'))
                    ->set_layout('tabbed-horizontal')
                    ->add_fields([
                        Field::make('text', 'question_title', __('Question')),
                        Field::make('image', 'question_image', __('Main Image'))->set_required(true),
                        Field::make('complex', 'question_options', __('Options'))
                            ->set_layout('tabbed-horizontal')
                            ->add_fields([
                                Field::make('image', 'option_image', __('Option Image'))->set_required(true),
                                Field::make('text', 'caption', __('Caption')),
                                Field::make('radio', 'correct', __('Is Correct?'))
                                    ->add_options([
                                        'no' => 'No',
                                        'yes' => 'Yes',
                                    ]),
                            ]),
                    ]),
                Field::make('complex', 'question_scales', __('IQ Scales'))
                    ->set_layout('tabbed-horizontal')
                    ->add_fields([
                        Field::make('text', 'scale_correct_count', __('Correct Answer Count')),
                        Field::make('text', 'scale_iq', __('IQ')),
                    ]),
            ]);
    }
}
