<?php
/**
 * Plugin Name: NaniMade Complete Pickle Commerce Suite
 * Plugin URI: https://nanimade.com
 * Description: Complete mobile commerce solution for pickle businesses with advanced Elementor Pro integration, PWA features, and modern mobile app aesthetics.
 * Version: 1.0.0
 * Author: NaniMade
 * Text Domain: nanimade-suite
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('NANIMADE_SUITE_VERSION', '1.0.0');
define('NANIMADE_SUITE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NANIMADE_SUITE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('NANIMADE_SUITE_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Main plugin class
class NaniMadeCompleteSuite {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Plugin activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Load plugin components
        $this->load_dependencies();
    }
    
    public function init() {
        // Load text domain
        load_plugin_textdomain('nanimade-suite', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize components
        if (class_exists('WooCommerce')) {
            new NaniMade_WooCommerce_Integration();
        }
        
        if (did_action('elementor/loaded')) {
            new NaniMade_Elementor_Integration();
        }
        
        new NaniMade_PWA_Features();
        new NaniMade_Mobile_Optimization();
        new NaniMade_Dynamic_Content();
    }
    
    public function enqueue_scripts() {
        // Main plugin styles
        wp_enqueue_style(
            'nanimade-suite-main',
            NANIMADE_SUITE_PLUGIN_URL . 'assets/css/main.css',
            array(),
            NANIMADE_SUITE_VERSION
        );
        
        // Mobile-first responsive styles
        wp_enqueue_style(
            'nanimade-suite-mobile',
            NANIMADE_SUITE_PLUGIN_URL . 'assets/css/mobile.css',
            array('nanimade-suite-main'),
            NANIMADE_SUITE_VERSION
        );
        
        // Main plugin scripts
        wp_enqueue_script(
            'nanimade-suite-main',
            NANIMADE_SUITE_PLUGIN_URL . 'assets/js/main.js',
            array('jquery'),
            NANIMADE_SUITE_VERSION,
            true
        );
        
        // PWA service worker
        wp_enqueue_script(
            'nanimade-suite-pwa',
            NANIMADE_SUITE_PLUGIN_URL . 'assets/js/pwa.js',
            array(),
            NANIMADE_SUITE_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('nanimade-suite-main', 'nanimade_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nanimade_nonce'),
            'api_endpoints' => array(
                'unsplash' => 'https://api.unsplash.com/',
                'iconify' => 'https://api.iconify.design/',
                'lorem_picsum' => 'https://picsum.photos/'
            )
        ));
    }
    
    public function admin_enqueue_scripts() {
        wp_enqueue_style(
            'nanimade-suite-admin',
            NANIMADE_SUITE_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            NANIMADE_SUITE_VERSION
        );
        
        wp_enqueue_script(
            'nanimade-suite-admin',
            NANIMADE_SUITE_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            NANIMADE_SUITE_VERSION,
            true
        );
    }
    
    private function load_dependencies() {
        // Core classes
        require_once NANIMADE_SUITE_PLUGIN_PATH . 'includes/class-woocommerce-integration.php';
        require_once NANIMADE_SUITE_PLUGIN_PATH . 'includes/class-elementor-integration.php';
        require_once NANIMADE_SUITE_PLUGIN_PATH . 'includes/class-pwa-features.php';
        require_once NANIMADE_SUITE_PLUGIN_PATH . 'includes/class-mobile-optimization.php';
        require_once NANIMADE_SUITE_PLUGIN_PATH . 'includes/class-dynamic-content.php';
        
        // API integrations
        require_once NANIMADE_SUITE_PLUGIN_PATH . 'includes/api-integrations/class-icon-api.php';
        require_once NANIMADE_SUITE_PLUGIN_PATH . 'includes/api-integrations/class-image-api.php';
        require_once NANIMADE_SUITE_PLUGIN_PATH . 'includes/api-integrations/class-font-api.php';
        
        // Elementor widgets
        require_once NANIMADE_SUITE_PLUGIN_PATH . 'includes/elementor-widgets/class-pickle-product-grid.php';
        require_once NANIMADE_SUITE_PLUGIN_PATH . 'includes/elementor-widgets/class-mobile-cart.php';
        require_once NANIMADE_SUITE_PLUGIN_PATH . 'includes/elementor-widgets/class-recipe-showcase.php';
    }
    
    public function activate() {
        // Create necessary database tables
        $this->create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Clean up temporary files
        $this->cleanup_temp_files();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table for storing dynamic content cache
        $table_name = $wpdb->prefix . 'nanimade_dynamic_content';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            content_type varchar(50) NOT NULL,
            content_key varchar(255) NOT NULL,
            content_data longtext NOT NULL,
            expires_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY content_key (content_key)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    private function set_default_options() {
        $defaults = array(
            'nanimade_mobile_first' => true,
            'nanimade_pwa_enabled' => true,
            'nanimade_dynamic_icons' => true,
            'nanimade_api_cache_duration' => 24, // hours
            'nanimade_color_scheme' => 'pickle-green',
            'nanimade_font_family' => 'Inter'
        );
        
        foreach ($defaults as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
    
    private function cleanup_temp_files() {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/nanimade-temp/';
        
        if (is_dir($temp_dir)) {
            $files = glob($temp_dir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($temp_dir);
        }
    }
}

// Initialize the plugin
function nanimade_suite_init() {
    return NaniMadeCompleteSuite::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'nanimade_suite_init');

// Helper functions
function nanimade_get_dynamic_icon($icon_name, $style = 'outline', $size = 24) {
    $icon_api = new NaniMade_Icon_API();
    return $icon_api->get_icon($icon_name, $style, $size);
}

function nanimade_get_food_image($category = 'pickles', $width = 400, $height = 300) {
    $image_api = new NaniMade_Image_API();
    return $image_api->get_food_image($category, $width, $height);
}

function nanimade_generate_css_pattern($type = 'dots', $color = '#10B981') {
    $css_generator = new NaniMade_CSS_Generator();
    return $css_generator->generate_pattern($type, $color);
}
