<?php

function np_translate($string) {
  return __($string, 'nepirity-services');
}

function encrypt_data($public_key, &$ekey, $data) {
  $publicKey = openssl_get_publickey($public_key);

  // encrypt data using public key into $sealed
  $sealed = '';
  openssl_seal($data, $sealed, $ekeys, array($publicKey));
  openssl_free_key($publicKey);

  $ekey = $ekeys[0];

  return $sealed;
}

?>
