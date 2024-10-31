<?php

namespace nepirity {

class Setup {
  const STEP_DEFAULT = 'default';
  const STEP_NEPIRITY = 'nepirity';
  const STEP_GOOGLE_AUTHTYPE = 'google_authtype';
  const STEP_GOOGLE_ACCOUNT = 'google_account';
  const STEP_GOOGLE_PROFILE = 'google_profile';

  private static $STEP = array(
    self::STEP_DEFAULT, self::STEP_NEPIRITY,
    self::STEP_GOOGLE_AUTHTYPE,
    self::STEP_GOOGLE_ACCOUNT, self::STEP_GOOGLE_PROFILE);

  private static function get_current_step_value() {
    if (isset($_POST['step'])) {
      return $_POST['step'];
    }

    return 0;
  }

  private static function get_next_step_value() {
    return self::get_current_step_value() + 1;
  }

  private static function print_wizard_pannel($title, $description, $form_data, $module='') {
    $button_name = np_translate("Next");

    if (!Setup::get_current_step_value()) {
      $button_name = np_translate("Start");
    }

    if (Setup::get_current_step_value() + 1 == sizeof(self::$STEP)) {
      $button_name = np_translate("Done");
    }
?>
    <div class="wrap">
       <div id="welcome-panel" class="welcome-panel">
        <div class="welcome-panel-content">

        <h2><?=$title?></h2>
        <p><?=$description?></p>

<?php
    if (!Setup::get_current_step_value()) {
      global $wp_version;

      if (version_compare($wp_version, NP_REQUIRE_WP_VERSION, '<')) {
        $msg = np_translate("This plugin has not been tested with your version of WordPress");
        $msg .= " ". $wp_version . ". ";
        $msg .= np_translate("Before installing the plugin, we recommend that you upgrade to WordPress first.");

        echo '<div class="alert alert-info">';
        echo '<strong>Info!</strong> '. $msg;
        echo '</div>';
      }
    }
?>
        <form  method="post" id="np_setup" enctype="multipart/form-data">
          <table class="form-table">
<?php
    if (sizeof($form_data)) {
      foreach ($form_data as $data) {
        echo "          <tr>";
        if ($data['type'] == "text") {
          echo '<th scope="row">'.NP()->settings()->get_option_label_tag($data).'</th>';
          echo '<td>'. NP()->settings()->get_option_form_tag($data) .'</td>';
        }

        if ($data['type'] == "textarea") {
          echo "          <td>";
          echo NP()->settings()->get_option_label_tag($data);
          echo NP()->settings()->get_option_form_tag($data);
          echo "          </td>";
        }

        if ($data['type'] == "selector") {
          echo '<th scope="row">'.NP()->settings()->get_option_label_tag($data).'</th>';
          echo '<td>'. NP()->settings()->get_google_view_selector_tag($data) .'</td>';
        }

        if ($data['type'] == "radio") {
          echo '<th scope="row">'.NP()->settings()->get_option_label_tag($data).'</th>';
          echo '<td>'. NP()->views()->get_html_from_option($data) .'</td>';
        }
        echo "          </tr>";
      }
    }
?>
          </table>
        <div align='center'>
          <input type='hidden' name='step' value=<?=Setup::get_next_step_value()?> />
<?php
    if (Setup::get_current_step_value()) {
      echo '<a class="button tips" href="#" onclick="window.history.back();">'.np_translate("Go Back").'</a>';
    }

    if (sizeof($form_data) || !Setup::get_current_step_value()) {
      echo ' <input type="submit" class="button button-primary" value="'. $button_name. '" />';
    }
?>
          <br />
          <br />
        </div>
        </form>
<?php
    if ($module != '') {
      NP()->views()->print_common_module_layout("setup", $module);
    }
?>
        </div>
       </div>
    </div>
<?php
    if ($module != '') {
      NP()->views()->print_common_module_script("setup", $module);
    }
  }

  private static function update_setting_values($options, $values) {
    if (sizeof($options)) {
      foreach ($options as $data) {
        $name = $data['name'];
        $value = (isset($values[$name]))?$values[$name]:'';

        if ($value) {
          NP()->settings()->update_option_value($name, $value);
        }
      }
    }
  }

  private static function update_settings($options) {
    self::update_setting_values($options, $_POST);
  }

  private static function get_wizard_title($msg) {
    $level_msg = "";

    if (self::get_current_step_value()) {
      $level_msg = " (". self::get_current_step_value() . "/" . (sizeof(self::$STEP) - 1) . ")";
    }

    return $msg . $level_msg;
  }

