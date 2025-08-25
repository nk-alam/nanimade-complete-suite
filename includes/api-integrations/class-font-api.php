<?php
/**
 * Font API Integration Class
 * Handles dynamic font loading from Google Fonts and other APIs
 */

if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_Font_API {
    
    private $google_fonts_api = 'https://fonts.googleapis.com/css2';
    private $adobe_fonts_api = 'https://use.typekit.net/';
    
    public function __construct() {
        add_action('wp_head', array($this, 'load_dynamic_fonts'));
        add_action('wp_ajax_get_font_combinations', array($this, 'ajax_get_font_combinations'));
        add_action('wp_ajax_nopriv_get_font_combinations', array($this, 'ajax_get_font_combinations'));
    }
    
    public function load_dynamic_fonts() {
        $font_family = get_option('nanimade_font_family', 'Inter');
        $font_weights = get_option('nanimade_font_weights', array(300, 400, 500, 600, 700));
        
        $this->enqueue_google_font($font_family, $font_weights);
    }
    
    public function enqueue_google_font($font_family, $weights = array(400), $subsets = array('latin')) {
        $font_families = $this->get_font_families_for_pickle_business();
        
        if (!in_array($font_family, array_keys($font_families))) {
            $font_family = 'Inter'; // Fallback
        }
        
        $weights_string = implode(';', $weights);
        $subsets_string = implode(',', $subsets);
        
        $font_url = add_query_arg(array(
            'family' => $font_family . ':wght@' . $weights_string,
            'subset' => $subsets_string,
            'display' => 'swap'
        ), $this->google_fonts_api);
        
        wp_enqueue_style(
            'nanimade-google-fonts',
            $font_url,
            array(),
            null
        );
    }
    
    public function get_font_families_for_pickle_business() {
        return array(
            // Modern & Clean
            'Inter' => array(
                'name' => 'Inter',
                'category' => 'sans-serif',
                'description' => 'Modern, highly readable sans-serif perfect for mobile interfaces',
                'weights' => array(100, 200, 300, 400, 500, 600, 700, 800, 900),
                'best_for' => array('body', 'ui', 'mobile')
            ),
            'Poppins' => array(
                'name' => 'Poppins',
                'category' => 'sans-serif',
                'description' => 'Friendly and approachable geometric sans-serif',
                'weights' => array(100, 200, 300, 400, 500, 600, 700, 800, 900),
                'best_for' => array('headings', 'body', 'branding')
            ),
            'Nunito' => array(
                'name' => 'Nunito',
                'category' => 'sans-serif',
                'description' => 'Rounded sans-serif with a friendly personality',
                'weights' => array(200, 300, 400, 500, 600, 700, 800, 900),
                'best_for' => array('body', 'ui', 'friendly')
            ),
            
            // Professional & Trustworthy
            'Roboto' => array(
                'name' => 'Roboto',
                'category' => 'sans-serif',
                'description' => 'Google\'s signature font, optimized for mobile reading',
                'weights' => array(100, 300, 400, 500, 700, 900),
                'best_for' => array('body', 'mobile', 'professional')
            ),
            'Open Sans' => array(
                'name' => 'Open Sans',
                'category' => 'sans-serif',
                'description' => 'Highly legible humanist sans-serif',
                'weights' => array(300, 400, 500, 600, 700, 800),
                'best_for' => array('body', 'readable', 'professional')
            ),
            'Lato' => array(
                'name' => 'Lato',
                'category' => 'sans-serif',
                'description' => 'Elegant and friendly sans-serif with excellent readability',
                'weights' => array(100, 300, 400, 700, 900),
                'best_for' => array('body', 'headings', 'elegant')
            ),
            
            // Distinctive & Brand-focused
            'Montserrat' => array(
                'name' => 'Montserrat',
                'category' => 'sans-serif',
                'description' => 'Urban-inspired geometric sans-serif with strong personality',
                'weights' => array(100, 200, 300, 400, 500, 600, 700, 800, 900),
                'best_for' => array('headings', 'branding', 'modern')
            ),
            'Raleway' => array(
                'name' => 'Raleway',
                'category' => 'sans-serif',
                'description' => 'Elegant sans-serif with a sophisticated feel',
                'weights' => array(100, 200, 300, 400, 500, 600, 700, 800, 900),
                'best_for' => array('headings', 'elegant', 'sophisticated')
            ),
            'Source Sans Pro' => array(
                'name' => 'Source Sans Pro',
                'category' => 'sans-serif',
                'description' => 'Adobe\'s first open source font family, designed for UI',
                'weights' => array(200, 300, 400, 600, 700, 900),
                'best_for' => array('body', 'ui', 'professional')
            ),
            
            // Traditional & Authentic (for traditional pickle brands)
            'Playfair Display' => array(
                'name' => 'Playfair Display',
                'category' => 'serif',
                'description' => 'Elegant serif with high contrast, perfect for luxury brands',
                'weights' => array(400, 500, 600, 700, 800, 900),
                'best_for' => array('headings', 'luxury', 'traditional')
            ),
            'Merriweather' => array(
                'name' => 'Merriweather',
                'category' => 'serif',
                'description' => 'Readable serif designed for screens',
                'weights' => array(300, 400, 700, 900),
                'best_for' => array('body', 'readable', 'traditional')
            ),
            'Lora' => array(
                'name' => 'Lora',
                'category' => 'serif',
                'description' => 'Contemporary serif with calligraphic roots',
                'weights' => array(400, 500, 600, 700),
                'best_for' => array('body', 'elegant', 'readable')
            )
        );
    }
    
    public function get_font_combinations() {
        return array(
            'modern-clean' => array(
                'name' => 'Modern & Clean',
                'heading' => 'Poppins',
                'body' => 'Inter',
                'accent' => 'Montserrat',
                'description' => 'Perfect for modern pickle brands targeting young consumers',
                'mood' => array('modern', 'clean', 'friendly')
            ),
            'professional-trust' => array(
                'name' => 'Professional & Trustworthy',
                'heading' => 'Montserrat',
                'body' => 'Open Sans',
                'accent' => 'Roboto',
                'description' => 'Builds trust and credibility for established pickle businesses',
                'mood' => array('professional', 'trustworthy', 'reliable')
            ),
            'elegant-premium' => array(
                'name' => 'Elegant & Premium',
                'heading' => 'Playfair Display',
                'body' => 'Lato',
                'accent' => 'Raleway',
                'description' => 'Ideal for premium, artisanal pickle brands',
                'mood' => array('elegant', 'premium', 'sophisticated')
            ),
            'friendly-approachable' => array(
                'name' => 'Friendly & Approachable',
                'heading' => 'Nunito',
                'body' => 'Nunito',
                'accent' => 'Poppins',
                'description' => 'Great for family-owned pickle businesses',
                'mood' => array('friendly', 'approachable', 'family')
            ),
            'traditional-authentic' => array(
                'name' => 'Traditional & Authentic',
                'heading' => 'Merriweather',
                'body' => 'Lora',
                'accent' => 'Playfair Display',
                'description' => 'Perfect for traditional, heritage pickle brands',
                'mood' => array('traditional', 'authentic', 'heritage')
            ),
            'mobile-optimized' => array(
                'name' => 'Mobile Optimized',
                'heading' => 'Inter',
                'body' => 'Inter',
                'accent' => 'Roboto',
                'description' => 'Optimized for mobile commerce and app-like experiences',
                'mood' => array('mobile', 'readable', 'optimized')
            )
        );
    }
    
    public function generate_font_css($combination_key) {
        $combinations = $this->get_font_combinations();
        $combination = $combinations[$combination_key] ?? $combinations['modern-clean'];
        
        $css = "
        /* Font Combination: {$combination['name']} */
        :root {
            --font-heading: '{$combination['heading']}', sans-serif;
            --font-body: '{$combination['body']}', sans-serif;
            --font-accent: '{$combination['accent']}', sans-serif;
        }
        
        /* Headings */
        h1, h2, h3, h4, h5, h6,
        .nanimade-heading {
            font-family: var(--font-heading);
            font-weight: 600;
            line-height: 1.2;
        }
        
        /* Body text */
        body, p, div, span,
        .nanimade-body {
            font-family: var(--font-body);
            font-weight: 400;
            line-height: 1.6;
        }
        
        /* Accent text */
        .nanimade-accent,
        .button, .btn,
        .nanimade-cta {
            font-family: var(--font-accent);
            font-weight: 500;
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            body {
                font-size: 16px; /* Prevent zoom on iOS */
                line-height: 1.5;
            }
            
            h1 { font-size: 2rem; }
            h2 { font-size: 1.75rem; }
            h3 { font-size: 1.5rem; }
            h4 { font-size: 1.25rem; }
            h5 { font-size: 1.125rem; }
            h6 { font-size: 1rem; }
        }
        ";
        
        return $css;
    }
    
    public function get_font_loading_strategy() {
        return array(
            'preload' => array(
                'Inter-400.woff2',
                'Inter-600.woff2'
            ),
            'fallbacks' => array(
                'sans-serif' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
                'serif' => 'Georgia, "Times New Roman", Times, serif',
                'monospace' => '"SF Mono", Monaco, Inconsolata, "Roboto Mono", Consolas, "Courier New", monospace'
            ),
            'font_display' => 'swap'
        );
    }
    
    public function optimize_font_loading() {
        // Add font preload links
        $strategy = $this->get_font_loading_strategy();
        
        foreach ($strategy['preload'] as $font_file) {
            echo '<link rel="preload" href="https://fonts.gstatic.com/s/inter/v12/' . $font_file . '" as="font" type="font/woff2" crossorigin>' . "\n";
        }
        
        // Add font-display: swap CSS
        echo '<style>
        @font-face {
            font-family: "Inter";
            font-display: swap;
        }
        </style>' . "\n";
    }
    
    public function ajax_get_font_combinations() {
        check_ajax_referer('nanimade_nonce', 'nonce');
        
        $combinations = $this->get_font_combinations();
        $font_families = $this->get_font_families_for_pickle_business();
        
        wp_send_json_success(array(
            'combinations' => $combinations,
            'families' => $font_families
        ));
    }
}