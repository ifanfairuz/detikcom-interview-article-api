<?php

namespace App\Validation;

use App\Validation\Exception\InvalidRuleValidationException;
use App\Validation\Exception\InvalidValidationException;

class Validation
{
    protected $rules;
    protected $values = [];
    protected $messages = [];

    public function __construct($rules)
    {
        $this->rules = $rules;
        $this->messages = include(config_dir('validation_messages.php'));
    }

    private function getErrorMessage($type, $field, ...$args)
    {
        return sprintf($this->messages[$type] ?? "%s salah", $field, ...$args);
    }

    private function error($type, $field, $message)
    {
        throw new InvalidValidationException($type, $field, $message);
        return false;
    }

    public function required($field)
    {
        $value = @$this->values[$field];

        if (!@$this->values[$field] || empty($value)) {
            return $this->error('required', $field, $this->getErrorMessage('required', $field));
        }

        return true;
    }

    public function string($field)
    {
        $value = @$this->values[$field];

        if (!is_string($value)) {
            return $this->error('string', $field, $this->getErrorMessage('string', $field));
        }

        return true;
    }

    public function max_length($field, $length)
    {
        $value = @$this->values[$field];
        $length = (int) $length;

        if (strlen($value) > $length) {
            return $this->error('max_length', $field, $this->getErrorMessage('max_length', $field, $length));
        }

        return true;
    }

    public function integer($field)
    {
        $value = @$this->values[$field];

        if (!is_integer($field) && !preg_match('/^\d+$/', $value)) {
            return $this->error('integer', $field, $this->getErrorMessage('integer', $field));
        }

        return true;
    }

    public function greater_than_equal_to($field, $val)
    {
        $value = (int) @$this->values[$field];
        $val = (int) $val;

        if ($value < $val) {
            return $this->error('greater_than_equal_to', $field, $this->getErrorMessage('greater_than_equal_to', $field, $val));
        }

        return true;
    }

    public function less_than_equal_to($field, $val)
    {
        $value = (int) @$this->values[$field];
        $val = (int) $val;

        if ($value > $val) {
            return $this->error('greater_than_equal_to', $field, $this->getErrorMessage('greater_than_equal_to', $field, $val));
        }

        return true;
    }

    public function between($field, $min, $max)
    {
        $value = (int) @$this->values[$field];
        $min = (int) $min;
        $max = (int) $max;

        if ($value < $min || $value > $max) {
            return $this->error('between', $field, $this->getErrorMessage('between', $field, $min, $max));
        }

        return true;
    }

    public function validate($values)
    {
        $this->values = $values;

        foreach ($this->rules as $field => $rule) {
            // split rules
            $rules = explode('|', $rule);
            foreach ($rules as $method) {
                // parse args if exists
                $args = [];
                $exploded = explode('[', $method, 2);
                if (count($exploded) > 1) {
                    $method = array_shift($exploded);
                    $args = explode(',', rtrim(array_shift($exploded), ']'));
                }

                // call method validation if exist
                if (method_exists(self::class, $method)) {
                    if (!$this->$method($field, ...$args)) {
                        return false;
                    }
                } else {
                    throw new InvalidRuleValidationException();
                }
            }
        }

        return true;
    }
}
