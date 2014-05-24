<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor;

class TrustedTokenGenerator
{
    protected static $charspace = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    /**
     * Generate trusted computer token
     *
     * @param  integer $length
     * @return string
     */
    public static function generateToken($length)
    {
        // Symfony >= 2.2: Use SecureRandom class
        if (static::useSecureRandom()) {
            return self::generateSecureToken($length);
        }

        // Everything else: Use less secure string generator
        else {
            return self::generateFallbackToken($length);
        }
    }

    /**
     * Check if to use Symfony's SecureRandom generator
     *
     * @return boolean
     */
    protected static function useSecureRandom()
    {
        return class_exists("Symfony\Component\Security\Core\Util\SecureRandom");
    }

    /**
     * Generate a secure token with Symfony's SecureRandom generator
     *
     * @param integer $length
     * @return string
     */
    private static function generateSecureToken($length)
    {
        $generator = new \Symfony\Component\Security\Core\Util\SecureRandom();

        return substr(base64_encode($generator->nextBytes($length)), 0, $length);
    }

    /**
     * Generate a random string
     * TODO: Implement a more secure solution
     *
     * @param integer $length
     * @return string
     */
    private static function generateFallbackToken($length)
    {
        $characters = static::$charspace;
        $string = "";
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

}
