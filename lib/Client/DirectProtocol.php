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

    // Сумма платежа
    protected $amount;

    // Валюта платежа
    protected $currency;

    // Описание платежа
    protected $description;

    // Номер транзации в системе Paymaster
    protected $processor_transaction_id;

    // Номер платежа в системе
    protected $payment_id;

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
        $this->getIat(); // установка текущего времени
        $this->type = 'rest';
        $this->grant_type = 'authorization_code';
        $this->currency = 'RUB';

    }


    /**
     * Получение подписи
     */
    public function getSign($type = null)
    {
        // Получение какая функция вызвала getSign
        if (!$type) {
            $backtrace = debug_backtrace();
            $type = $backtrace[1]['function'];
        }

        // Тело подписи
        switch ($type) {
            case "token": // Тело подписи при запросе постоянного токена
                $body = 'client_id=' . $this->client_id . '&' . 'code=' . $this->code . '&' . 'grant_type=' .
                    $this->grant_type . '&' . 'redirect_uri=' . urlencode($this->redirect_uri) . '&' . 'type=' .
                    $this->type;
                break;
            case "revoke": // TODO отзыв token узнать как делать подпись в этот раз
                $body = 'access_token=' . $this->access_token . '&' . 'client_id=' . $this->client_id . '&' .
                    'code=' . $this->code . '&' . 'grant_type=' . $this->grant_type . '&' . 'redirect_uri=' .
                    urlencode($this->redirect_uri) . '&' . 'type=' . $this->type;
                break;
            case "init": // Тело подписи при инициализации платежа
                $body = 'access_token=' . $this->access_token . '&' . 'merchant_id=' . $this->client_id .
                    '&' . 'merchant_transaction_id=' . urlencode($this->merchant_transaction_id) . '&' . 'amount=' .
                    $this->amount . '&' . 'currency=' . $this->currency . '&' . 'description='
                    . urlencode($this->description) . '&' . 'type=' . $this->type;
                break;
            case "complete": // Тело подписи при проведении платежа
                $body = 'access_token=' . $this->access_token . '&' . 'merchant_id=' . $this->merchant_id . '&' .
                    'merchant_transaction_id=' . urlencode($this->merchant_transaction_id) . '&' .
                    'processor_transaction_id=' . $this->processor_transaction_id . '&' . 'type=' . $this->type;
                break;
            default:   // По умолчанию и при инийциализации
            case "auth":
                $body = 'response_type=' . $this->response_type . '&' . 'client_id=' . $this->client_id . '&' .
                    'redirect_uri=' . urlencode($this->redirect_uri) . '&' . 'scope=' . $this->scope . '&' .
                    'type=' . $this->type;
                break;
        }

        // строка подписи
        $clear_sign = $body . ';' . $this->iat . ';' . $this->secret;
        // вычисление подписи
        $this->sign = base64_encode(hash('sha256', $clear_sign, true));
        // Возвращаем подпись
        return $this->sign;
    }


    /**
     * Авторизация с получением временного токена
     * @return mixed|\PaymasterSdkPHP\Common\ResponseObject
     */
    public function auth()
    {
        $fields = array(
            'response_type' => $this->response_type,
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'scope' => $this->scope,
            'type' => $this->type,
            'sign' => $this->getSign(),
            'iat' => $this->iat
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
            'values[card_pan]' => "4100000000000010", // Номер карты (фейковый)
            'values[card_month]' => "6", // месяц карты
            'values[card_year]' => (date("Y") + 5), // год карты
            'values[card_cvv]' => "111", // CVV
        );

        $respond = $this->request->call($this->urlGetAuthActionForm2, 'POST', $form2SubmitArray);

        $this->code = $this->_getToken($respond->getBody());

        // Получаем
        return $this->code;
    }


    /**
     * Получение постоянного токена
     * @return mixed|\PaymasterSdkPHP\Common\ResponseObject
     */
    public function token()
    {
        $fields = array(
            'client_id' => $this->client_id,
            'code' => $this->code,
            'grant_type' => $this->grant_type,
            'redirect_uri' => $this->redirect_uri,
            'sign' => $this->getSign('token'),
            'type' => $this->type,
            'iat' => $this->iat
        );

        try {
            $respond = $this->request->call($this->urlGetToken, 'POST', $fields);
            $respondObject = json_decode($respond->getBody());

            if (isset($respondObject->status)) {
                if ($respondObject->status != "failure") {
                    $this->access_token = $respondObject->access_token;
                    $this->token_type = $respondObject->token_type;
                    $this->expires_in = $respondObject->expires_in;
                    $this->account_identifier = $respondObject->account_identifier;
                } else {
                    throw new \Exception("\nI can't get token. Error is happen.\n");
                    die;
                }
            } else {
                $this->access_token = $respondObject->access_token;
                $this->token_type = $respondObject->token_type;
                $this->expires_in = $respondObject->expires_in;
                $this->account_identifier = $respondObject->account_identifier;
            }
        } catch (\Exception $exception) {
            echo 'Error: ' . $exception->getMessage();
            die;
        }

        return $respond->getBody();
    }


    /**
     * Отзыв токена
     * todo нужно смотреть на метод получение подаиси для отзыва tokenа
     * @return mixed|\PaymasterSdkPHP\Common\ResponseObject
     */
    public function revoke()
    {
        $fields = array(
            'client_id' => $this->client_id,
            'access_token' => $this->access_token,
            'sign' => $this->getSign('revoke'),
            'type' => $this->type,
            'iat' => $this->iat,
        );
        $respond = $this->request->call($this->urlRevoke, 'POST', $fields);
        return $respond;
    }


    /**
     * Подготовка к проведению транзакции
     * @param $transaction_id
     * @param $amount
     * @param $desc
     * @return mixed
     * @throws \Exception
     */
    public function init($transaction_id, $amount, $desc)
    {
        if ((!$transaction_id) || (!$amount) || (!$desc)) {
            if (!$transaction_id) {
                throw new \Exception("\nTransaction id is must set!");
            }
            if (!$amount) {
                throw new \Exception("\nTransaction amount is must set!");
            }
            if (!$desc) {
                throw new \Exception("\nTransaction description is must set!");
            }
            die;
        }


        if (!$this->access_token) {
            throw new \Exception("\nAccess token is must set! ".
                "Please make auth at first and get constanly token!");
            die;
        }

        // Присваиваем переменные
        $this->merchant_transaction_id = $transaction_id;
        $this->setAmount($amount);
        $this->description = $desc;

        // Заполняем поля
        $fields = array(
            'access_token' => $this->access_token,
            'merchant_id' => $this->merchant_id,
            'merchant_transaction_id' => $this->merchant_transaction_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'sign' => $this->getSign('init'),
            'type' => $this->type,
            'iat' => $this->iat
        );


        try {
            $respond = $this->request->call($this->urlPaymentInit, 'POST', $fields);
            $respondObject = json_decode($respond->getBody());

            if (isset($respondObject->status)) {
                if ($respondObject->status != "failure") {
                    $this->processor_transaction_id = $respondObject->processor_transaction_id;
                } else {
                    throw new \Exception("\nI can't get processor transaction id. Error is happen.\n");
                    die;
                }
            } else {
                $this->processor_transaction_id = $respondObject->processor_transaction_id;
            }
        } catch (\Exception $exception) {
            echo 'Error: ' . $exception->getMessage();
            die;
        }

        return $respond->getBody();
    }

    /**
     * Проведение платежа
     * @throws \Exception
     */
    public function complete()
    {

        if (!$this->processor_transaction_id) {
            throw new \Exception("\nProcessor transaction id is must set! ".
                "Please make auth at first, init and get constanly token!");
            die;
        }

        $fields = array(
            'access_token' => $this->access_token,
            'merchant_id' => $this->merchant_id,
            'merchant_transaction_id' => $this->merchant_transaction_id,
            'processor_transaction_id' => $this->processor_transaction_id,
            'sign' => $this->getSign('complete'),
            'type' => $this->type,
            'iat' => $this->iat
        );

        try {
            $respond = $this->request->call($this->urlPaymentComplete, 'POST', $fields);
            $respondObject = json_decode($respond->getBody());
            if (isset($respondObject->status)) {
                if ($respondObject->status != "failure") {
                    $this->processor_transaction_id = $respondObject->processor_transaction_id;
                    $this->payment_id = $respondObject->payment_id;
                } else {
                    throw new \Exception("\nI can't get processor transaction id. Error is happen.\n");
                    die;
                }
            } else {
                $this->processor_transaction_id = $respondObject->processor_transaction_id;
                $this->payment_id = $respondObject->payment_id;
            }
        } catch (\Exception $exception) {
            echo 'Error: ' . $exception->getMessage();
            die;
        }

        return $respond->getBody();

    }

    /**
     * TODO обработка карт
     */
    public function card()
    {

    }

    /**
     * TODO конфирмация карт
     */
    public function confirm()
    {

    }

    /**
     * TODO 3D конфирмация карт
     */
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
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }


    /**
     * Получение текущего времени в формате unix
     * @return int|string
     */
    public function getIat()
    {
        $this->iat = strval(time());
        return $this->iat;
    }


    /**
     * Setter merchant_transaction_id
     * @param $merchant_transaction_id
     */
    public function setMerchantTransactionId($merchant_transaction_id)
    {
        $this->merchant_transaction_id = $merchant_transaction_id;
    }

    /**
     * Getter merchant_transaction_id
     */
    public function getMerchantTransactionId()
    {
        return $this->merchant_transaction_id;
    }


    /**
     * Setter amount
     * @param $amount
     */
    public function setAmount($amount)
    {
        $this->amount = number_format($amount, 2, '.', ''); // пробуем числовой формат через точку с разделением как 0.00
    }

    /**
     * Getter amount
     */
    public function getAmount()
    {
        return $this->amount;
    }


    /**
     * Setter amount
     * @param $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * Getter amount
     */
    public function getCurrency()
    {
        return $this->currency;
    }


    /**
     * Setter description
     * @param $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Getter description
     */
    public function getDescription()
    {
        return $this->description;
    }


    //processor_transaction_id

    /**
     * Setter processor_transaction_id
     * @param $processor_transaction_id
     */
    public function setProcessorTransactionId($processor_transaction_id)
    {
        $this->processor_transaction_id = $processor_transaction_id;
    }

    /**
     * Getter processor_transaction_id
     */
    public function getProcessorTransactionId()
    {
        return $this->description;
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