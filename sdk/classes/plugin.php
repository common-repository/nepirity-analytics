<?php
namespace jsonrpc {

class Plugin extends JsonRpcBase {
  function __construct($server) {
    parent::__construct($server, "plugin.php");
  }

  protected function download_url($url) {
    if (function_exists('wp_remote_request') && function_exists('wp_remote_retrieve_body')) {
      $response = wp_remote_request($url, array('method' => 'GET'));
      return wp_remote_retrieve_body($response);
    }

    return file_get_contents($url);
  }

  public function get_layout($group, $module, $key='') {
    $url = $this->get_baseurl();
    $url .= "templates/layouts.php?group=".$group."&module=".$module;

    $url .= "&locale=". get_locale();
    $url .= "&version=". NP()->get_plugin_version();
    if ($key != '') $url .= "&key=".$key;

    return $this->download_url($url);
  }

  public function get_script_link($group, $module, $key, $ids, $auth) {
    $url = $this->get_baseurl();
    $url .= "templates/scripts.php?group=".$group."&module=".$module;

    $url .= "&locale=". get_locale();
    if ($key != '') $url .=  "&key=".$key;
    if ($ids != '') $url .=  "&ids=".$ids;
    if ($auth != '') $url .= "&auth=".urlencode(base64_encode($auth));

    return $url;
  }

  public function get_script($group, $module, $key='', $ids='', $auth='') {
    return $this->download_url($this->get_script_link($group, $module, $key, $ids, $auth));
  }
}

}

?>
