<?php

namespace jsonrpc {

include_once "libs/vendor/autoload.php";

use JsonRPC\Client;

define('NP_DEFAULT_SERVER', 'https://service.nepirity.com/');

abstract class JsonRpcBase {
  protected $jsonrpc_url;
  protected $base_url;
  protected $client;
  protected $module;

  public function __construct($server, $module_name) {
    $this->module = $module_name;
    $this->base_url = $server;
    $this->client = new Client($this->get_jsonrpc_url());
  }

  public function __call($name, $params) {
    $result = NULL;
    try {
      $result = $this->client->execute($name, $params);
    } catch (Exception $e) {
      throw $e;
    }
    return $result;
  }

  public function get_baseurl() {
    return $this->base_url;
  }

  public function get_jsonrpc_url($server='') {
    if ($server != '') {
      return $server . $this->module;
    }

    return $this->base_url . $this->module;
  }
}

}
