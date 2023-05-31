<?php

namespace Core\Http\Exception;

use Exception;

class HttpException extends Exception
{
    public $httpResponseCode;

    public function __construct($message = "Server Error", $httpResponseCode = 500)
    {
        parent::__construct($message);
        $this->httpResponseCode = $httpResponseCode;
    }

    public function getJsonMessage()
    {
        return json_encode([
            'status' => $this->httpResponseCode,
            'message' => $this->getMessage(),
        ]);
    }
}
