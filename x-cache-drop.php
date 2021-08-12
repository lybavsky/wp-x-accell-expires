<?php

include "consts.php";

define('WP_USE_THEMES', false);
global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
require($_SERVER['DOCUMENT_ROOT'] . "/wp-load.php");

$is_can = current_user_can(CAP);

if (!isset($_POST["url"])) {
    echo "url param is empty";
}

$drop_url = $_POST["url"];

$url = get_option(OPTION_DROP_URL_NAME);
$token = get_option(OPTION_DROP_TOKEN_NAME);

$data = "URL=" . $drop_url;

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_URL, $url);

curl_setopt($ch, CURLOPT_USERPWD, $token);

curl_setopt($ch, CURLOPT_POST, 1);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$return = curl_exec($ch);

if (!$return) {
    echo curl_error($ch);
    echo "Maybe error";
} else {
    echo "request was send";

}

curl_close($ch);


?>