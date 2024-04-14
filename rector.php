<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $config): void {
    $config->import(SetList::DEAD_CODE);
    $config->import(LevelSetList::UP_TO_PHP_80);
    $config->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $config->import(PHPUnitSetList::PHPUNIT_100);
    $config->import(PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES);
    $config->import(PHPUnitSetList::PHPUNIT_CODE_QUALITY);
    $config->parallel();
    $config->paths([__DIR__ . '/src', __DIR__ . '/tests']);
    $config->skip([PreferPHPUnitThisCallRector::class]);
    $config->phpVersion(PhpVersion::PHP_81);
    $config->importNames();
    $config->importShortClasses();
};
