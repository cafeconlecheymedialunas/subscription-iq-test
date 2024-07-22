<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Cancelation {
 
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
            'name' => 'Cancelations',
            'singular_name' => 'Cancelation',
            'menu_name' => 'Cancelations',
            'name_admin_bar' => 'Cancelation',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Cancelation',
            'new_item' => 'New Cancelation',
            'edit_item' => 'Edit Cancelation',
            'view_item' => 'View Cancelation',
            'all_items' => 'All Cancelations',
            'search_items' => 'Search Cancelations',
            'not_found' => 'No cancelations found',
            'not_found_in_trash' => 'No cancelations found in trash',
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor'],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-list-view',
        ];

        register_post_type('cancelation', $args);
    }

    public function registerFields() {
        Container::make( 'post_meta', __( 'Cancelation Info' ) )
        ->add_fields( array(
            
            Field::make('select', 'user', __('User'))
            ->add_options([$this,'get_users']),
            Field::make( 'select', 'subscription', 'Subscription' )->add_options([$this,"get_subscription_plans"] ),
            Field::make('date_time', 'cancelation_date', 'Cancelation Date'),
        ) )->where('post_type', '=', 'cancelation');
    
    }

    public function get_subscription_plans() {
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
        $options = [];
        if (!empty($query->posts)) {
            $options[""] = "Select a Subscription";
            foreach ($query->posts as $subscription) {
                $options[$subscription->ID] = $subscription->post_title;
            }
        }
    
        return $options;
    }

    public function get_users()
    {
        $users = get_users();
    
        $options = [""=>"Select an User"];
    
        if (!empty($users)) {
            
            foreach ($users as $user) {
                $options[$user->ID] = $user->display_name;
            }
        }
    
        return $options;
    }
}

