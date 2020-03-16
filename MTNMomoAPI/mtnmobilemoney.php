<?php
require_once dirname(__FILE__).'/MTNApiClass.php';

$momo = new MTNApiClass();
$uuid = $momo->createUUID();    //Create a xReference
$createUser = $momo->createAPIUSer($uuid);
$getUser = $momo->getCreatedUser($uuid);
$apiKeyGen = $momo->getApiKey($uuid);
$getAPIToken = $momo->generatingApiToken($uuid,$apiKeyGen);
$reqestToPay = $momo->requestToPay($getAPIToken,$uuid);
$getReqestToPay = $momo->getRequestToPay($getAPIToken,$uuid);
echo $getReqestToPay;


