<?php
/*
Plugin Name: Nepirity Analytics
Description: Nepirity Analytics is a plugin that provides web traffic data analysis feature with data gathered from Google Analytics.
Version:   1.1.6
Author:    Nepirity Corp.
Author URI:  https://www.nepirity.com/
Text Domain: nepirity-services
Domain Path: /languages/
*/

if (!defined('ABSPATH')) exit;

if (!class_exists('Nepirity')) :

include_once("config.php");

final class Nepirity {
  protected static $_instance = null;

  protected $_settings = null;
  protected $_views = null;
  protected $_analytics = null;
  protected $_jsonrpc = null;

  public static function instance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  public function __construct() {
    $this->define_constants();

    $this->load_includes();
    $this->load_modules();

    $this->init_variables();
    $this->init_hooks();
  }

  private function __clone() {}
  private function __sleep() {}
  private function __wakeup() {}

  public function views() {
    return $this->_views;
  }

  public function settings() {
    return $this->_settings;
  }

  public function analytics() {
    return $this->_analytics;
  }

  public function jsonrpc() {
    return $this->_jsonrpc;
  }

  private function define_constants() {
    /* ex) /data/www/wp/wp-content/plugins/nepirity/ */
    define('NP_ABSPATH', dirname(__FILE__) . '/' );

    /* ex) nepirity */
    define('NP_BASENAME', basename(dirname(__FILE__)));

    /*
     * Bootstrap v4.0.0-alpha.6 requires jQuery 1.9.1 or later.
     * jQuery UI v1.12.1 requires jQuery 1.7 or later.
     *
     * WordPress 3.6 included jQuery 1.10.2
     * Requires Wordpress NP_REQUIRE_WP_VERSION or later.
     */
    define('NP_REQUIRE_WP_VERSION', '3.6');
  }

  /* ex) https://wp.nepirity.com/wp-content/plugins/nepirity/ */
  public static function get_plugin_url() {
    return plugins_url() . "/" . NP_BASENAME . "/";
  }

  private function init_hooks() {
    load_plugin_textdomain('nepirity-services', false, NP_BASENAME . '/languages' );
    add_action('wp_head', array($this, 'set_google_tracking_code'));
    register_uninstall_hook(__FILE__, 'uninstall');
    add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'settings_link' ) );
  }

  private function init_variables() {
    $this->_views = new nepirity\Views();
    $this->_settings = new nepirity\Setting();
    $this->_analytics = new nepirity\Analytics();

    if (is_admin()) {
      $server = $this->_settings->get_nepirity_server();
      if ($server == "") {
        $server = NP_DEFAULT_SERVER;
      }

      $this->_jsonrpc = new jsonrpc\JsonRpc($server);
    }
  }

  private function load_includes() {
    include_once("includes/functions.php");
  }

  private function load_modules() {
    include_once("modules/settings.php");
    include_once("modules/views.php");
    include_once("modules/analytics.php");

    if (is_admin()) {
      include_once("sdk/classes/jsonrpc.php");
      include_once("modules/setup.php");
      include_once("modules/admin.php");
    }
  }

  public function set_google_tracking_code() {
    if ($this->_settings->is_enabled_google_tracking()) {
      if(!(current_user_can('editor') || current_user_can('administrator'))) {
        $tracking_id = $this->_settings->get_google_property_id();

        if ($this->_settings->gtag_script()) {
          $this->_analytics->print_gtag_tracking_code($tracking_id);
        } else {
          $this->_analytics->print_analytics_tracking_code($tracking_id);
        }
      }
    }
  }

  public function settings_link( $links ) {
    $link = nepirity\Admin::get_index_link();

    if (NP()->settings()->is_configured()) {
      $link = nepirity\Admin::get_settings_link();
    }
    $settings_link = '<a href="'.$link.'">' . np_translate("Settings") . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
  }

  public function get_plugin_version() {
    $data = get_plugin_data(__FILE__);
    return $data['Version'];
  }
}

function NP() {
  return Nepirity::instance();
}

function uninstall() {
  NP()->settings()->delete_all_options();
}

function np_activate() {
  if (NP()->settings()->is_configured()) {
    NP()->settings()->update_version();
    NP()->settings()->update_modules();
  }
}

register_activation_hook( __FILE__, 'np_activate' );

NP();

endif;

?>
