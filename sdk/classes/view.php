<?php

namespace jsonrpc {

class View extends JsonRpcBase {
  function __construct($server) {
    parent::__construct($server, "view.php");
  }
}

}

?>
