<?php

namespace Core\Http\Exception;

class HttpBadRequestException extends HttpException
{
    public function __construct($message = "Bad Request")
    {
        parent::__construct($message, 400);
    }
}
