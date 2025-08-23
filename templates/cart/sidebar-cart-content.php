<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WooCommerce') || !WC()->cart) {
    return;
}

if (WC()->cart->is_empty()) {
    ?>
    <div class="nanimade-empty-cart">
        <div class="nanimade-empty-icon">
            <i class="fas fa-jar"></i>
        </div>
        <h4><?php _e('Your jar is empty!', 'nanimade-suite'); ?></h4>
        <p><?php _e('Add some delicious pickles to get started.', 'nanimade-suite'); ?></p>
        <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="nanimade-btn nanimade-btn-primary">
            <i class="fas fa-shopping-bag"></i>
            <?php _e('Start Shopping', 'nanimade-suite'); ?>
        </a>
    </div>
    <?php
    return;
}
?>

<div class="nanimade-cart-items">
    <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item): ?>
        <?php
        $product = $cart_item['data'];
        $quantity = $cart_item['quantity'];
        ?>
        <div class="nanimade-cart-item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
            <div class="nanimade-item-details">
                <h4 class="nanimade-item-name"><?php echo esc_html($product->get_name()); ?></h4>
                
                <div class="nanimade-item-meta">
                    <div class="nanimade-item-price">
                        <?php echo WC()->cart->get_product_price($product); ?>
                    </div>
                    
                    <div class="nanimade-quantity-controls">
                        <button class="nanimade-qty-btn nanimade-qty-minus" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="nanimade-quantity"><?php echo $quantity; ?></span>
                        <button class="nanimade-qty-btn nanimade-qty-plus" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <button class="nanimade-remove-item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    <?php endforeach; ?>
</div>