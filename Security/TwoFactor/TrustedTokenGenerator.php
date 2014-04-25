<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor;

class TrustedTokenGenerator
{

    /**
     * Generate trusted computer token
     *
     * @param integer $length
     * @return string
     */
    public static function generateToken($length)
    {
        // Symfony >= 2.2: Use SecureRandom class
        if (class_exists("Symfony\Component\Security\Core\Util\SecureRandom")) {
            $generator = new \Symfony\Component\Security\Core\Util\SecureRandom();
            return substr(base64_encode($generator->nextBytes($length)), 0, $length);
        }

        // Everything else: Use less secure string generator
        else {
            // TODO: Implement a more secure solution
            $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
            $string = "";
            for ($p = 0; $p < $length; $p++) {
                $string .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            return $string;
        }
    }
}
