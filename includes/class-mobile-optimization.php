<?php
/**
 * Mobile Optimization Class
 * Handles mobile-specific optimizations and features
 */

if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_Mobile_Optimization {
    
    public function __construct() {
        add_action('wp_head', array($this, 'add_mobile_meta_tags'));
        add_action('wp_footer', array($this, 'add_mobile_scripts'));
        add_filter('body_class', array($this, 'add_mobile_body_classes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_mobile_styles'));
        
        // Mobile-specific hooks
        add_action('wp_ajax_mobile_search', array($this, 'handle_mobile_search'));
        add_action('wp_ajax_nopriv_mobile_search', array($this, 'handle_mobile_search'));
        add_action('wp_ajax_mobile_filter', array($this, 'handle_mobile_filter'));
        add_action('wp_ajax_nopriv_mobile_filter', array($this, 'handle_mobile_filter'));
    }
    
    public function add_mobile_meta_tags() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">' . "\n";
        echo '<meta name="format-detection" content="telephone=no">' . "\n";
        echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";
        echo '<meta name="apple-touch-fullscreen" content="yes">' . "\n";
        
        // Prevent zoom on input focus
        if ($this->is_mobile()) {
            echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">' . "\n";
        }
    }
    
    public function add_mobile_body_classes($classes) {
        if ($this->is_mobile()) {
            $classes[] = 'nanimade-mobile';
        }
        
        if ($this->is_tablet()) {
            $classes[] = 'nanimade-tablet';
        }
        
        if ($this->is_touch_device()) {
            $classes[] = 'nanimade-touch';
        }
        
        return $classes;
    }
    
    public function enqueue_mobile_styles() {
        if ($this->is_mobile()) {
            wp_enqueue_style(
                'nanimade-mobile-priority',
                NANIMADE_SUITE_PLUGIN_URL . 'assets/css/mobile-priority.css',
                array(),
                NANIMADE_SUITE_VERSION
            );
        }
    }
    
    public function add_mobile_scripts() {
        if ($this->is_mobile()) {
            ?>
            <script>
            // Mobile-specific JavaScript
            document.addEventListener('DOMContentLoaded', function() {
                // Touch gestures
                let startY = 0;
                let startX = 0;
                
                document.addEventListener('touchstart', function(e) {
                    startY = e.touches[0].clientY;
                    startX = e.touches[0].clientX;
                });
                
                document.addEventListener('touchmove', function(e) {
                    // Prevent pull-to-refresh on certain elements
                    if (e.target.closest('.nanimade-no-refresh')) {
                        e.preventDefault();
                    }
                });
                
                // Swipe gestures for product gallery
                document.addEventListener('touchend', function(e) {
                    if (!startY || !startX) return;
                    
                    let endY = e.changedTouches[0].clientY;
                    let endX = e.changedTouches[0].clientX;
                    
                    let diffY = startY - endY;
                    let diffX = startX - endX;
                    
                    if (Math.abs(diffX) > Math.abs(diffY)) {
                        if (diffX > 50) {
                            // Swipe left
                            triggerSwipeLeft(e.target);
                        } else if (diffX < -50) {
                            // Swipe right
                            triggerSwipeRight(e.target);
                        }
                    }
                    
                    startY = 0;
                    startX = 0;
                });
                
                // Mobile search with autocomplete
                const mobileSearch = document.querySelector('.nanimade-mobile-search');
                if (mobileSearch) {
                    let searchTimeout;
                    
                    mobileSearch.addEventListener('input', function(e) {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(() => {
                            performMobileSearch(e.target.value);
                        }, 300);
                    });
                }
                
                // Sticky add to cart button
                const addToCartBtn = document.querySelector('.single_add_to_cart_button');
                if (addToCartBtn) {
                    createStickyCartButton(addToCartBtn);
                }
                
                // Mobile menu toggle
                const mobileMenuToggle = document.querySelector('.nanimade-mobile-menu-toggle');
                if (mobileMenuToggle) {
                    mobileMenuToggle.addEventListener('click', toggleMobileMenu);
                }
                
                // Infinite scroll for product listings
                if (document.querySelector('.woocommerce-products-header')) {
                    initInfiniteScroll();
                }
                
                // Mobile-optimized image lazy loading
                initMobileLazyLoading();
                
                // Haptic feedback for touch interactions
                initHapticFeedback();
            });
            
            function triggerSwipeLeft(target) {
                const gallery = target.closest('.woocommerce-product-gallery');
                if (gallery) {
                    const nextBtn = gallery.querySelector('.flex-next');
                    if (nextBtn) nextBtn.click();
                }
            }
            
            function triggerSwipeRight(target) {
                const gallery = target.closest('.woocommerce-product-gallery');
                if (gallery) {
                    const prevBtn = gallery.querySelector('.flex-prev');
                    if (prevBtn) prevBtn.click();
                }
            }
            
            function performMobileSearch(query) {
                if (query.length < 2) return;
                
                fetch(nanimade_ajax.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'mobile_search',
                        query: query,
                        nonce: nanimade_ajax.nonce
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displaySearchResults(data.data.results);
                    }
                });
            }
            
            function displaySearchResults(results) {
                const resultsContainer = document.querySelector('.nanimade-search-results');
                if (!resultsContainer) return;
                
                resultsContainer.innerHTML = '';
                
                results.forEach(result => {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'search-result-item';
                    resultItem.innerHTML = `
                        <div class="result-image">
                            <img src="${result.image}" alt="${result.title}" loading="lazy">
                        </div>
                        <div class="result-content">
                            <h4>${result.title}</h4>
                            <p class="result-price">${result.price}</p>
                        </div>
                    `;
                    
                    resultItem.addEventListener('click', () => {
                        window.location.href = result.url;
                    });
                    
                    resultsContainer.appendChild(resultItem);
                });
                
                resultsContainer.classList.add('show');
            }
            
            function createStickyCartButton(originalBtn) {
                const stickyBtn = originalBtn.cloneNode(true);
                stickyBtn.className += ' nanimade-sticky-cart-btn';
                stickyBtn.style.display = 'none';
                
                document.body.appendChild(stickyBtn);
                
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            stickyBtn.style.display = 'none';
                        } else {
                            stickyBtn.style.display = 'block';
                        }
                    });
                });
                
                observer.observe(originalBtn);
            }
            
            function toggleMobileMenu() {
                const menu = document.querySelector('.nanimade-mobile-menu');
                const overlay = document.querySelector('.nanimade-mobile-overlay');
                
                if (menu && overlay) {
                    menu.classList.toggle('active');
                    overlay.classList.toggle('active');
                    document.body.classList.toggle('menu-open');
                }
            }
            
            function initInfiniteScroll() {
                let loading = false;
                let page = 2;
                
                const loadMore = () => {
                    if (loading) return;
                    loading = true;
                    
                    const loader = document.createElement('div');
                    loader.className = 'nanimade-loading';
                    loader.innerHTML = '<?php echo nanimade_get_dynamic_icon('loader', 'outline', 24); ?> <?php _e('Loading more products...', 'nanimade-suite'); ?>';
                    
                    document.querySelector('.products').appendChild(loader);
                    
                    // Simulate loading more products
                    setTimeout(() => {
                        loader.remove();
                        loading = false;
                        page++;
                    }, 1000);
                };
                
                window.addEventListener('scroll', () => {
                    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 1000) {
                        loadMore();
                    }
                });
            }
            
            function initMobileLazyLoading() {
                const images = document.querySelectorAll('img[data-src]');
                
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                images.forEach(img => imageObserver.observe(img));
            }
            
            function initHapticFeedback() {
                if ('vibrate' in navigator) {
                    document.addEventListener('click', function(e) {
                        if (e.target.matches('button, .button, input[type="submit"]')) {
                            navigator.vibrate(50);
                        }
                    });
                }
            }
            </script>
            <?php
        }
    }
    
    public function handle_mobile_search() {
        check_ajax_referer('nanimade_nonce', 'nonce');
        
        $query = sanitize_text_field($_POST['query']);
        
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 10,
            's' => $query,
            'meta_query' => array(
                array(
                    'key' => '_visibility',
                    'value' => array('catalog', 'visible'),
                    'compare' => 'IN'
                )
            )
        );
        
        $products = new WP_Query($args);
        $results = array();
        
        if ($products->have_posts()) {
            while ($products->have_posts()) {
                $products->the_post();
                $product = wc_get_product(get_the_ID());
                
                $results[] = array(
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'price' => $product->get_price_html(),
                    'image' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') ?: nanimade_get_food_image('pickle', 100, 100)
                );
            }
            wp_reset_postdata();
        }
        
        wp_send_json_success(array('results' => $results));
    }
    
    public function handle_mobile_filter() {
        check_ajax_referer('nanimade_nonce', 'nonce');
        
        $filters = $_POST['filters'];
        
        // Process mobile filters
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 12,
            'meta_query' => array()
        );
        
        if (!empty($filters['spice_level'])) {
            $args['meta_query'][] = array(
                'key' => '_pickle_spice_level',
                'value' => sanitize_text_field($filters['spice_level']),
                'compare' => '='
            );
        }
        
        if (!empty($filters['price_range'])) {
            $price_range = explode('-', $filters['price_range']);
            $args['meta_query'][] = array(
                'key' => '_price',
                'value' => array(floatval($price_range[0]), floatval($price_range[1])),
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            );
        }
        
        $products = new WP_Query($args);
        
        ob_start();
        if ($products->have_posts()) {
            while ($products->have_posts()) {
                $products->the_post();
                wc_get_template_part('content', 'product');
            }
            wp_reset_postdata();
        }
        $html = ob_get_clean();
        
        wp_send_json_success(array('html' => $html));
    }
    
    private function is_mobile() {
        return wp_is_mobile();
    }
    
    private function is_tablet() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        return preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $user_agent);
    }
    
    private function is_touch_device() {
        return $this->is_mobile() || $this->is_tablet();
    }
}