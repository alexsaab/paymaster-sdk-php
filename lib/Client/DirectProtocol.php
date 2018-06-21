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

    // Идентификатор Проавца в системе PayMaster (тоже самое, что и client_id)
    protected $merchant_id;

    // Идентификатор платежа в системе обязательный параметр, номер транзакции
    protected $merchant_transaction_id;

    // URL для перенаправления клиента после успешной авторизации.  НЕ кодированная.
    protected $redirect_uri;

    // Идентификатор платежной системы
    protected $scope; // 503 тест, рабочие режимы bankcard webmoney

    // Временный токен, присвоенный при запросе на авторизацию
    protected $code;

    // Постоянный token доступа
    protected $access_token;

    // Константа. Всегда должен быть установлен на "authorization_code"
    protected $grant_type;

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
        $this->grant_type = "authorization_code";
    }


    /**
     * Получение подписи
     */
    public function getSign()
    {
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
    public function auth()
    {
        $fields = array(
            'response_type' => $this->response_type,
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'type' => $this->type,
            'scope' => $this->scope,
            'iat' => $this->iat,
            'sign' => $this->getSign()
        );

        var_dump($fields);


        $respond = $this->request->call($this->urlGetAuth, 'POST', $fields);
        return $respond;
    }

    /**
     * Получение токена
     * @return mixed|\PaymasterSdkPHP\Common\ResponseObject
     */
    public function token()
    {
        $fields = array(
            'client_id' => $this->client_id,
            'code' => $this->code,
            'grand_type' => $this->grant_type,
            'redirect_uri' => $this->redirect_uri,
            'type' => $this->type,
            'iat' => $this->iat,
            'sign' => $this->getSign()
        );

        $respond = $this->request->call($this->urlGetToken, 'POST', $fields);
        return $respond;
    }


    /**
     * Отзыв токена
     * @return mixed|\PaymasterSdkPHP\Common\ResponseObject
     */
    public function revoke()
    {
        $fields = array(
            'client_id' => $this->client_id,
            'access_token' => $this->access_token,
            'type' => $this->type,
            'iat' => $this->iat,
            'sign' => $this->getSign()
        );

        $respond = $this->request->call($this->urlRevoke, 'POST', $fields);
        return $respond;
    }


    public function init()
    {

    }

    public function complete()
    {

    }

    public function card()
    {

    }

    public function confirm()
    {

    }

    public function complete3ds()
    {

    }

    /**
     * Setter client_id
     * @param $client_id
     */
    public function setClientId($client_id)
    {
        $this->merchant_id_id = $client_id;
        $this->client_id = $client_id;

    }

    /**
     * Getter client_id
     * @param $client_id
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Setter merchant_id
     * @param $client_id
     */
    public function setMerchantId($merchant_id)
    {
        $this->merchant_id_id = $merchant_id;
        $this->client_id = $merchant_id;

    }

    /**
     * Getter merchant_id
     * @param $client_id
     */
    public function getMerchantId()
    {
        return $this->merchant_id;
    }

    /**
     * Setter scope
     * @param $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * Getter scope
     * @param $scope
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Setter redirect
     * @param $redirect
     */
    public function setRedirectUri($redirect_uri)
    {
        $this->redirect_uri = $redirect_uri;
    }

    /**
     * Getter redirect
     * @param $redirect
     */
    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    /**
     * Setter secret
     * @param $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * Gettersecret
     * @param $secret
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Setter code
     * @param $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Getter code
     * @param $code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Setter grand_type
     * @param $grand_type
     */
    public function setGrandType($grand_type)
    {
        $this->grand_type = $grand_type;
    }

    /**
     * Getter access_token
     * @param $access_token
     */
    public function getGrandType()
    {
        return $this->access_token;
    }

    /**
     * Setter access_token
     * @param $access_token
     */
    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    /**
     * Getter grand_type
     * @param $grand_type
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }


    /**
     * Setter
     * @param $variable
     * @param $value
     */
    public function set($variable, $value)
    {
        $this->$variable = $value;
    }

    /**
     * Getter
     * @param $variable
     * @param $value
     */
    public function get($variable, $value)
    {
        $this->$variable = $value;
    }

}