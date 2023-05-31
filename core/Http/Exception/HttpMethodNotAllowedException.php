<?php

namespace Core\Http\Exception;

class HttpMethodNotAllowedException extends HttpException
{
    public function __construct($message = "Method Not Allowed")
    {
        parent::__construct($message, 405);
    }
}
