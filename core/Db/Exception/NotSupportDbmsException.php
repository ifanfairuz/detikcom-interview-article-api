<?php

namespace Core\Db\Exception;

use Exception;

class NotSupportDbmsException extends Exception
{
    public function __construct()
    {
        parent::__construct("Not supported DBMS.");
    }
}
