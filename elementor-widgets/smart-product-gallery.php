<?php
if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_Smart_Product_Gallery extends \Elementor\Widget_Base {

    public function get_name() {
        return 'nanimade-smart-gallery';
    }

    public function get_title() {
        return __('Smart Product Gallery', 'nanimade-suite');
    }

    public function get_icon() {
        return 'fas fa-images';
    }

    public function get_categories() {
        return ['nanimade-suite'];
    }

    public function get_keywords() {
        return ['gallery', 'product', 'images', 'zoom', '360'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Gallery Settings', 'nanimade-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'enable_zoom',
            [
                'label' => __('Enable Zoom', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <div class="nanimade-smart-gallery">
            <div class="nanimade-gallery-container">
                <img src="https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg" alt="Product Image" class="nanimade-zoomable">
            </div>
        </div>
        <?php
    }
}
?>