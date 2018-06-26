<?php
/**
 * Created by PhpStorm.
 * User: alexey
 * Date: 20.06.18
 * Time: 7:01
 */


include_once ('../vendor/autoload.php');

use PaymasterSdkPHP\Client;



$protocol = new Client('common');

// Какие параметры обязательные
// protected $required = array('LMI_MERCHANT_ID', 'LMI_PAYMENT_AMOUNT', 'LMI_CURRENCY', 'LMI_PAYMENT_DESC', 'KEYPASS');

$protocol->client->set('LMI_MERCHANT_ID','e430408c-3213-4580-9c25-946677a01ea8');
$protocol->client->set('LMI_PAYMENT_AMOUNT', 500);
$protocol->client->set('LMI_CURRENCY', 'RUB');
$protocol->client->set('LMI_PAYMENT_DESC', 'Тестовая транзакция');
$protocol->client->set('KEYPASS', '12345');

echo $protocol->client->getForm();

