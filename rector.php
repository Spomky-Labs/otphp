<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(SetList::DEAD_CODE);
    $containerConfigurator->import(SetList::PHP_80);
    $containerConfigurator->import(SymfonySetList::SYMFONY_52);
    $containerConfigurator->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $containerConfigurator->import(SymfonySetList::SYMFONY_52_VALIDATOR_ATTRIBUTES);
    $containerConfigurator->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);
    $containerConfigurator->import(SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES);
    $containerConfigurator->import(DoctrineSetList::DOCTRINE_CODE_QUALITY);
    $containerConfigurator->import(DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_EXCEPTION);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_SPECIFIC_METHOD);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_91);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_YIELD_DATA_PROVIDER);
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    $parameters->set(Option::IMPORT_SHORT_CLASSES, true);

    $services = $containerConfigurator->services();
    $services->set(TypedPropertyRector::class);
};
