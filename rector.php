<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/bundle',
        __DIR__ . '/lib',
        __DIR__ . '/tests',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets()
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0)
    ->withAttributesSets()
    ->withComposerBased(
        twig: true,
        symfony: true,
        doctrine: true,
    )
    ->withRules(
        [
            DeclareStrictTypesRector::class,
        ]
    );
