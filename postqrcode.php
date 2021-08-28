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

  //  Init function
  function pqrc_init() {
    global $pqrc_countries;
    $pqrc_countries = apply_filters('pqrc_countries', $pqrc_countries);
  }
  add_action('init', 'pqrc_init');

  //  Countries list
  $pqrc_countries = array(
    __('Afganistan', 'postqrcode'),
    __('Bangladesh', 'postqrcode'),
    __('Bhutan', 'postqrcode'),
    __('India', 'postqrcode'),
    __('Maldives', 'postqrcode'),
    __('Nepaal', 'postqrcode'),
    __('Pakistan', 'postqrcode'),
    __('Sri Lanka', 'postqrcode')
  );

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
    // section register
    add_settings_section('pqrc_section', __('Post to QR Code', 'postqrcode'), 'pqrc_section_callback', 'general');

    // option register
    add_settings_field('pqrc_height', __('QR Code Height', 'postqrcode'), 'pqrc_display_field', 'general', 'pqrc_section', array('pqrc_height', 'px'));
    add_settings_field('pqrc_width', __('QR Code Width', 'postqrcode'), 'pqrc_display_field', 'general', 'pqrc_section', array('pqrc_width', 'px'));
    // add_settings_field('pqrc_extra', __('QR Code Extra', 'postqrcode'), 'pqrc_display_field', 'general', 'pqrc_section', array('pqrc_extra', 'optional'));
    add_settings_field('pqrc_select', __('Where are you located?', 'postqrcode'), 'pqrc_display_select_field', 'general', 'pqrc_section');
    add_settings_field('pqrc_checkbox', __('Where you traveled?', 'postqrcode'), 'pqrc_display_checkbox_field', 'general', 'pqrc_section');

    // register options
    register_setting('general', 'pqrc_height', array('sanitize_callback' => 'esc_attr'));
    register_setting('general', 'pqrc_width', array('sanitize_callback' => 'esc_attr'));
    // register_setting('general', 'pqrc_extra', array('sanitize_callback' => 'esc_attr'));
    register_setting('general', 'pqrc_select', array('sanitize_callback' => 'esc_attr'));
    register_setting('general', 'pqrc_checkbox');
  }

  // section callback
  function pqrc_section_callback() {
    echo "<p>". __('Settings for Post to QR plugin','postqrcode') ."</p>";
  }

  // Input field
  function pqrc_display_field($args) {
    $option = get_option($args[0]);
    printf("<input type='text' id='%s' name='%s' value='%s' placeholder='%s' />", $args[0], $args[0], $option, $args[1]);
  }

  // select dropdown field
  function pqrc_display_select_field() {
    global $pqrc_countries;
    $option = get_option('pqrc_select');

    printf("<select id='%s' name='%s'>", 'pqrc_select', 'pqrc_select');
    foreach($pqrc_countries as $country) {
      $selected = '';
      if($option == $country) {
        $selected = 'selected';
      }
      printf("<option value='%s' %s>%s</option>", $country, $selected, $country);
    }
    echo "</select>";
  }

  function pqrc_display_checkbox_field() {
    global $pqrc_countries;
    $option = get_option('pqrc_checkbox');

    foreach($pqrc_countries as $country) {
      $selected = '';

      if( is_array($option) && in_array($country, $option) ) {
        $selected = 'checked';
      }
      printf("<input type='checkbox' name='pqrc_checkbox[]' value='%s' %s /> %s <br>", $country, $selected, $country);
    }
  }

  // height & width input
  // function pqrc_display_height() {
  //   $height = get_option('pqrc_height');
  //   printf("<input type='number' id='%s' name='%s' value='%s' />", 'pqrc_height', 'pqrc_height', $height);
  // }

  // function pqrc_display_width() {
  //   $width = get_option('pqrc_width');
  //   printf("<input type='number' id='%s' name='%s' value='%s' />", 'pqrc_width', 'pqrc_width', $width);
  // }

  add_action('admin_init', 'pqrc_settings_init');