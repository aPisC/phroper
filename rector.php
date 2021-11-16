<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Php74\TypeAnalyzer\PropertyUnionTypeResolver;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;


return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src'
    ]);

    // Define what rule sets will be applied
    //$containerConfigurator->import(SetList::DEAD_CODE);

    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_74);

    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();

    // register a single rule
    $services->set(TypedPropertyRector::class);
    $services->set(PropertyUnionTypeResolver::class);
    $services->set(DowngradeUnionTypeDeclarationRector::class);
    
};