  private static function print_wizard_default_step() {
    self::print_wizard_pannel(np_translate("Nepirity Analytics (Setup Wizard)"),
      np_translate("Click Start to Configure"), array(), 'welcome');
  }

  private static function print_wizard_nepirity_step() {
    $fields = NP()->settings()->get_nepirity_option_fields();

    for ($i=0; $i<count($fields); $i++) {
      if ($fields[$i]['name'] == Setting::FIELD_NEPIRITY_KEY) {
        $fields[$i]['desc'] = "";
        $fields[$i]['desc'] .= np_translate('Nepirity Key');
        $fields[$i]['desc'] .= " / ";
        $fields[$i]['desc'] .= np_translate('E-Mail');

        $defualt_value = NP()->settings()->get_nepirity_key();

        if ($defualt_value == '') {
          $defualt_value = get_option('admin_email');
        }

        $fields[$i]['value'] = $defualt_value;
      }
    }

    $msg = "";
    $msg .= np_translate("Please, enter your Nepirity plugin key or E-mail address.");
    $msg .= "<br />";
    $msg .= np_translate("If you enter an email address, the account will be created automatically with the nepirity key.");

    self::print_wizard_pannel(self::get_wizard_title(np_translate("Nepirity Plugin Setting")),
      $msg, $fields, 'nepirity_setup');
  }

  private static function print_wizard_nepirity_error() {
    $title = np_translate("Nepirity key information is not appropriate.");
    $msg = np_translate("Please, input appropriate Nepirity key information and check that the key settings are correct.");

    $msg .= "<br />";
    $msg .= "<br />";

    $msg .= np_translate("Your current site address is:");
    $msg .= "<br />".get_site_url();

    self::print_wizard_pannel($title, $msg, array(), 'nepirity_error');
  }

  private static function update_nepirity_setting() {
    if (isset($_POST[Setting::FIELD_NEPIRITY_KEY])) {
      $value = $_POST[Setting::FIELD_NEPIRITY_KEY];

      if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
        if (defined('NP_AUTHCODE')) {
          $_POST[Setting::FIELD_NEPIRITY_KEY]
            = NP()->jsonrpc()->account()->retrieve_auth_service_key(NP_AUTHCODE, $value, get_option("siteurl"));
        } else {
          $_POST[Setting::FIELD_NEPIRITY_KEY]
            = NP()->jsonrpc()->account()->retrieve_service_key($value, get_option("siteurl"));
        }
      } else {
        NP()->jsonrpc()->account()->update_service_key(defined('NP_AUTHCODE')?NP_AUTHCODE:'', $value);
      }
    }
    self::update_settings(NP()->settings()->get_nepirity_option_fields());
  }

  private static function print_wizard_google_authtype_step() {
    $fields = NP()->settings()->get_google_authtype_option_fields();
    $msg = np_translate("Please select a Google authentication method.");

    self::print_wizard_pannel(self::get_wizard_title(np_translate("Google Authentication Type")),
      $msg, $fields);
  }

  private static function update_google_authtype_setting() {
    self::update_settings(NP()->settings()->get_google_authtype_option_fields());
  }

  private static function print_wizard_google_account_step() {
    $msg = "";
    $title = "";
    $module = "";
    $fields = array();

    $auth_type = NP()->settings()->get_google_authtype();

    switch ($auth_type) {
      case Setting::GOOGLE_AUTHTYPE_OAUTH: {
        $module = "google_auth_oauth";
        $fields = NP()->settings()->get_google_oauth_option_fields();
        $title = self::get_wizard_title(np_translate("Google OAuth Setting"));
        $msg = np_translate("Please, enter Google OAuth code.");
        break;
      }

      case Setting::GOOGLE_AUTHTYPE_SERVER: {
        $module = "google_setup";
        $fields = NP()->settings()->get_google_account_option_fields();
        $title = self::get_wizard_title(np_translate("Google Service Account Setting"));

        $msg = np_translate("Please, enter your Google Service account information.");

        if (strlen(NP()->settings()->get_google_account_auth_config())) {
          $msg = np_translate("You can not modify the encrypted key.");
          $msg .= " ";

          $msg .= np_translate("Please save it again.");
          $msg .= "<br />";
          $msg .= np_translate("When you leave this page, the previously saved values are retained.");
        }

        break;
      }
    }

    self::print_wizard_pannel($title, $msg, $fields, $module);
  }
  private static function update_google_account_setting() {
    self::update_settings(NP()->settings()->get_google_account_option_fields());

    NP()->settings()->remove_cache_options();

    $auth_type = NP()->settings()->get_google_authtype();
    if ($auth_type == Setting::GOOGLE_AUTHTYPE_OAUTH) {
      $oauth_code = NP()->settings()->get_google_account_auth_config();

      $analytics = NP()->analytics();
      $token = $analytics->get_google_access_token($oauth_code);
      NP()->settings()->save_google_token($token);
    }
  }

