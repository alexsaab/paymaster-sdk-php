<?php
/**
 * Created by PhpStorm.
 * User: alexey
 * Date: 12.06.18
 * Time: 18:20
 */

/**
 * The MIT License
 *
 * Copyright (c) 2018 Paymaster LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace PaymasterSdkPHP\Client;


class CommonProtocol
{

    // Идентификатор продавца
    // Идентификатор сайта в системе PayMaster. Идентификатор можно увидеть в Личном Кабинете, на странице
    // "Список сайтов", в первой колонке.
    protected $LMI_MERCHANT_ID;

    // Сумма платежа
    // Сумма платежа, которую продавец желает получить от покупателя. Сумма должна быть больше нуля, дробная часть
    // отделяется точкой.
    protected $LMI_PAYMENT_AMOUNT = 0.00;

    // Валюта платежа
    // Идентификатор валюты платежа. Система PayMaster понимает как текстовый 3-буквенный код валюты (RUB),
    // так и ISO-код (643) (см. http://www.currency-iso.org/en/home/tables/table-a1.html)
    protected $LMI_CURRENCY = 'RUB';

    // Внутренний номер счета продавца
    // В этой переменной продавец задает номер счета (идентификатор покупки) в соответствии со своей системой учета.
    // Несмотря на то, что параметр не является обязательным, мы рекомендуем всегда задавать его. Идентификатор должен
    // представлять собой не пустую строку.
    protected $LMI_PAYMENT_NO;

    // Назначение платежа
    // Описание товара или услуги. Формируется продавцом. Максимальная длина - 255 символов.
    protected $LMI_PAYMENT_DESC;

    // Режим тестирования
    // Дополнительное поле, определяющее режим тестирования. Действует только в режиме тестирования и может
    // принимать одно из следующих значений:
    // 0 или отсутствует: Для всех тестовых платежей сервис будет имитировать успешное выполнение;
    // 1: Для всех тестовых платежей сервис будет имитировать выполнение с ошибкой (платеж не выполнен);
    // 2: Около 80% запросов на платеж будут выполнены успешно, а 20% - не выполнены.
    protected $LMI_SIM_MODE = 0;

    // Замена Invoice Confirmation URL
    // Если присутствует, то запрос Invoice Confirmation будет отправляться по указанному URL
    // (а не установленному в настройках). Этот параметр игнорируется, если в настройках сайта запрещена замена URL.
    protected $LMI_INVOICE_CONFIRMATION_URL;

    // Замена Payment Notification URL
    // Если присутствует, то запрос Payment Notification будет отправляться по указанному URL
    // (а не установленному в настройках).
    // Этот параметр игнорируется, если в настройках сайта запрещена замена URL.
    protected $LMI_PAYMENT_NOTIFICATION_URL;

    // Замена Success URL
    // Если присутствует, то при успешном платеже пользователь будет отправлен по указанному URL
    // (а не установленному в настройках).
    //Этот параметр игнорируется, если в настройках сайта запрещена замена URL.
    protected $LMI_SUCCESS_URL;

    // Замена Failure URL
    // Если присутствует, то при отмене платежа пользователь будет отправлен по указанному
    // URL (а не установленному в настройках).
    //Этот параметр игнорируется, если в настройках сайта запрещена замена URL.
    protected $LMI_FAILURE_URL;

    // Телефон покупателя
    // Номер телефона покупателя в международном формате без ведущих символов + (например, 79031234567).
    // Эти данные используются системой PayMaster для оповещения пользователя о статусе платежа. Кроме того,
    // некоторые платежные системы требуют указания номера телефона.
    protected $LMI_PAYER_PHONE_NUMBER;

    // E-mail покупателя
    // E-mail покупателя. Эти данные используются системой PayMaster для оповещения пользователя о статусе платежа.
    // Кроме того, некоторые платежные системы требуют указания e-mail.
    protected $LMI_PAYER_EMAIL;

    // Срок истечения счета
    // Дата и время, до которого действует выписанный счет. Формат YYYY-MM-DDThh:mm:ss, часовой пояс UTC.
    //Внимание: система PayMaster приложит все усилия, чтобы отклонить платеж при истечении срока, но не
    // может гарантировать этого.
    protected $LMI_EXPIRES;

    // Идентификатор платежного метода
    // Идентификатор платежного метода, выбранный пользователем. Отсутствие означает, что пользователь будет
    // выбирать платежный метод на странице оплаты PayMaster.
    //Платежный метод указан в настройках сайта в квадратных скобках рядом с названием платежной системы
    // (Например: Webmoney [WebMoney]).
    //Рекомендуется поменять параметр LMI_PAYMENT_SYSTEM на LMI_PAYMENT_METHOD.
    //Но LMI_PAYMENT_SYSTEM по-прежнему принимается и обрабатывается системой.
    protected $LMI_PAYMENT_METHOD;

    // Внешний идентификатор магазина в платежной системе
    // Внешний идентификатор магазина, передаваемый интегратором в платежную систему.
    // Указывается только при явном определении платежной системы (Указан параметр LMI_PAYMENT_SYSTEM).
    // Для каждой платежной системы формат согласовывается отдельно.
    // (Только для интеграторов!!!)
    protected $LMI_SHOP_ID;

    // Ключ
    // Самое важно из этого всего ключевая фраза, которая испрользуется для формирования обоих хешей
    // (Подписи и самого хеша)
    protected $KEYPASS;


    // Подпись запроса (SIGN)
    // Этого параметра нет в https://paymaster.ru/Partners/ru/docs/protocol
    // Так он необходим только для идентификации платежа
    protected $SIGN;

    // Как работаем с хешем, по какому алгоритму его шифруем для проверки подлинности запроса
    protected $HASH_METHOD = 'md5';


    // Какие параметры обязательные
    protected $required = array('LMI_MERCHANT_ID', 'LMI_PAYMENT_AMOUNT', 'LMI_CURRENCY', 'LMI_PAYMENT_DESC', 'KEYPASS');

    // Начинаем работать с онлайн-кассой
    // Для начала забиваем корзину товара
    protected $LMI_SHOPPINGCART = array();

    // Массив с обязательными параметрами для онлайн позиции (товара) онлайн кассы
    protected $cart_required = array('NAME', 'QTY', 'PRICE', 'TAX');

    // URL для оплаты через форму
    // Очень важно
    protected $url = 'https://paymaster.ru/Payment/Init';

    // Типы НДС и значения
    protected $vatValues = array(
        'vat18',	// НДС 18%
        'vat10', 	// НДС 10%
        'vat118', 	// НДС по формуле 18/118
        'vat110', 	// НДС по формуле 10/110
        'vat0', 	// НДС 0%
        'no_vat', 	// НДС не облагается
    );

    // Переменная для хранения запроса
    protected $request = array();

    /**
     * CommonProtocol constructor.
     */
    public function __construct()
    {
        $this->request = (object) $_REQUEST;

        // Здесь прописываем все переменные если они конечно есть в POST или GET запросе

        if (isset($this->request->LMI_MERCHANT_ID))
            $this->LMI_MERCHANT_ID = $this->request->LMI_MERCHANT_ID;

        if (isset($this->request->LMI_PAYMENT_NO))
            $this->LMI_PAYMENT_NO = $this->request->LMI_PAYMENT_NO;

        if (isset($this->request->LMI_SYS_PAYMENT_ID))
            $this->LMI_SYS_PAYMENT_ID = $this->request->LMI_SYS_PAYMENT_ID;

        if (isset($this->request->LMI_SYS_PAYMENT_DATE))
            $this->LMI_SYS_PAYMENT_DATE = $this->request->LMI_SYS_PAYMENT_DATE;

        if (isset($this->request->LMI_PAYMENT_AMOUNT))
            $this->LMI_PAYMENT_AMOUNT = $this->request->LMI_PAYMENT_AMOUNT;

        if (isset($this->request->LMI_CURRENCY))
            $this->LMI_CURRENCY = $this->request->LMI_CURRENCY;

        if (isset($this->request->LMI_PAID_AMOUNT))
            $this->LMI_PAID_AMOUNT = $this->request->LMI_PAID_AMOUNT;

        if (isset($this->request->LMI_PAID_CURRENCY))
            $this->LMI_PAID_CURRENCY = $this->request->LMI_PAID_CURRENCY;

        if (isset($this->request->LMI_PAYMENT_SYSTEM))
            $this->LMI_PAYMENT_SYSTEM = $this->request->LMI_PAYMENT_SYSTEM;

        if (isset($this->request->LMI_SIM_MODE))
            $this->LMI_SIM_MODE = $this->request->LMI_SIM_MODE;

    }

    /**
     * Setter
     * @param $variable
     * @param $value
     */
    public function set($variable, $value) {
        $this->$variable = $variable;
    }

    /**
     * Getter
     * @param $variable
     * @param $value
     */
    public function get($variable, $default = null) {
        if (isset($variable))
            return $variable;
        else
            return $default;
    }



    /**
     * Получение подписи
     * Просто делаем ее по MD5
     */
    public function getSIGN() {
        $sign = $this->LMI_MERCHANT_ID . ':' . $this->LMI_PAYMENT_AMOUNT . ':' . $this->LMI_PAYMENT_DESC . ':' . $this->KEYPASS;
        return md5($sign);
    }


    /**
     * Получение проверочного хэша
     */
    public function getLMI_HASH() {
        // Подготавливаем строчку для хеша
        $stringToHash = $this->LMI_MERCHANT_ID . ";" . $this->LMI_PAYMENT_NO . ";" . $this->LMI_SYS_PAYMENT_ID . ";"
            . $this->LMI_SYS_PAYMENT_DATE . ";" . $this->LMI_PAYMENT_AMOUNT . ";" . $this->LMI_CURRENCY . ";"
            . $this->LMI_PAID_AMOUNT . ";" . $this->LMI_PAID_CURRENCY . ";" . $this->LMI_PAYMENT_SYSTEM . ";"
            . $this->LMI_SIM_MODE . ";" . $this->KEYPASS;
        // И кодируем хеш в соответствии с установленным алгоритмом для шифорования
        $hash = base64_encode(hash($this->HASH_METHOD, $stringToHash, true));
        return $hash;
    }

    /**
     * Получение формы оплаты
     */
    public function getForm() {
        // Проверяем форму основную
        $this->__checkForm1();

        // Основные значения
        $html = "<input type='hidden' name='LMI_MERCHANT_ID' value='{$this->LMI_MERCHANT_ID}'/>\n";
        // Приводим значения к формату 0.00
        $LMI_PAYMENT_AMOUNT = number_format($this->LMI_PAYMENT_AMOUNT,2);
        $html .= "<input type='hidden' name='LMI_PAYMENT_AMOUNT' value='{$LMI_PAYMENT_AMOUNT}'/>\n";
        $html .= "<input type='hidden' name='LMI_CURRENCY' value='{$this->LMI_CURRENCY}'/>\n";
        $html .= "<input type='hidden' name='LMI_PAYMENT_DESC' value='{$this->LMI_PAYMENT_DESC}'/>\n";

        // Формируем и выводим подпись
        $html .= "<input type='hidden' name='SIGN' value='{$this->getSIGN()}'/>\n";

        // Теперь выводим все вспомогальтельные параметры
        if ($this->LMI_PAYMENT_NO)
            $html .= "<input type='hidden' name='LMI_PAYMENT_NO' value='{$this->LMI_PAYMENT_NO}'/>\n";
        if ($this->LMI_SIM_MODE)
            $html .= "<input type='hidden' name='LMI_SIM_MODE' value='{$this->LMI_SIM_MODE}'/>\n";
        if ($this->LMI_INVOICE_CONFIRMATION_URL)
            $html .= "<input type='hidden' name='LMI_INVOICE_CONFIRMATION_URL' value='{$this->LMI_INVOICE_CONFIRMATION_URL}'/>\n";
        if ($this->LMI_PAYMENT_NOTIFICATION_URL)
            $html .= "<input type='hidden' name='LMI_PAYMENT_NOTIFICATION_URL' value='{$this->LMI_PAYMENT_NOTIFICATION_URL}'/>\n";
        if ($this->LMI_SUCCESS_URL)
            $html .= "<input type='hidden' name='LMI_SUCCESS_URL' value='{$this->LMI_SUCCESS_URL}'/>\n";
        if ($this->LMI_FAILURE_URL)
            $html .= "<input type='hidden' name='LMI_FAILURE_URL' value='{$this->LMI_FAILURE_URL}'/>\n";
        if ($this->LMI_PAYER_PHONE_NUMBER)
            $html .= "<input type='hidden' name='LMI_PAYER_PHONE_NUMBER' value='{$this->LMI_PAYER_PHONE_NUMBER}'/>\n";
        if ($this->LMI_PAYMENT_METHOD)
            $html .= "<input type='hidden' name='LMI_PAYMENT_METHOD' value='{$this->LMI_PAYMENT_METHOD}'/>\n";
        if ($this->LMI_SHOP_ID)
            $html .= "<input type='hidden' name='LMI_SHOP_ID' value='{$this->LMI_SHOP_ID}'/>\n";


        // Теперь выводим товарыные позиции
        if (count($this->LMI_SHOPPINGCART) > 0) {
            // Сумма позиций в заказе
            $amount = 0.00;
            foreach ($this->LMI_SHOPPINGCART as $key=>$ITEM) {
                $this->__checkForm2($ITEM);
                $PRICE = number_format($ITEM['PRICE'],2);
                $html .= "<input type='hidden' name='LMI_SHOPPINGCART.ITEM[{$key}].NAME' value='{$ITEM['NAME']}'/>\n";
                $html .= "<input type='hidden' name='LMI_SHOPPINGCART.ITEM[{$key}].QTY' value='{$ITEM['QTY']}'/>\n";
                $html .= "<input type='hidden' name='LMI_SHOPPINGCART.ITEM[{$key}].PRICE' value='{$PRICE}'/>\n";
                $html .= "<input type='hidden' name='LMI_SHOPPINGCART.ITEM[{$key}].TAX' value='{$ITEM['TAX']}'/>\n";
                $amount += $PRICE*$ITEM['QTY'];
            }
        }

        if (isset($amount))
            if ($amount != $LMI_PAYMENT_AMOUNT)
                throw new \Exception('Не совпадают суммы. Сумма заказа '.$LMI_PAYMENT_AMOUNT.', а сумма товарных позиций '.$amount.' !');

        echo $html;
    }


    /**
     * Проверка основных переменных формы
     */
    private function __checkForm1() {
        foreach ($this->required as $var)
            if (!isset($this->$var))
                throw new \Exception('Не хватает переменных для получения формы оплаты. Не задана переменная "'.$var.'" !');

    }

    /**
     * Проверка переменных формы для онлайн кассы (товарных позиций)
     */
    private function __checkForm2($ITEM) {
        foreach ($this->cart_required as $var)
            if (!isset($ITEM))
                throw new \Exception('Не хватает переменных для получения формы оплаты в товарной позиции. Не задана переменная "'.$var.'" !');

        if (!in_array($ITEM['TAX'],$this->vatValues))
            throw new \Exception('НДС для товарной позиции задан неверно "'.$ITEM['TAX'].'" !');
    }


}