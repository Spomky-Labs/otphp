<?php

namespace Scheb\TwoFactorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GoogleSecretCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('scheb:two-factor:google-secret')
            ->setDescription('Generate a secret for Google Authenticator.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->getContainer()->has('scheb_two_factor.security.google_authenticator')) {
            throw new \RuntimeException('Google Authenticator two-factor authentication is not enabled.');
        }

        $googleAuthenticator = $this->getContainer()->get('scheb_two_factor.security.google_authenticator');
        $secret = $googleAuthenticator->generateSecret();

        $output->writeln('<info>Secret:</info> '.$secret);
    }
}
