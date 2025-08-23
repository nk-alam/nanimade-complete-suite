<?php
if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_Trust_Signals_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nanimade-trust-signals';
    }

    public function get_title() {
        return __('Trust Signals Widget', 'nanimade-suite');
    }

    public function get_icon() {
        return 'fas fa-shield-alt';
    }

    public function get_categories() {
        return ['nanimade-suite'];
    }

    public function get_keywords() {
        return ['trust', 'signals', 'badges', 'quality'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Trust Signals', 'nanimade-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_freshness',
            [
                'label' => __('Show Freshness Badge', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <div class="nanimade-trust-signals-widget">
            <div class="nanimade-trust-signals">
                <div class="nanimade-trust-item">
                    <i class="fas fa-shield-alt"></i>
                    <span><?php _e('Handmade Fresh', 'nanimade-suite'); ?></span>
                </div>
                <div class="nanimade-trust-item">
                    <i class="fas fa-leaf"></i>
                    <span><?php _e('No Preservatives', 'nanimade-suite'); ?></span>
                </div>
                <div class="nanimade-trust-item">
                    <i class="fas fa-heart"></i>
                    <span><?php _e('Made by Nani', 'nanimade-suite'); ?></span>
                </div>
            </div>
        </div>
        <?php
    }
}
?>