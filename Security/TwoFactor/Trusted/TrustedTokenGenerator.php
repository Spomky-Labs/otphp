<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Trusted;

class TrustedTokenGenerator
{

    /**
     * @var string
     */
    protected $charspace = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    /**
     * Generate trusted computer token
     *
     * @param  integer $length
     * @return string
     */
    public function generateToken($length)
    {
        // Symfony >= 2.2: Use SecureRandom class
        if ($this->useSecureRandom()) {
            return $this->generateSecureToken($length);
        }

        // Everything else: Use less secure string generator
        else {
            return $this->generateFallbackToken($length);
        }
    }

    /**
     * Check if to use Symfony's SecureRandom generator
     *
     * @return boolean
     */
    protected function useSecureRandom()
    {
        return class_exists("Symfony\Component\Security\Core\Util\SecureRandom");
    }

    /**
     * Generate a secure token with Symfony's SecureRandom generator
     *
     * @param  integer $length
     * @return string
     */
    private function generateSecureToken($length)
    {
        $generator = new \Symfony\Component\Security\Core\Util\SecureRandom();

        return substr(base64_encode($generator->nextBytes($length)), 0, $length);
    }

    /**
     * Generate a random string
     *
     * @param  integer $length
     * @return string
     */
    private function generateFallbackToken($length)
    {
        $string = "";
        for ($p = 0; $p < $length; $p++) {
            $string .= $this->charspace[mt_rand(0, strlen($this->charspace) - 1)];
        }

        return $string;
    }

}
