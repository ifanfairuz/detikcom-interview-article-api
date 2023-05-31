<?php

namespace Core\Http;

use Core\Http\Exception\HttpBadRequestException;
use Core\Http\Exception\HttpUnsupportedMediaTypeException;

class BodyParser
{
    public $parsed = false;
    private $method;
    private $contentType;

    public $data = [];
    public $files = [];

    public function __construct($method, $contentType)
    {
        $this->method = $method;
        $this->contentType = $contentType;
    }

    /**
     * Converts bytes to kb mb etc..
     * Taken from https://github.com/notihnio/php-multipart-form-data-parser/blob/master/src/MultipartFormDataParser.php
     *
     * @param string $raw
     *
     * @return void
     */
    private function parseFormData($raw)
    {
        $matches = "";
        if (!preg_match('/boundary=(.*)$/is', $this->contentType, $matches)) {
            throw new HttpBadRequestException();
            return;
        }
        $boundary = $matches[1];
        $bodyParts = preg_split('/\\R?-+' . preg_quote($boundary, '/') . '/s', $raw);


        foreach ($bodyParts as $bodyPart) {
            if (empty($bodyPart)) {
                continue;
            }

            $temp = preg_split('/\\R\\R/', $bodyPart, 2);
            $headers = $temp[0];
            $value = @$temp[1];
            $headers = $this->parseHeaders($headers);
            if (!isset($headers['content-disposition']['name'])) {
                continue;
            }

            if (isset($headers['content-disposition']['filename'])) {
                $file = [
                    'name' => $headers['content-disposition']['filename'],
                    'type' => array_key_exists('content-type', $headers) ? $headers['content-type'] : 'application/octet-stream',
                    'size' => mb_strlen($value, '8bit'),
                    'error' => UPLOAD_ERR_OK,
                    'tmp_name' => null,
                ];

                if ($file['size'] > formatedBytestoBytes(ini_get('upload_max_filesize'))) {
                    $file['error'] = UPLOAD_ERR_INI_SIZE;
                } else {
                    $tmpResource = tmpfile();
                    if ($tmpResource === false) {
                        $file['error'] = UPLOAD_ERR_CANT_WRITE;
                    } else {
                        $tmpResourceMetaData = stream_get_meta_data($tmpResource);
                        $tmpFileName = $tmpResourceMetaData['uri'];
                        if (empty($tmpFileName)) {
                            $file['error'] = UPLOAD_ERR_CANT_WRITE;
                            @fclose($tmpResource);
                        } else {
                            fwrite($tmpResource, $value);
                            $file['tmp_name'] = $tmpFileName;
                            $file['tmp_resource'] = $tmpResource;
                        }
                    }
                }

                $file["size"]   = toFormattedBytes($file["size"]);
                $_FILES[$headers['content-disposition']['name']] = $file;
                $this->files[$headers['content-disposition']['name']] = $file;
            } else {
                $this->data[$headers['content-disposition']['name']] = $value;
            }
        }
    }


    /**
     * parse raw body
     */
    private function parseRaw()
    {
        $raw = file_get_contents('php://input');
        if (!empty($this->contentType)) {
            $contentType = explode(';', $this->contentType)[0];
        }

        if ($contentType === 'application/x-www-form-urlencoded') {
            parse_str($raw, $this->data);
            $this->parsed = true;
            return;
        }

        if ($contentType === 'application/json') {
            $this->data = json_decode($raw, true);
            $this->parsed = true;
            return;
        }

        if ($contentType === 'multipart/form-data') {
            $this->parseFormData($raw);
            $this->parsed = true;
            return;
        }

        throw new HttpUnsupportedMediaTypeException();
    }

    /**
     * parse body request
     */
    public function parse()
    {
        if ($this->parsed) {
            return;
        }

        if ($this->method === 'GET') {
            $this->data = $_GET;
            $this->parsed = true;
            return;
        }

        if ($this->method === 'POST') {
            $this->data = $_POST;
            $this->files = $_FILES;
            $this->parsed = true;
            return;
        }

        $this->parseRaw();
    }

    /**
     * Parses body param headers
     * Taken from https://github.com/notihnio/php-multipart-form-data-parser/blob/master/src/MultipartFormDataParser.php
     *
     * @param string $headerContent
     *
     * @return array
     */
    private function parseHeaders(string $headerContent): array
    {
        $headers = [];
        $headerParts = preg_split('/\\R/s', $headerContent, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($headerParts as $headerPart) {
            if (!strpos($headerPart, ':') !== false) {
                continue;
            }
            [$headerName, $headerValue] = explode(':', $headerPart, 2);
            $headerName = strtolower(trim($headerName));
            $headerValue = trim($headerValue);
            if (!strpos($headerValue, ';') !== false) {
                $headers[$headerName] = $headerValue;
            } else {
                $headers[$headerName] = [];
                foreach (explode(';', $headerValue) as $part) {
                    $part = trim($part);
                    if (!strpos($part, '=') !== false) {
                        $headers[$headerName][] = $part;
                    } else {
                        [$name, $value] = explode('=', $part, 2);
                        $name = strtolower(trim($name));
                        $value = trim(trim($value), '"');
                        $headers[$headerName][$name] = $value;
                    }
                }
            }
        }
        return $headers;
    }
}
