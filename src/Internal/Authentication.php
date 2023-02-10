<?php
namespace Edutiek\LongEssayAssessmentService\Internal;

use Edutiek\LongEssayAssessmentService\Data\ApiToken;

class Authentication
{
    public const PURPOSE_DATA = 'data';
    public const PURPOSE_FILE = 'file';

    /**
     * Generate a new token
     * @param string $purpose
     * @return ApiToken
     */
    public function generateApiToken(string $purpose): ApiToken
    {
        switch ($purpose) {
            case self::PURPOSE_FILE:
                $expires = 0;           // no expiration
                break;
            case self::PURPOSE_DATA:
            default:
                $expires = time() + 3600;
                break;
        }

        // generate a random uuid like string for the token
        $value = sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535));

        return new ApiToken($value, $_SERVER['REMOTE_ADDR'], $expires);
    }

    /**
     * Check a request signature
     */
    public function checkSignature(ApiToken $token, string $user_key, string $env_key, string $signature) : bool
    {
        return (md5($user_key . $env_key . $token->getValue()) == $signature);
    }

    /**
     * Check if the client ip address is allowed for a token
     */
    public function checkRemoteAddress(ApiToken $token) : bool
    {
        return ($token->getIpAddress() == $_SERVER['REMOTE_ADDR']);
    }

    /**
     * Check if a token is still valid
     */
    public function checkTokenValid(ApiToken $token) : bool
    {
        return $token->getExpires() == 0 || $token->getExpires() >= time();
    }
}