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
        return $this->generateURI('hotp', array('counter'=>$this->getInitialCount()));
    }

    /**
     * @param integer $counter The new initial counter (a positive integer)
     */
    abstract protected function updateInitialCount($counter);
    
    /**
     * {@inheritdoc}
     * @throws \Exception If counter passed as argument is lower than the initial counter
     */
    public function at($counter)
    {
        if($counter < $this->getInitialCount())
        {
            throw new \Exception("Invalid counter. Must be at least ".$this->getInitialCount());
        }
        $this->updateInitialCount($counter+1);
        return parent::at($counter);
    }
}
