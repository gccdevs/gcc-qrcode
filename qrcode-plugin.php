<?php
/**
 * Plugin Name: QR Code Generator
 * Plugin URI: https://github.com/haog1/gcc-qrcode
 * Description: The QR code generator, made by Glory City Church of Melbourne
 * Version: 1.0
 * Author: Tony Gao
 * Author URI: https://github.com/haog1
 */

require_once 'vendor/autoload.php';
require_once 'helper.php';

add_action('plugins_loaded', 'plugin_loaded');

function plugin_loaded() {
  require_once 'enqueue.php';
  require_once 'lib/qrlib.php';
  require_once 'ajax.php';
  require_once 'main.php';
  require_once 'setting.php';
}
