<?php

namespace Sqlr\Throttling\Exception;

use Throwable;

class RequestThrottled extends \Exception
{
    /**
     * Amount of seconds in which retrying the same action is useless
     *
     * @var int|null
     */
    protected $retryAfter = null;

    /**
     * ThrottleException constructor.
     *
     * @param int|null $retryAfter
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(int $retryAfter = null, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->retryAfter = $retryAfter;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int|null
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
