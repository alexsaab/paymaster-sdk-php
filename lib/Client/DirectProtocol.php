<?php
/**
 * Created by PhpStorm.
 * User: alexey
 * Date: 19.06.18
 * Time: 7:23
 */

namespace PaymasterSdkPHP\Client;


class DirectProtocol
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


    public function auth() {

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

}