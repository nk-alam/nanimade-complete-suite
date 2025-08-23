<?php
if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_Analytics {
    
    public function __construct() {
        add_action('wp_ajax_nanimade_track_events', array($this, 'ajax_track_events'));
        add_action('wp_ajax_nopriv_nanimade_track_events', array($this, 'ajax_track_events'));
    }
    
    public function ajax_track_events() {
        check_ajax_referer('nanimade_nonce', 'nonce');
        
        $events = json_decode(stripslashes($_POST['events']), true);
        
        if (!is_array($events)) {
            wp_send_json_error('Invalid events data');
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'nanimade_analytics';
        
        foreach ($events as $event) {
            $wpdb->insert(
                $table_name,
                array(
                    'event_type' => sanitize_text_field($event['type']),
                    'event_data' => wp_json_encode($event['data']),
                    'user_id' => get_current_user_id(),
                    'session_id' => sanitize_text_field($event['sessionId']),
                    'timestamp' => current_time('mysql')
                ),
                array('%s', '%s', '%d', '%s', '%s')
            );
        }
        
        wp_send_json_success('Events tracked');
    }
}
?>