<?php
if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_Sidebar_Cart {
    
    public function __construct() {
        add_action('wp_ajax_nanimade_get_cart_content', array($this, 'ajax_get_cart_content'));
        add_action('wp_ajax_nopriv_nanimade_get_cart_content', array($this, 'ajax_get_cart_content'));
    }
    
    public function ajax_get_cart_content() {
        check_ajax_referer('nanimade_nonce', 'nonce');
        
        if (!class_exists('WooCommerce') || !WC()->cart) {
            wp_send_json_error(array('message' => 'WooCommerce not available'));
            return;
        }
        
        ob_start();
        $this->render_cart_items();
        $html = ob_get_clean();
        
        wp_send_json_success(array(
            'html' => $html,
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_total' => WC()->cart->get_cart_total()
        ));
    }
    
    private function render_cart_items() {
        if (WC()->cart->is_empty()) {
            echo '<div class="nanimade-empty-cart">';
            echo '<div class="nanimade-empty-icon"><i class="fas fa-jar"></i></div>';
            echo '<h4>' . __('Your jar is empty!', 'nanimade-suite') . '</h4>';
            echo '<p>' . __('Add some delicious pickles to get started.', 'nanimade-suite') . '</p>';
            echo '</div>';
            return;
        }
        
        echo '<div class="nanimade-cart-items">';
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $quantity = $cart_item['quantity'];
            
            echo '<div class="nanimade-cart-item" data-cart-item-key="' . esc_attr($cart_item_key) . '">';
            echo '<div class="nanimade-item-details">';
            echo '<h4 class="nanimade-item-name">' . esc_html($product->get_name()) . '</h4>';
            echo '<div class="nanimade-item-price">' . WC()->cart->get_product_price($product) . '</div>';
            echo '<div class="nanimade-quantity-controls">';
            echo '<button class="nanimade-qty-btn nanimade-qty-minus" data-cart-item-key="' . esc_attr($cart_item_key) . '">';
            echo '<i class="fas fa-minus"></i></button>';
            echo '<span class="nanimade-quantity">' . $quantity . '</span>';
            echo '<button class="nanimade-qty-btn nanimade-qty-plus" data-cart-item-key="' . esc_attr($cart_item_key) . '">';
            echo '<i class="fas fa-plus"></i></button>';
            echo '</div></div></div>';
        }
        echo '</div>';
    }
}
?>