<?php

/**
 * nmc_social function.
 *
 * @access public
 * @param string $url - NMC Proxy URL required
 * @return json
 */
function nmc_social($url) {

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_SSL_VERIFYHOST => 2,
      CURLOPT_CAINFO => "/var/apps/caseytrees/current/certs/cacert.pem"
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    $data = json_decode($response);

    if ($err) {
      return "cURL Error #:" . $err;
    } else {
      return $data;
    }
}
