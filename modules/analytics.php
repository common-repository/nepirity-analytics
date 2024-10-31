<?php

namespace nepirity;

class Analytics {
  function __construct() {
  }

  public function get_google_auth_link() {
    return NP()->jsonrpc()->google()->get_auth_link();
  }

  protected function get_googleauth_server_access_token() {
    $google = NP()->jsonrpc()->google();
    $token = NP()->settings()->get_saved_google_token();
    $npkey = NP()->settings()->get_nepirity_key();

    if (!count($token)) {
      $auth = NP()->settings()->get_google_account_auth_config();
      $token = $google->get_access_token($npkey, $auth);
      NP()->settings()->save_google_token($token);
    }

    return $token;
  }

  protected function get_googleauth_oauth_access_token($key) {
    $google = NP()->jsonrpc()->google();

    if ($key) {
      $npkey = NP()->settings()->get_nepirity_key();
      return $google->get_access_token_using_authkey($npkey, $key);
    }

    $token = NP()->settings()->get_saved_google_token();

    if (!count($token)) {
      $refresh = NP()->settings()->get_saved_refresh_token();
      $token = $google->get_refresh_token($refresh);
      NP()->settings()->save_google_token($token);
    }

    return $token;
  }

  public function get_google_access_token($key=null) {
    $auth_type = NP()->settings()->get_google_authtype();

    switch ($auth_type) {
      case Setting::GOOGLE_AUTHTYPE_OAUTH: {
        return $this->get_googleauth_oauth_access_token($key);
      }
      case Setting::GOOGLE_AUTHTYPE_SERVER: {
        return $this->get_googleauth_server_access_token();
      }
    }

    return "";
  }

  protected function get_googleauth_server_profiles() {
    $google = NP()->jsonrpc()->google();
    $encrypted_google_key = NP()->settings()->get_google_account_auth_config();
    $npkey = NP()->settings()->get_nepirity_key();

    return $google->get_profiles($npkey, $encrypted_google_key);
  }

  protected function get_googleauth_oauth_profiles() {
    $google = NP()->jsonrpc()->google();

    $encoded_token = base64_encode(json_encode($this->get_googleauth_oauth_access_token(null)));
    return $google->get_profiles_using_token($encoded_token);
  }

  public function get_google_profiles() {
    $auth_type = NP()->settings()->get_google_authtype();

    switch ($auth_type) {
      case Setting::GOOGLE_AUTHTYPE_OAUTH: {
        return $this->get_googleauth_oauth_profiles();
      }
      case Setting::GOOGLE_AUTHTYPE_SERVER: {
        return $this->get_googleauth_server_profiles();
      }
    }

    return array();
  }

  public function get_google_authorize_obj() {
    try {
      $token_data = $this->get_google_access_token();

      if (is_array($token_data)) {
        return json_encode(array('serverAuth' => array('access_token' => $token_data['access_token'])));
      }
      return $token_data;
    } catch (\Exception $e) {
      return $e->getMessage();
    }

    return '';
  }

  public function print_gtag_tracking_code($tracking_id) {
?>
<!-- BEGIN Nepirity Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= $tracking_id ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '<?= $tracking_id ?>');
</script>
<!-- END Nepirity Analytics -->
<?php
  }

  public function print_analytics_tracking_code($tracking_id) {
?>
<!-- BEGIN Nepirity Analytics -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', '<?= $tracking_id ?>', 'auto');
  ga('require', 'displayfeatures');
  ga('send', 'pageview');
</script>
<!-- END Nepirity Analytics -->
<?php
  }
}

?>
