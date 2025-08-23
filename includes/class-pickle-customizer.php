<?php
if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_Pickle_Customizer {
    
    public function __construct() {
        add_action('wp_ajax_nanimade_customize_jar', array($this, 'ajax_customize_jar'));
        add_action('wp_ajax_nopriv_nanimade_customize_jar', array($this, 'ajax_customize_jar'));
    }
    
    public function ajax_customize_jar() {
        check_ajax_referer('nanimade_nonce', 'nonce');
        
        $jar_data = array(
            'spice_level' => sanitize_text_field($_POST['spice_level']),
            'jar_size' => sanitize_text_field($_POST['jar_size']),
            'extra_ingredients' => array_map('sanitize_text_field', $_POST['extra_ingredients']),
            'custom_message' => sanitize_text_field($_POST['custom_message'])
        );
        
        // Calculate price based on customizations
        $base_price = 120;
        $extras_price = count($jar_data['extra_ingredients']) * 10;
        $total_price = $base_price + $extras_price;
        
        wp_send_json_success(array(
            'jar_data' => $jar_data,
            'base_price' => $base_price,
            'extras_price' => $extras_price,
            'total_price' => $total_price
        ));
    }
}
?>