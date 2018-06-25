<?php

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

use Psr\Log\LoggerInterface;
use PaymasterSdkPHP\Common\ResponseObject;
use PaymasterSdkPHP\Helpers\RawHeadersParser;
use PaymasterSdkPHP\Common\Exceptions\ApiException;


/**
 * Class CurlClient
 * @package PaymasterSdkPHP\Client
 */
class CurlClient implements ApiClientInterface
{

    /**
     * @var $cookies
     */
    private $cookie;

    /**
     * @var string
     */
    private $cookieDir = '/../../cookies/';

    /**
     * @var int
     */
    private $timeout = 80;

    /**
     * @var int
     */
    private $connectionTimeout = 30;

    /**
     * @var bool
     */
    private $keepAlive = true;


    /**
     * @var resource
     */
    private $curl;

    /**
     * @var LoggerInterface|null
     */
    private $logger;


    /**
     * CurlClient constructor.
     */
    public function __construct()
    {
        $this->cookie = tempnam(__DIR__.$this->cookieDir, 'COO_');
    }

    /**
     * @param LoggerInterface|null $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function call($url, $method, $fields = null, $headers = array())
    {
        if ($this->logger !== null) {
            $message = 'Send request: ' . $method . ' ' . $url;
            if (!empty($httpBody)) {
                $message .= ' with body: ' . $httpBody;
            }
            if (!empty($httpBody)) {
                $message .= ' with headers: ' . json_encode($headers);
            }
            $this->logger->info($message);
        }

        $this->initCurl();

        $this->setCurlOption(CURLOPT_URL, $url);

        $this->setCurlOption(CURLOPT_RETURNTRANSFER, true);

        $this->setCurlOption(CURLOPT_FOLLOWLOCATION, true);

        $this->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);

        $this->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);

        $this->setCurlOption(CURLOPT_HEADER, true);

        $this->setCurlOption(CURLOPT_POST, true);

        $this->setCurlOption(CURLOPT_POSTFIELDS, $fields);

        if (!empty($headers)) {
            $this->setCurlOption(CURLOPT_HTTPHEADER, $headers);
        }

        $this->setCurlOption(CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);

        $this->setCurlOption(CURLOPT_TIMEOUT, $this->timeout);

        $this->setCurlOption(CURLOPT_COOKIESESSION, true);

        $this->setCurlOption(CURLOPT_COOKIEJAR, $this->cookie);

        $this->setCurlOption(CURLOPT_COOKIEFILE, $this->cookie);

        list($httpHeaders, $httpBody, $responseInfo) = $this->sendRequest();

        if (!$this->keepAlive) {
            $this->closeCurlConnection();
        }

        if ($this->logger !== null) {
            $message = 'Response with code ' . $responseInfo['http_code'] . ' received with headers: '
                . json_encode($httpHeaders);
            if (!empty($httpBody)) {
                $message .= ' and body: ' . $httpBody;
            }
            $this->logger->info($message);
        }

        return new ResponseObject(array(
            'code' => $responseInfo['http_code'],
            'headers' => $httpHeaders,
            'body' => $httpBody
        ));
    }

    /**
     * Функция которая возвращает закодированную строку без преобразования знаков /, \, пробел
     * @param $fields
     * @return string
     */
    public function _http_build_query($fields) {
        $strings = array();
        foreach ($fields as $name=>$value) {
            $strings[] = "{$name}={$value}";
        }
        $string = implode('&', $strings);

        return $string;
    }

    /**
     * @param $optionName
     * @param $optionValue
     * @return bool
     */
    public function setCurlOption($optionName, $optionValue)
    {
        return curl_setopt($this->curl, $optionName, $optionValue);
    }


    /**
     * @return resource
     */
    private function initCurl()
    {
        if (!$this->curl || !$this->keepAlive) {
            $this->curl = curl_init();
        }

        return $this->curl;
    }

    /**
     * Close connection
     */
    public function closeCurlConnection()
    {
        if ($this->curl !== null) {
            curl_close($this->curl);
        }
    }

    /**
     * @return array
     * @throws ApiConnectionException
     */
    public function sendRequest()
    {
        $response = curl_exec($this->curl);
        $httpHeaderSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $httpHeaders = RawHeadersParser::parse(substr($response, 0, $httpHeaderSize));
        $httpBody = substr($response, $httpHeaderSize);
        $responseInfo = curl_getinfo($this->curl);
        $curlError = curl_error($this->curl);
        $curlErrno = curl_errno($this->curl);
        if ($response === false) {
            $this->handleCurlError($curlError, $curlErrno);
        }

        return array($httpHeaders, $httpBody, $responseInfo);
    }

    /**
     * @return mixed
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param mixed $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return mixed
     */
    public function getConnectionTimeout()
    {
        return $this->connectionTimeout;
    }

    /**
     * @param mixed $connectionTimeout
     */
    public function setConnectionTimeout($connectionTimeout)
    {
        $this->connectionTimeout = $connectionTimeout;
    }


    /**
     * @param string $error
     * @param int $errno
     * @throws ApiConnectionException
     */
    private function handleCurlError($error, $errno)
    {
        switch ($errno) {
            case CURLE_COULDNT_CONNECT:
            case CURLE_COULDNT_RESOLVE_HOST:
            case CURLE_OPERATION_TIMEOUTED:
                $msg = "Could not connect to Paymaster API. Please check your internet connection and try again.";
                break;
            case CURLE_SSL_CACERT:
            case CURLE_SSL_PEER_CERTIFICATE:
                $msg = "Could not verify SSL certificate.";
                break;
            default:
                $msg = "Unexpected error communicating.";
        }
        $msg .= "\n\n(Network error [errno $errno]: $error)";
        throw new ApiConnectionException($msg);
    }


    /**
     * @param bool $keepAlive
     * @return CurlClient
     */
    public function setKeepAlive($keepAlive)
    {
        $this->keepAlive = $keepAlive;
        return $this;
    }

}