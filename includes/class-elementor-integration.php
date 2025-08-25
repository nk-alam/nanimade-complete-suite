<?php
/**
 * Elementor Integration Class
 * Provides custom Elementor widgets for pickle businesses
 */

if (!defined('ABSPATH')) {
    exit;
}

class NaniMade_Elementor_Integration {
    
    public function __construct() {
        add_action('elementor/widgets/widgets_registered', array($this, 'register_widgets'));
        add_action('elementor/elements/categories_registered', array($this, 'add_widget_categories'));
        add_action('elementor/frontend/after_enqueue_styles', array($this, 'enqueue_elementor_styles'));
    }
    
    public function add_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'nanimade-pickle',
            array(
                'title' => __('NaniMade Pickle', 'nanimade-suite'),
                'icon' => 'fa fa-leaf'
            )
        );
    }
    
    public function register_widgets() {
        // Register custom widgets
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new NaniMade_Pickle_Product_Grid());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new NaniMade_Mobile_Cart());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new NaniMade_Recipe_Showcase());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new NaniMade_Spice_Meter());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new NaniMade_Testimonial_Carousel());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new NaniMade_Nutrition_Facts());
    }
    
    public function enqueue_elementor_styles() {
        wp_enqueue_style(
            'nanimade-elementor',
            NANIMADE_SUITE_PLUGIN_URL . 'assets/css/elementor.css',
            array(),
            NANIMADE_SUITE_VERSION
        );
    }
}

/**
 * Spice Meter Widget
 */
