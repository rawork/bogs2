<?php

require_once "Config.php";
require_once "KaznacheyApi.php";

$api = new KaznacheyApi(KAZNACHEY_SECRET_KEY, KAZNACHEY_GUID);

try {
    $statusRequest = $api->GetStatusResponse();
    echo "ok";
} catch (Exception $ex) {
    print "Error!";
    print $ex->getMessage();
}

$fp = fopen('counter.txt', 'a');

fputs($fp, $statusRequest, strlen($statusRequest));
fclose($fp);