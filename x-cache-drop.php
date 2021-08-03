<?php

include "consts.php";

require_once('../../../wp-load.php');

if (!is_admin()) {
    return;
}

$url = get_option(OPTION_DROP_URL_NAME);
$token = get_option(OPTION_DROP_TOKEN_NAME);


$ch = curl_init($url);

?>