<?php

namespace jsonrpc {

class Support extends JsonRpcBase {
  function __construct($server) {
    parent::__construct($server, "support.php");
  }

  function get_fast_server() {
    $data = array();
    $echo_msg = "hello world";
    $servers = $this->get_service_servers();

    $mintime = 0;
    $fasturl = "";

    for ($i=0; $i<count($servers); $i++) {
      $recv_msg = '';
      $url = $this->get_jsonrpc_url($servers[$i]);
      $this->client = new \JsonRPC\Client($url);

      $diff_time = 0;
      $start_time = microtime(true);
      try {
        $recv_msg = $this->echo_test($echo_msg);
      }catch (\Exception $e) {
        echo $e->getMessage(). "::" . $servers[$i];
      }
      $end_time = microtime(true);
      $diff_time = $end_time - $start_time;

      if ($recv_msg == $echo_msg) {
        $data[] = array('url' => $servers[$i], 'time' => $diff_time);
      }

      if ($i == 0 || $diff_time < $mintime ) {
        $mintime = $diff_time;
        $fasturl = $servers[$i];
      }
    }

    return $fasturl;
  }
}

}

?>
