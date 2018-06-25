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

    // Тип токена
    protected $token_type;

    // Вермя действия (истечения)
    protected $expires_in;

    // Идентификатор учетной записи
    protected $account_identifier;

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

    // Базовый URL
    protected $urlBase = 'https://paymaster.ru/';

    // URL для формы авторизации (первый шаг)
    protected $urlGetAuthActionForm1;

    // URL для формы авторизации (второй шаг)
    protected $urlGetAuthActionForm2;

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
        $this->iat = strval(time());
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
     * Получение подписи
     */
    public function getSignToken()
    {
        // тело подписи
        $body = 'client_id=' . $this->client_id . '&' . 'code=' . $this->code . '&' . 'grant_type=' . $this->grant_type . '&' . 'redirect_uri=' . urlencode($this->redirect_uri) . '&' . 'type=' . $this->type;
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
            'client_id'     => $this->client_id,
            'redirect_uri'  => $this->redirect_uri,
            'scope'         => $this->scope,
            'type'          => $this->type,
            'sign'          => $this->getSign(),
            'iat'           => $this->iat
        );

        try {
            $respond = $this->request->call($this->urlGetAuth, 'POST', $fields);
        } catch (\Exception $exception) {
            echo "Error: " . $exception->getMessage();
            return;
        }

        // Получаем URL формы активации 1
        $this->urlGetAuthActionForm1 = $this->urlBase . $this->_getFormAction($respond->getBody());
        $respond = $this->request->call($this->urlGetAuthActionForm1, 'POST', array());

        // Получаем URL формы активации 2
        $this->urlGetAuthActionForm2 = $this->urlBase . $this->_getFormAction($respond->getBody());
        // Массив для передачи в форму
        $form2SubmitArray = array(
            'values[card_pan]'   => "4100000000000010", // Номер карты (фейковый)
            'values[card_month]' => "6", // месяц карты
            'values[card_year]'  => (date("Y") + 5), // год карты
            'values[card_cvv]'   => "111", // CVV
        );

        $respond = $this->request->call($this->urlGetAuthActionForm2, 'POST', $form2SubmitArray);

        $this->code = $this->_getToken($respond->getBody());

        // Получаем
        return $this->code;
    }

    /**
     * Получение токена
     * @return mixed|\PaymasterSdkPHP\Common\ResponseObject
     */
    public function token()
    {
        $fields = array(
            'client_id'    => $this->client_id,
            'code'         => $this->code,
            'grant_type'   => $this->grant_type,
            'redirect_uri' => $this->redirect_uri,
            'sign'         => $this->getSignToken(),
            'type'         => $this->type,
            'iat'          => $this->iat
        );

        try {
            $respond = $this->request->call($this->urlGetToken, 'POST', $fields);
            $respondObject = json_decode($respond->getBody());

            if ($respondObject->status != "failure") {
                $this->access_token = $respondObject->access_token;
                $this->token_type = $respondObject->token_type;
                $this->expires_in = $respondObject->expires_in;
                $this->account_identifier = $respondObject->account_identifier;
            } else {
                throw new \Exception("I can't get token. Error is happen.");
            }
        } catch (\Exception $exception) {
            echo 'Error: ' . $exception->getMessage();
        }

        return $respond->getBody();
    }


    /**
     * Отзыв токена
     * @return mixed|\PaymasterSdkPHP\Common\ResponseObject
     */
    public function revoke()
    {
        $fields = array(
            'client_id'    => $this->client_id,
            'access_token' => $this->access_token,
            'type'         => $this->type,
            'iat'          => $this->iat,
            'sign'         => $this->getSign()
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
        $this->merchant_id = $client_id;
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
        $this->merchant_id = $merchant_id;
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
     * Получение текущего времени в формате unix
     * @return int|string
     */
    public function getIat() {
        $this->iat = time();
        return $this->iat;
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

    /**
     * Получаем данные формы
     * Версия на регулярных выражениях
     * @param $html
     * @return mixed|void
     * @throws \Exception
     */
    private function _getFormAction($html)
    {
        // TODO
        // Сообщить в Paymaster что у них не закрыт тег <p>
        $matches = array();
        preg_match('|form action="([^"]*?)"|i', $html, $matches);

        if (!isset($matches[1])) {
            throw new \Exception("Form action not found");
            return;
        }

        return $matches[1];
    }

    /**
     * Возвращаем временный токен
     * @param $html
     * @return mixed|void
     * @throws \Exception
     */
    private function _getToken($html)
    {
        $matches = $urlVars = array();
        preg_match('|a href="([^"]*?)" class="pp-button-ok pp-rounded-5px|i', $html, $matches);
        if (!isset($matches[1])) {
            throw new \Exception("URL button not found");
            return;
        }
        parse_str(parse_url($matches[1], PHP_URL_QUERY), $urlVars);
        return $urlVars['code'];
    }

}