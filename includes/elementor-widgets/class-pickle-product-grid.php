<?php
/**
 * Pickle Product Grid Elementor Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_Pickle_Product_Grid extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'nanimade_pickle_product_grid';
    }
    
    public function get_title() {
        return __('Pickle Product Grid', 'nanimade-suite');
    }
    
    public function get_icon() {
        return 'eicon-products';
    }
    
    public function get_categories() {
        return array('nanimade-pickle');
    }
    
    public function get_keywords() {
        return array('pickle', 'product', 'grid', 'woocommerce', 'shop');
    }
    
    protected function _register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Product Settings', 'nanimade-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );
        
        $this->add_control(
            'products_per_page',
            array(
                'label' => __('Products Per Page', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 1,
                'max' => 24,
            )
        );
        
        $this->add_control(
            'columns',
            array(
                'label' => __('Columns', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '4',
                'options' => array(
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ),
            )
        );
        
        $this->add_control(
            'orderby',
            array(
                'label' => __('Order By', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => array(
                    'date' => __('Date', 'nanimade-suite'),
                    'title' => __('Title', 'nanimade-suite'),
                    'price' => __('Price', 'nanimade-suite'),
                    'popularity' => __('Popularity', 'nanimade-suite'),
                    'rating' => __('Rating', 'nanimade-suite'),
                    'rand' => __('Random', 'nanimade-suite'),
                ),
            )
        );
        
        $this->add_control(
            'order',
            array(
                'label' => __('Order', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'desc',
                'options' => array(
                    'asc' => __('ASC', 'nanimade-suite'),
                    'desc' => __('DESC', 'nanimade-suite'),
                ),
            )
        );
        
        $this->add_control(
            'show_filters',
            array(
                'label' => __('Show Filters', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'nanimade-suite'),
                'label_off' => __('Hide', 'nanimade-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->add_control(
            'mobile_optimized',
            array(
                'label' => __('Mobile Optimized', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'nanimade-suite'),
                'label_off' => __('No', 'nanimade-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->end_controls_section();
        
        // Filter Section
        $this->start_controls_section(
            'filter_section',
            array(
                'label' => __('Filter Settings', 'nanimade-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => array(
                    'show_filters' => 'yes',
                ),
            )
        );
        
        $this->add_control(
            'filter_by_spice',
            array(
                'label' => __('Filter by Spice Level', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->add_control(
            'filter_by_type',
            array(
                'label' => __('Filter by Pickle Type', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->add_control(
            'filter_by_price',
            array(
                'label' => __('Filter by Price', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->end_controls_section();
        
        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Style', 'nanimade-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );
        
        $this->add_control(
            'card_style',
            array(
                'label' => __('Card Style', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'modern',
                'options' => array(
                    'modern' => __('Modern', 'nanimade-suite'),
                    'classic' => __('Classic', 'nanimade-suite'),
                    'minimal' => __('Minimal', 'nanimade-suite'),
                    'premium' => __('Premium', 'nanimade-suite'),
                ),
            )
        );
        
        $this->add_responsive_control(
            'card_spacing',
            array(
                'label' => __('Card Spacing', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range' => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ),
                ),
                'default' => array(
                    'unit' => 'px',
                    'size' => 20,
                ),
            )
        );
        
        $this->add_control(
            'primary_color',
            array(
                'label' => __('Primary Color', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#10B981',
            )
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => $settings['products_per_page'],
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
            'meta_query' => array(
                array(
                    'key' => '_visibility',
                    'value' => array('catalog', 'visible'),
                    'compare' => 'IN'
                )
            )
        );
        
        $products = new WP_Query($args);
        
        $wrapper_classes = array(
            'nanimade-pickle-product-grid',
            'card-style-' . $settings['card_style'],
            'columns-' . $settings['columns']
        );
        
        if ($settings['mobile_optimized'] === 'yes') {
            $wrapper_classes[] = 'mobile-optimized';
        }
        
        echo '<div class="' . implode(' ', $wrapper_classes) . '">';
        
        // Filters
        if ($settings['show_filters'] === 'yes') {
            $this->render_filters($settings);
        }
        
        // Products Grid
        echo '<div class="products-grid" style="gap: ' . $settings['card_spacing']['size'] . 'px;">';
        
        if ($products->have_posts()) {
            while ($products->have_posts()) {
                $products->the_post();
                $this->render_product_card($settings);
            }
            wp_reset_postdata();
        } else {
            echo '<div class="no-products-found">';
            echo '<div class="no-products-icon">' . nanimade_get_dynamic_icon('search', 'outline', 48) . '</div>';
            echo '<h3>' . __('No products found', 'nanimade-suite') . '</h3>';
            echo '<p>' . __('Try adjusting your filters or check back later for new products.', 'nanimade-suite') . '</p>';
            echo '</div>';
        }
        
        echo '</div>';
        
        // Load More Button (for mobile)
        if ($settings['mobile_optimized'] === 'yes' && $products->found_posts > $settings['products_per_page']) {
            echo '<div class="load-more-container">';
            echo '<button class="load-more-btn" data-page="2">';
            echo nanimade_get_dynamic_icon('refresh', 'outline', 20);
            echo '<span>' . __('Load More Products', 'nanimade-suite') . '</span>';
            echo '</button>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    private function render_filters($settings) {
        echo '<div class="nanimade-product-filters">';
        echo '<div class="filters-header">';
        echo '<h4>' . __('Filter Products', 'nanimade-suite') . '</h4>';
        echo '<button class="filters-toggle mobile-only">';
        echo nanimade_get_dynamic_icon('filter', 'outline', 20);
        echo '</button>';
        echo '</div>';
        
        echo '<div class="filters-content">';
        
        // Spice Level Filter
        if ($settings['filter_by_spice'] === 'yes') {
            echo '<div class="filter-group">';
            echo '<label>' . __('Spice Level', 'nanimade-suite') . '</label>';
            echo '<div class="filter-options spice-filter">';
            
            $spice_levels = array(
                'mild' => __('Mild', 'nanimade-suite'),
                'medium' => __('Medium', 'nanimade-suite'),
                'hot' => __('Hot', 'nanimade-suite'),
                'extra_hot' => __('Extra Hot', 'nanimade-suite')
            );
            
            foreach ($spice_levels as $level => $label) {
                echo '<label class="filter-option">';
                echo '<input type="checkbox" name="spice_level[]" value="' . $level . '">';
                echo '<span class="checkmark"></span>';
                echo $label;
                echo '</label>';
            }
            
            echo '</div>';
            echo '</div>';
        }
        
        // Pickle Type Filter
        if ($settings['filter_by_type'] === 'yes') {
            echo '<div class="filter-group">';
            echo '<label>' . __('Pickle Type', 'nanimade-suite') . '</label>';
            echo '<div class="filter-options type-filter">';
            
            $pickle_types = array(
                'mango' => __('Mango', 'nanimade-suite'),
                'lime' => __('Lime', 'nanimade-suite'),
                'mixed' => __('Mixed Vegetable', 'nanimade-suite'),
                'garlic' => __('Garlic', 'nanimade-suite'),
                'ginger' => __('Ginger', 'nanimade-suite')
            );
            
            foreach ($pickle_types as $type => $label) {
                echo '<label class="filter-option">';
                echo '<input type="checkbox" name="pickle_type[]" value="' . $type . '">';
                echo '<span class="checkmark"></span>';
                echo $label;
                echo '</label>';
            }
            
            echo '</div>';
            echo '</div>';
        }
        
        // Price Filter
        if ($settings['filter_by_price'] === 'yes') {
            echo '<div class="filter-group">';
            echo '<label>' . __('Price Range', 'nanimade-suite') . '</label>';
            echo '<div class="price-range-slider">';
            echo '<input type="range" name="price_min" min="0" max="1000" value="0" class="price-slider">';
            echo '<input type="range" name="price_max" min="0" max="1000" value="1000" class="price-slider">';
            echo '<div class="price-display">';
            echo '<span class="price-min">$0</span> - <span class="price-max">$1000</span>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        
        echo '<div class="filter-actions">';
        echo '<button class="apply-filters-btn">' . __('Apply Filters', 'nanimade-suite') . '</button>';
        echo '<button class="clear-filters-btn">' . __('Clear All', 'nanimade-suite') . '</button>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }
    
    private function render_product_card($settings) {
        global $product;
        $product = wc_get_product(get_the_ID());
        
        if (!$product) return;
        
        $spice_level = get_post_meta($product->get_id(), '_pickle_spice_level', true);
        $pickle_type = get_post_meta($product->get_id(), '_pickle_type', true);
        
        echo '<div class="product-card" data-product-id="' . $product->get_id() . '">';
        
        // Product Image
        echo '<div class="product-image">';
        if ($product->get_image_id()) {
            echo wp_get_attachment_image($product->get_image_id(), 'medium');
        } else {
            $fallback_image = nanimade_get_food_image($pickle_type ?: 'pickles', 300, 300);
            echo '<img src="' . esc_url($fallback_image) . '" alt="' . esc_attr($product->get_name()) . '" loading="lazy">';
        }
        
        // Quick Actions Overlay
        echo '<div class="quick-actions">';
        echo '<button class="quick-view" data-product-id="' . $product->get_id() . '">';
        echo nanimade_get_dynamic_icon('eye', 'outline', 18);
        echo '</button>';
        echo '<button class="add-to-wishlist" data-product-id="' . $product->get_id() . '">';
        echo nanimade_get_dynamic_icon('heart', 'outline', 18);
        echo '</button>';
        echo '</div>';
        
        // Sale Badge
        if ($product->is_on_sale()) {
            echo '<div class="sale-badge">' . __('Sale', 'nanimade-suite') . '</div>';
        }
        
        echo '</div>';
        
        // Product Info
        echo '<div class="product-info">';
        
        // Spice Level Indicator
        if ($spice_level) {
            echo '<div class="spice-indicator spice-' . esc_attr($spice_level) . '">';
            $spice_count = array('mild' => 1, 'medium' => 2, 'hot' => 3, 'extra_hot' => 4);
            $count = $spice_count[$spice_level] ?? 1;
            
            for ($i = 1; $i <= 4; $i++) {
                $active = $i <= $count ? 'active' : '';
                echo '<span class="spice-dot ' . $active . '">' . nanimade_get_dynamic_icon('fire', 'solid', 12) . '</span>';
            }
            echo '</div>';
        }
        
        // Product Title
        echo '<h3 class="product-title">';
        echo '<a href="' . get_permalink() . '">' . $product->get_name() . '</a>';
        echo '</h3>';
        
        // Product Type
        if ($pickle_type) {
            echo '<div class="product-type">' . ucfirst(str_replace('_', ' ', $pickle_type)) . '</div>';
        }
        
        // Rating
        if ($product->get_average_rating()) {
            echo '<div class="product-rating">';
            $rating = $product->get_average_rating();
            for ($i = 1; $i <= 5; $i++) {
                $star_class = $i <= $rating ? 'active' : '';
                echo '<span class="star ' . $star_class . '">' . nanimade_get_dynamic_icon('star', 'solid', 14) . '</span>';
            }
            echo '<span class="rating-count">(' . $product->get_review_count() . ')</span>';
            echo '</div>';
        }
        
        // Price
        echo '<div class="product-price">' . $product->get_price_html() . '</div>';
        
        // Add to Cart
        echo '<div class="product-actions">';
        if ($product->is_purchasable() && $product->is_in_stock()) {
            echo '<button class="add-to-cart-btn" data-product-id="' . $product->get_id() . '">';
            echo nanimade_get_dynamic_icon('shopping-cart', 'outline', 16);
            echo '<span>' . __('Add to Cart', 'nanimade-suite') . '</span>';
            echo '</button>';
        } else {
            echo '<button class="out-of-stock-btn" disabled>';
            echo __('Out of Stock', 'nanimade-suite');
            echo '</button>';
        }
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }
}