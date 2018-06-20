<?php
/**
 * Created by PhpStorm.
 * User: alexey
 * Date: 20.06.18
 * Time: 7:01
 */


include_once ('vendor/autoload.php');

use PaymasterSdkPHP\Client\DirectProtocol;

$client = new DirectProtocol();

$client->set('client_id','e430408c-3213-4580-9c25-946677a01ea8');
$client->set('scope','503');
$client->set('redirect','http://test1.techpaymaster.ru');
$client->set('secret','12345');


var_dump($client->auth());
