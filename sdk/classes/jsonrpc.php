<?php

namespace jsonrpc {

include_once "abstract.jsonrpc.php";
include_once "account.php";
include_once "support.php";
include_once "google.php";
include_once "view.php";
include_once "plugin.php";

class JsonRpc {
  var $_account;
  var $_support;
  var $_google;
  var $_view;
  var $_plugin;

  public function __construct($server) {
    $this->_account = new Account($server);
    $this->_support = new Support($server);
    $this->_google = new Google($server);
    $this->_view = new View($server);
    $this->_plugin = new Plugin($server);
  }

  public function account() {
    return $this->_account;
  }

  public function support() {
    return $this->_support;
  }

  public function google() {
    return $this->_google;
  }

  public function view() {
    return $this->_view;
  }

  public function plugin() {
    return $this->_plugin;
  }
}

}
