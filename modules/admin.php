<?php

namespace nepirity {

class Admin {
  const MENU_SLUG_INDEX = "nepirity_admin_index";
  const MENU_SLUG_SETTINGS = "nepirity_admin_settings";

  function __construct() {}

  static function init() {
    add_action('admin_init', array(__CLASS__, 'admin_init') );
    add_action('admin_menu', array(__CLASS__, 'admin_menu') );
  }

  static function get_index_link() {
    return "/wp-admin/admin.php?page=". self::MENU_SLUG_INDEX;
  }

  static function get_settings_link() {
    return "/wp-admin/admin.php?page=". self::MENU_SLUG_SETTINGS;
  }

  static function get_settings_setup_link() {
    return self::get_settings_link(). "&action=setup";
  }

  static function get_settings_slug() {
    return self::MENU_SLUG_SETTINGS;
  }

  static function load_custom_assets($hook) {
    if (strpos($hook, 'nepirity') !== false) {
      $base_path = plugins_url()."/".NP_BASENAME."/templates/";

      $css_files = array("jquery-ui", "bootstrap-nepirity", "jqcloud", "np");
      $js_files = array("jquery-ui", "jquery.matchHeight", "google_analytics", "jqcloud");

      foreach ($css_files as $css) {
        wp_register_style($css, $base_path . "css/" . $css . ".css");
        wp_enqueue_style($css);
      }

      foreach ($js_files as $js) {
        wp_register_script($js, $base_path . "js/" . $js . ".js");
        wp_enqueue_script($js);
      }
    }
  }

  static function admin_init() {
    add_action( 'admin_enqueue_scripts', array(__CLASS__, 'load_custom_assets' ) );
  }

  static function admin_menu() {
    add_menu_page('Nepirity Analytics', np_translate('Nepirity'), 'manage_options', self::MENU_SLUG_INDEX,
      array(__CLASS__, 'index_page'), 'dashicons-chart-area');

    $settings = NP()->settings();

    if ($settings->is_configured()) {
      if ($settings->check_update_values()) {
        $settings->update_setting_values();
      }

      $module_menus = NP()->settings()->get_nepirity_modules();

      for ($n=0; $n<count($module_menus); $n++) {
        $module = $module_menus[$n];
        $slug = self::MENU_SLUG_INDEX;

        if ($n != 0) {
          $slug = 'nepirity_admin_'. $module['name'];
        }

        add_submenu_page(self::MENU_SLUG_INDEX, $module['title'] , $module['menu'],
          'manage_options', $slug, array(__CLASS__, 'module_page'));
      }

      add_submenu_page(self::MENU_SLUG_INDEX, 'Nepirity Analytics Settings', np_translate('Settings'),
        'manage_options', 'nepirity_admin_settings', array(__CLASS__, 'settings_page'));
    }
  }

  static function index_page() {
    $group_name = "";

    if (NP()->settings()->is_configured()) {
      $module_menus = NP()->settings()->get_nepirity_modules();

      if (count($module_menus)) {
        $group_name = $module_menus[0]['name'];
      }
    }

    self::print_module_page($group_name);
  }

  static function module_page() {
    $group_name = "";

    if (isset($_GET['page'])) {
      $e = explode("_", $_GET['page']);

      if (count($e) == 3) {
        $group_name = $e[2];
      }
    }

    self::print_module_page($group_name);
  }

  static function print_module_page($group_name="") {
    $tabs = array();
    $module_menus = NP()->settings()->get_nepirity_modules();

    foreach ($module_menus as $module) {
      if ($group_name == $module['name']) {
        foreach ($module['list'] as $tab_data) {
          $tabs[$tab_data['name']] = $tab_data['menu'];
        }
        break;
      }
    }

    NP()->views()->print_tab_page($group_name, $tabs);
  }

  static function settings_page() {
    $tabs = array('general' => np_translate('General'));
    NP()->views()->print_tab_page("settings", $tabs);
  }
}

Admin::init();
}

?>
