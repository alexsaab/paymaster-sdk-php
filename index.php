,<?php
/**
Форма отправки Запроса на получение токена доступа Direct
 */

echo "Форма отправки Запроса на получение токена доступа Direct, Payloads (данные) Rest, тип подписи SHA256";
echo "<a href='https://paymaster.ru/Partners/ru/docs/direct#h.98lt7thw13ce' target='blank' >  техническая документация на сайте Paymaster <br/> </a>";
echo "Для удобства, в форму можно отправлять POST данные: client_id=&code=&grant_type=&redirect_uri=&type=&iat= <br/>";
echo "Если вы отправляете POST запрос и у вас отсутсвуют некоторые данные, их необходимо дополнительно внести в форму, также необходимо ввести секретный ключ и после этого перерасчитать подпись. <br/>";

// проверки присутствия POST данных и подстановка в переменные при наличии
if(isset($_POST['iat']) && isset($_POST['client_id']))
{
    $client_id=$_POST['client_id'];
    $code=$_POST['code'];
    $grant_type=$_POST['grant_type'];
    $redirect=$_POST['redirect_uri'];
    $sign=$_POST['sign'];
    $type=$_POST['type'];
    $iat=$_POST['iat'];
    $secret=$_POST['secret'];

    echo "<br/> По введенным данным. <br/>";

}
else
{

    $client_id = 'e430408c-3213-4580-9c25-946677a01ea8'; // Идентификатор Продавца в системе PayMaster
    $code = 'nteN8soKLg'; // Временный токен, присвоенный при запросе на авторизацию
    $grant_type = 'authorization_code'; // Константа. Всегда должен быть установлен на "authorization_code"
    $redirect = 'http://test1.techpaymaster.ru'; // Всегда в точности должен совпадать с redirect_uri указанной в запросе авторизации
    $sign = '3QjrwH4wyM0P62bJKQHN53AHh/fVXc/g09yS7bl71Pg='; // Подпись запроса
    $type = 'rest';		// Тип запроса
    $secret = '12345'; // Секретный ключ  DIRECT от сайта
    $iat = time(); // Микровремя  iat

    echo "Исходные данные для расчета. <br/>";
    echo "Внимание, с каждым новым сбросом подставляется текущий iat. <br/>";
}

$service_url = 'https://paymaster.ru/direct/security/token'; // URL для авторизации получения токена direct
// $service_url = 'http://test1.techpaymaster.ru/';   // убрать коммент если надо помотреть данные в запросе

$redirect_uri = urlencode ( $redirect );


// передача пустого POST запроса для сброса данных
echo <<<HTML

<form id="pay_internal" name="pay_internal" method="POST" >
<input style="margin-top: 20px" type="submit" value="Сбросить в Исходные тестовые данные">
</form>

HTML;


$body = 'client_id='.$client_id.'&'.'code='.$code.'&'.'grant_type='.$grant_type.'&'.'redirect_uri='.$redirect_uri.'&'.'type='.$type;   // тело подписи
$clear_sign =  $body.';'.$iat.';'.$secret;					// строка подписи
$sign = base64_encode(hash('sha256', $clear_sign, true));	// вычисление подписи

// ввод данных для расчета подписи
echo <<<HTML

<form id="pay_internal" name="pay_internal" method="POST" >
<input type="text" name="client_id" size="40" value="$client_id"> <label> client_id , идентификатор Продавца в системе PayMaster. </label> <br/>
<input type="text" name="code" size="40" value="$code"> <label> code , временный токен, присвоенный при запросе на авторизацию. </label> <br/>
<input type="text" name="grant_type" size="40" value="$grant_type"> <label> grant_type , константа. Всегда должен быть установлен на "authorization_code". </label> <br/>
<input type="text" name="redirect_uri" size="100" value="$redirect"> <label> redirect_uri , всегда в точности должен совпадать с redirect_uri указанной в запросе авторизации. </label> <br/>
<input type="text" name="sign" size="60" value="$sign"> <label> sign , подпись рассчитывается автоматически. </label> <br/>
<input type="text" name="type" size="40" value="$type"> <label> type , тип запроса, для данной тестовой формы поддерживаестя только rest. </label> <br/>
<input type="text" name="iat" size="40" value="$iat"> <label> iat , время формирования запроса. </label> <br/>
<input type="text" name="secret" size="60" value="$secret"> <label> секретный ключ из ЛК Paymaster/Настройки подписи Direct, вводится для рассчета и проверки подписи. </label> <br/>



