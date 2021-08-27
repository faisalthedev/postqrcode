<?php
  /**
   * Plugin Name:       Post QR Code Generator
   * Plugin URI:        https://example.com/plugins/postqrcode/
   * Description:       Post QR Code Generator plugin
   * Version:           0.0.1
   * Requires at least: 5.2
   * Requires PHP:      7.2
   * Author:            Faisal Ahammad
   * Author URI:        https://faisalahammad.com/
   * License:           GPL v2 or later
   * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
   * Text Domain:       postqrcode
   * Domain Path:       /languages
   */

   // load plugin text domain
   function postqrcode_textdomain_load() {
      load_muplugin_textdomain( 'postqrcode', false, dirname(__FILE__).'/languages' );
   }
   add_action('plugins_loaded', 'postqrcode_textdomain_load');

  //  generate the qr
  function pqrc_display_qr_code($content) {
    // Get the current post ID
    $current_post_id = get_the_ID();
    // Get the current post url and encode it
    $current_post_url = urlencode(get_the_permalink($current_post_id));
    // get post type
    $current_post_type = get_post_type($current_post_id);

    /**
     * Check post type
     * and remove from specific post type
     */
    $excluded_post_types = apply_filters('pqrc_excluded_post_types', array());
    if(in_array($current_post_type, $excluded_post_types)) {
      return $content;
    }

    // QR Code dimension
    $height = get_option('pqrc_height');
    $width = get_option('pqrc_width');
    $height = $height ? $height : '180';
    $width = $width ? $width : '180';
    $dimension = apply_filters('pqrc_qrcode_diamention', "{$height}x{$width}");

    // Generate the QR code
    $qr_img_src = sprintf('https://api.qrserver.com/v1/create-qr-code/?size=%s&ecc=L&qzone=1&data=%s', $dimension, $current_post_url);
    // Insert the QR code at the bottom of content
    $content .= sprintf('<div class="pqrc"><img src="%s" alt="Post QR code" /></div>', $qr_img_src);

    return $content;
  }
  add_filter('the_content', 'pqrc_display_qr_code');

  // register settings fields
  function pqrc_settings_init() {
    add_settings_field('pqrc_height', __('QR Code Height', 'postqrcode'), 'pqrc_display_height', 'general');
    add_settings_field('pqrc_width', __('QR Code Width', 'postqrcode'), 'pqrc_display_width', 'general');

    register_setting('general', 'pqrc_height', array('sanitize_callback' => 'esc_attr'));
    register_setting('general', 'pqrc_width', array('sanitize_callback' => 'esc_attr'));
  }

  // height input
  function pqrc_display_height() {
    $height = get_option('pqrc_height');
    printf("<input type='number' id='%s' name='%s' value='%s' />", 'pqrc_height', 'pqrc_height', $height);
  }

  function pqrc_display_width() {
    $width = get_option('pqrc_width');
    printf("<input type='number' id='%s' name='%s' value='%s' />", 'pqrc_width', 'pqrc_width', $width);
  }

  add_action('admin_init', 'pqrc_settings_init');