<?php

namespace jsonrpc {

class Account extends JsonRpcBase {
  function __construct($server) {
    parent::__construct($server, "account.php");
  }
}

}

?>
