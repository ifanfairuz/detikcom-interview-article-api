<?php

namespace App\Validation\Exception;

use Exception;

class InvalidRuleValidationException extends Exception
{
    public function __construct()
    {
        parent::__construct("Invalid rule validation.");
    }
}
