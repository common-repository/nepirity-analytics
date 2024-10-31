<?php
namespace nepirity {

class Views {
  function __construct() {
  }

  protected function print_notice($type, $msg) {
    echo "<div class='".$type." notice is-dismissible'>";
    echo "<p><b>Note: </b>" . $msg . "</p>";
    echo '<button type="button" class="notice-dismiss"></button>';
    echo "</div>";
  }

  public function print_notice_normal($msg) {
    $this->print_notice("updated", $msg);
  }

  public function print_notice_error($msg) {
    $this->print_notice("error", $msg);
  }

  public function print_admin_header() {
    echo '<div class="wrap">';
  }

  public function print_admin_footer() {
    echo '</div>';
  }

  public function print_tab_page($tabname, $tabs) {
    $this->print_admin_header();

    if (NP()->settings()->is_configured()) {
      $this->print_tabs($tabname, $tabs);
    } else {
      NP()->settings()->setup_wizard();
    }

    $this->print_admin_footer();
  }

  protected function print_tabs($tabname, $tabs) {
    if (!isset($_GET['page'])) return;

    $page = $_GET['page'];
    $current = isset($_GET['tab'])?$_GET['tab']:'';

    if ($current != '' && !array_key_exists($current, $tabs)) {
      return;
    }

    echo '<ul class="nav nav-tabs">';
    foreach( $tabs as $tab => $name ){
      if ($current == '') {
        $current = $tab;
      }

      $class = ( $tab == $current ) ? ' class="active"' : '';
      echo "<li".$class."><a href='?page=".$page."&tab=$tab'>$name</a></li>";
    }
    echo '</ul>';

    if ($tabname == "settings") {
      $tabfile = NP_ABSPATH . "templates/admin/".$tabname ."/". $current . ".php";

      if (file_exists($tabfile)) {
        include_once($tabfile);
      }
    } else {
      $this->print_plugin_module_page($tabname, $current);
    }
  }

  public function print_common_module_layout($group, $module) {
    $plugin = NP()->jsonrpc()->plugin();
    $key = NP()->settings()->get_nepirity_key();

    echo $plugin->get_layout($group, $module, $key);
  }

  public function print_common_module_script($group, $module, $key='', $ids='', $auth='') {
    $plugin = NP()->jsonrpc()->plugin();
    $script_link = $plugin->get_script_link($group, $module, $key, $ids, $auth);
    echo "<script src='$script_link'></script>";
  }

  public function print_plugin_module_page($group, $module) {
    $key = NP()->settings()->get_nepirity_key();
    $ids = NP()->settings()->get_google_view_id();
    $auth = NP()->analytics()->get_google_authorize_obj();

    if (is_array(json_decode($auth, true))) {
      $this->print_common_module_layout($group, $module);
      $this->print_common_module_script($group, $module, $key, $ids, $auth);
    } else {
      $this->print_plugin_module_error_page($auth);
    }
  }

  public function print_plugin_module_error_page($msg) {
    $link = Admin::get_settings_setup_link();
    $html = "";

    $html .= "<br />";
    $html .= '<div align="center">' .$msg. ' [<a href="'.$link.'">' . np_translate("Settings") . '</a>]' . '</div>';

    echo $html;
  }

  public function get_html_from_option($option) {
    $html = "";

    if ($option['type'] == 'radio') {
      $name = $option['name'];

      foreach ($option['options'] as $data) {
        $checked = "";
        if ($data['checked']) $checked = ' checked="checked"';

        $html .= '<input type="radio" name="'.$name.'" value="'.$data['value'].'" '.$checked.'>';
        $html .= $data['desc']. '<br />';
      }
    }

    return $html;
  }

  public function get_date_string($unixtime) {
    return date_i18n(get_option('date_format'), $unixtime);
  }

  public function print_board_data() {
    $npkey = NP()->settings()->get_nepirity_key();
    $data = NP()->jsonrpc()->support()->get_board_data($npkey, get_locale());

    if (count($data) > 0) {
      $this->print_card_box($data);
    }
  }

  public function print_card_box($data) {
    $date_str = $this->get_date_string($data['reg_date']);
    ?>
    <div class="card">
      <div class="card-block">
      <h4 class="card-title"><?=$data['subject']?></h4>
      <h6 class="card-subtitle mb-2 text-muted"><?= $date_str ?></h6>
      <p class="card-text"><?=$data['message']?></p>
      <!--<a href="#" class="card-link" target="_blank">link</a> -->
      <?php if(isset($data['link'])) echo $data['link']; ?>
      </div>
    </div>
    <?php
  }
}

}
