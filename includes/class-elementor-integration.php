<?php
if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_Elementor_Integration {
    
    public function __construct() {
        add_action('elementor/widgets/widgets_registered', array($this, 'register_widgets'));
        add_action('elementor/elements/categories_registered', array($this, 'register_categories'));
        add_action('elementor/frontend/after_enqueue_styles', array($this, 'enqueue_elementor_styles'));
    }
    
    public function register_categories($elements_manager) {
        $elements_manager->add_category(
            'nanimade-suite',
            array(
                'title' => __('NaniMade Pickle Suite', 'nanimade-suite'),
                'icon' => 'fa fa-pepper-hot',
            )
        );
    }
    
    public function register_widgets() {
        // Load widget files
        $widget_files = array(
            'pickle-jar-customizer.php',
            'recipe-story-widget.php',
            'taste-profile-selector.php',
            'smart-product-gallery.php',
            'mobile-menu-pro.php',
            'sidebar-cart-widget.php',
            'trust-signals-widget.php'
        );
        
        foreach ($widget_files as $file) {
            $file_path = NANIMADE_SUITE_PLUGIN_PATH . 'elementor-widgets/' . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
        
        // Register widgets
        $widget_classes = array(
            'NaniMade_Pickle_Jar_Customizer',
            'NaniMade_Recipe_Story_Widget',
            'NaniMade_Taste_Profile_Selector',
            'NaniMade_Smart_Product_Gallery',
            'NaniMade_Mobile_Menu_Pro',
            'NaniMade_Sidebar_Cart_Widget',
            'NaniMade_Trust_Signals_Widget'
        );
        
        foreach ($widget_classes as $widget_class) {
            if (class_exists($widget_class)) {
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new $widget_class());
            }
        }
    }
    
    public function enqueue_elementor_styles() {
        wp_enqueue_style(
            'nanimade-elementor-widgets',
            NANIMADE_SUITE_PLUGIN_URL . 'assets/css/elementor-widgets.css',
            array(),
            NANIMADE_SUITE_VERSION
        );
    }
}
?>