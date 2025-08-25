<?php
/**
 * Dynamic Content Class
 * Handles API integrations for icons, images, and other dynamic content
 */

if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_Dynamic_Content {
    
    private $cache_duration = 86400; // 24 hours
    
    public function __construct() {
        add_action('wp_ajax_refresh_dynamic_content', array($this, 'refresh_content_cache'));
        add_action('wp_ajax_nopriv_refresh_dynamic_content', array($this, 'refresh_content_cache'));
        add_action('nanimade_daily_cleanup', array($this, 'cleanup_expired_cache'));
        
        // Schedule daily cleanup
        if (!wp_next_scheduled('nanimade_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'nanimade_daily_cleanup');
        }
    }
    
    public function get_cached_content($key, $callback, $expiry = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'nanimade_dynamic_content';
        $expiry = $expiry ?: $this->cache_duration;
        
        // Check for existing cached content
        $cached = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE content_key = %s AND (expires_at IS NULL OR expires_at > NOW())",
            $key
        ));
        
        if ($cached) {
            return json_decode($cached->content_data, true);
        }
        
        // Generate new content
        $content = call_user_func($callback);
        
        if ($content) {
            // Cache the content
            $wpdb->replace(
                $table_name,
                array(
                    'content_key' => $key,
                    'content_type' => 'api_response',
                    'content_data' => json_encode($content),
                    'expires_at' => date('Y-m-d H:i:s', time() + $expiry),
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s', '%s')
            );
        }
        
        return $content;
    }
    
    public function generate_css_gradient($type = 'pickle-fresh') {
        $gradients = array(
            'pickle-fresh' => 'linear-gradient(135deg, #10B981 0%, #059669 100%)',
            'spicy-warm' => 'linear-gradient(135deg, #F59E0B 0%, #D97706 100%)',
            'cool-mint' => 'linear-gradient(135deg, #06B6D4 0%, #0891B2 100%)',
            'sunset-glow' => 'linear-gradient(135deg, #F97316 0%, #EA580C 100%)',
            'royal-purple' => 'linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%)'
        );
        
        return $gradients[$type] ?? $gradients['pickle-fresh'];
    }
    
    public function generate_css_pattern($type = 'dots', $color = '#10B981') {
        $patterns = array(
            'dots' => "radial-gradient(circle, {$color} 1px, transparent 1px)",
            'lines' => "repeating-linear-gradient(45deg, transparent, transparent 10px, {$color} 10px, {$color} 11px)",
            'grid' => "linear-gradient({$color} 1px, transparent 1px), linear-gradient(90deg, {$color} 1px, transparent 1px)",
            'waves' => "radial-gradient(ellipse at center, transparent 20%, {$color} 50%, transparent 70%)",
            'hexagon' => "radial-gradient(circle at 50% 50%, {$color} 2px, transparent 2px)"
        );
        
        return $patterns[$type] ?? $patterns['dots'];
    }
    
    public function get_food_photography($category = 'pickles', $width = 400, $height = 300) {
        $cache_key = "food_photo_{$category}_{$width}_{$height}";
        
        return $this->get_cached_content($cache_key, function() use ($category, $width, $height) {
            // Try Unsplash first
            $unsplash_url = "https://source.unsplash.com/{$width}x{$height}/?{$category},food";
            
            // Fallback to Lorem Picsum with food-related seed
            $picsum_seed = crc32($category);
            $picsum_url = "https://picsum.photos/seed/{$picsum_seed}/{$width}/{$height}";
            
            // Test if Unsplash is available
            $response = wp_remote_head($unsplash_url);
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                return $unsplash_url;
            }
            
            return $picsum_url;
        });
    }
    
    public function get_avatar_image($name, $size = 60) {
        $cache_key = "avatar_{$name}_{$size}";
        
        return $this->get_cached_content($cache_key, function() use ($name, $size) {
            // Use Pravatar for consistent avatars
            $seed = urlencode($name);
            return "https://api.pravatar.cc/{$size}?u={$seed}";
        });
    }
    
    public function generate_loading_animation($type = 'spinner') {
        $animations = array(
            'spinner' => array(
                'html' => '<div class="nanimade-spinner"></div>',
                'css' => '
                    .nanimade-spinner {
                        width: 40px;
                        height: 40px;
                        border: 4px solid #f3f3f3;
                        border-top: 4px solid #10B981;
                        border-radius: 50%;
                        animation: spin 1s linear infinite;
                    }
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }'
            ),
            'dots' => array(
                'html' => '<div class="nanimade-dots"><span></span><span></span><span></span></div>',
                'css' => '
                    .nanimade-dots {
                        display: flex;
                        gap: 4px;
                    }
                    .nanimade-dots span {
                        width: 8px;
                        height: 8px;
                        border-radius: 50%;
                        background: #10B981;
                        animation: bounce 1.4s ease-in-out infinite both;
                    }
                    .nanimade-dots span:nth-child(1) { animation-delay: -0.32s; }
                    .nanimade-dots span:nth-child(2) { animation-delay: -0.16s; }
                    @keyframes bounce {
                        0%, 80%, 100% { transform: scale(0); }
                        40% { transform: scale(1); }
                    }'
            ),
            'pulse' => array(
                'html' => '<div class="nanimade-pulse"></div>',
                'css' => '
                    .nanimade-pulse {
                        width: 40px;
                        height: 40px;
                        background: #10B981;
                        border-radius: 50%;
                        animation: pulse 1.5s ease-in-out infinite;
                    }
                    @keyframes pulse {
                        0% { transform: scale(0); opacity: 1; }
                        100% { transform: scale(1); opacity: 0; }
                    }'
            )
        );
        
        return $animations[$type] ?? $animations['spinner'];
    }
    
    public function get_color_palette($theme = 'pickle-business') {
        $palettes = array(
            'pickle-business' => array(
                'primary' => '#10B981',
                'secondary' => '#059669',
                'accent' => '#F59E0B',
                'success' => '#22C55E',
                'warning' => '#F59E0B',
                'error' => '#EF4444',
                'neutral' => array(
                    '50' => '#F9FAFB',
                    '100' => '#F3F4F6',
                    '200' => '#E5E7EB',
                    '300' => '#D1D5DB',
                    '400' => '#9CA3AF',
                    '500' => '#6B7280',
                    '600' => '#4B5563',
                    '700' => '#374151',
                    '800' => '#1F2937',
                    '900' => '#111827'
                )
            ),
            'spicy-theme' => array(
                'primary' => '#F97316',
                'secondary' => '#EA580C',
                'accent' => '#FCD34D',
                'success' => '#22C55E',
                'warning' => '#F59E0B',
                'error' => '#EF4444',
                'neutral' => array(
                    '50' => '#FFF7ED',
                    '100' => '#FFEDD5',
                    '200' => '#FED7AA',
                    '300' => '#FDBA74',
                    '400' => '#FB923C',
                    '500' => '#F97316',
                    '600' => '#EA580C',
                    '700' => '#C2410C',
                    '800' => '#9A3412',
                    '900' => '#7C2D12'
                )
            )
        );
        
        return $palettes[$theme] ?? $palettes['pickle-business'];
    }
    
    public function refresh_content_cache() {
        check_ajax_referer('nanimade_nonce', 'nonce');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'nanimade_dynamic_content';
        
        // Clear all cached content
        $wpdb->query("DELETE FROM $table_name");
        
        wp_send_json_success(array(
            'message' => __('Dynamic content cache refreshed successfully', 'nanimade-suite')
        ));
    }
    
    public function cleanup_expired_cache() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nanimade_dynamic_content';
        
        $wpdb->query("DELETE FROM $table_name WHERE expires_at < NOW()");
    }
}