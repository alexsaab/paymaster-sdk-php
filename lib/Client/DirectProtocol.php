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
    protected $redirect_uri;

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

    // Авторизация
    protected $urlGetAuth = 'https://paymaster.ru/direct/security/auth';

    // Получение token
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
        $body = 'response_type=' . $this->response_type . '&' . 'client_id=' . $this->client_id . '&' . 'redirect_uri=' . urlencode($this->redirect_uri) . '&' . 'scope=' . $this->scope . '&' . 'type=' . $this->type;
        // строка подписи
        $clear_sign = $body . ';' . $this->iat . ';' . $this->secret;
        // вычисление подписи
        $this->sign = base64_encode(hash('sha256', $clear_sign, true));
        // Возвращаем подпись
        return $this->sign;
    }


    /**
     * Авторизация
     * @return mixed|\PaymasterSdkPHP\Common\ResponseObject
     */
    public function auth() {
        $fields = array(
            'response_type' => $this->response_type,
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'type' => $this->type,
            'scope' => $this->scope,
            'iat' => $this->iat,
            'sign' => $this->getSign()
        );

        $headers = array(
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'accept-encoding: gzip, deflate, br',
            'accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7,de;q=0.6',
            'cache-control: max-age=0',
            'upgrade-insecure-requests: 1',
            'user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36',
            'x-client-data: CJS2yQEIo7bJAQipncoBCKijygE=',
            'x-compress: null',
        );

        $respond = $this->request->call($this->urlGetAuth, 'POST', $fields, $headers);
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
     * Setter client_id
     * @param $client_id
     */
    public function setClientId($client_id) {
        $this->client_id = $client_id;
    }

    /**
     * Setter client_id
     * @param $client_id
     */
    public function getClientId() {
        return $this->client_id;
    }

    /**
     * Setter scope
     * @param $scope
     */
    public function setScope($scope) {
        $this->scope = $scope;
    }

    /**
     * Setter scope
     * @param $scope
     */
    public function getScope() {
        return $this->scope;
    }

    /**
     * Setter redirect
     * @param $redirect
     */
    public function setRedirectUri($redirect_uri) {
        $this->redirect_uri = $redirect_uri;
    }

    /**
     * Setter redirect
     * @param $redirect
     */
    public function getRedirectUri() {
        return $this->redirect_uri;
    }

    /**
     * Setter secret
     * @param $secret
     */
    public function setSecret($secret) {
        $this->secret = $secret;
    }

    /**
     * Setter secret
     * @param $secret
     */
    public function getSecret() {
        return $this->secret;
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