<input style="margin-top: 20px" type="submit" value="Перерасчитать подпись direct по введенным данным">
</form>

HTML;



echo "Строка Body:<br/>";
echo "$body <br/>";
echo "<br/> Строка подписи (Body + iat + секретный ключ): <br/>";
echo "$clear_sign <br/>";

$new_date = date('Y-m-d H:i:s', $iat);
echo "<br/> iat:";
echo "$iat; $new_date <br/>";

echo "<br/> Рассчитанная подпись: <br/>";
echo "$sign <br/>";



//Curl request perform
$curl = curl_init();
$data_json = json_encode(array(
    'client_id'     => $client_id,
    'code'          => $code,
    'grant_type'    => $grant_type,
    'redirect_uri'  => $redirect,
    'sign'          => $sign,
    'type' 			=> $type,
    'iat'			=> $iat,
));

/*
echo "<br/> data_json  <br/>";
echo "$data_json <br/>";
echo "<br/>";
*/

$data = (array(
    'client_id'     => $client_id,
    'code'          => $code,
    'grant_type'    => $grant_type,
    'redirect_uri'  => $redirect,
    'sign'          => $sign,
    'type' 			=> $type,
    'iat'			=> $iat,
));

$data_http_build = http_build_query($data);

echo "<br/> data_http_build  <br/>";
echo "$data_http_build <br/>";
echo "<br/>";

// форма перенаправления на авторизацию
echo <<<HTML

<form id="pay" name="pay" method="POST" action="$service_url">
<input type="hidden" name="client_id" size="40" value="$client_id">
<input type="hidden" name="code" size="40" value="$code">
<input type="hidden" name="grant_type" size="40" value="$grant_type">
<input type="hidden" name="redirect_uri" size="150" value="$redirect">
<input type="hidden" name="sign" size="150" value="$sign">
<input type="hidden" name="type" size="40" value="$type">
<input type="hidden" name="iat" size="40" value="$iat">

<input style="margin-top: 20px" type="submit" value="Получить токен доступа direct">
</form>

HTML;


$geturi = $service_url.'?'.$data_http_build;
echo "<br/> Полная строка запроса для быстрой проверки в браузере <br/>";
echo "<a href='$geturi' target='blank' >  $geturi </a>";
echo "<br/>";
echo "<br/>";



echo "<br/> Примеры ответа сервера: <br/>";
echo "В запросе неверные данные, подпись, секретный ключ или старый iat: <br/>";
echo '{"status":"failure","error":"PlanStr was: client_id=e430408c-3213-4580-9c25-946677a01ea8&code=nteN8soKLg&grant_type=authorization_code&redirect_uri=http%3A%2F%2Ftest1.techpaymaster.ru&type=rest;1522475138","error_code":"verification_failure"} <br/>';
echo "<br/>";
echo "В запросе неверный Временный токен авторизации: <br/>";
echo '{"status":"failure","error":"unauthorized client","error_code":"unauthorized_client"} <br/>';
echo "<br/>";
echo "Правильный запрос - ответ сервера (сайт в ЛК Paymaster в тестовом режиме): <br/>";
echo '{"access_token":"K58-JLiSOqAQXggviqKc5NrinZjd2T_KWvTpAFDKefg","account_identifier":"4XXXXXXXXXXX0010","expires_in":13286217,"public_params":null,"token_type":"bearer"} <br/>';

?>