<?php

namespace PaymasterSdkPHP\Common\Exceptions;

use Exception;
use Throwable;

class ApiException extends Exception
{

    /**
     * @var null
     */
    protected $responseBody;


    /**
     * @var array
     */
    protected $responseHeaders;


    /**
     * ApiException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param array $responseHeaders
     * @param null $responseBody
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, $responseHeaders = array(), $responseBody = null)
    {
        parent::__construct($message, $code, $previous);
        $this->responseHeaders = $responseHeaders;
        $this->responseBody = $responseBody;
    }

    /**
     * @return array
     */
    public function getResponseHeaders() {
        return $this->responseHeaders;
    }

    /**
     * @return null
     */
    public function getResponseBody() {
        return $this->responseBody;
    }

}
