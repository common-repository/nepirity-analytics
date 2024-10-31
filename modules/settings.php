<?php

namespace nepirity {

class Setting {
  const FIELD_NEPIRITY_KEY = 'np_nepirity_key';
  const FIELD_GOOGLE_AUTHTYPE = 'np_google_auth_type';
  const FIELD_GOOGLE_ACCOUNT_AUTH_CONFIG = 'np_google_auth_config';
  const FIELD_GOOGLE_PROFILE_ACCOUNT_ID = 'np_google_profile_account_id';
  const FIELD_GOOGLE_PROFILE_PROPERTY_ID = 'np_google_profile_property_id';
  const FIELD_GOOGLE_PROFILE_VIEW_ID = 'np_google_profile_view_id';

  const FIELD_GOOGLE_TRACKING_ENABLED = 'np_google_tracking_enabled';
  const FIELD_GOOGLE_TRACKING_SCRIPT = 'np_google_tracking_script';

  const FIELD_NEPIRITY_VERSION = 'np_nepirity_version';
  const FIELD_NEPIRITY_SERVER = 'np_nepirity_server';
  const FIELD_NEPIRITY_MODULES = 'np_nepirity_modules';

  const FIELD_GOOGLE_ACCESS_TOKEN = 'np_google_access_token';

  const GOOGLE_AUTHTYPE_OAUTH = 1;
  const GOOGLE_AUTHTYPE_SERVER = 2;

  function __construct() {
  }

  public function is_configured() {
    $options = array();

    $options = array_merge($options, $this->get_nepirity_option_fields());
    $options = array_merge($options, $this->get_google_authtype_option_fields());
    $options = array_merge($options, $this->get_google_account_option_fields());
    $options = array_merge($options, $this->get_google_profile_option_fields());

    foreach ($options as $option) {
      if (!$this->get_option_value($option['name'])) return false;
    }

    if (!$this->get_option_value(self::FIELD_NEPIRITY_MODULES)) {
      return false;
    }

    return true;
  }

  public function is_configured_error() {
    $key = $this->get_nepirity_key();
    if (!$this->is_validated_nepirity_key($key)) {
      return true;
    }

    return false;
  }

  public function print_updated_message() {
    $html = '';
    $html .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">';
    $html .= '<p><strong>'. np_translate("Settings saved.") .'</strong></p>';
    $html .= '<button type="button" class="notice-dismiss">';
    $html .= '<span class="screen-reader-text">'. np_translate("Dismiss this notice.").'</span>';
    $html .= '</button>';
    $html .= '</div>';

    echo $html;
  }

  public function check_update_values() {
    if (isset($_POST['update_setting_values']) && $_POST['update_setting_values']) {
      return true;
    }

    return false;
  }

  public function update_setting_values() {
    foreach ($_POST as $key => $value) {
      if (strpos($key, "np_") !== false) {
        $this->update_option_value($key, $value);
      }
    }

    $options = $this->get_all_option_fields();
    foreach ($options as $option) {
      if ($option['type'] == 'checkbox') {
        if (!array_key_exists($option['name'], $_POST)) {
          $this->update_option_value($option['name'], 0);
        }
      }
    }

    $this->update_modules();
  }

  private function print_setting_field($option) {
    $html = "";
    $form_tag = $this->get_option_form_tag($option);

    switch($option['type']) {
      case 'checkbox': {
        $html .= '<tr>';
        $html .= '<th scope="row"><b>'.$option['desc'].'</b></th>';
        $html .= '<td>';
        $html .= '<label for="'.$option['name'].'">'.$form_tag. '</label>';
        $html .= '</td>';
        $html .= '</tr>';
        break;
      }
      default: {
        $html = '<tr><th scope="row"><label for="'.$option['name'].'">'.$option['desc'];
        $html .= '</label></th><td>'.$form_tag.'</td></tr>';
      }
    }

    echo $html;
  }

  protected function get_nepirity_key_memo() {
    $key = $this->get_nepirity_key();
    $data = $this->get_nepirity_service_info($key);

    return "";
  }

  public function get_settings_setup_link() {
    return Admin::get_settings_setup_link();
  }

