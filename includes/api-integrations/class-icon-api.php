<?php
/**
 * Icon API Integration Class
 * Handles dynamic icon fetching from various APIs
 */

if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_Icon_API {
    
    private $apis = array(
        'iconify' => 'https://api.iconify.design/',
        'heroicons' => 'https://heroicons.com/',
        'feather' => 'https://feathericons.com/'
    );
    
    private $icon_sets = array(
        'heroicons' => 'heroicons',
        'feather' => 'feather',
        'material' => 'material-symbols',
        'phosphor' => 'ph',
        'tabler' => 'tabler'
    );
    
    public function __construct() {
        add_action('wp_ajax_get_dynamic_icon', array($this, 'ajax_get_icon'));
        add_action('wp_ajax_nopriv_get_dynamic_icon', array($this, 'ajax_get_icon'));
    }
    
    public function get_icon($icon_name, $style = 'outline', $size = 24, $color = null) {
        $cache_key = "icon_{$icon_name}_{$style}_{$size}_{$color}";
        
        $dynamic_content = new NaniMade_Dynamic_Content();
        
        return $dynamic_content->get_cached_content($cache_key, function() use ($icon_name, $style, $size, $color) {
            return $this->fetch_icon($icon_name, $style, $size, $color);
        });
    }
    
    private function fetch_icon($icon_name, $style, $size, $color) {
        // Try different icon sets
        $icon_sets_to_try = array(
            'heroicons' => $this->get_heroicon($icon_name, $style, $size, $color),
            'feather' => $this->get_feather_icon($icon_name, $size, $color),
            'material' => $this->get_material_icon($icon_name, $style, $size, $color),
            'phosphor' => $this->get_phosphor_icon($icon_name, $style, $size, $color)
        );
        
        foreach ($icon_sets_to_try as $set => $icon_svg) {
            if ($icon_svg) {
                return $icon_svg;
            }
        }
        
        // Fallback to CSS-generated icon
        return $this->generate_css_icon($icon_name, $size, $color);
    }
    
    private function get_heroicon($icon_name, $style, $size, $color) {
        $style_map = array(
            'outline' => 'outline',
            'solid' => 'solid',
            'mini' => 'mini'
        );
        
        $heroicon_style = $style_map[$style] ?? 'outline';
        $color = $color ?: 'currentColor';
        
        // Map common icon names to Heroicons
        $icon_map = array(
            'fire' => 'fire',
            'leaf' => 'leaf',
            'shopping-cart' => 'shopping-cart',
            'heart' => 'heart',
            'star' => 'star',
            'eye' => 'eye',
            'user' => 'user',
            'menu' => 'bars-3',
            'close' => 'x-mark',
            'search' => 'magnifying-glass',
            'filter' => 'funnel',
            'check' => 'check',
            'arrow-right' => 'arrow-right',
            'arrow-left' => 'arrow-left',
            'chevron-right' => 'chevron-right',
            'chevron-left' => 'chevron-left',
            'plus' => 'plus',
            'minus' => 'minus',
            'calendar' => 'calendar-days',
            'clock' => 'clock',
            'location' => 'map-pin',
            'phone' => 'phone',
            'email' => 'envelope',
            'share' => 'share',
            'download' => 'arrow-down-tray',
            'upload' => 'arrow-up-tray',
            'edit' => 'pencil',
            'delete' => 'trash',
            'settings' => 'cog-6-tooth',
            'info' => 'information-circle',
            'warning' => 'exclamation-triangle',
            'error' => 'x-circle',
            'success' => 'check-circle',
            'home' => 'home',
            'shop' => 'building-storefront',
            'cart' => 'shopping-bag',
            'credit-card' => 'credit-card',
            'gift' => 'gift',
            'truck' => 'truck',
            'package' => 'cube',
            'wifi' => 'wifi',
            'wifi-off' => 'wifi',
            'loader' => 'arrow-path'
        );
        
        $heroicon_name = $icon_map[$icon_name] ?? $icon_name;
        
        // Use Iconify API for Heroicons
        $url = "https://api.iconify.design/heroicons:{$heroicon_name}.svg?color=" . urlencode($color) . "&width={$size}&height={$size}";
        
        $response = wp_remote_get($url, array('timeout' => 10));
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            return wp_remote_retrieve_body($response);
        }
        
        return false;
    }
    
    private function get_feather_icon($icon_name, $size, $color) {
        $color = $color ?: 'currentColor';
        
        $url = "https://api.iconify.design/feather:{$icon_name}.svg?color=" . urlencode($color) . "&width={$size}&height={$size}";
        
        $response = wp_remote_get($url, array('timeout' => 10));
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            return wp_remote_retrieve_body($response);
        }
        
        return false;
    }
    
    private function get_material_icon($icon_name, $style, $size, $color) {
        $color = $color ?: 'currentColor';
        $style_suffix = $style === 'outline' ? '-outlined' : '';
        
        $url = "https://api.iconify.design/material-symbols:{$icon_name}{$style_suffix}.svg?color=" . urlencode($color) . "&width={$size}&height={$size}";
        
        $response = wp_remote_get($url, array('timeout' => 10));
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            return wp_remote_retrieve_body($response);
        }
        
        return false;
    }
    
    private function get_phosphor_icon($icon_name, $style, $size, $color) {
        $color = $color ?: 'currentColor';
        $style_map = array(
            'outline' => '',
            'solid' => '-fill',
            'bold' => '-bold'
        );
        
        $phosphor_style = $style_map[$style] ?? '';
        
        $url = "https://api.iconify.design/ph:{$icon_name}{$phosphor_style}.svg?color=" . urlencode($color) . "&width={$size}&height={$size}";
        
        $response = wp_remote_get($url, array('timeout' => 10));
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            return wp_remote_retrieve_body($response);
        }
        
        return false;
    }
    
    private function generate_css_icon($icon_name, $size, $color) {
        $color = $color ?: '#10B981';
        
        // CSS-based icon fallbacks
        $css_icons = array(
            'fire' => "
                <div class='css-icon css-fire' style='width: {$size}px; height: {$size}px; color: {$color};'>
                    <div style='width: 100%; height: 100%; background: radial-gradient(ellipse at bottom, {$color} 0%, transparent 70%); border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;'></div>
                </div>
            ",
            'leaf' => "
                <div class='css-icon css-leaf' style='width: {$size}px; height: {$size}px; color: {$color};'>
                    <div style='width: 100%; height: 100%; background: {$color}; border-radius: 0 100% 0 100%; transform: rotate(45deg);'></div>
                </div>
            ",
            'heart' => "
                <div class='css-icon css-heart' style='width: {$size}px; height: {$size}px; color: {$color}; position: relative;'>
                    <div style='width: 100%; height: 100%; background: {$color}; transform: rotate(-45deg); border-radius: 50% 50% 0 50%;'></div>
                    <div style='position: absolute; top: 0; left: 50%; width: 50%; height: 50%; background: {$color}; border-radius: 50%; transform: translateX(-50%);'></div>
                    <div style='position: absolute; top: 0; right: 0; width: 50%; height: 50%; background: {$color}; border-radius: 50%;'></div>
                </div>
            ",
            'star' => "
                <div class='css-icon css-star' style='width: {$size}px; height: {$size}px; color: {$color}; position: relative;'>
                    <div style='width: 0; height: 0; border-left: " . ($size/2) . "px solid transparent; border-right: " . ($size/2) . "px solid transparent; border-bottom: " . ($size*0.7) . "px solid {$color}; position: absolute; top: 0; left: 0;'></div>
                    <div style='width: 0; height: 0; border-left: " . ($size/2) . "px solid transparent; border-right: " . ($size/2) . "px solid transparent; border-top: " . ($size*0.7) . "px solid {$color}; position: absolute; bottom: 0; left: 0;'></div>
                </div>
            "
        );
        
        return $css_icons[$icon_name] ?? $this->generate_default_icon($size, $color);
    }
    
    private function generate_default_icon($size, $color) {
        return "
            <div class='css-icon css-default' style='width: {$size}px; height: {$size}px; background: {$color}; border-radius: 2px; display: flex; align-items: center; justify-content: center; color: white; font-size: " . ($size * 0.6) . "px; font-weight: bold;'>
                ?
            </div>
        ";
    }
    
    public function ajax_get_icon() {
        check_ajax_referer('nanimade_nonce', 'nonce');
        
        $icon_name = sanitize_text_field($_POST['icon_name']);
        $style = sanitize_text_field($_POST['style']) ?: 'outline';
        $size = intval($_POST['size']) ?: 24;
        $color = sanitize_text_field($_POST['color']);
        
        $icon_svg = $this->get_icon($icon_name, $style, $size, $color);
        
        if ($icon_svg) {
            wp_send_json_success(array('icon' => $icon_svg));
        } else {
            wp_send_json_error(array('message' => __('Icon not found', 'nanimade-suite')));
        }
    }
}