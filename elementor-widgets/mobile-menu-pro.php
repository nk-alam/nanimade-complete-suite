<?php
if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_Mobile_Menu_Pro extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nanimade-mobile-menu-pro';
    }

    public function get_title() {
        return __('Mobile Menu Pro', 'nanimade-suite');
    }

    public function get_icon() {
        return 'fas fa-mobile-alt';
    }

    public function get_categories() {
        return ['nanimade-suite'];
    }

    public function get_keywords() {
        return ['mobile', 'menu', 'navigation', 'bottom'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Menu Settings', 'nanimade-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'menu_style',
            [
                'label' => __('Menu Style', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'modern',
                'options' => [
                    'modern' => __('Modern', 'nanimade-suite'),
                    'classic' => __('Classic', 'nanimade-suite'),
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <div class="nanimade-mobile-menu-widget">
            <p><?php _e('Mobile menu will appear at the bottom of the page on mobile devices.', 'nanimade-suite'); ?></p>
        </div>
        <?php
    }
}
?>