  public function print_nepirity_summary() {
    echo '<h3 class="title">'.np_translate("Nepirity Settings").'</h3>';
    echo '<p>';
    echo np_translate("License key to manage plug-in information.");
    echo '<br />';
    echo np_translate("Please click the update button below when the license information is modified.");
    echo '</p>';

    echo '<form  method="post" enctype="multipart/form-data">';
    echo '<table class="form-table">';
    $options = $this->get_nepirity_option_fields();
    foreach ($options as $option) {
      if ($option['name'] == self::FIELD_NEPIRITY_KEY) {
        $option['memo'] = $this->get_nepirity_key_memo();
      }
      $this->print_setting_field($option);
    }
    echo '</table>';

    $tracking_name = self::FIELD_GOOGLE_TRACKING_ENABLED;
    $tracking_value= $this->get_option_value(self::FIELD_GOOGLE_TRACKING_ENABLED);
?>
    <div align='center'>
      <input type="hidden" name="update_setting_values" value="1" />
      <input type="hidden" name="<?= $tracking_name ?>" value="<?= $tracking_value ?>" />
      <input type="submit" class="button button-primary" value="<?= np_translate("Update") ?>" />
    </div>
<?php
    echo '</form>';
    echo '<hr width="80%" />';
  }

  public function print_google_summary($profiles='') {
    echo '<form  method="post" enctype="multipart/form-data">';
    echo '<h3 class="title">'.np_translate("Google Account Setting").'</h3>';
    $setup_link = $this->get_settings_setup_link();

    echo '<p>';

    $msg = np_translate("Click [Here], If you want to bring up the Nepirity Setup Wizard.");
    $link = " <a href='".$setup_link."'>".np_translate("[Here]")."</a>";

    $msg = str_replace("[Here]", $link, $msg);
    echo $msg;

    echo '</p>';
    echo '<hr width="80%" />';

    if ($profiles) {
      echo '<h3 class="title">'.np_translate("Goolge View Setting").'</h3>';
      echo '<p>'.np_translate("In order to use Nepirity Analysis Service, please select your Goolge view setting.").'</p>';

      $account_value = $this->get_option_value(self::FIELD_GOOGLE_PROFILE_ACCOUNT_ID);
      $property_value = $this->get_option_value(self::FIELD_GOOGLE_PROFILE_PROPERTY_ID);
      $view_value = $this->get_google_view_id();

      $this->print_google_view_selector($profiles, $account_value, $property_value, $view_value);

      echo '<hr width="80%" />';
    }

    echo '<h3 class="title">'.np_translate("Goolge Tracking Options").'</h3>';

    echo '<table class="form-table">';
    $options = $this->get_google_tracking_option_fields();
    foreach ($options as $option) {
      $this->print_setting_field($option);
    }
    echo '</table>';
    echo '<hr width="80%" />';

?>
    <div align='center'>
      <input type="hidden" name="update_setting_values" value="1" />
      <input type="submit" class="button button-primary" value="<?= np_translate("Update") ?>" />
    </div>
<?php
    echo '</form>';

    $this->print_google_tracking_option_script();
  }

  public function setup_wizard($step=0) {
    Setup::run($step);
  }

  protected function print_google_tracking_option_script() {
    echo '<script src="'. NP()->get_plugin_url() .'templates/js/google-tracking-option.js"></script>';
  }

  public function get_option_label_tag($option) {
    $id = '';

    if (isset($option['name'])) $id = $option['name'];
    if (isset($option['id'])) $id = $option['id'];

    return "<label for='".$id."'>".$option['desc']."</label>";
  }

  public function is_included_selctor_tag($option_array) {
    foreach ($option_array as $option) {
      if ($option['type'] == 'selector') return true;
    }

    return false;
  }

  protected function get_option_form_checkbox($option, $value) {
    $html = "";
    $checked = ($value)?"checked":"";
    $html .= "<input type='checkbox' name='".$option['name']."' id='".$option['name']."' value='1' class='regular-text code' $checked/>";
    $html .= " ";
    $html .= $option['title'];
    $html .= '<p class="description">'. $option['memo'] . '</p>';

    return $html;
  }

