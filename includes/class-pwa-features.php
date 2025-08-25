<?php
/**
 * PWA Features Class
 * Implements Progressive Web App functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_PWA_Features {
    
    public function __construct() {
        add_action('wp_head', array($this, 'add_pwa_meta_tags'));
        add_action('wp_footer', array($this, 'add_pwa_scripts'));
        add_action('init', array($this, 'register_pwa_endpoints'));
        add_action('wp_ajax_install_pwa', array($this, 'handle_pwa_install'));
        add_action('wp_ajax_nopriv_install_pwa', array($this, 'handle_pwa_install'));
    }
    
    public function add_pwa_meta_tags() {
        echo '<meta name="theme-color" content="#10B981">' . "\n";
        echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";
        echo '<meta name="apple-mobile-web-app-title" content="' . get_bloginfo('name') . '">' . "\n";
        echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
        
        // Manifest link
        echo '<link rel="manifest" href="' . home_url('/pwa-manifest.json') . '">' . "\n";
        
        // Apple touch icons (generated dynamically)
        $icon_sizes = array(57, 60, 72, 76, 114, 120, 144, 152, 180);
        foreach ($icon_sizes as $size) {
            echo '<link rel="apple-touch-icon" sizes="' . $size . 'x' . $size . '" href="' . $this->generate_app_icon($size) . '">' . "\n";
        }
        
        // Favicon
        echo '<link rel="icon" type="image/png" sizes="32x32" href="' . $this->generate_app_icon(32) . '">' . "\n";
        echo '<link rel="icon" type="image/png" sizes="16x16" href="' . $this->generate_app_icon(16) . '">' . "\n";
    }
    
    public function add_pwa_scripts() {
        if (get_option('nanimade_pwa_enabled', true)) {
            ?>
            <script>
            // PWA Install Prompt
            let deferredPrompt;
            
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                showInstallButton();
            });
            
            function showInstallButton() {
                const installButton = document.createElement('div');
                installButton.className = 'nanimade-pwa-install-prompt';
                installButton.innerHTML = `
                    <div class="install-prompt-content">
                        <div class="install-icon"><?php echo nanimade_get_dynamic_icon('download', 'outline', 24); ?></div>
                        <div class="install-text">
                            <h4><?php _e('Install App', 'nanimade-suite'); ?></h4>
                            <p><?php _e('Add to home screen for quick access', 'nanimade-suite'); ?></p>
                        </div>
                        <button class="install-btn" onclick="installPWA()"><?php _e('Install', 'nanimade-suite'); ?></button>
                        <button class="close-btn" onclick="closeInstallPrompt()"><?php echo nanimade_get_dynamic_icon('x', 'outline', 20); ?></button>
                    </div>
                `;
                
                document.body.appendChild(installButton);
                
                setTimeout(() => {
                    installButton.classList.add('show');
                }, 1000);
            }
            
            function installPWA() {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then((choiceResult) => {
                        if (choiceResult.outcome === 'accepted') {
                            console.log('PWA installed');
                        }
                        deferredPrompt = null;
                        closeInstallPrompt();
                    });
                }
            }
            
            function closeInstallPrompt() {
                const prompt = document.querySelector('.nanimade-pwa-install-prompt');
                if (prompt) {
                    prompt.remove();
                }
            }
            
            // Service Worker Registration
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('<?php echo home_url('/sw.js'); ?>')
                        .then((registration) => {
                            console.log('SW registered: ', registration);
                        })
                        .catch((registrationError) => {
                            console.log('SW registration failed: ', registrationError);
                        });
                });
            }
            
            // Push Notifications
            function requestNotificationPermission() {
                if ('Notification' in window) {
                    Notification.requestPermission().then((permission) => {
                        if (permission === 'granted') {
                            console.log('Notification permission granted');
                        }
                    });
                }
            }
            
            // Offline indicator
            function updateOnlineStatus() {
                const statusIndicator = document.querySelector('.nanimade-online-status');
                if (statusIndicator) {
                    if (navigator.onLine) {
                        statusIndicator.classList.remove('offline');
                        statusIndicator.innerHTML = '<?php echo nanimade_get_dynamic_icon('wifi', 'outline', 16); ?> <?php _e('Online', 'nanimade-suite'); ?>';
                    } else {
                        statusIndicator.classList.add('offline');
                        statusIndicator.innerHTML = '<?php echo nanimade_get_dynamic_icon('wifi-off', 'outline', 16); ?> <?php _e('Offline', 'nanimade-suite'); ?>';
                    }
                }
            }
            
            window.addEventListener('online', updateOnlineStatus);
            window.addEventListener('offline', updateOnlineStatus);
            
            // Add online status indicator to page
            document.addEventListener('DOMContentLoaded', () => {
                const statusIndicator = document.createElement('div');
                statusIndicator.className = 'nanimade-online-status';
                document.body.appendChild(statusIndicator);
                updateOnlineStatus();
            });
            </script>
            <?php
        }
    }
    
    public function register_pwa_endpoints() {
        add_rewrite_rule('^pwa-manifest\.json$', 'index.php?nanimade_pwa=manifest', 'top');
        add_rewrite_rule('^sw\.js$', 'index.php?nanimade_pwa=sw', 'top');
        
        add_filter('query_vars', function($vars) {
            $vars[] = 'nanimade_pwa';
            return $vars;
        });
        
        add_action('template_redirect', array($this, 'handle_pwa_requests'));
    }
    
    public function handle_pwa_requests() {
        $pwa_request = get_query_var('nanimade_pwa');
        
        if ($pwa_request === 'manifest') {
            $this->serve_manifest();
        } elseif ($pwa_request === 'sw') {
            $this->serve_service_worker();
        }
    }
    
    private function serve_manifest() {
        header('Content-Type: application/json');
        
        $manifest = array(
            'name' => get_bloginfo('name'),
            'short_name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'start_url' => home_url('/'),
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#10B981',
            'orientation' => 'portrait-primary',
            'categories' => array('food', 'shopping', 'business'),
            'icons' => array()
        );
        
        // Generate icons
        $icon_sizes = array(72, 96, 128, 144, 152, 192, 384, 512);
        foreach ($icon_sizes as $size) {
            $manifest['icons'][] = array(
                'src' => $this->generate_app_icon($size),
                'sizes' => $size . 'x' . $size,
                'type' => 'image/png',
                'purpose' => 'any maskable'
            );
        }
        
        echo json_encode($manifest, JSON_PRETTY_PRINT);
        exit;
    }
    
    private function serve_service_worker() {
        header('Content-Type: application/javascript');
        header('Service-Worker-Allowed: /');
        
        ?>
        const CACHE_NAME = 'nanimade-pickle-v1';
        const urlsToCache = [
            '/',
            '/wp-content/plugins/nanimade-complete-suite/assets/css/main.css',
            '/wp-content/plugins/nanimade-complete-suite/assets/js/main.js',
            '/wp-content/plugins/nanimade-complete-suite/assets/css/mobile.css'
        ];
        
        self.addEventListener('install', (event) => {
            event.waitUntil(
                caches.open(CACHE_NAME)
                    .then((cache) => cache.addAll(urlsToCache))
            );
        });
        
        self.addEventListener('fetch', (event) => {
            event.respondWith(
                caches.match(event.request)
                    .then((response) => {
                        if (response) {
                            return response;
                        }
                        return fetch(event.request);
                    })
            );
        });
        
        self.addEventListener('push', (event) => {
            const options = {
                body: event.data ? event.data.text() : 'New pickle deals available!',
                icon: '<?php echo $this->generate_app_icon(192); ?>',
                badge: '<?php echo $this->generate_app_icon(72); ?>',
                vibrate: [100, 50, 100],
                data: {
                    dateOfArrival: Date.now(),
                    primaryKey: 1
                },
                actions: [
                    {
                        action: 'explore',
                        title: 'View Deals',
                        icon: '<?php echo $this->generate_app_icon(32); ?>'
                    },
                    {
                        action: 'close',
                        title: 'Close',
                        icon: '<?php echo $this->generate_app_icon(32); ?>'
                    }
                ]
            };
            
            event.waitUntil(
                self.registration.showNotification('<?php echo get_bloginfo('name'); ?>', options)
            );
        });
        
        self.addEventListener('notificationclick', (event) => {
            event.notification.close();
            
            if (event.action === 'explore') {
                event.waitUntil(
                    clients.openWindow('<?php echo home_url('/shop'); ?>')
                );
            }
        });
        <?php
        
        exit;
    }
    
    private function generate_app_icon($size) {
        // Generate dynamic app icon using CSS and SVG
        $icon_url = 'https://api.iconify.design/mdi/food-apple.svg?color=%2310B981&width=' . $size . '&height=' . $size;
        return $icon_url;
    }
    
    public function handle_pwa_install() {
        check_ajax_referer('nanimade_nonce', 'nonce');
        
        // Log PWA installation
        $install_data = array(
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'timestamp' => current_time('mysql'),
            'ip_address' => $_SERVER['REMOTE_ADDR']
        );
        
        update_option('nanimade_pwa_installs', get_option('nanimade_pwa_installs', array()) + array($install_data));
        
        wp_send_json_success(array(
            'message' => __('PWA installation tracked', 'nanimade-suite')
        ));
    }
}