<?php
/**
 * Created by PhpStorm.
 * User: alexey
 * Date: 19.06.18
 * Time: 7:23
 */

namespace PaymasterSdkPHP\Client;


use PaymasterSdkPHP\Client\CurlClient;


class DirectProtocol
{

    /*
     * Объект для запросов
     */
    protected $request;

    // Константа. Параметр всегда должен иметь значение "code"
    protected $response_type;

    // Микровремя  iat
    protected $iat;

    // Идентификатор Продавца в системе PayMaster
    protected $client_id;

    // URL для перенаправления клиента после успешной авторизации.  НЕ кодированная.
    protected $redirect;

    // Идентификатор платежной системы
    protected $scope; // 503 тест, рабочие режимы bankcard webmoney

    // Секретный ключ DIRECT от сайта
    protected $secret;

    // тип запроса
    protected $type;

    //  подпись
    protected $sign;

    /**
     * URLы список
     */
    //Получение token
    protected $urlGetToken = 'https://paymaster.ru/direct/security/token';

    // Отзыв токена
    protected $urlRevoke = 'https://paymaster.ru/direct/security/revoke';

    // Инициализация платежа
    protected $urlPaymentInit = 'https://paymaster.ru/direct/payment/init';

    // Проведение платежа
    protected $urlPaymentComplete = 'https://paymaster.ru/direct/payment/complete';

    /**
     * Инлайн токенизация карт
     */
    // Запрос авторизации
    protected $urlAuthorizeCard = 'https://paymaster.ru/direct/authorize/card';

    // Подтверждение суммы списания
    protected $urlAuthorizeConfirm = 'https://paymaster.ru/direct/authorize/confirm';

    /**
     * Проведение 3DSecure авторизации
     */
    // Завершение 3DSecure авторизации
    protected $urlAuthorizeComplete3ds = 'https://paymaster.ru/direct/authorize/complete3ds';


    /**
     * DirectPaymasterProtocol constructor.
     */
    public function __construct()
    {
        $this->request = new CurlClient();
        $this->response_type = 'code';
        $this->iat = time();
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


    public function auth() {
        $requestArray = array(
            'response_type' => $this->response_type, // Есть
            'client_id' => $this->client_id, // нет
            'redirect_uri' => $this->redirect, // нет
            'scope' => $this->scope, // нет
            'sign' => $this->getSign(), // есть
            'iat' => $this->iat, // есть
        );

        $respond = $this->request->call($this->urlAuthorizeConfirm, 'POST', $requestArray);

        return $respond;

    }

    public function revoke() {

    }

    public function init(){

    }

    public function complete() {

    }

    public function card() {

    }

    public function confirm() {

    }

    public function complete3ds() {

    }

    /**
     * Setter
     * @param $variable
     * @param $value
     */
    public function set($variable, $value) {
        $this->$variable = $value;
    }

    /**
     * Getter
     * @param $variable
     * @param $value
     */
    public function get($variable, $value) {
        $this->$variable = $value;
    }



}