class NaniMade_Spice_Meter extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'nanimade_spice_meter';
    }
    
    public function get_title() {
        return __('Spice Meter', 'nanimade-suite');
    }
    
    public function get_icon() {
        return 'eicon-progress-tracker';
    }
    
    public function get_categories() {
        return array('nanimade-pickle');
    }
    
    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Content', 'nanimade-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );
        
        $this->add_control(
            'spice_level',
            array(
                'label' => __('Spice Level', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'medium',
                'options' => array(
                    'mild' => __('Mild', 'nanimade-suite'),
                    'medium' => __('Medium', 'nanimade-suite'),
                    'hot' => __('Hot', 'nanimade-suite'),
                    'extra_hot' => __('Extra Hot', 'nanimade-suite'),
                ),
            )
        );
        
        $this->add_control(
            'show_label',
            array(
                'label' => __('Show Label', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'nanimade-suite'),
                'label_off' => __('Hide', 'nanimade-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->end_controls_section();
        
        // Style section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Style', 'nanimade-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );
        
        $this->add_control(
            'meter_color',
            array(
                'label' => __('Meter Color', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ff6b35',
            )
        );
        
        $this->add_responsive_control(
            'meter_size',
            array(
                'label' => __('Size', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range' => array(
                    'px' => array(
                        'min' => 20,
                        'max' => 60,
                        'step' => 2,
                    ),
                ),
                'default' => array(
                    'unit' => 'px',
                    'size' => 30,
                ),
            )
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        $spice_level = $settings['spice_level'];
        $show_label = $settings['show_label'] === 'yes';
        
        echo '<div class="nanimade-spice-meter-widget">';
        
        if ($show_label) {
            echo '<span class="spice-label">' . __('Spice Level:', 'nanimade-suite') . '</span>';
        }
        
        echo '<div class="spice-meter spice-' . esc_attr($spice_level) . '">';
        
        $levels = array('mild' => 1, 'medium' => 2, 'hot' => 3, 'extra_hot' => 4);
        $current_level = $levels[$spice_level];
        
        for ($i = 1; $i <= 4; $i++) {
            $active = $i <= $current_level ? 'active' : '';
            echo '<span class="spice-dot ' . $active . '" style="color: ' . esc_attr($settings['meter_color']) . '; font-size: ' . esc_attr($settings['meter_size']['size']) . 'px;">';
            echo nanimade_get_dynamic_icon('fire', 'solid', $settings['meter_size']['size']);
            echo '</span>';
        }
        
        echo '</div>';
        echo '</div>';
    }
}

/**
 * Testimonial Carousel Widget
 */
class NaniMade_Testimonial_Carousel extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'nanimade_testimonial_carousel';
    }
    
    public function get_title() {
        return __('Pickle Testimonials', 'nanimade-suite');
    }
    
    public function get_icon() {
        return 'eicon-testimonial-carousel';
    }
    
    public function get_categories() {
        return array('nanimade-pickle');
    }
    
    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Testimonials', 'nanimade-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );
        
        $repeater = new \Elementor\Repeater();
        
        $repeater->add_control(
            'customer_name',
            array(
                'label' => __('Customer Name', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Happy Customer', 'nanimade-suite'),
            )
        );
        
        $repeater->add_control(
            'testimonial_text',
            array(
                'label' => __('Testimonial', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __('These pickles are absolutely delicious! The perfect blend of spices.', 'nanimade-suite'),
            )
        );
        
        $repeater->add_control(
            'rating',
            array(
                'label' => __('Rating', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '5',
                'options' => array(
                    '5' => '5 Stars',
                    '4' => '4 Stars',
                    '3' => '3 Stars',
                    '2' => '2 Stars',
                    '1' => '1 Star',
                ),
            )
        );
        
        $this->add_control(
            'testimonials',
            array(
                'label' => __('Testimonials', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => array(
                    array(
                        'customer_name' => __('Priya Sharma', 'nanimade-suite'),
                        'testimonial_text' => __('Best mango pickle I\'ve ever tasted! Reminds me of my grandmother\'s recipe.', 'nanimade-suite'),
                        'rating' => '5',
                    ),
                    array(
                        'customer_name' => __('Rajesh Kumar', 'nanimade-suite'),
                        'testimonial_text' => __('Perfect spice level and amazing flavor. Will definitely order again!', 'nanimade-suite'),
                        'rating' => '5',
                    ),
                ),
                'title_field' => '{{{ customer_name }}}',
            )
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        if (empty($settings['testimonials'])) {
            return;
        }
        
        echo '<div class="nanimade-testimonial-carousel">';
        echo '<div class="testimonial-slider">';
        
        foreach ($settings['testimonials'] as $testimonial) {
            echo '<div class="testimonial-slide">';
            echo '<div class="testimonial-content">';
            echo '<div class="testimonial-text">"' . esc_html($testimonial['testimonial_text']) . '"</div>';
            echo '<div class="testimonial-rating">';
            
            for ($i = 1; $i <= 5; $i++) {
                $star_class = $i <= intval($testimonial['rating']) ? 'active' : '';
                echo '<span class="star ' . $star_class . '">' . nanimade_get_dynamic_icon('star', 'solid', 16) . '</span>';
            }
            
            echo '</div>';
            echo '<div class="testimonial-author">';
            echo '<div class="author-avatar">';
            echo '<img src="https://api.pravatar.cc/60?u=' . urlencode($testimonial['customer_name']) . '" alt="' . esc_attr($testimonial['customer_name']) . '">';
            echo '</div>';
            echo '<div class="author-name">' . esc_html($testimonial['customer_name']) . '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '<div class="carousel-controls">';
        echo '<button class="carousel-prev">' . nanimade_get_dynamic_icon('chevron-left', 'outline', 20) . '</button>';
        echo '<button class="carousel-next">' . nanimade_get_dynamic_icon('chevron-right', 'outline', 20) . '</button>';
        echo '</div>';
        echo '</div>';
    }
}

/**
 * Nutrition Facts Widget
 */
class NaniMade_Nutrition_Facts extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'nanimade_nutrition_facts';
    }
    
    public function get_title() {
        return __('Nutrition Facts', 'nanimade-suite');
    }
    
    public function get_icon() {
        return 'eicon-table';
    }
    
    public function get_categories() {
        return array('nanimade-pickle');
    }
    
    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Nutrition Information', 'nanimade-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );
        
        $this->add_control(
            'serving_size',
            array(
                'label' => __('Serving Size', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '1 tablespoon (15g)',
            )
        );
        
        $this->add_control(
            'calories',
            array(
                'label' => __('Calories', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5,
            )
        );
        
        $this->add_control(
            'sodium',
            array(
                'label' => __('Sodium (mg)', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 180,
            )
        );
        
        $this->add_control(
            'vitamin_c',
            array(
                'label' => __('Vitamin C (%)', 'nanimade-suite'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 15,
            )
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        echo '<div class="nanimade-nutrition-facts">';
        echo '<div class="nutrition-header">';
        echo '<h3>' . __('Nutrition Facts', 'nanimade-suite') . '</h3>';
        echo '<div class="serving-size">' . esc_html($settings['serving_size']) . '</div>';
        echo '</div>';
        
        echo '<div class="nutrition-content">';
        echo '<div class="nutrition-row calories">';
        echo '<span class="label">' . __('Calories', 'nanimade-suite') . '</span>';
        echo '<span class="value">' . esc_html($settings['calories']) . '</span>';
        echo '</div>';
        
        echo '<div class="nutrition-row">';
        echo '<span class="label">' . __('Sodium', 'nanimade-suite') . '</span>';
        echo '<span class="value">' . esc_html($settings['sodium']) . 'mg</span>';
        echo '</div>';
        
        echo '<div class="nutrition-row">';
        echo '<span class="label">' . __('Vitamin C', 'nanimade-suite') . '</span>';
        echo '<span class="value">' . esc_html($settings['vitamin_c']) . '%</span>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
    }
}