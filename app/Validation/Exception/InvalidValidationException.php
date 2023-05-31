<?php

namespace App\Validation\Exception;

use Core\Http\Exception\HttpBadRequestException;

class InvalidValidationException extends HttpBadRequestException
{
    public $validationMessage;
    public $validationType;
    public $field;

    public function __construct($type = "", $field = "", $message = "")
    {
        parent::__construct();
        $this->validationType = $type;
        $this->field = $field;
        $this->validationMessage = $message;
    }

    public function getJsonMessage()
    {
        return json_encode([
            'status' => $this->httpResponseCode,
            'message' => $this->getMessage(),
            'info' => [
                'error' => 'InvalidValidation',
                'type' => $this->validationType,
                'field' => $this->field,
                'message' => $this->validationMessage,
            ]
        ]);
    }
}
