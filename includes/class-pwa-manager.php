<?php
if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_PWA_Manager {
    
    public function __construct() {
        add_action('wp_head', array($this, 'add_pwa_meta_tags'));
        add_action('init', array($this, 'add_manifest_rewrite'));
        add_action('template_redirect', array($this, 'serve_manifest'));
        add_action('template_redirect', array($this, 'serve_service_worker'));
    }
    
    public function add_pwa_meta_tags() {
        ?>
        <link rel="manifest" href="<?php echo home_url('/manifest.json'); ?>">
        <meta name="theme-color" content="#ff6b35">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="NaniMade">
        <?php
    }
    
    public function add_manifest_rewrite() {
        add_rewrite_rule('^manifest\.json$', 'index.php?nanimade_manifest=1', 'top');
        add_rewrite_rule('^nanimade-sw\.js$', 'index.php?nanimade_sw=1', 'top');
        
        add_filter('query_vars', function($vars) {
            $vars[] = 'nanimade_manifest';
            $vars[] = 'nanimade_sw';
            return $vars;
        });
    }
    
    public function serve_manifest() {
        if (get_query_var('nanimade_manifest')) {
            header('Content-Type: application/json');
            echo json_encode($this->get_manifest_data());
            exit;
        }
    }
    
    public function serve_service_worker() {
        if (get_query_var('nanimade_sw')) {
            header('Content-Type: application/javascript');
            echo $this->get_service_worker_content();
            exit;
        }
    }
    
    private function get_manifest_data() {
        return array(
            'name' => 'NaniMade Pickles',
            'short_name' => 'NaniMade',
            'description' => 'Authentic homemade pickles',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#ff6b35',
            'icons' => array(
                array(
                    'src' => NANIMADE_SUITE_PLUGIN_URL . 'assets/images/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png'
                ),
                array(
                    'src' => NANIMADE_SUITE_PLUGIN_URL . 'assets/images/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png'
                )
            )
        );
    }
    
    private function get_service_worker_content() {
        return "
        const CACHE_NAME = 'nanimade-v1';
        const urlsToCache = [
            '/',
            '/shop/',
            '/cart/'
        ];
        
        self.addEventListener('install', function(event) {
            event.waitUntil(
                caches.open(CACHE_NAME)
                    .then(function(cache) {
                        return cache.addAll(urlsToCache);
                    })
            );
        });
        
        self.addEventListener('fetch', function(event) {
            event.respondWith(
                caches.match(event.request)
                    .then(function(response) {
                        if (response) {
                            return response;
                        }
                        return fetch(event.request);
                    }
                )
            );
        });
        ";
    }
}
?>