  protected function get_option_form_radio($option, $value) {
    // 기본값에서, 저장된 값으로 수정
    if ($value != "") {
      for ($i=0; $i<count($option['options']); $i++) {
        if ($option['options'][$i]['value'] == $value) {
          $option['options'][$i]['checked'] = true;
        } else {
          $option['options'][$i]['checked'] = false;
        }
      }
    }

    return NP()->views()->get_html_from_option($option);
  }

  public function get_option_form_tag($option) {
    $type = $option['type'];
    $value = "";

    if ($option['name'] != self::FIELD_GOOGLE_ACCOUNT_AUTH_CONFIG) {
      $value = $this->get_option_value($option['name']);
    }

    if (isset($option['value'])) {
      $value = $option['value'];
    }

    if ($type == 'textarea') {
      return '<textarea class="large-text code" rows="6" name="'.$option['name'].'" id="'.$option['name'].'">'.$value.'</textarea>';
    }

    if ($type == 'checkbox') {
      return $this->get_option_form_checkbox($option, $value);
    }

    if ($type == "radio") {
      return $this->get_option_form_radio($option, $value);
    }

    $html = "";
    $html .= "<input type='".$type."' name='".$option['name']."' id='".$option['name']."' value='".$value."' class='regular-text code' />";
    if (isset($option['memo']) && strlen($option['memo'])) {
      $html .= '<p class="description">'. $option['memo'] . '</p>';
    }

    return $html;
  }

  public function get_nepirity_option_fields() {
    return array(
      array(
        "name"=>self::FIELD_NEPIRITY_KEY,
        "type"=>"text", "desc" => np_translate("Nepirity Service Key")
      )
    );
  }

  public function get_google_authtype() {
    return $this->get_option_value(self::FIELD_GOOGLE_AUTHTYPE);
  }

  public function is_google_oauth_type() {
    if (intval($this->get_option_value(self::FIELD_GOOGLE_AUTHTYPE))
        == self::GOOGLE_AUTHTYPE_OAUTH) {
      return true;
    }

    return false;
  }

  public function get_google_authtype_option_fields() {
    $option = array(
      array(
        "name"=>self::FIELD_GOOGLE_AUTHTYPE,
        "type"=>"radio", "desc" => np_translate("Google Authentication Type"),
        "options" => array(
          array("desc"=>"OAuth Authentication (Recommeded)", "value"=>self::GOOGLE_AUTHTYPE_OAUTH, "checked"=>true),
          array("desc"=>"Server to Server Authentication", "value"=>self::GOOGLE_AUTHTYPE_SERVER, "checked"=>false)
        )
      )
    );

    $checked_value = intval($this->get_google_authtype());

    if ($checked_value > 0 && count($option[0]['options']) <= $checked_value) {
      for ($i=0; $i<count($option[0]['options']); $i++) {
        $option[0]['options'][$i]['checked'] = false;
      }

      $option[0]['options'][$checked_value -1]['checked'] = true;
    }

    return $option;
  }

  public function get_google_account_option_fields() {
    return array(
      array(
        "name"=>self::FIELD_GOOGLE_ACCOUNT_AUTH_CONFIG,
        "type"=>"textarea", "desc" => np_translate("Google Account Service Key")
      )
    );
  }

  public function get_google_oauth_option_fields() {
    $link = NP()->analytics()->get_google_auth_link();
    $memo = np_translate("You can check Google OAuth code");
    $memo .= " [<a href='".$link."' target = 'popup' onclick = window.open(this.href,'popup','width=500,height=600')>here</a>]";

    return array(
      array(
        "name"=>self::FIELD_GOOGLE_ACCOUNT_AUTH_CONFIG, /* The same with google account*/
        "type"=>"text", "desc" => np_translate("Google OAuth Code"),
        "memo"=>$memo
      )
    );
  }

  public function get_google_profile_option_fields() {
    return array(
      array(
        "name"=>self::FIELD_GOOGLE_PROFILE_ACCOUNT_ID,
        "id"=>"account",
        "type"=>"selector", "desc" => np_translate("account")
      ),
      array(
        "name"=>self::FIELD_GOOGLE_PROFILE_PROPERTY_ID,
        "id"=>"property",
        "type"=>"selector", "desc" => np_translate("property")
      ),
      array(
        "name"=>self::FIELD_GOOGLE_PROFILE_VIEW_ID,
        "id"=>"view",
        "type"=>"selector", "desc" => np_translate("view")
      )
    );
  }

