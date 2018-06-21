<?php
/**
 * Форма проверки и отправки Запроса авторизации Direct
 */

echo "Форма расчета подписи Direct, проверяем заданный тип подписи SHA256 (секретный ключ) в ЛК Payamster/Настройки подписи Direct. <br/>";
echo "Авторизации и получения временного токена:";
echo "<a href='https://paymaster.ru/Partners/ru/docs/direct#h.2k7091jhaxic' target='blank' >  техническая документация на сайте Paymaster <br/> </a>";


// проверки присутствия POST данных и подстановка в переменные при наличии
if (isset($_POST['iat']) && isset($_POST['client_id'])) {
    $response_type = $_POST['response_type'];
    $client_id = $_POST['client_id'];
    $redirect = $_POST['redirect_uri'];
    $scope = $_POST['scope'];
    $type = $_POST['type'];
    $iat = $_POST['iat'];
    $sign = $_POST['sign'];
    $secret = $_POST['secret'];

    echo " По введенным данным. <br/>";
    echo " Внимание, если вы подставляете свой старый iat, будте готовы, что подпись будет рассчитана правильно, но сервер Paymaster, при авторизации, может выдать ошибку неверная подпись. <br/>";
} else {
    $iat = time(); // Микровремя  iat
    $response_type = 'code'; // Константа. Параметр всегда должен иметь значение "code"
    $client_id = 'e430408c-3213-4580-9c25-946677a01ea8'; // Идентификатор Продавца в системе PayMaster
    $redirect = 'http://test1.techpaymaster.ru'; // URL для перенаправления клиента после успешной авторизации.  НЕ кодированная.

    $scope = '503'; // Идентификатор платежной системы
    $secret = '12345'; // Секретный ключ  DIRECT от сайта
    $cookie_file = __DIR__ . '/cookies.txt';   // Пока непонятно для чего
    $type = 'rest';        // Тип запроса
    echo "Исходные данные для расчета. <br/>";
    echo "Внимание, с каждым новым сбросом подставляется текущий iat.";
}

$service_url = 'https://paymaster.ru/direct/security/auth'; // URL для авторизации direct
// $service_url = 'http://test1.techpaymaster.ru/';   // убрать коммент если надо помотреть данные в запросе

$redirect_uri = urlencode($redirect);


// передача пустого POST запроса для сброса данных
echo <<<HTML

<form id="pay_internal" name="pay_internal" method="POST" >
<input style="margin-top: 20px" type="submit" value="Сбросить в Исходные тестовые данные">
</form>

HTML;


$body = 'response_type=' . $response_type . '&' . 'client_id=' . $client_id . '&' . 'redirect_uri=' . $redirect_uri . '&' . 'scope=' . $scope . '&' . 'type=' . $type;   // тело подписи
$clear_sign = $body . ';' . $iat . ';' . $secret;                    // строка подписи
$sign = base64_encode(hash('sha256', $clear_sign, true));    // вычисление подписи

// ввод данных для расчета подписи
echo <<<HTML

<form id="pay_internal" name="pay_internal" method="POST" >
<p> <input type="text" name="response_type" size="40" value="$response_type">  response_type , всегда должен иметь значение "code" </p> 
<p> <input type="text" name="client_id" size="40" value="$client_id"> client_id , идентификатор Продавца в системе PayMaster </p>
<p> <input type="text" name="redirect_uri" size="105" value="$redirect"> redirect_uri , url  для перенаправления после успешной авторизации</p>
<p> <input type="text" name="scope" size="40" value="$scope"> scope , в тестовом режиме 503 , в рабочем режиме bankcard или webmoney (если поключено) </p>
<p> <input type="text" name="type" size="40" value="$type"> type , тип запроса, для данной тестовой формы поддерживаестя только rest </p>
<p> <input type="text" name="iat" size="40" value="$iat"> iat , время формирования запроса </p>
<p> <input type="text" name="sign" size="60" value="$sign"> sign , подпись рассчитывается автоматически </p>
<p> <input type="text" name="secret" size="60" value="$secret">  секретный ключ из ЛК Payamster/Настройки подписи Direct, вводится для рассчета и проверки подписи </p>

<input style="margin-top: 20px" type="submit" value="Перерасчитать подпись direct по введенным данным">
</form>

