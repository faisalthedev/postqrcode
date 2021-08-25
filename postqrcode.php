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

    // Generate the QR code
    $qr_img_src = sprintf('https://api.qrserver.com/v1/create-qr-code/?size=185x185&ecc=L&qzone=1&data=%s', $current_post_url);
    // Insert the QR code at the bottom of content
    $content .= sprintf('<div class="pqrc"><img src="%s" alt="Post QR code" /></div>', $qr_img_src);

    return $content;
  }
  add_filter('the_content', 'pqrc_display_qr_code');