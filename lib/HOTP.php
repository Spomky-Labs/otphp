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
     * @return integer The initial counter (a positive integer)
     */
    public function getCounter()
    {
        return $this->getInitialCount();
    }

    /**
     * @param integer $counter The new initial counter (a positive integer)
     */
    abstract protected function updateCounter($counter);

    /**
     * {@inheritdoc}
     */
    public function verify($otp, $counter)
    {
        if ($counter < $this->getCounter()) {
            return false;
        }

        $result = $otp === $this->at($counter);
        
        if (true === $result) {
            $this->updateCounter($counter+1);
        }

        return $result;
    }
}
