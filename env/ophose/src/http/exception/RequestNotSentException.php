<?php

namespace Ophose\Http\Exception;

class RequestNotSentException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Request not sent');
    }
}