<?php
/**
 * Created by PhpStorm.
 * User: alexey
 * Date: 20.06.18
 * Time: 7:01
 */


include_once ('vendor/autoload.php');

use PaymasterSdkPHP\Client;

$protocol = new Client('direct');

$protocol->client->setClientId('e430408c-3213-4580-9c25-946677a01ea8');
$protocol->client->setScope('503');
$protocol->client->setRedirectUri('http://test1.techpaymaster.ru');
$protocol->client->setSecret('12345');


var_dump($protocol->client->auth()->body());