  public function get_google_tracking_option_fields() {
    return array(
      array(
        "name"=>self::FIELD_GOOGLE_TRACKING_ENABLED,
        "id"=>"tracking_enabled",
        "type"=>"checkbox", "desc" => np_translate("Tracking Option"),
        "title" => np_translate("Enable Standard Tracking"),
        "memo" => np_translate("You don't need to enable this if already inserted Google tracking code."),
        "default" => 1
      ),
      array(
        "name"=>self::FIELD_GOOGLE_TRACKING_SCRIPT,
        "id"=>"tracking_script",
        "type"=>"radio", "desc" => np_translate("Tracking Script"),
        "options" => array(
          array("desc"=>"analytics", "value"=>"analytics", "checked"=>true),
          array("desc"=>"gtag", "value"=>"gtag", "checked"=>false)
        )
      )
    );
  }

  public function get_nepirity_server_option_name() {
    return self::FIELD_NEPIRITY_SERVER;
  }

  public function get_nepirity_module_option_name() {
    return self::FIELD_NEPIRITY_MODULES;
  }

  public function get_nepirity_version_option_name() {
    return self::FIELD_NEPIRITY_VERSION;
  }

  public function get_all_option_fields() {
    $options = array();

    $options = array_merge($options, $this->get_nepirity_option_fields());
    $options = array_merge($options, $this->get_google_authtype_option_fields());
    $options = array_merge($options, $this->get_google_account_option_fields());
    $options = array_merge($options, $this->get_google_profile_option_fields());
    $options = array_merge($options, $this->get_google_tracking_option_fields());

    return $options;
  }

  public function get_option_value($name) {
    return stripslashes(get_option($name));
  }

  public function update_option_value($name, $value) {
    if ($name == self::FIELD_GOOGLE_ACCOUNT_AUTH_CONFIG) {
      $account = NP()->jsonrpc()->account();
      $npkey = NP()->settings()->get_nepirity_key();
      $pubkey = $account->get_public_key($npkey);

      $encrypted_data = \encrypt_data($pubkey, $ekey, $value);
      $account->update_data($npkey,
        array("env_key"=>base64_encode($ekey), "locale"=>get_locale()));

      update_option($name, base64_encode($encrypted_data));
    } else {
      update_option($name, $value);
    }
  }

  public function get_google_account_auth_config() {
    return $this->get_option_value(self::FIELD_GOOGLE_ACCOUNT_AUTH_CONFIG);
  }

  public function get_nepirity_server() {
    return $this->get_option_value(self::FIELD_NEPIRITY_SERVER);
  }

  public function print_google_view_selector_head_script() {
    echo '<script src="'. NP()->get_plugin_url() .'templates/js/setup.js"></script>';
  }

  public function print_google_view_selector_foot_script($profiles, $account_value='', $property_value='', $view_value='') {
?>
      <script>
        var profiles = <?= json_encode($profiles) ?>;

        var account_value = '<?= $account_value ?>';
        var property_value = '<?= $property_value ?>';
        var view_value = '<?= $view_value ?>';

        var account_element = document.getElementById('account');
        var property_element = document.getElementById('property');
        var view_element = document.getElementById('view');

        np_update_select_data('account', profiles);
        np_select_init(account_element);

        if (account_value.length) {
          np_set_selected_option(account_element, account_value);
          np_select_init(account_element);
        }

        if (property_value.length) {
          np_set_selected_option(property_element, property_value);
          np_select_init(property_element);
        }

        if (view_value.length) {
          np_set_selected_option(view_element, view_value);
        }

      </script>
<?php
  }

  public function get_google_view_selector_tag($option=null) {
    $html = '';
    if ($option) {
      $html .= '<select name="'.$option['name'].'" id="'.$option['id'].'" onchange="np_select_init(this)"></select>';
    } else {
      $html .= '<select name="'.self::FIELD_GOOGLE_PROFILE_ACCOUNT_ID.'" id="account" onchange="np_select_init(this)"></select>';
      $html .= '<select name="'.self::FIELD_GOOGLE_PROFILE_PROPERTY_ID.'" id="property" onchange="np_select_init(this)"></select>';
      $html .= '<select name="'.self::FIELD_GOOGLE_PROFILE_VIEW_ID .'" id="view" onchange="np_select_init(this)"></select>';
    }

    return $html;
  }

