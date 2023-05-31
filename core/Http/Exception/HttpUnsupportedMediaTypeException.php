<?php

namespace Core\Http\Exception;

class HttpUnsupportedMediaTypeException extends HttpException
{
    public function __construct($message = "Unsupported Media Type")
    {
        parent::__construct($message, 415);
    }
}
