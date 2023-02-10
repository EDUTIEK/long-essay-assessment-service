<?php

namespace Edutiek\LongEssayAssessmentService\Data;

class Alert
{
    private string $key;
    private string $message;
    private int $time;

    public function __construct(
        string $key,
        string $message,
        int $time
    )
    {
        $this->key = $key;
        $this->message = $message;
        $this->time = $time;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

}