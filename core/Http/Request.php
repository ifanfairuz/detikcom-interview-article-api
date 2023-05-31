<?php

namespace Core\Http;

use Core\Http\Exception\HttpBadRequestException;

class Request
{
    /**
     * @var \Core\Http\BodyParser
     */
    private $bodyParser;

    public $uri;
    public $method;
    public $accept;
    public $userAgent;
    public $origin;
    public $allowMethods;
    public $allowHeaders;
    public $contentType;
    public $accepts = [];
    public $data = [];
    public $files = [];

    public function __construct()
    {
        $path = $_SERVER['PATH_INFO'] ?? "/";
        $this->uri = $path == "" ? "/" : $path;
        $this->method = $_SERVER['REQUEST_METHOD'] ?? null;
        $this->accept = $_SERVER['HTTP_ACCEPT'] ?? null;
        $this->origin = $_SERVER['HTTP_ORIGIN'] ?? null;
        $this->allowMethods = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ?? null;
        $this->allowHeaders = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? null;
        $this->contentType = $_SERVER['HTTP_CONTENT_TYPE'] ?? $_SERVER['CONTENT_TYPE'] ?? null;
    }

    public function validate(): bool
    {
        if (in_array(null, [$this->method, $this->accept])) {
            throw new HttpBadRequestException();
            return false;
        }

        if ($this->method === 'PUT' && !$this->contentType) {
            throw new HttpBadRequestException();
            return false;
        }

        return true;
    }

    public function parseBody()
    {
        $this->bodyParser = new BodyParser($this->method, $this->contentType);
        $this->bodyParser->parse();
        $this->data = $this->bodyParser->data;
        $this->files = $this->bodyParser->files;
    }

    private function generateAccepts()
    {
        if (count($this->accepts) === 0) {
            $accepts = explode(',', $this->accept ?? "");
            $this->accepts = array_map(function ($accept) {
                $accept = explode(';', rtrim($accept, ';'))[0];
                return convertWildcardToPattern(trim($accept));
            }, $accepts);
        }
    }

    public function accepting(string $expect): bool
    {
        $this->generateAccepts();
        foreach ($this->accepts as $accept) {
            if (preg_match($accept, $expect)) {
                return true;
            }
        }

        return false;
    }

    public function input($key, $default = null)
    {
        return @$this->data[$key] ?? $default;
    }

    public function only($keys = [])
    {
        return array_filter($this->data, function ($key) use ($keys) {
            return in_array($key, $keys);
        }, ARRAY_FILTER_USE_KEY);
    }

    public function param($key, $default = null)
    {
        return @$_GET[$key] ?? $default;
    }

    public function file($key, $default = null)
    {
        return @$this->files[$key] ?? $default;
    }
}
