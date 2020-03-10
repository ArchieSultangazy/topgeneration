<?php


namespace App\Achievement\Exceptions;


use Throwable;

/**
 * Class AchievementExistsException
 * @package App\Achievement\Exceptions
 */
class AchievementExistsException extends \Exception
{
    /**
     * AchievementExistsException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}