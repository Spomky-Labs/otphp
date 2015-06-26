<?php

require_once implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'vendor', 'autoload.php'));

use MyProject\TOTP;
use MyProject\HOTP;
use Base32\Base32;

$secret = trim(Base32::encode(uniqid('', true)), '=');

$totp = new TOTP();

$totp->setSecret($secret)
     ->setDigest('sha256')
     ->setDigits(8)
     ->setLabel('alice@localhost')
     ->setIssuer('MyProject')
     ->setInterval(60)
     ->setIssuerIncludedAsParameter(true);

$hotp = new HOTP();

$hotp->setSecret($secret)
    ->setDigest('sha512')
    ->setDigits(5)
    ->setLabel('alice@localhost')
    ->setIssuer('MyProject')
    ->setCounter(1000)
    ->setIssuerIncludedAsParameter(true);

printf(str_repeat('=', 50)."\r\n");
printf("Time Based On-time password\r\n");
printf(str_repeat('=', 50)."\r\n");
printf("Secret is %s\r\n", $secret);
printf("TOTP provisioning URI is %s\r\n", $totp->getProvisioningUri());
printf("Current code is %s\r\n", $totp->now());

printf(str_repeat("\r\n", 3));

printf(str_repeat('=', 50)."\r\n");
printf("Counter Based On-time password\r\n");
printf(str_repeat('=', 50)."\r\n");
printf("Secret is %s\r\n", $secret);
printf("TOTP provisioning URI is %s\r\n", $hotp->getProvisioningUri());
printf("Current code is %s\r\n", $hotp->at(1010));
