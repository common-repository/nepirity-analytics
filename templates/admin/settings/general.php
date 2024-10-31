<div class = "panel panel-default">
  <div class= "panel-body">
<?php

$settings = NP()->settings();

if ($settings->check_update_values()) {
  $settings->print_updated_message();
}

if (isset($_GET['action']) && $_GET['action'] == "setup") {
  NP()->settings()->setup_wizard();
} else {
  $profiles = null;

  try {
    $profiles = NP()->analytics()->get_google_profiles();
  }catch (Exception $e) {
    $profiles = $e->getMessage();
  }

  if (NP()->settings()->is_profiles($profiles)) {
    $site_url = get_option("siteurl");
    $property_url = "";
    $view_id = NP()->settings()->get_google_view_id();

    for ($i=0; $i<count($profiles); $i++) {
      $properties = $profiles[$i]['properties'];

      for ($j=0; $j<count($properties); $j++) {
        $views = $properties[$j]['views'];

        for ($k=0; $k<count($views); $k++) {
          if ($views[$k]['id'] == $view_id) {
            $property_url = $properties[$j]['websiteUrl'];
          }
        }
      }
    }

    if ($property_url != $site_url && NP()->settings()->is_enabled_google_tracking()) {
      $msg = "";
      $msg .= np_translate("Domain information is incorrect.");
      $msg .= " ";
      $msg .= np_translate("Data may not be collected correctly.");
      $msg .= " ";
      $msg .= np_translate("Your wordpress domain is <b>{siteurl}</b>, but the currently configured Google analytics domain is <b>{propertyurl}</b>.");
      $msg .= " ";
      $msg .= np_translate("The two domains must match.");

      $msg = str_replace("{siteurl}", $site_url, $msg);
      $msg = str_replace("{propertyurl}", $property_url, $msg);

      NP()->views()->print_notice_error($msg);
    }
  }

?>
<div class="container-fluid">
  <div class="row">
  <div class="col-sm-8">
    <?php
      if (NP()->settings()->is_profiles($profiles)) {
        NP()->settings()->print_google_summary($profiles);
      } else {
        $html  = "<div align='center'>";
        $html .= np_translate("Can't load google profile information. Please setup plugin again.");
        $html .= " [<a href='". NP()->settings()->get_settings_setup_link() . "'>Here</a>]";
        $html .= "</div>";

        echo $html;
      }
    ?>
  </div>
  <div class="col-sm-4">
    <?php
      NP()->views()->print_board_data();

      $message = np_translate("Please feel free to contact us if there are any errors in the plugin or if you have any comments.");
      $message .= " ";
      $message .= np_translate("You can send email us at contact@nepirity.com or fill out the form {here}.");

      $message = str_replace("{here}", "<a href='https://www.nepirity.com/contact' target='_blank'>".np_translate('[Here]')."</a>", $message);

      $data= array(
        "subject"=> np_translate("Please give us your feedback."),
        "message"=> $message,
        "reg_date"=> time());
      NP()->views()->print_card_box($data);
    ?>
  </div>
  </div>
</div>
<?php

}

?>
  </div>
</div>