  private static function print_wizard_google_profile_step($profiles) {
    NP()->settings()->print_google_view_selector_head_script();

    $fields = NP()->settings()->get_google_profile_option_fields();
    self::print_wizard_pannel(self::get_wizard_title(np_translate("Google Profile Setting")),
      np_translate("Please, select your Google View information."), $fields);

    NP()->settings()->print_google_view_selector_foot_script($profiles);
  }

  private static function update_google_profile_setting() {
    self::update_settings(NP()->settings()->get_google_profile_option_fields());
  }

  private static function update_default_settings() {
    $values = array();
    $options = NP()->settings()->get_all_option_fields();

    foreach ($options as $option) {
      if (isset($option['default'])) {
        $name = $option['name'];
        $value = $option['default'];
        $values[$name] = $value;
      }
    }

    $option_name = NP()->settings()->get_nepirity_server_option_name();
    $options[] = array("name" => $option_name);
    $values[$option_name] = NP()->jsonrpc()->support()->get_fast_server();

    NP()->settings()->update_version();
    NP()->settings()->update_modules();

    self::update_setting_values($options, $values);
  }

  public static function get_google_error($msg) {
    $data = json_decode($msg, true);

    if (!is_array($data)) {
      return $msg;
    }

    if (isset($data['error'])) {
      if (isset($data['error']['errors'])) {
        $errors = $data['error']['errors'];

        $html = "";
        $html .= "<table class='table table-bordered'>";

        for ($i=0; $i<count($errors); $i++) {
          $error = $errors[$i];

          foreach ($error as $key => $value) {
            $html .= "<tr><th>$key</th><td>$value</td></tr>";
          }
        }

        $html .= "</table><br />";
        $html .= "<table class='table table-bordered'>";
        $html .= "<tr><th>code</th><td>".$data['error']['code']."</td></tr>";
        $html .= "<tr><th>message</th><td>".$data['error']['message']."</td></tr>";
        $html .= "</table>";

        return $html;
      }

      return $data['error'];
    }

    return $msg;
  }

  public static function run($step_value=0) {
    if (self::get_current_step_value()) {
      $step_value = self::get_current_step_value();
    }

    $step_name = '';

    if (sizeof(self::$STEP) != $step_value) {
      $step_name = self::$STEP[$step_value];
    }

    switch ($step_name) {
      case self::STEP_DEFAULT: {
        self::print_wizard_default_step();
        break;
      }
      case self::STEP_NEPIRITY: {
        self::print_wizard_nepirity_step();
        break;
      }
      case self::STEP_GOOGLE_AUTHTYPE: {
        self::update_nepirity_setting(); // update and check it
        $key = NP()->settings()->get_nepirity_key();

        if (NP()->settings()->is_validated_nepirity_key($key)) {
          self::print_wizard_google_authtype_step();
        } else {
          self::print_wizard_nepirity_error();
        }
        break;
      }
      case self::STEP_GOOGLE_ACCOUNT: {
        self::update_google_authtype_setting();
        self::print_wizard_google_account_step();
        break;
      }
      case self::STEP_GOOGLE_PROFILE: {
        self::update_google_account_setting();

        $profiles = null;
        $error_msg = null;
        $analytics = NP()->analytics();

        try {
          $profiles = $analytics->get_google_profiles();
        }catch (\Exception $e) {
          $error_msg = $e->getMessage();
        }

        if (count($profiles)) {
          self::print_wizard_google_profile_step($profiles);
        } else {
          $msg = "";
          $title = np_translate("Google Setting information is not appropriate.");

          $msg .= np_translate("Please set up your account information correctly.")."<br />";
          $msg .= "<br />". self::get_google_error($error_msg);

          self::print_wizard_pannel($title, $msg, array(), 'google_error');
        }

        break;
      }
      default : {
        self::update_google_profile_setting();
        self::update_default_settings();
        echo "<script>location.href = '".Admin::get_index_link()."';</script>";
      }
    }
  }
}

}
?>
