<?php

namespace Fuga\Component\Exception;

class AccessDeniedHttpException extends HttpException
{

    public function __construct($message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct(403, $message, $previous, array(), $code);
    }
}
