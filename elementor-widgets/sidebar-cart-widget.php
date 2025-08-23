<?php
if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_Sidebar_Cart_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nanimade-sidebar-cart';
    }

    public function get_title() {
        return __('Sidebar Cart Widget', 'nanimade-suite');
    }

    public function get_icon() {
        return 'fas fa-shopping-basket';
    }

    public function get_categories() {
        return ['nanimade-suite'];
    }

    public function get_keywords() {
        return ['cart', 'sidebar', 'shopping', 'woocommerce'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Cart Settings', 'nanimade-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'cart_style',
            [
                'label' => __('Cart Style', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'slide',
                'options' => [
                    'slide' => __('Slide In', 'nanimade-suite'),
                    'fade' => __('Fade In', 'nanimade-suite'),
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <div class="nanimade-sidebar-cart-widget">
            <p><?php _e('Sidebar cart will be triggered by the mobile menu cart button.', 'nanimade-suite'); ?></p>
        </div>
        <?php
    }
}
?>