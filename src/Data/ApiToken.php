<?php

namespace Edutiek\LongEssayService\Data;

/**
 * Data object for Access Tokens
 */
class ApiToken
{
    /**
     * Constructor
     *
     * @param string $value         must be 20 chars and not change by url encoding
     * @param string $ip_address    must be max 45 chars
     * @param int $expires           unix timestamp when the token expires
     */
    public function __construct(string $value, string $ip_address, int $expires)
    {
        if (strlen($value) != 32) {
            throw new \InvalidArgumentException("token length must be 20, given: $value");
        }

        if (urlencode($value) != $value) {
            throw new \InvalidArgumentException("token must not change by urlencode, given: $value");
        }

        if (strlen($ip_address) >45) {
            throw new \InvalidArgumentException("ip length must be max 45 chars, given: $ip_address");
        }

        $this->value = $value;
        $this->ip_address = $ip_address;
        $this->expires = $expires;
    }

    protected $value;
    protected $ip_address;
    protected $expires;


    /**
     * Token value
     */
    public function getValue(): string
    {
        return $this->value;
    }


    /**
     * IP Address for which the token is valid
     */
    public function getIpAddress(): string
    {
        return $this->ip_address;
    }


    /**
     * Unix timestamp when the token expires
     */
    public function getExpires(): int
    {
        return $this->expires;
    }
}