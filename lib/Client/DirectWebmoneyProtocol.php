<?php
/**
 * Created by PhpStorm.
 * User: alexey
 * Date: 19.06.18
 * Time: 7:23
 */

namespace PaymasterSdkPHP\Client;


class DirectWebmoneyProtocol
{
    // Константа. Параметр всегда должен иметь значение "code"
    protected $response_type;

    // Микровремя  iat
    protected $iat;

    // Идентификатор Продавца в системе PayMaster
    protected $client_id;

    // URL для перенаправления клиента после успешной авторизации.  НЕ кодированная.
    protected $redirect;

    // Идентификатор платежной системы
    protected $scope;

    // Секретный ключ  DIRECT от сайта
    protected $secret;

    // Файл cookies
    public $cookie_file;

    // тип запроса
    protected $type;

    //  подпись
    protected $sign;

    /**
     * DirectWebmoneyProtocol constructor.
     */
    public function __construct()
    {
        $this->response_type = 'code';
        $this->iat = time();
        $this->scope = '503';
        $this->cookie_file = __DIR__.'/../../cookies.txt';
        $this->type = 'rest';
    }


    /**
     * Получение подписи
     */
    public function getSign() {
        // тело подписи
        $body = 'response_type=' . $this->response_type . '&' . 'client_id=' . $this->client_id . '&' . 'redirect_uri=' . $this->redirect_uri . '&' . 'scope=' . $this->scope . '&' . 'type=' . $this->type;
        // строка подписи
        $clear_sign = $body . ';' . $this->iat . ';' . $this->secret;
        // вычисление подписи
        $this->sign = base64_encode(hash('sha256', $clear_sign, true));

        // Возвращаем подпись
        return $this->sign;
    }

}