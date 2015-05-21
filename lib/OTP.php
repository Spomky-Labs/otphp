<?php

namespace OTPHP;

use Base32\Base32;

abstract class OTP implements OTPInterface
{
    /**
     * @param int $input
     *
     * @return string The OTP at the specified input
     */
    protected function generateOTP($input)
    {
        $hash = hash_hmac($this->getDigest(), $this->intToByteString($input), $this->getDecodedSecret());
        $hmac = array();
        foreach (str_split($hash, 2) as $hex) {
            $hmac[] = hexdec($hex);
        }
        $offset = $hmac[count($hmac) - 1] & 0xF;
        $code = ($hmac[$offset + 0] & 0x7F) << 24 |
            ($hmac[$offset + 1] & 0xFF) << 16 |
            ($hmac[$offset + 2] & 0xFF) << 8 |
            ($hmac[$offset + 3] & 0xFF);

        $otp = $code % pow(10, $this->getDigits());

        return str_pad((string) $otp, $this->getDigits(), '0', STR_PAD_LEFT);
    }

    /**
     * @return bool Return true is it must be included as parameter, else false
     */
    protected function issuerAsParameter()
    {
        if (!is_null($this->getIssuer()) && $this->isIssuerIncludedAsParameter() === true) {
            return true;
        }

        return false;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    private function getParameters()
    {
        $options = array(
            'algorithm' => $this->getDigest(),
            'digits' => $this->getDigits(),
            'secret' => $this->getSecret(),
        );
        if ($this->issuerAsParameter()) {
            $options['issuer'] = $this->getIssuer();
        }

        return $options;
    }

    /**
     * @param array $options
     * @param bool  $google_compatible
     */
    protected function filterOptions(array &$options, $google_compatible)
    {
        if (true === $google_compatible) {
            foreach (array('algorithm' => 'sha1', 'period' => 30, 'digits' => 6) as $key => $default) {
                if (isset($options[$key]) && $default === $options[$key]) {
                    unset($options[$key]);
                }
            }
        }

        ksort($options);
    }

    /**
     * @param       $type
     * @param array $options
     * @param       $google_compatible
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function generateURI($type, array $options = array(), $google_compatible)
    {
        if (is_null($this->getLabel())) {
            throw new \InvalidArgumentException('No label defined.');
        }
        $options = array_merge($options, $this->getParameters());
        $this->filterOptions($options, $google_compatible);

        $params = str_replace(
            array('+', '%7E'),
            array('%20', '~'),
            http_build_query($options)
        );

        return sprintf(
            'otpauth://%s/%s?%s',
            $type,
            rawurlencode((!is_null($this->getIssuer()) ? $this->getIssuer().':' : '').$this->getLabel()),
            $params
        );
    }

    /**
     * {@inheritdoc}
     */
    public function at($input)
    {
        return $this->generateOTP($input);
    }

    /**
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private function getDecodedSecret()
    {
        $secret = Base32::decode($this->getSecret());

        return $secret;
    }

    /**
     * @param int $int
     *
     * @return string
     */
    private function intToByteString($int)
    {
        $result = array();
        while (0 !== $int) {
            $result[] = chr($int & 0xFF);
            $int >>= 8;
        }

        return str_pad(implode(array_reverse($result)), 8, "\000", STR_PAD_LEFT);
    }
}
