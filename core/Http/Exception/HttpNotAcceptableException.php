<?php

namespace Core\Http\Exception;

class HttpNotAcceptableException extends HttpException
{
    public function __construct($message = "Not Acceptable")
    {
        parent::__construct($message, 406);
    }
}
