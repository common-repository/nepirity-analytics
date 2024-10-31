<?php

namespace jsonrpc {

class Google extends JsonRpcBase {
  function __construct($server) {
    parent::__construct($server, "google.php");
  }
}

}

?>
