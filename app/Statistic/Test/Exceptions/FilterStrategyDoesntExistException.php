<?php


namespace App\Statistic\Test\Exceptions;


use Throwable;

class FilterStrategyDoesntExistException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}