<?php

namespace App\Controller;

use Core\Http\Response;

class Controller
{
    public function response($data = null)
    {
        $res = new Response();
        if ($data !== null) $res->body($data);
        return $res;
    }

    public function jsonResponse($data)
    {
        return $this->response()->json($data);
    }
}
