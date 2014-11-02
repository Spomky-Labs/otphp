<?php

namespace OTPHP;

abstract class HOTP extends OTP implements HOTPInterface
{
    /**
     * {@inheritdoc}
     */
    public function getProvisioningUri()
    {
        return $this->generateURI('hotp', array('counter'=>$this->getCounter()));
    }

    /**
     * @param integer $counter The new initial counter (a positive integer)
     */
    abstract protected function updateCounter($counter);

    /**
     * {@inheritdoc}
     */
    public function verify($otp, $counter, $window = null)
    {
        if ($counter < $this->getCounter()) {
            return false;
        }

        if (!is_integer($window)) {
            $window = 0;
        }

        for ($i=$counter; $i <= $counter+abs($window); $i++) {
            if ($otp === $this->at($i)) {
                $this->updateCounter($i+1);

                return true;
            }
        }

        return false;
    }
}
