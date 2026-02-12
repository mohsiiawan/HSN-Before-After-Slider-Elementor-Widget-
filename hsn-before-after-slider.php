<?php
/**
 * Plugin Name: HSN Before/After Slider (Elementor Widget)
 * Description: Elementor widget for before/after image comparison with Dynamic Tags support.
 * Version: 1.2.0
 * Author: Dev Mohsan
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class HSN_Before_After_Slider {
  const VERSION = '1.2.0';

  public function __construct() {
    add_action( 'plugins_loaded', [ $this, 'init' ] );
  }

  public function init() {
    if ( ! did_action( 'elementor/loaded' ) ) return;

    add_action( 'elementor/widgets/register', [ $this, 'register_widget' ] );
    add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
  }

  public function register_assets() {
    $url = plugin_dir_url( __FILE__ );

    wp_register_script(
      'hsn-ba-slider',
      $url . 'assets/ba-slider.js',
      [],
      self::VERSION,
      true
    );

    wp_register_style(
      'hsn-ba-slider',
      $url . 'assets/ba-slider.css',
      [],
      self::VERSION
    );
  }

  public function register_widget( $widgets_manager ) {
    require_once __DIR__ . '/widgets/class-hsn-before-after-widget.php';
    $widgets_manager->register( new \HSN_Before_After_Widget() );
  }
}

new HSN_Before_After_Slider();