HTML;


echo "Строка Body:<br/>";
echo "$body <br/>";
echo "<br/> Строка подписи (Body + iat + секретный ключ): <br/>";
echo "$clear_sign <br/>";
echo "<br/> iat:	";
echo "$iat <br/>";
echo "<br/> Рассчитанная подпись: <br/>";
echo "$sign <br/>";
echo "<br/> URL для перенаправления клиента после успешной авторизации в urlencode: <br/>";
echo "$redirect_uri <br/>";

//Curl request perform
$curl = curl_init();

$data = (array(
    'response_type' => $response_type,
    'client_id'     => $client_id,
    'redirect_uri'  => $redirect,
    'scope'         => $scope,
    'type'          => $type,
    'iat'           => $iat,
    'sign'          => $sign,
));

$data_json = json_encode($data);
// echo "<br/> data_json  <br/>";
// echo "$data_json <br/>";
// echo "<br/>";

$data_http_build = http_build_query($data);
echo "<br/> data_http_build  <br/>";
echo "$data_http_build <br/>";

// форма перенаправления на авторизацию
echo <<<HTML

<form id="pay" name="pay" method="POST" action="$service_url" target="blank">
<input type="hidden" name="response_type" value="$response_type">
<input type="hidden" name="client_id" value="$client_id">
<input type="hidden" name="redirect_uri" value="$redirect">
<input type="hidden" name="scope" value="$scope">
<input type="hidden" name="type" value="$type">
<input type="hidden" name="iat" value="$iat">
<input type="hidden" name="sign" value="$sign">


<input style="margin-top: 20px" type="submit" value="Авторизоваться по протоколу direct">
</form>

HTML;
echo " Для тестового режима!!! Номер карты: 4100000000000010 , Срок действия до: позже текущей даты  , CVV/CVC: любой";
echo "<br/> Реакцией на успешное завершение процесса авторизации должно быть перенаправление пользователя на предоставленный redirect_uri ,";
echo "<br/> с передачей Временного токена авторизации в парараметре ?code=  , ?result=CANCEL в случае отказа клиента от привязки карты <br/>";

$geturi = $service_url . '?' . $data_http_build;
echo "<br/> Полная строка запроса для быстрой проверки в браузере <br/>";
echo "<a href='$geturi' target='blank' >  $geturi </a>";
echo "<br/>";
echo "<br/>";

/*
// кусок кода взял отсюда https://toster.ru/q/505769        отправляет данные и отображает результат без перехода на сайт

function redirect_post($url, array $data, array $headers = null) {
    $params = array(
        'http' => array(
            'method' => 'POST',
            'content' => http_build_query($data)
        )
    );
    if (!is_null($headers)) {
        $params['http']['header'] = '';
        foreach ($headers as $k => $v) {
            $params['http']['header'] .= "$k: $v\n";
        }
    }
    $ctx = stream_context_create($params);
    $fp = @fopen($url, 'rb', false, $ctx);
    if ($fp) {
        echo @stream_get_contents($fp);
        die();
    } else {
        // Error
        throw new Exception("Error loading '$url', $php_errormsg");
    }
}
redirect_post($service_url, $data);
// кусок кода окончение

*/

/*
// кусок кода  еще один  также отправляет данные и отображает результат без перехода на сайт
 // преобразуем массив в URL-кодированную строку
$vars = http_build_query($data);
// создаем параметры контекста
$options = array(
    'http' => array(
                'method'  => 'POST',  // метод передачи данных
                'header'  => 'Content-type: application/x-www-form-urlencoded',  // заголовок
                'content' => $vars,  // переменные
            )
);
$context  = stream_context_create($options);  // создаём контекст потока
$result = file_get_contents($service_url, false, $context); //отправляем запрос
var_dump($result); // вывод результата

*/

/*


curl_setopt($ch, CURLOPT_URL, $service_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$curl_response = curl_exec($curl);



if ($curl_response === false) {
    $info = curl_getinfo($curl);
    curl_close($curl);
    die('Error occured during curl exec. Additional info: ' . var_export($info));
}

curl_close($curl);

$decoded = json_decode($curl_response);
if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
    die('error occured: ' . $decoded->response->errormessage);
}
echo 'Response ok!';
var_export($decoded->response);

?>