function update_google_tracking_option() {
  var track_opt = document.getElementById('np_google_tracking_enabled');
  var radios = document.getElementsByName('np_google_tracking_script');

  var radiodisable = true;

  if (track_opt.checked) {
    radiodisable = false;
  }

  for (var i=0, iLen=radios.length; i<iLen; i++) {
      radios[i].disabled = radiodisable;
  }
}

update_google_tracking_option();

var checkbox = document.getElementById('np_google_tracking_enabled')

checkbox.addEventListener('change', (event) => {
  update_google_tracking_option();
});
