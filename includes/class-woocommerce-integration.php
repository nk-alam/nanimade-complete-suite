<?php
/**
 * WooCommerce Integration Class
 * Handles all WooCommerce-specific functionality for pickle businesses
 */

if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_WooCommerce_Integration {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_filter('woocommerce_product_tabs', array($this, 'add_pickle_tabs'));
        add_action('woocommerce_single_product_summary', array($this, 'add_pickle_info'), 25);
        add_filter('woocommerce_cart_item_thumbnail', array($this, 'dynamic_cart_thumbnails'), 10, 3);
        add_action('wp_ajax_add_to_cart_mobile', array($this, 'mobile_add_to_cart'));
        add_action('wp_ajax_nopriv_add_to_cart_mobile', array($this, 'mobile_add_to_cart'));
    }
    
    public function init() {
        // Add custom product fields for pickles
        add_action('woocommerce_product_options_general_product_data', array($this, 'add_pickle_fields'));
        add_action('woocommerce_process_product_meta', array($this, 'save_pickle_fields'));
        
        // Customize shop loop for mobile
        add_action('woocommerce_before_shop_loop_item_title', array($this, 'mobile_product_overlay'), 15);
        
        // Add mobile-optimized checkout
        add_action('woocommerce_checkout_before_customer_details', array($this, 'mobile_checkout_header'));
    }
    
    public function add_pickle_fields() {
        global $post;
        
        echo '<div class="options_group">';
        
        // Spice level
        woocommerce_wp_select(array(
            'id' => '_pickle_spice_level',
            'label' => __('Spice Level', 'nanimade-suite'),
            'options' => array(
                'mild' => __('Mild', 'nanimade-suite'),
                'medium' => __('Medium', 'nanimade-suite'),
                'hot' => __('Hot', 'nanimade-suite'),
                'extra_hot' => __('Extra Hot', 'nanimade-suite')
            )
        ));
        
        // Pickle type
        woocommerce_wp_select(array(
            'id' => '_pickle_type',
            'label' => __('Pickle Type', 'nanimade-suite'),
            'options' => array(
                'mango' => __('Mango Pickle', 'nanimade-suite'),
                'lime' => __('Lime Pickle', 'nanimade-suite'),
                'mixed' => __('Mixed Vegetable', 'nanimade-suite'),
                'garlic' => __('Garlic Pickle', 'nanimade-suite'),
                'ginger' => __('Ginger Pickle', 'nanimade-suite')
            )
        ));
        
        // Ingredients
        woocommerce_wp_textarea_input(array(
            'id' => '_pickle_ingredients',
            'label' => __('Main Ingredients', 'nanimade-suite'),
            'placeholder' => __('List main ingredients...', 'nanimade-suite')
        ));
        
        // Shelf life
        woocommerce_wp_text_input(array(
            'id' => '_pickle_shelf_life',
            'label' => __('Shelf Life (months)', 'nanimade-suite'),
            'type' => 'number',
            'custom_attributes' => array(
                'min' => '1',
                'max' => '24'
            )
        ));
        
        echo '</div>';
    }
    
    public function save_pickle_fields($post_id) {
        $fields = array('_pickle_spice_level', '_pickle_type', '_pickle_ingredients', '_pickle_shelf_life');
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
    
    public function add_pickle_tabs($tabs) {
        $tabs['pickle_info'] = array(
            'title' => __('Pickle Info', 'nanimade-suite'),
            'priority' => 50,
            'callback' => array($this, 'pickle_info_tab_content')
        );
        
        return $tabs;
    }
    
    public function pickle_info_tab_content() {
        global $product;
        
        $spice_level = get_post_meta($product->get_id(), '_pickle_spice_level', true);
        $pickle_type = get_post_meta($product->get_id(), '_pickle_type', true);
        $ingredients = get_post_meta($product->get_id(), '_pickle_ingredients', true);
        $shelf_life = get_post_meta($product->get_id(), '_pickle_shelf_life', true);
        
        echo '<div class="nanimade-pickle-info">';
        
        if ($spice_level) {
            echo '<div class="pickle-detail">';
            echo '<span class="pickle-icon">' . nanimade_get_dynamic_icon('fire', 'solid', 20) . '</span>';
            echo '<strong>' . __('Spice Level:', 'nanimade-suite') . '</strong> ';
            echo '<span class="spice-level spice-' . esc_attr($spice_level) . '">' . ucfirst($spice_level) . '</span>';
            echo '</div>';
        }
        
        if ($pickle_type) {
            echo '<div class="pickle-detail">';
            echo '<span class="pickle-icon">' . nanimade_get_dynamic_icon('leaf', 'outline', 20) . '</span>';
            echo '<strong>' . __('Type:', 'nanimade-suite') . '</strong> ' . esc_html(ucfirst(str_replace('_', ' ', $pickle_type)));
            echo '</div>';
        }
        
        if ($ingredients) {
            echo '<div class="pickle-detail">';
            echo '<span class="pickle-icon">' . nanimade_get_dynamic_icon('list', 'outline', 20) . '</span>';
            echo '<strong>' . __('Main Ingredients:', 'nanimade-suite') . '</strong><br>';
            echo '<p>' . esc_html($ingredients) . '</p>';
            echo '</div>';
        }
        
        if ($shelf_life) {
            echo '<div class="pickle-detail">';
            echo '<span class="pickle-icon">' . nanimade_get_dynamic_icon('calendar', 'outline', 20) . '</span>';
            echo '<strong>' . __('Shelf Life:', 'nanimade-suite') . '</strong> ' . esc_html($shelf_life) . ' ' . __('months', 'nanimade-suite');
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    public function add_pickle_info() {
        global $product;
        
        $spice_level = get_post_meta($product->get_id(), '_pickle_spice_level', true);
        
        if ($spice_level) {
            echo '<div class="nanimade-spice-indicator mobile-optimized">';
            echo '<span class="spice-label">' . __('Spice Level:', 'nanimade-suite') . '</span>';
            echo '<div class="spice-meter spice-' . esc_attr($spice_level) . '">';
            
            for ($i = 1; $i <= 4; $i++) {
                $active = '';
                switch ($spice_level) {
                    case 'mild': $active = $i <= 1 ? 'active' : ''; break;
                    case 'medium': $active = $i <= 2 ? 'active' : ''; break;
                    case 'hot': $active = $i <= 3 ? 'active' : ''; break;
                    case 'extra_hot': $active = $i <= 4 ? 'active' : ''; break;
                }
                
                echo '<span class="spice-dot ' . $active . '">' . nanimade_get_dynamic_icon('fire', 'solid', 16) . '</span>';
            }
            
            echo '</div>';
            echo '</div>';
        }
    }
    
    public function mobile_product_overlay() {
        global $product;
        
        echo '<div class="nanimade-mobile-overlay">';
        echo '<div class="quick-actions">';
        echo '<button class="quick-view-btn" data-product-id="' . $product->get_id() . '">';
        echo nanimade_get_dynamic_icon('eye', 'outline', 18);
        echo '</button>';
        echo '<button class="quick-add-btn" data-product-id="' . $product->get_id() . '">';
        echo nanimade_get_dynamic_icon('shopping-cart', 'outline', 18);
        echo '</button>';
        echo '</div>';
        echo '</div>';
    }
    
    public function dynamic_cart_thumbnails($thumbnail, $cart_item, $cart_item_key) {
        if (empty($thumbnail)) {
            $product = $cart_item['data'];
            $pickle_type = get_post_meta($product->get_id(), '_pickle_type', true);
            
            if ($pickle_type) {
                $dynamic_image = nanimade_get_food_image($pickle_type, 100, 100);
                return '<img src="' . esc_url($dynamic_image) . '" alt="' . esc_attr($product->get_name()) . '" class="dynamic-thumbnail">';
            }
        }
        
        return $thumbnail;
    }
    
    public function mobile_add_to_cart() {
        check_ajax_referer('nanimade_nonce', 'nonce');
        
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']) ?: 1;
        
        $result = WC()->cart->add_to_cart($product_id, $quantity);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Product added to cart!', 'nanimade-suite'),
                'cart_count' => WC()->cart->get_cart_contents_count(),
                'cart_total' => WC()->cart->get_cart_total()
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to add product to cart.', 'nanimade-suite')
            ));
        }
    }
    
    public function mobile_checkout_header() {
        echo '<div class="nanimade-mobile-checkout-header">';
        echo '<div class="checkout-progress">';
        echo '<div class="progress-step active">';
        echo nanimade_get_dynamic_icon('shopping-bag', 'solid', 20);
        echo '<span>' . __('Cart', 'nanimade-suite') . '</span>';
        echo '</div>';
        echo '<div class="progress-step current">';
        echo nanimade_get_dynamic_icon('credit-card', 'outline', 20);
        echo '<span>' . __('Payment', 'nanimade-suite') . '</span>';
        echo '</div>';
        echo '<div class="progress-step">';
        echo nanimade_get_dynamic_icon('check-circle', 'outline', 20);
        echo '<span>' . __('Complete', 'nanimade-suite') . '</span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}