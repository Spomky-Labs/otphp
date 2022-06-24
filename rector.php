<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(SetList::DEAD_CODE);
    $rectorConfig->import(LevelSetList::UP_TO_PHP_81);
    $rectorConfig->import(SymfonyLevelSetList::UP_TO_SYMFONY_60);
    $rectorConfig->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $rectorConfig->import(SymfonySetList::SYMFONY_52_VALIDATOR_ATTRIBUTES);
    $rectorConfig->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);
    $rectorConfig->import(SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES);
    $rectorConfig->import(DoctrineSetList::DOCTRINE_CODE_QUALITY);
    $rectorConfig->import(DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES);
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_EXCEPTION);
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_SPECIFIC_METHOD);
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_91);
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_YIELD_DATA_PROVIDER);
    $rectorConfig->paths([__DIR__ . '/src']);
    $rectorConfig->phpVersion(PhpVersion::PHP_81);
    $rectorConfig->importNames();
    $rectorConfig->importShortClasses();

    $services = $rectorConfig->services();
    $services->set(TypedPropertyRector::class);
};
