<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Result {
 
    private static $instance = null;

    private function __construct() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register() {
        $labels = [
            'name' => 'Results',
            'singular_name' => 'Result',
            'menu_name' => 'IQ Test Results',
            'name_admin_bar' => 'Result',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Result',
            'new_item' => 'New Result',
            'edit_item' => 'Edit Result',
            'view_item' => 'View Result',
            'all_items' => 'All Results',
            'search_items' => 'Search Results',
            'not_found' => 'No results found',
            'not_found_in_trash' => 'No results found in trash',
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor'],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-list-view',
        ];

        register_post_type('iq_result', $args);
    }

    public function registerFields() {
        Container::make('post_meta', 'IQ Test Results')
        ->where('post_type', '=', 'iq_result')
        ->add_fields([
            Field::make('text', 'user_id', __('User ID'))->set_classes('readonly-field'),
            Field::make('text', 'test_id', __('Test ID'))->set_classes('readonly-field'),
            Field::make('text', 'total_correct_responses', __('Total Correct Responses'))->set_classes('readonly-field'),
            Field::make('text', 'total_score', __('Total IQ Test Score'))->set_classes('readonly-field'),
            Field::make('date_time', 'result_date', __('Test Date'))->set_classes('readonly-field'),
            Field::make('complex', 'user_responses', __('User Responses'))
                ->set_layout('tabbed-horizontal')
                ->add_fields([
                    Field::make('image', 'response_image', __('Image'))->set_classes('readonly-field'),
                    Field::make('text', 'response_value', __('Response Value'))->set_classes('readonly-field'),
                ]),
        ]);
    
    }
}