  public function print_google_view_selector($profiles, $account_value='', $property_value='', $view_value='') {
    if (sizeof($profiles)) {
      $this->print_google_view_selector_head_script();
      echo $this->get_google_view_selector_tag();
      $this->print_google_view_selector_foot_script($profiles, $account_value, $property_value, $view_value);
    } else {
      echo np_translate("Google Profile Error!");
    }
  }

  public function get_google_view_id() {
    return $this->get_option_value(self::FIELD_GOOGLE_PROFILE_VIEW_ID);
  }

  public function is_enabled_google_tracking() {
    if ($this->get_option_value(self::FIELD_GOOGLE_TRACKING_ENABLED))
      return true;
    return false;
  }

  public function gtag_script() {
    $script = $this->get_option_value(self::FIELD_GOOGLE_TRACKING_SCRIPT);

    if ($script == "gtag") {
      return true;
    }

    return false;
  }

  public function get_google_property_id() {
    return $this->get_option_value(self::FIELD_GOOGLE_PROFILE_PROPERTY_ID);
  }

  protected function get_nepirity_service_info($key) {
    if (($jsonrpc = NP()->jsonrpc())) {
      return $jsonrpc->account()->get_service_info($key);
    }

    return array();
  }

  public function is_validated_nepirity_key($key, $service=array()) {
    if (!count($service)) {
      $service = $this->get_nepirity_service_info($key);
    }

    if (!isset($service["service_url"])) {
      return false;
    }

    if ($service["service_url"] == get_option("siteurl")) {
      return true;
    }

    return false;
  }

  public function get_nepirity_key() {
    return $this->get_option_value(self::FIELD_NEPIRITY_KEY);
  }

  public function get_nepirity_modules() {
    $modules = $this->get_option_value(self::FIELD_NEPIRITY_MODULES);

    if ($modules != "") {
      return unserialize($modules);
    }

    return array();
  }

  public function remove_cache_options() {
    $options = array(self::FIELD_GOOGLE_ACCESS_TOKEN);

    for ($i=0; $i<sizeof($options); $i++) {
      delete_option($options[$i]);
    }
  }

  public function delete_all_options() {
    $options = $this->get_all_option_fields();

    for ($i=0; $i<sizeof($options); $i++) {
      delete_option($options[$i]['name']);
    }

    $options = array(
      self::FIELD_GOOGLE_ACCESS_TOKEN,
      self::FIELD_NEPIRITY_SERVER,
      self::FIELD_NEPIRITY_VERSION,
      self::FIELD_NEPIRITY_MODULES
    );
    for ($i=0; $i<sizeof($options); $i++) {
      delete_option($options[$i]);
    }
  }

  public function get_saved_google_token() {
    $token = json_decode($this->get_option_value(self::FIELD_GOOGLE_ACCESS_TOKEN), true);

    if (sizeof($token) && isset($token['created']) && isset($token['expires_in'])) {
      if ($diff = time() + 30 < $token['created'] + $token['expires_in']) {
        return $token;
      }
    }

    return array();
  }

  public function save_google_token($token) {
    $this->update_option_value(self::FIELD_GOOGLE_ACCESS_TOKEN, json_encode($token));
  }

  public function get_saved_refresh_token() {
    $token = json_decode($this->get_option_value(self::FIELD_GOOGLE_ACCESS_TOKEN), true);

    if (isset($token['refresh_token'])) return $token['refresh_token'];

    return "";
  }

  public function update_modules() {
    $npkey = $this->get_nepirity_key();
    $modules = NP()->jsonrpc()->account()->get_available_module_menus($npkey, get_locale());

    $this->update_option_value(self::FIELD_NEPIRITY_MODULES, base64_decode($modules));
  }

  public function update_version() {
    $this->update_option_value(self::FIELD_NEPIRITY_VERSION, NP()->get_plugin_version());
  }

  public function get_plugin_version() {
    return $this->get_option_value(self::FIELD_NEPIRITY_VERSION);
  }

  public function is_profiles($profiles) {
    if ($profiles && is_array($profiles) && count($profiles)) {
      return true;
    }

    return false;
  }
}

}

?>
