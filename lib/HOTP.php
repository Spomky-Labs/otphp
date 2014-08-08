<?php

namespace OTPHP;

use OTPHP\OTP;

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
     * @throws \Exception If counter passed as argument is lower than the initial counter
     */
    public function at($counter)
    {
        if($counter < $this->getCounter())
        {
            throw new \Exception("Invalid counter. Must be at least ".$this->getCounter());
        }
        $this->updateCounter($counter+1);
        return parent::at($counter);
    }
}
