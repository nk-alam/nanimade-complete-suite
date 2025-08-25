<?php
/**
 * Image API Integration Class
 * Handles dynamic image fetching from various APIs
 */

if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_Image_API {
    
    private $apis = array(
        'unsplash' => 'https://source.unsplash.com/',
        'picsum' => 'https://picsum.photos/',
        'pravatar' => 'https://api.pravatar.cc/'
    );
    
    public function __construct() {
        add_action('wp_ajax_get_dynamic_image', array($this, 'ajax_get_image'));
        add_action('wp_ajax_nopriv_get_dynamic_image', array($this, 'ajax_get_image'));
    }
    
    public function get_food_image($category = 'pickles', $width = 400, $height = 300, $quality = 80) {
        $cache_key = "food_image_{$category}_{$width}_{$height}_{$quality}";
        
        $dynamic_content = new NaniMade_Dynamic_Content();
        
        return $dynamic_content->get_cached_content($cache_key, function() use ($category, $width, $height, $quality) {
            return $this->fetch_food_image($category, $width, $height, $quality);
        });
    }
    
    private function fetch_food_image($category, $width, $height, $quality) {
        // Food category mapping for better results
        $category_map = array(
            'pickles' => 'pickles,indian food,vegetables',
            'mango' => 'mango pickle,indian cuisine,spicy food',
            'lime' => 'lime pickle,citrus,indian food',
            'mixed' => 'mixed vegetables,indian pickle,colorful food',
            'garlic' => 'garlic pickle,spices,indian cuisine',
            'ginger' => 'ginger,spices,healthy food',
            'spicy' => 'spicy food,chili,hot sauce',
            'vegetables' => 'fresh vegetables,organic,healthy',
            'herbs' => 'fresh herbs,cooking,ingredients',
            'spices' => 'indian spices,colorful spices,cooking'
        );
        
        $search_terms = $category_map[$category] ?? $category;
        
        // Try Unsplash first
        $unsplash_url = $this->get_unsplash_image($search_terms, $width, $height);
        if ($this->test_image_url($unsplash_url)) {
            return $unsplash_url;
        }
        
        // Fallback to Picsum with food-related seed
        return $this->get_picsum_image($category, $width, $height);
    }
    
    private function get_unsplash_image($search_terms, $width, $height) {
        $encoded_terms = urlencode($search_terms);
        return "https://source.unsplash.com/{$width}x{$height}/?{$encoded_terms}";
    }
    
    private function get_picsum_image($seed, $width, $height) {
        $seed_number = crc32($seed) % 1000;
        return "https://picsum.photos/seed/{$seed_number}/{$width}/{$height}";
    }
    
    public function get_placeholder_image($width = 400, $height = 300, $text = null, $bg_color = 'cccccc', $text_color = '666666') {
        $text = $text ?: "{$width}x{$height}";
        return "https://via.placeholder.com/{$width}x{$height}/{$bg_color}/{$text_color}?text=" . urlencode($text);
    }
    
    public function get_avatar_image($identifier, $size = 60, $style = 'default') {
        $cache_key = "avatar_{$identifier}_{$size}_{$style}";
        
        $dynamic_content = new NaniMade_Dynamic_Content();
        
        return $dynamic_content->get_cached_content($cache_key, function() use ($identifier, $size, $style) {
            return $this->fetch_avatar_image($identifier, $size, $style);
        });
    }
    
    private function fetch_avatar_image($identifier, $size, $style) {
        $seed = urlencode($identifier);
        
        // Different avatar styles
        switch ($style) {
            case 'initials':
                return $this->generate_initials_avatar($identifier, $size);
            case 'geometric':
                return "https://api.pravatar.cc/{$size}?u={$seed}";
            case 'robohash':
                return "https://robohash.org/{$seed}?size={$size}x{$size}";
            default:
                return "https://api.pravatar.cc/{$size}?u={$seed}";
        }
    }
    
    private function generate_initials_avatar($name, $size) {
        $initials = $this->get_initials($name);
        $bg_color = $this->generate_color_from_string($name);
        $text_color = $this->get_contrast_color($bg_color);
        
        return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&size={$size}&background=" . ltrim($bg_color, '#') . "&color=" . ltrim($text_color, '#');
    }
    
    private function get_initials($name) {
        $words = explode(' ', trim($name));
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
                if (strlen($initials) >= 2) break;
            }
        }
        
        return $initials ?: 'U';
    }
    
    private function generate_color_from_string($string) {
        $hash = md5($string);
        $color = '#' . substr($hash, 0, 6);
        return $color;
    }
    
    private function get_contrast_color($hex_color) {
        $hex_color = ltrim($hex_color, '#');
        $r = hexdec(substr($hex_color, 0, 2));
        $g = hexdec(substr($hex_color, 2, 2));
        $b = hexdec(substr($hex_color, 4, 2));
        
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        
        return $brightness > 155 ? '#000000' : '#ffffff';
    }
    
    public function get_pattern_image($pattern = 'dots', $width = 400, $height = 300, $color = '10B981') {
        $cache_key = "pattern_{$pattern}_{$width}_{$height}_{$color}";
        
        $dynamic_content = new NaniMade_Dynamic_Content();
        
        return $dynamic_content->get_cached_content($cache_key, function() use ($pattern, $width, $height, $color) {
            return $this->generate_pattern_svg($pattern, $width, $height, $color);
        });
    }
    
    private function generate_pattern_svg($pattern, $width, $height, $color) {
        $svg_patterns = array(
            'dots' => $this->generate_dots_pattern($width, $height, $color),
            'lines' => $this->generate_lines_pattern($width, $height, $color),
            'grid' => $this->generate_grid_pattern($width, $height, $color),
            'waves' => $this->generate_waves_pattern($width, $height, $color),
            'hexagon' => $this->generate_hexagon_pattern($width, $height, $color)
        );
        
        return $svg_patterns[$pattern] ?? $svg_patterns['dots'];
    }
    
    private function generate_dots_pattern($width, $height, $color) {
        $svg = "<svg width='{$width}' height='{$height}' xmlns='http://www.w3.org/2000/svg'>";
        $svg .= "<defs><pattern id='dots' x='0' y='0' width='20' height='20' patternUnits='userSpaceOnUse'>";
        $svg .= "<circle cx='10' cy='10' r='2' fill='#{$color}' opacity='0.3'/>";
        $svg .= "</pattern></defs>";
        $svg .= "<rect width='100%' height='100%' fill='url(#dots)'/>";
        $svg .= "</svg>";
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    private function generate_lines_pattern($width, $height, $color) {
        $svg = "<svg width='{$width}' height='{$height}' xmlns='http://www.w3.org/2000/svg'>";
        $svg .= "<defs><pattern id='lines' x='0' y='0' width='20' height='20' patternUnits='userSpaceOnUse'>";
        $svg .= "<line x1='0' y1='10' x2='20' y2='10' stroke='#{$color}' stroke-width='1' opacity='0.3'/>";
        $svg .= "</pattern></defs>";
        $svg .= "<rect width='100%' height='100%' fill='url(#lines)'/>";
        $svg .= "</svg>";
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    private function generate_grid_pattern($width, $height, $color) {
        $svg = "<svg width='{$width}' height='{$height}' xmlns='http://www.w3.org/2000/svg'>";
        $svg .= "<defs><pattern id='grid' x='0' y='0' width='20' height='20' patternUnits='userSpaceOnUse'>";
        $svg .= "<path d='M 20 0 L 0 0 0 20' fill='none' stroke='#{$color}' stroke-width='1' opacity='0.3'/>";
        $svg .= "</pattern></defs>";
        $svg .= "<rect width='100%' height='100%' fill='url(#grid)'/>";
        $svg .= "</svg>";
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    private function generate_waves_pattern($width, $height, $color) {
        $svg = "<svg width='{$width}' height='{$height}' xmlns='http://www.w3.org/2000/svg'>";
        $svg .= "<defs><pattern id='waves' x='0' y='0' width='40' height='20' patternUnits='userSpaceOnUse'>";
        $svg .= "<path d='M0 10 Q10 0 20 10 T40 10' fill='none' stroke='#{$color}' stroke-width='2' opacity='0.3'/>";
        $svg .= "</pattern></defs>";
        $svg .= "<rect width='100%' height='100%' fill='url(#waves)'/>";
        $svg .= "</svg>";
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    private function generate_hexagon_pattern($width, $height, $color) {
        $svg = "<svg width='{$width}' height='{$height}' xmlns='http://www.w3.org/2000/svg'>";
        $svg .= "<defs><pattern id='hexagon' x='0' y='0' width='30' height='26' patternUnits='userSpaceOnUse'>";
        $svg .= "<polygon points='15,2 25,8 25,18 15,24 5,18 5,8' fill='none' stroke='#{$color}' stroke-width='1' opacity='0.3'/>";
        $svg .= "</pattern></defs>";
        $svg .= "<rect width='100%' height='100%' fill='url(#hexagon)'/>";
        $svg .= "</svg>";
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    private function test_image_url($url) {
        $response = wp_remote_head($url, array('timeout' => 5));
        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }
    
    public function ajax_get_image() {
        check_ajax_referer('nanimade_nonce', 'nonce');
        
        $type = sanitize_text_field($_POST['type']);
        $category = sanitize_text_field($_POST['category']);
        $width = intval($_POST['width']) ?: 400;
        $height = intval($_POST['height']) ?: 300;
        
        switch ($type) {
            case 'food':
                $image_url = $this->get_food_image($category, $width, $height);
                break;
            case 'avatar':
                $identifier = sanitize_text_field($_POST['identifier']);
                $image_url = $this->get_avatar_image($identifier, $width);
                break;
            case 'pattern':
                $color = sanitize_text_field($_POST['color']) ?: '10B981';
                $image_url = $this->get_pattern_image($category, $width, $height, $color);
                break;
            default:
                $image_url = $this->get_placeholder_image($width, $height);
        }
        
        if ($image_url) {
            wp_send_json_success(array('image_url' => $image_url));
        } else {
            wp_send_json_error(array('message' => __('Image not found', 'nanimade-suite')));
        }
    }
}