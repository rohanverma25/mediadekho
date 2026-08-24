<?php

namespace App\Exceptions;

class RazorpayNotConfiguredException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Online payment is temporarily unavailable. Please use the proposal request option instead.');
    }
}
