<?php

namespace Core\Http;

use Core\Http\Exception\HttpException;

class Response
{
    protected $status = 200;
    protected $headers = [
        'Content-Length' => 0
    ];
    protected $data = "";

    public function body($data)
    {
        $data = is_string($data) ? $data : json_encode($data);
        $this->data = $data;
        return $this;
    }

    public function json($data = NULL)
    {
        $this->headers['Content-Type'] = 'application/json';
        if ($data) $this->body($data);
        return $this;
    }

    public function status(int $status)
    {
        $this->status = $status;
        return $this;
    }

    public function header($name, $value = NULL)
    {
        if ($value === NULL) return $this->headers[$name] ?? NULL;

        $this->headers[$name] = $value;
        return $this;
    }

    public function configureCors(Request $req, $isOptions = false, $allowedMethods = [])
    {
        $cors = config('cors');

        // configure origin
        $allowOriginAny = in_array('*', $cors['allowed_origins']);
        if ($allowOriginAny && !$cors['supports_credentials']) {
            $this->header('Access-Control-Allow-Origin', '*');
        } elseif (!$allowOriginAny && count($cors['allowed_origins']) === 1) {
            $this->header('Access-Control-Allow-Origin', array_values($cors['allowed_origins'])[0]);
        } else {
            $allowed = array_map('convertWildcardToPattern', $cors['allowed_origins']);
            if ($allowOriginAny || $this->allowedOrigins($allowed, $req->origin)) {
                $this->header('Access-Control-Allow-Origin', $req->origin);
            }
        }

        if ($this->header('Access-Control-Allow-Origin')) {

            // configure allow credential
            if (!!$cors['supports_credentials']) {
                $this->header('Access-Control-Allow-Credentials', 'true');
            }

            if ($isOptions) {
                // configure allow headers
                if (in_array('*', $cors['allowed_headers'])) {
                    if ($req->allowHeaders) $this->header('Access-Control-Allow-Headers', $req->allowHeaders);
                } else {
                    $this->header('Access-Control-Allow-Headers', join(', ', $cors['allowed_headers']));
                }

                // configure allow method
                if (in_array('*', $cors['allowedMethods'])) {
                    if ($req->allowMethods) $this->header('Access-Control-Allow-Methods', join(', ', $allowedMethods) . ", OPTIONS");
                } else {
                    $this->header('Access-Control-Allow-Methods', join(', ', $cors['allowedMethods']));
                }

                // configure max age
                if ($cors['max_age'] !== NULL) {
                    $this->header('Access-Control-Allow-Max-Age', (int) $cors['allowed_headers']);
                }
            } else {
                // configure exposed header
                if (@$cors['exposed_headers'] && count($cors['exposed_headers']) > 0) {
                    $this->header('Access-Control-Expose-Headers', join(', ', $cors['exposed_headers']));
                }
            }
        }

        return $this;
    }

    private function allowedOrigins(array $allowed, $origin)
    {
        if ($origin === NULL) {
            return false;
        }

        if (in_array($origin, $allowed)) {
            return true;
        }

        foreach ($allowed as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }

        return false;
    }

    public function render()
    {
        $this->beforeRender();
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->data;

        exit;
    }

    private function beforeRender()
    {
        $this->headers['Content-Length'] = strlen($this->data);
        header_remove("X-Powered-By");
    }

    public static function error(Request $req, HttpException $e)
    {
        $res = new self();
        if ($req->accepting('application/json')) {
            $res->json($e->getJsonMessage());
        } else {
            $res->body($e->getMessage());
        }
        return $res->configureCors($req)->status($e->httpResponseCode);
    }
}
