<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Icons_Manager;

class HSN_Before_After_Widget extends Widget_Base {

  public function get_name() { return 'hsn_before_after'; }
  public function get_title() { return 'Before / After Slider (HSN)'; }
  public function get_icon() { return 'eicon-image-before-after'; }
  public function get_categories() { return [ 'general' ]; }
  public function get_style_depends() { return [ 'hsn-ba-slider' ]; }
  public function get_script_depends() { return [ 'hsn-ba-slider' ]; }

  protected function register_controls() {

    $this->start_controls_section('section_content', ['label' => 'Content']);

    // NOTE: User wants "Before overlays After". We'll render After as base, Before as clipped overlay.
    $this->add_control(
      'after_image',
      [
        'label' => 'After Image (Base)',
        'type'  => Controls_Manager::MEDIA,
        'dynamic' => ['active' => true],
      ]
    );

    $this->add_control(
      'before_image',
      [
        'label' => 'Before Image (Overlay)',
        'type'  => Controls_Manager::MEDIA,
        'dynamic' => ['active' => true],
      ]
    );

    $this->add_group_control(
      Group_Control_Image_Size::get_type(),
      [
        'name' => 'img',
        'default' => 'full',
      ]
    );

    $this->add_responsive_control(
      'slider_height',
      [
        'label' => 'Height',
        'type' => Controls_Manager::SLIDER,
        'size_units' => ['px','vh'],
        'range' => [
          'px' => ['min' => 150, 'max' => 1240],
          'vh' => ['min' => 20, 'max' => 100],
        ],
        'default' => ['unit' => 'px', 'size' => 520],
        'selectors' => [
          '{{WRAPPER}} .hsn-ba-wrap' => 'height: {{SIZE}}{{UNIT}};',
        ],
      ]
    );

    $this->add_control(
      'start_position',
      [
        'label' => 'Start Position (%)',
        'type' => Controls_Manager::SLIDER,
        'size_units' => ['%'],
        'range' => ['%' => ['min' => 0, 'max' => 100]],
        'default' => ['unit' => '%', 'size' => 50],
      ]
    );

    $this->add_control(
  'handle_icon',
  [
    'label' => 'Handle Icon',
    'type' => Controls_Manager::ICONS,
    'skin' => 'inline',
    'label_block' => true,
    'default' => [
      'value' => 'fas fa-arrows-alt-h',
      'library' => 'fa-solid',
    ],
  ]
);


    $this->end_controls_section();

    // STYLE
    $this->start_controls_section(
      'section_style',
      [ 'label' => 'Style', 'tab' => Controls_Manager::TAB_STYLE ]
    );

    $this->add_control(
      'radius',
      [
        'label' => 'Border Radius (px)',
        'type' => Controls_Manager::NUMBER,
        'default' => 0,
        'min' => 0,
        'max' => 100,
        'selectors' => [
          '{{WRAPPER}} .hsn-ba-wrap' => 'border-radius: {{VALUE}}px;',
        ],
      ]
    );

    $this->add_control(
      'divider_color',
      [
        'label' => 'Divider Color',
        'type' => Controls_Manager::COLOR,
        'default' => 'rgba(255,255,255,0.75)',
        'selectors' => [
          '{{WRAPPER}} .hsn-ba-line' => 'background: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'handle_size',
      [
        'label' => 'Handle Size (px)',
        'type' => Controls_Manager::NUMBER,
        'default' => 46,
        'min' => 24,
        'max' => 140,
        'selectors' => [
          '{{WRAPPER}} .hsn-ba-knob' => 'width: {{VALUE}}px; height: {{VALUE}}px;',
        ],
      ]
    );

    $this->add_control(
      'handle_bg',
      [
        'label' => 'Handle Background',
        'type' => Controls_Manager::COLOR,
        'default' => 'rgba(255,255,255,0.85)',
        'selectors' => [
          '{{WRAPPER}} .hsn-ba-knob' => 'background: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'icon_fill',
      [
        'label' => 'Icon Color (SVG)',
        'type' => Controls_Manager::COLOR,
        'default' => '#000000',
        'selectors' => [
  		'{{WRAPPER}} .hsn-ba-knob svg' => 'fill: {{VALUE}}; color: {{VALUE}};',
  		'{{WRAPPER}} .hsn-ba-knob svg *' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
  		'{{WRAPPER}} .hsn-ba-knob i' => 'color: {{VALUE}};',
		],
      ]
    );

    $this->end_controls_section();
  }

  private function resolve_image_url( $media, $size_key = 'img' ) : string {
    $id  = ! empty( $media['id'] ) ? (int) $media['id'] : 0;
    $url = ! empty( $media['url'] ) ? $media['url'] : '';

    if ( $id ) {
      $sized = Group_Control_Image_Size::get_attachment_image_src( $id, $size_key, $this->get_settings_for_display() );
      if ( $sized ) return $sized;

      $fallback = wp_get_attachment_image_url( $id, 'full' );
      if ( $fallback ) return $fallback;
    }
    return $url ?: '';
  }

  private function svg_from_attachment_id( $id ) : string {
    if ( ! $id ) return '';
    $path = get_attached_file( $id );
    if ( ! $path || ! file_exists( $path ) ) return '';

    $ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
    if ( $ext !== 'svg' ) return '';

    $raw = file_get_contents( $path );
    if ( ! $raw ) return '';

    // Allow safe subset only
    $allowed = [
      'svg' => [
        'xmlns' => true, 'viewBox' => true, 'width' => true, 'height' => true,
        'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true,
        'stroke-linejoin' => true, 'aria-hidden' => true, 'focusable' => true, 'role' => true,
        'class' => true
      ],
      'path' => [
        'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true,
        'stroke-linecap' => true, 'stroke-linejoin' => true, 'opacity' => true
      ],
      'g' => [ 'fill' => true, 'stroke' => true, 'opacity' => true, 'transform' => true ],
      'circle' => [ 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'opacity' => true ],
      'rect' => [ 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'opacity' => true ],
      'line' => [ 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'opacity' => true ],
      'polyline' => [ 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'opacity' => true ],
    ];

    $clean = wp_kses( $raw, $allowed );
    return $clean ?: '';
  }

  private function default_svg() : string {
    return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
      <path d="M7.5 7.5 3 12l4.5 4.5M16.5 7.5 21 12l-4.5 4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M10 12h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </svg>';
  }

  protected function render() {
    $settings = $this->get_settings_for_display();

    $after  = $this->resolve_image_url( $settings['after_image'], 'img' );
    $before = $this->resolve_image_url( $settings['before_image'], 'img' );

    if ( ! $before || ! $after ) {
      echo '<div>Please select both images.</div>';
      return;
    }

    $start = isset($settings['start_position']['size']) ? (float) $settings['start_position']['size'] : 50;
    $start = max(0, min(100, $start));

    $icon_id  = ! empty($settings['handle_icon_media']['id']) ? (int)$settings['handle_icon_media']['id'] : 0;
    $icon_url = ! empty($settings['handle_icon_media']['url']) ? $settings['handle_icon_media']['url'] : '';

    $inline_svg = $this->svg_from_attachment_id($icon_id);
    $uid = 'hsn-ba-' . $this->get_id();
    ?>

    <div
      id="<?php echo esc_attr($uid); ?>"
      class="hsn-ba-wrap"
      data-start="<?php echo esc_attr($start); ?>"
      role="group"
      aria-label="Before after image comparison"
    >
      <!-- AFTER: base image -->
      <img class="hsn-ba-img hsn-ba-img--after" src="<?php echo esc_url($after); ?>" alt="After" loading="lazy" />

      <!-- BEFORE: overlay clipped -->
      <div class="hsn-ba-before" aria-hidden="true">
        <img class="hsn-ba-img hsn-ba-img--before" src="<?php echo esc_url($before); ?>" alt="Before" loading="lazy" />
      </div>

      <div class="hsn-ba-line" aria-hidden="true"></div>

      <div class="hsn-ba-knob" aria-hidden="true">
  <?php
    $icon_settings = $settings['handle_icon'] ?? [];

    if ( ! empty($icon_settings['value']) ) {
      Icons_Manager::render_icon(
        $icon_settings,
        [ 'aria-hidden' => 'true', 'class' => 'hsn-ba-icon' ]
      );
    } else {
      echo $this->default_svg();
    }
  ?>
</div>


      <!-- Hidden range for keyboard accessibility -->
      <input class="hsn-ba-range" type="range" min="0" max="100" value="<?php echo esc_attr($start); ?>" aria-label="Slider position" />
    </div>

    <?php
  }
}
