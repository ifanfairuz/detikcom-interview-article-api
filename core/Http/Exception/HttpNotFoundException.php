<?php

namespace Core\Http\Exception;

class HttpNotFoundException extends HttpException
{
    public function __construct($message = "Not Found")
    {
        parent::__construct($message, 404);
    }
}
